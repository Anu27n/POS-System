<?php

namespace App\Http\Controllers\StoreOwner;

use App\Http\Controllers\Controller;
use App\Models\Store;
use App\Services\QRCodeService;
use App\Helpers\StorageHelper;
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
        $store = auth()->user()->getEffectiveStore();
        $subscription = $store->activeSubscription()->with('plan')->first();

        return view('store-owner.settings.index', compact('store', 'subscription'));
    }

    /**
     * Update store settings
     */
    public function update(Request $request)
    {
        $store = auth()->user()->getEffectiveStore();

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'type' => 'required|string|in:grocery,clothing,department',
            'description' => 'nullable|string',
            'address' => 'nullable|string|max:500',
            'phone' => 'nullable|string|max:20',
            'email' => 'nullable|email|max:255',
            'currency' => 'nullable|string|in:USD,EUR,GBP,INR',
            'logo' => 'nullable|image|max:2048',
            'is_active' => 'nullable',
        ]);

        if ($request->hasFile('logo')) {
            // Delete old logo
            if ($store->logo) {
                StorageHelper::delete($store->logo);
            }
            $validated['logo'] = StorageHelper::store($request->file('logo'), 'stores');
        }

        // Map is_active to status
        $validated['status'] = $request->input('is_active') == '1' ? 'active' : 'inactive';
        unset($validated['is_active']);

        $store->update($validated);

        return back()->with('success', 'Store settings updated successfully.');
    }

    /**
     * Generate store QR code
     */
    public function generateQRCode()
    {
        $store = auth()->user()->getEffectiveStore();

        $url = route('store.show', $store->slug);
        
        // Generate PNG QR code as base64 data URI
        $qrDataUri = $this->qrCodeService->generatePngDataUri($url);
        
        // Extract pure base64 data from data URI and decode to binary
        $base64Data = str_replace('data:image/png;base64,', '', $qrDataUri);
        $pngBinary = base64_decode($base64Data);

        // Save QR code as PNG
        $filename = 'qrcodes/store-' . $store->id . '.png';
        Storage::disk('public')->put($filename, $pngBinary);
        StorageHelper::copyToPublic($filename); // Sync to public storage

        $store->update(['qr_code' => $filename]);

        return back()->with('success', 'QR code generated successfully.');
    }

    /**
     * Download store QR code
     */
    public function downloadQRCode()
    {
        $store = auth()->user()->getEffectiveStore();

        if (!$store->qr_code || !Storage::disk('public')->exists($store->qr_code)) {
            return back()->with('error', 'QR code not found. Please generate it first.');
        }

        return Storage::disk('public')->download($store->qr_code, 'store-qr-' . $store->slug . '.png');
    }
}
