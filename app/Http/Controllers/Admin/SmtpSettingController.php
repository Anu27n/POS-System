<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\SystemSetting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;

class SmtpSettingController extends Controller
{
    /**
     * Display SMTP settings
     */
    public function index()
    {
        $settings = [
            'mail_mailer' => SystemSetting::get('mail_mailer', 'smtp'),
            'mail_host' => SystemSetting::get('mail_host', ''),
            'mail_port' => SystemSetting::get('mail_port', '587'),
            'mail_username' => SystemSetting::get('mail_username', ''),
            'mail_password' => SystemSetting::get('mail_password', ''),
            'mail_encryption' => SystemSetting::get('mail_encryption', 'tls'),
            'mail_from_address' => SystemSetting::get('mail_from_address', ''),
            'mail_from_name' => SystemSetting::get('mail_from_name', config('app.name')),
            'notifications_enabled' => SystemSetting::get('notifications_enabled', false),
            'notify_new_order' => SystemSetting::get('notify_new_order', true),
            'notify_order_status' => SystemSetting::get('notify_order_status', true),
        ];

        return view('admin.settings.smtp', compact('settings'));
    }

    /**
     * Update SMTP settings
     */
    public function update(Request $request)
    {
        $validated = $request->validate([
            'mail_mailer' => 'required|in:smtp,sendmail,log',
            'mail_host' => 'nullable|string|max:255',
            'mail_port' => 'nullable|string|max:10',
            'mail_username' => 'nullable|string|max:255',
            'mail_password' => 'nullable|string|max:255',
            'mail_encryption' => 'nullable|in:tls,ssl,null',
            'mail_from_address' => 'nullable|email|max:255',
            'mail_from_name' => 'nullable|string|max:255',
        ]);

        // Save settings to database
        foreach ($validated as $key => $value) {
            SystemSetting::set($key, $value ?? '');
        }

        // Save notification preferences
        SystemSetting::set('notifications_enabled', $request->boolean('notifications_enabled'));
        SystemSetting::set('notify_new_order', $request->boolean('notify_new_order'));
        SystemSetting::set('notify_order_status', $request->boolean('notify_order_status'));

        // Update runtime config
        config([
            'mail.default' => $validated['mail_mailer'],
            'mail.mailers.smtp.host' => $validated['mail_host'],
            'mail.mailers.smtp.port' => $validated['mail_port'],
            'mail.mailers.smtp.username' => $validated['mail_username'],
            'mail.mailers.smtp.password' => $validated['mail_password'],
            'mail.mailers.smtp.encryption' => $validated['mail_encryption'] === 'null' ? null : $validated['mail_encryption'],
            'mail.from.address' => $validated['mail_from_address'],
            'mail.from.name' => $validated['mail_from_name'],
        ]);

        // Clear config cache
        try {
            Artisan::call('config:clear');
        } catch (\Exception $e) {
            // Ignore if command fails
        }

        return back()->with('success', 'SMTP settings updated successfully.');
    }

    /**
     * Test SMTP configuration
     */
    public function test(Request $request)
    {
        $request->validate([
            'test_email' => 'required|email',
        ]);

        try {
            // Apply settings temporarily
            $this->applySmtpSettings();

            // Send test email
            \Mail::raw('This is a test email from ' . config('app.name') . '. If you received this, your SMTP settings are working correctly!', function ($message) use ($request) {
                $message->to($request->test_email)
                    ->subject('Test Email from ' . config('app.name'));
            });

            return response()->json([
                'success' => true,
                'message' => 'Test email sent successfully! Check your inbox.',
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to send test email: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Apply SMTP settings from database to config
     */
    private function applySmtpSettings()
    {
        config([
            'mail.default' => SystemSetting::get('mail_mailer', 'smtp'),
            'mail.mailers.smtp.host' => SystemSetting::get('mail_host'),
            'mail.mailers.smtp.port' => SystemSetting::get('mail_port'),
            'mail.mailers.smtp.username' => SystemSetting::get('mail_username'),
            'mail.mailers.smtp.password' => SystemSetting::get('mail_password'),
            'mail.mailers.smtp.encryption' => SystemSetting::get('mail_encryption') === 'null' ? null : SystemSetting::get('mail_encryption'),
            'mail.from.address' => SystemSetting::get('mail_from_address'),
            'mail.from.name' => SystemSetting::get('mail_from_name'),
        ]);
    }
}
