<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\LoginAttempt;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;

class AuthController extends Controller
{
    /**
     * Show the login form
     */
    public function showLogin()
    {
        if (Auth::check()) {
            return redirect()->route('dashboard');
        }
        return view('auth.login');
    }

    /**
     * Handle login request
     */
    public function authenticate(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|email',
            'password' => 'required|min:6',
        ]);

        if ($validator->fails()) {
            return back()->withErrors($validator)->withInput();
        }

        // Check for too many login attempts
        $recentFailedAttempts = LoginAttempt::where('email', $request->email)
            ->where('success', false)
            ->where('created_at', '>=', now()->subMinutes(15))
            ->count();

        if ($recentFailedAttempts >= 5) {
            return back()->withErrors([
                'email' => 'Too many failed login attempts. Please try again in 15 minutes.',
            ])->withInput($request->only('email'));
        }

        // Attempt authentication
        $credentials = [
            'email' => $request->email,
            'password' => $request->password,
        ];

        if (Auth::attempt($credentials, $request->filled('remember'))) {
            // Record successful login
            LoginAttempt::recordAttempt($request->email, true);

            // Update last login timestamp
            Auth::user()->update(['last_login_at' => now()]);

            $request->session()->regenerate();
            return redirect()->intended(route('dashboard'))->with('success', 'Welcome back!');
        }

        // Record failed login
        LoginAttempt::recordAttempt($request->email, false);

        return back()->withErrors([
            'email' => 'Invalid email or password.',
        ])->withInput($request->only('email'));
    }

    /**
     * Show registration form
     */
    public function showRegister()
    {
        if (Auth::check()) {
            return redirect()->route('dashboard');
        }
        return view('auth.register');
    }

    /**
     * Handle registration
     */
    public function register(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',
            'password' => 'required|min:8|confirmed',
            'password_confirmation' => 'required|same:password',
        ]);

        if ($validator->fails()) {
            return back()->withErrors($validator)->withInput();
        }

        // Create user with staff role by default
        $staffRole = \App\Models\Role::where('name', 'staff')->first();

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'role_id' => $staffRole->id ?? null,
            'is_active' => true,
        ]);

        // Log the action
        \App\Models\AuditLog::create([
            'user_id' => null,
            'action' => 'created',
            'model_type' => 'User',
            'model_id' => $user->id,
            'new_values' => ['name' => $user->name, 'email' => $user->email],
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
            'description' => 'User self-registered',
        ]);

        Auth::login($user);
        return redirect()->route('dashboard')->with('success', 'Account created successfully!');
    }

    /**
     * Handle logout
     */
    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        return redirect(route('login'))->with('success', 'Logged out successfully');
    }

    /**
     * Show forgot password form
     */
    public function showForgotPassword()
    {
        return view('auth.forgot-password');
    }

    /**
     * Handle forgot password request
     */
    public function sendResetLink(Request $request)
    {
        $request->validate(['email' => 'required|email|exists:users,email']);

        // TODO: Send password reset email using Laravel's built-in reset functionality
        // For now, we'll use a simple approach

        return back()->with('status', 'If an account exists with this email, a reset link will be sent.');
    }

    /**
     * Show reset password form
     */
    public function showResetPassword($token)
    {
        return view('auth.reset-password', ['token' => $token]);
    }
}
