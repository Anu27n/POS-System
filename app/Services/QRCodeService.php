<?php

namespace App\Services;

use App\Models\Order;
use App\Models\Store;
use chillerlan\QRCode\QRCode;
use chillerlan\QRCode\QROptions;

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
     * Generate QR code for order verification
     */
    public function generateOrderQR(Order $order): string
    {
        $options = new QROptions([
            'outputType' => QRCode::OUTPUT_MARKUP_SVG,
            'eccLevel' => QRCode::ECC_H,
            'scale' => 8,
            'imageTransparent' => false,
        ]);

        // QR code contains the verification code
        $data = json_encode([
            'order_id' => $order->id,
            'verification_code' => $order->verification_code,
            'store_id' => $order->store_id,
        ]);

        return (new QRCode($options))->render($data);
    }

    /**
     * Generate QR code as base64 data URI for inline display
     */
    public function generateDataURI(string $data): string
    {
        $options = new QROptions([
            'outputType' => QRCode::OUTPUT_MARKUP_SVG,
            'eccLevel' => QRCode::ECC_M,
            'scale' => 6,
        ]);

        return (new QRCode($options))->render($data);
    }
}
