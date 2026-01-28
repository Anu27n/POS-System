<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\SystemSetting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class CustomizationController extends Controller
{
    /**
     * Display customization settings
     */
    public function index()
    {
        $settings = [
            'app_name' => SystemSetting::get('app_name', 'POS System'),
            'app_phone' => SystemSetting::get('app_phone', ''),
            'app_email' => SystemSetting::get('app_email', ''),
            'app_address' => SystemSetting::get('app_address', ''),
            'app_logo' => SystemSetting::get('app_logo', ''),
            'app_favicon' => SystemSetting::get('app_favicon', ''),
            'app_tagline' => SystemSetting::get('app_tagline', ''),
            'footer_text' => SystemSetting::get('footer_text', ''),
        ];

        return view('admin.settings.customization', compact('settings'));
    }

    /**
     * Update customization settings
     */
    public function update(Request $request)
    {
        $validated = $request->validate([
            'app_name' => 'required|string|max:255',
            'app_phone' => 'nullable|string|max:50',
            'app_email' => 'nullable|email|max:255',
            'app_address' => 'nullable|string|max:500',
            'app_tagline' => 'nullable|string|max:255',
            'footer_text' => 'nullable|string|max:500',
            'app_logo' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg|max:2048',
            'app_favicon' => 'nullable|image|mimes:ico,png,jpg,gif|max:512',
        ]);

        // Handle logo upload
        if ($request->hasFile('app_logo')) {
            $oldLogo = SystemSetting::get('app_logo');
            if ($oldLogo && Storage::disk('public')->exists($oldLogo)) {
                Storage::disk('public')->delete($oldLogo);
            }
            $logoPath = $request->file('app_logo')->store('branding', 'public');
            SystemSetting::set('app_logo', $logoPath, 'customization');
        }

        // Handle favicon upload
        if ($request->hasFile('app_favicon')) {
            $oldFavicon = SystemSetting::get('app_favicon');
            if ($oldFavicon && Storage::disk('public')->exists($oldFavicon)) {
                Storage::disk('public')->delete($oldFavicon);
            }
            $faviconPath = $request->file('app_favicon')->store('branding', 'public');
            SystemSetting::set('app_favicon', $faviconPath, 'customization');
        }

        // Save text settings
        SystemSetting::set('app_name', $validated['app_name'], 'customization');
        SystemSetting::set('app_phone', $validated['app_phone'] ?? '', 'customization');
        SystemSetting::set('app_email', $validated['app_email'] ?? '', 'customization');
        SystemSetting::set('app_address', $validated['app_address'] ?? '', 'customization');
        SystemSetting::set('app_tagline', $validated['app_tagline'] ?? '', 'customization');
        SystemSetting::set('footer_text', $validated['footer_text'] ?? '', 'customization');

        return back()->with('success', 'Customization settings updated successfully.');
    }

    /**
     * Remove logo
     */
    public function removeLogo()
    {
        $logo = SystemSetting::get('app_logo');
        if ($logo && Storage::disk('public')->exists($logo)) {
            Storage::disk('public')->delete($logo);
        }
        SystemSetting::set('app_logo', '', 'customization');

        return back()->with('success', 'Logo removed successfully.');
    }

    /**
     * Remove favicon
     */
    public function removeFavicon()
    {
        $favicon = SystemSetting::get('app_favicon');
        if ($favicon && Storage::disk('public')->exists($favicon)) {
            Storage::disk('public')->delete($favicon);
        }
        SystemSetting::set('app_favicon', '', 'customization');

        return back()->with('success', 'Favicon removed successfully.');
    }
}
