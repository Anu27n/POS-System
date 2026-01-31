<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Mail\PasswordResetMail;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use Illuminate\Validation\Rules\Password;

class AuthController extends Controller
{
    /**
     * Show the login form
     */
    public function showLoginForm()
    {
        return view('auth.login');
    }

    /**
     * Handle login request
     */
    public function login(Request $request)
    {
        \Log::info('Login attempt', [
            'email' => $request->input('email'),
            'has_password' => !empty($request->input('password')),
            'ip' => $request->ip(),
        ]);

        $loginField = $request->input('email');

        // Check if login field is email or phone
        $fieldType = filter_var($loginField, FILTER_VALIDATE_EMAIL) ? 'email' : 'phone';

        $request->validate([
            'email' => 'required|string',
            'password' => 'required',
        ]);

        $credentials = [
            $fieldType => $loginField,
            'password' => $request->password
        ];

        \Log::info('Attempting authentication', [
            'field_type' => $fieldType,
            'credentials_key' => $fieldType,
        ]);

        // Manual authentication to avoid session regeneration issues in Codespaces
        $user = User::where($fieldType, $loginField)->first();

        if ($user && Hash::check($request->password, $user->password)) {
            if (!$user->is_active) {
                \Log::warning('User account deactivated', ['user_id' => $user->id]);
                return back()->withErrors([
                    'email' => 'Your account has been deactivated.',
                ]);
            }

            // Store auth in the EXISTING session directly - don't migrate session
            $sessionKey = 'login_web_' . sha1('Illuminate\Auth\SessionGuard');
            $request->session()->put($sessionKey, $user->id);
            $request->session()->save();

            // Set the user in the guard manually
            Auth::setUser($user);

            \Log::info('Authentication successful', [
                'user_id' => $user->id,
                'role' => $user->role,
                'is_active' => $user->is_active,
                'session_id' => $request->session()->getId(),
                'session_key' => $sessionKey,
                'auth_id' => auth()->id(),
            ]);

            // Check if user is coming from pricing flow with a plan
            $planId = $request->input('plan');
            $redirectTo = $request->input('redirect_to');

            if ($redirectTo === 'pricing' && $planId) {
                // If user is a store owner, redirect to plan checkout
                if ($user->isStoreOwner() && $user->store) {
                    return redirect()->route('pricing.checkout', ['plan' => $planId]);
                }
                // If user is a store owner without a store, redirect to dashboard to create store first
                if ($user->isStoreOwner() && !$user->store) {
                    return redirect()->route('store-owner.dashboard')
                        ->with('info', 'Please complete your store setup first, then you can subscribe to a plan.');
                }
                // If user is not a store owner but came from pricing, just go to pricing page
                return redirect()->route('pricing')
                    ->with('info', 'To subscribe to a plan, you need to register as a store owner.');
            }

            // Default redirect based on role
            $defaultRedirect = match ($user->role) {
                'admin' => route('admin.dashboard'),
                'store_owner', 'staff' => route('store-owner.dashboard'),
                default => route('home'),
            };

            \Log::info('Redirecting user', [
                'redirect_to' => $defaultRedirect,
                'auth_id' => auth()->id(),
            ]);

            return redirect()->intended($defaultRedirect);
        }

        \Log::warning('Authentication failed', ['email' => $loginField]);

        return back()->withErrors([
            'email' => 'The provided credentials do not match our records.',
        ])->onlyInput('email');
    }

    /**
     * Show the registration form
     */
    public function showRegisterForm(Request $request)
    {
        $registerAs = $request->get('register_as', 'customer');
        $plan = $request->get('plan');
        
        return view('auth.register', [
            'registerAs' => $registerAs,
            'plan' => $plan,
        ]);
    }

    /**
     * Handle registration request
     */
    public function register(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'phone' => 'required|string|max:20|unique:users',
            'email' => 'nullable|string|email|max:255|unique:users',
            'password' => ['required', 'confirmed', Password::defaults()],
            'register_as' => 'nullable|in:customer,store_owner',
            'plan' => 'nullable|integer|exists:plans,id',
        ]);

        // Determine the role based on register_as parameter
        $role = ($request->input('register_as') === 'store_owner') ? 'store_owner' : 'customer';

        $user = User::create([
            'name' => $validated['name'],
            'phone' => $validated['phone'],
            'email' => $validated['email'] ?? null,
            'password' => Hash::make($validated['password']),
            'role' => $role,
        ]);

        Auth::login($user);

        // If registering as store owner with a plan, redirect to pricing checkout
        if ($role === 'store_owner' && $request->filled('plan')) {
            return redirect()->route('pricing.checkout', ['plan' => $request->input('plan')]);
        }

        // Redirect based on role
        if ($role === 'store_owner') {
            return redirect()->route('pricing');
        }

        return redirect()->route('home');
    }

    /**
     * Handle logout request
     */
    public function logout(Request $request)
    {
        Auth::logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login');
    }

    /**
     * Show forgot password form
     */
    public function showForgotPasswordForm()
    {
        return view('auth.forgot-password');
    }

    /**
     * Send password reset link
     */
    public function sendResetLink(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
        ]);

        $user = User::where('email', $request->email)->first();

        if (!$user) {
            return back()->withErrors(['email' => 'We could not find an account with that email address.']);
        }

        // Generate token
        $token = Str::random(64);

        // Delete any existing tokens for this email
        DB::table('password_reset_tokens')->where('email', $request->email)->delete();

        // Insert new token
        DB::table('password_reset_tokens')->insert([
            'email' => $request->email,
            'token' => Hash::make($token),
            'created_at' => now(),
        ]);

        // Generate reset URL
        $resetUrl = route('password.reset', ['token' => $token, 'email' => $request->email]);

        // Send email
        try {
            Mail::to($request->email)->send(new PasswordResetMail($resetUrl, $user->name));
            return back()->with('status', 'We have emailed your password reset link!');
        } catch (\Exception $e) {
            \Log::error('Password reset email failed: ' . $e->getMessage());
            return back()->withErrors(['email' => 'Failed to send reset email. Please try again later.']);
        }
    }

    /**
     * Show reset password form
     */
    public function showResetPasswordForm(Request $request, $token)
    {
        return view('auth.reset-password', [
            'token' => $token,
            'email' => $request->email,
        ]);
    }

    /**
     * Reset password
     */
    public function resetPassword(Request $request)
    {
        $request->validate([
            'token' => 'required',
            'email' => 'required|email',
            'password' => ['required', 'confirmed', Password::defaults()],
        ]);

        // Check token
        $record = DB::table('password_reset_tokens')
            ->where('email', $request->email)
            ->first();

        if (!$record) {
            return back()->withErrors(['email' => 'Invalid or expired reset token.']);
        }

        if (!Hash::check($request->token, $record->token)) {
            return back()->withErrors(['email' => 'Invalid or expired reset token.']);
        }

        // Check if token is expired (60 minutes)
        if (now()->diffInMinutes($record->created_at) > 60) {
            DB::table('password_reset_tokens')->where('email', $request->email)->delete();
            return back()->withErrors(['email' => 'This reset token has expired. Please request a new one.']);
        }

        // Update password
        $user = User::where('email', $request->email)->first();
        if (!$user) {
            return back()->withErrors(['email' => 'User not found.']);
        }

        $user->password = Hash::make($request->password);
        $user->save();

        // Delete token
        DB::table('password_reset_tokens')->where('email', $request->email)->delete();

        return redirect()->route('login')->with('status', 'Your password has been reset! You can now login with your new password.');
    }
}
