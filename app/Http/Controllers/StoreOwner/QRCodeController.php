<?php

namespace App\Http\Controllers\StoreOwner;

use App\Http\Controllers\Controller;
use App\Services\QRCodeService;
use Illuminate\Support\Facades\Storage;

class QRCodeController extends Controller
{
    protected QRCodeService $qrCodeService;

    public function __construct(QRCodeService $qrCodeService)
    {
        $this->qrCodeService = $qrCodeService;
    }

    /**
     * Display QR code management page
     */
    public function index()
    {
        $store = auth()->user()->getEffectiveStore();

        return view('store-owner.settings.qr-code', compact('store'));
    }

    /**
     * Generate store QR code
     */
    public function generate()
    {
        $store = auth()->user()->getEffectiveStore();

        $url = route('store.show', $store->slug);
        
        // Generate PNG QR code as base64 data URI
        $qrDataUri = $this->qrCodeService->generatePngDataUri($url);
        
        // Extract pure base64 data from data URI and decode to binary
        $base64Data = str_replace('data:image/png;base64,', '', $qrDataUri);
        $pngBinary = base64_decode($base64Data);

        // Save QR code as PNG
        $filename = 'qrcodes/store-' . $store->id . '-' . time() . '.png';
        Storage::disk('public')->put($filename, $pngBinary);

        // Delete old QR code if exists
        if ($store->qr_code && Storage::disk('public')->exists($store->qr_code)) {
            Storage::disk('public')->delete($store->qr_code);
        }

        $store->update(['qr_code' => $filename]);

        return back()->with('success', 'QR code generated successfully.');
    }

    /**
     * Download store QR code
     */
    public function download()
    {
        $store = auth()->user()->getEffectiveStore();

        if (!$store->qr_code || !Storage::disk('public')->exists($store->qr_code)) {
            return back()->with('error', 'QR code not found. Please generate it first.');
        }

        return Storage::disk('public')->download($store->qr_code, 'store-qr-' . $store->slug . '.png');
    }
}
