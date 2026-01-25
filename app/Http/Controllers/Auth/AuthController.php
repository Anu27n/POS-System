<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
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

            // Redirect based on role
            $redirectTo = match ($user->role) {
                'admin' => route('admin.dashboard'),
                'store_owner', 'staff' => route('store-owner.dashboard'),
                default => route('home'),
            };

            \Log::info('Redirecting user', [
                'redirect_to' => $redirectTo,
                'auth_id' => auth()->id(),
            ]);

            return redirect()->intended($redirectTo);
        }

        \Log::warning('Authentication failed', ['email' => $loginField]);

        return back()->withErrors([
            'email' => 'The provided credentials do not match our records.',
        ])->onlyInput('email');
    }

    /**
     * Show the registration form
     */
    public function showRegisterForm()
    {
        return view('auth.register');
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
        ]);

        $user = User::create([
            'name' => $validated['name'],
            'phone' => $validated['phone'],
            'email' => $validated['email'] ?? null,
            'password' => Hash::make($validated['password']),
            'role' => 'customer',
        ]);

        Auth::login($user);

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
}
