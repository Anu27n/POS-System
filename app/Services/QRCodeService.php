<?php

namespace App\Services;

use App\Models\Order;
use App\Models\Store;
use chillerlan\QRCode\QRCode;
use chillerlan\QRCode\QROptions;
use chillerlan\QRCode\Data\QRMatrix;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class QRCodeService
{
    /**
     * Generate QR code for a store's public URL
     */
    public function generateStoreQR(Store $store, string $url): string
    {
        $options = new QROptions([
            'outputType' => QRCode::OUTPUT_MARKUP_SVG,
            'eccLevel' => QRCode::ECC_M,
            'scale' => 10,
            'imageTransparent' => false,
            'svgViewBoxSize' => 200,
        ]);

        return (new QRCode($options))->render($url);
    }

    /**
     * Generate PNG QR code as base64 data URI for inline display
     */
    public function generatePngDataUri(string $data): string
    {
        $options = new QROptions([
            'outputType' => QRCode::OUTPUT_IMAGE_PNG,
            'eccLevel' => QRCode::ECC_M,
            'scale' => 10,
            'imageTransparent' => false,
            'imageBase64' => true,
        ]);

        return (new QRCode($options))->render($data);
    }

    /**
     * Generate a secure verification token for orders
     * Creates a cryptographically secure token that cannot be guessed
     */
    public function generateSecureToken(): string
    {
        return hash('sha256', Str::uuid() . microtime(true) . random_bytes(32));
    }

    /**
     * Generate QR code for order verification and save to storage
     * Returns the storage path of the saved QR image
     */
    public function generateAndSaveOrderQR(Order $order): string
    {
        // Create secure QR data payload
        $qrPayload = json_encode([
            'oid' => $order->id,
            'sid' => $order->store_id,
            'token' => $order->verification_code, // This is now a secure hash token
            'ts' => $order->created_at->timestamp,
        ]);

        // Generate PNG QR code as base64 data URI
        $options = new QROptions([
            'outputType' => QRCode::OUTPUT_IMAGE_PNG,
            'eccLevel' => QRCode::ECC_H,
            'scale' => 10,
            'imageTransparent' => false,
            'imageBase64' => true,
            'addQuietzone' => true,
            'quietzoneSize' => 2,
        ]);

        $qrCode = (new QRCode($options))->render($qrPayload);

        // Create directory path: qr-codes/orders/YYYY/MM/
        $directory = 'qr-codes/orders/' . $order->created_at->format('Y/m');

        // Generate unique filename - save as .txt containing the data URI
        $filename = $order->order_number . '_' . substr($order->verification_code, 0, 8) . '.txt';
        $storagePath = $directory . '/' . $filename;

        // Ensure directory exists and save the data URI
        Storage::disk('public')->put($storagePath, $qrCode);

        return $storagePath;
    }

    /**
     * Get the public URL for a stored QR code
     */
    public function getQRCodeUrl(string $storagePath): string
    {
        return Storage::disk('public')->url($storagePath);
    }

    /**
     * Check if QR code file exists
     */
    public function qrCodeExists(string $storagePath): bool
    {
        return Storage::disk('public')->exists($storagePath);
    }

    /**
     * Get QR code content for inline display (base64 data URI)
     */
    public function getQRCodeDataUri(string $storagePath): ?string
    {
        if (!$this->qrCodeExists($storagePath)) {
            return null;
        }

        // The stored file contains the base64 data URI directly
        $content = Storage::disk('public')->get($storagePath);
        
        // If it's already a data URI, return as-is
        if (str_starts_with($content, 'data:image/')) {
            return $content;
        }
        
        // Otherwise wrap as SVG (legacy support)
        return 'data:image/svg+xml;base64,' . base64_encode($content);
    }

    /**
     * Delete QR code file from storage
     */
    public function deleteQRCode(string $storagePath): bool
    {
        if ($this->qrCodeExists($storagePath)) {
            return Storage::disk('public')->delete($storagePath);
        }
        return false;
    }

    /**
     * Verify scanned QR data against order
     * Returns validation result with order if valid
     */
    public function verifyOrderQR(string $qrData, int $storeId): array
    {
        try {
            $payload = json_decode($qrData, true);

            if (!$payload || !isset($payload['oid'], $payload['sid'], $payload['token'])) {
                return [
                    'valid' => false,
                    'error' => 'Invalid QR code format',
                    'order' => null,
                ];
            }

            // Fetch the order
            $order = Order::with(['customer', 'items.product', 'store'])
                ->where('id', $payload['oid'])
                ->first();

            if (!$order) {
                return [
                    'valid' => false,
                    'error' => 'Order not found',
                    'order' => null,
                ];
            }

            // Security: Verify store ownership
            if ($order->store_id !== $storeId) {
                return [
                    'valid' => false,
                    'error' => 'This order belongs to a different store',
                    'order' => null,
                ];
            }

            // Security: Verify token matches
            if ($order->verification_code !== $payload['token']) {
                return [
                    'valid' => false,
                    'error' => 'Invalid verification token',
                    'order' => null,
                ];
            }

            // Security: Check if order is already completed
            if ($order->order_status === 'completed') {
                return [
                    'valid' => false,
                    'error' => 'This order has already been completed and cannot be scanned again',
                    'order' => $order,
                ];
            }

            // Security: Check if order is cancelled
            if ($order->order_status === 'cancelled') {
                return [
                    'valid' => false,
                    'error' => 'This order has been cancelled',
                    'order' => $order,
                ];
            }

            return [
                'valid' => true,
                'error' => null,
                'order' => $order,
            ];
        } catch (\Exception $e) {
            return [
                'valid' => false,
                'error' => 'Failed to parse QR code data',
                'order' => null,
            ];
        }
    }

    /**
     * Generate QR code as base64 data URI for inline display (PNG format)
     */
    public function generateDataURI(string $data): string
    {
        $options = new QROptions([
            'outputType' => QRCode::OUTPUT_IMAGE_PNG,
            'eccLevel' => QRCode::ECC_M,
            'scale' => 8,
            'imageBase64' => true,
            'imageTransparent' => false,
        ]);

        return (new QRCode($options))->render($data);
    }

    /**
     * Generate QR code for order and return as base64 PNG data URI
     */
    public function generateOrderQR(Order $order): string
    {
        $qrPayload = json_encode([
            'oid' => $order->id,
            'sid' => $order->store_id,
            'token' => $order->verification_code,
            'ts' => $order->created_at->timestamp,
        ]);

        return $this->generatePngDataUri($qrPayload);
    }
}
