<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\LoginAttempt;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;
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

        // CAPTCHA check after 3 failed attempts
        if ($recentFailedAttempts >= 3) {
            if (! session()->has('captcha')) {
                $request->session()->put('captcha', [
                    'question' => $a = random_int(1, 20) . ' + ' . $b = random_int(1, 20),
                    'answer' => $a + $b,
                ]);
                return back()->withInput($request->only('email'));
            }
            $captcha = session('captcha');
            if (! $request->captcha_answer || (int) $request->captcha_answer !== $captcha['answer']) {
                $request->session()->forget('captcha');
                return back()->withErrors(['captcha_answer' => 'Incorrect answer.'])->withInput($request->only('email'));
            }
            $request->session()->forget('captcha');
        }

        // Attempt authentication
        $credentials = [
            'email' => $request->email,
            'password' => $request->password,
        ];

        if (Auth::attempt($credentials, $request->filled('remember'))) {
            $user = Auth::user();

            // Record successful login
            LoginAttempt::recordAttempt($request->email, true);

            // Check MFA
            if ($user->mfa_enabled) {
                Auth::logout();
                session(['mfa_pending' => $user->id, 'mfa_remember' => $request->filled('remember')]);
                return redirect()->route('mfa.verify');
            }

            // Update last login timestamp
            $user->update(['last_login_at' => now()]);

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

        // Create user with cashier role by default (lowest access)
        $defaultRole = \App\Models\Role::where('name', 'cashier')->first();

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'role_id' => $defaultRole?->id,
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

        $status = Password::sendResetLink($request->only('email'));

        return $status === Password::RESET_LINK_SENT
            ? back()->with('status', __($status))
            : back()->withErrors(['email' => __($status)]);
    }

    /**
     * Show reset password form
     */
    public function showResetPassword($token)
    {
        return view('auth.reset-password', ['token' => $token]);
    }

    public function showMfaVerify()
    {
        if (! session('mfa_pending')) {
            return redirect()->route('login');
        }
        return view('auth.mfa-verify');
    }

    public function verifyMfa(Request $request)
    {
        $request->validate(['code' => 'required|string|size:6']);

        $userId = session('mfa_pending');
        if (! $userId) {
            return redirect()->route('login');
        }

        $user = \App\Models\User::find($userId);
        if (! $user || ! $user->mfa_enabled || ! $user->mfa_secret) {
            session()->forget(['mfa_pending', 'mfa_remember']);
            return redirect()->route('login');
        }

        if (! \App\Http\Controllers\ProfileController::verifyTotpStatic($user->mfa_secret, $request->code)) {
            return back()->withErrors(['code' => 'Invalid verification code.']);
        }

        Auth::login($user, session('mfa_remember', false));
        session()->forget(['mfa_pending', 'mfa_remember']);

        $user->update(['last_login_at' => now()]);
        $request->session()->regenerate();

        return redirect()->intended(route('dashboard'))->with('success', 'Welcome back!');
    }

    /**
     * Handle password reset
     */
    public function reset(Request $request)
    {
        $request->validate([
            'token' => 'required',
            'email' => 'required|email|exists:users,email',
            'password' => 'required|min:8|confirmed',
        ]);

        $status = Password::reset(
            $request->only('email', 'password', 'password_confirmation', 'token'),
            function (User $user, string $password) {
                $user->forceFill([
                    'password' => Hash::make($password),
                ])->save();

                \App\Models\AuditLog::create([
                    'user_id' => $user->id,
                    'action' => 'password_reset',
                    'model_type' => 'User',
                    'model_id' => $user->id,
                    'description' => 'Password reset via email',
                    'ip_address' => request()->ip(),
                    'user_agent' => request()->userAgent(),
                ]);

                Auth::login($user);
            }
        );

        return $status === Password::PASSWORD_RESET
            ? redirect()->route('dashboard')->with('success', __($status))
            : back()->withErrors(['email' => [__($status)]]);
    }
}
