<?php

namespace App\Http\Controllers\StoreOwner;

use App\Http\Controllers\Controller;
use App\Models\Store;
use App\Services\QRCodeService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class StoreSettingController extends Controller
{
    protected QRCodeService $qrCodeService;

    public function __construct(QRCodeService $qrCodeService)
    {
        $this->qrCodeService = $qrCodeService;
    }

    /**
     * Show store settings
     */
    public function index()
    {
        $store = auth()->user()->store;

        return view('store-owner.settings.index', compact('store'));
    }

    /**
     * Update store settings
     */
    public function update(Request $request)
    {
        $store = auth()->user()->store;

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'address' => 'nullable|string|max:500',
            'phone' => 'nullable|string|max:20',
            'email' => 'nullable|email|max:255',
            'logo' => 'nullable|image|max:2048',
        ]);

        if ($request->hasFile('logo')) {
            // Delete old logo
            if ($store->logo) {
                Storage::disk('public')->delete($store->logo);
            }
            $validated['logo'] = $request->file('logo')->store('stores', 'public');
        }

        $store->update($validated);

        return back()->with('success', 'Store settings updated successfully.');
    }

    /**
     * Generate store QR code
     */
    public function generateQRCode()
    {
        $store = auth()->user()->store;

        $url = route('store.show', $store->slug);
        
        // Generate PNG QR code as base64 data URI
        $qrDataUri = $this->qrCodeService->generatePngDataUri($url);
        
        // Extract pure base64 data from data URI and decode to binary
        $base64Data = str_replace('data:image/png;base64,', '', $qrDataUri);
        $pngBinary = base64_decode($base64Data);

        // Save QR code as PNG
        $filename = 'qrcodes/store-' . $store->id . '.png';
        Storage::disk('public')->put($filename, $pngBinary);

        $store->update(['qr_code' => $filename]);

        return back()->with('success', 'QR code generated successfully.');
    }

    /**
     * Download store QR code
     */
    public function downloadQRCode()
    {
        $store = auth()->user()->store;

        if (!$store->qr_code || !Storage::disk('public')->exists($store->qr_code)) {
            return back()->with('error', 'QR code not found. Please generate it first.');
        }

        return Storage::disk('public')->download($store->qr_code, 'store-qr-' . $store->slug . '.png');
    }
}
