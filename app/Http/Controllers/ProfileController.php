<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;
use SimpleSoftwareIO\QrCode\Facades\QrCode;

class ProfileController extends Controller
{
    public function edit(Request $request)
    {
        return view('profile.edit', ['user' => $request->user()]);
    }

    public function update(Request $request)
    {
        $validated = $request->validate([
            'name'  => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', 'unique:users,email,' . $request->user()->id],
        ]);

        $request->user()->fill($validated)->save();

        return redirect()->route('profile.edit')->with('status', 'profile-updated');
    }

    public function updatePassword(Request $request)
    {
        $validated = $request->validateWithBag('updatePassword', [
            'current_password' => ['required', 'current_password'],
            'password'         => ['required', Password::min(8)->mixedCase()->numbers(), 'confirmed'],
        ]);

        $request->user()->update([
            'password' => Hash::make($validated['password']),
        ]);

        return redirect()->route('profile.edit')->with('status', 'password-updated');
    }

    public function setupMfa()
    {
        $user = auth()->user();

        if (! $user->mfa_secret) {
            $user->mfa_secret = $this->generateTotpSecret();
            $user->save();
        }

        $qrUrl = $this->getTotpQrUrl($user->email, $user->mfa_secret);
        $qrSvg = QrCode::format('svg')->size(200)->generate($qrUrl);

        return view('profile.mfa-setup', compact('qrUrl', 'qrSvg'));
    }

    public function enableMfa(Request $request)
    {
        $user = auth()->user();
        $request->validate(['code' => 'required|string|size:6']);

        if ($this->verifyTotp($user->mfa_secret, $request->code)) {
            $user->update(['mfa_enabled' => true]);

            \App\Models\AuditLog::create([
                'user_id' => $user->id,
                'action' => 'mfa_enabled',
                'model_type' => 'User',
                'model_id' => $user->id,
                'description' => 'Multi-factor authentication enabled',
                'ip_address' => $request->ip(),
                'user_agent' => $request->userAgent(),
            ]);

            return redirect()->route('profile.edit')->with('status', 'mfa-enabled');
        }

        return back()->withErrors(['code' => 'Invalid verification code.']);
    }

    public function disableMfa(Request $request)
    {
        $user = auth()->user();
        $request->validate(['password' => 'required|current_password']);

        $user->update(['mfa_enabled' => false, 'mfa_secret' => null]);

        \App\Models\AuditLog::create([
            'user_id' => $user->id,
            'action' => 'mfa_disabled',
            'model_type' => 'User',
            'model_id' => $user->id,
            'description' => 'Multi-factor authentication disabled',
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
        ]);

        return redirect()->route('profile.edit')->with('status', 'mfa-disabled');
    }

    private function generateTotpSecret(): string
    {
        $chars = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ234567';
        $secret = '';
        for ($i = 0; $i < 32; $i++) {
            $secret .= $chars[random_int(0, 31)];
        }
        return $secret;
    }

    private function getTotpQrUrl(string $email, string $secret): string
    {
        $issuer = urlencode(config('app.name', 'QueenBuilders-IMS'));
        return "otpauth://totp/{$issuer}:" . urlencode($email) . "?secret={$secret}&issuer={$issuer}&algorithm=SHA1&digits=6&period=30";
    }

    private function verifyTotp(string $secret, string $code): bool
    {
        for ($i = -1; $i <= 1; $i++) {
            if (self::computeTotp($secret, (int) (floor(time() / 30) + $i)) === $code) {
                return true;
            }
        }
        return false;
    }

    public static function verifyTotpStatic(string $secret, string $code): bool
    {
        return (new self())->verifyTotp($secret, $code);
    }

    private function computeTotp(string $secret, int $timeSlice): string
    {
        $secret = $this->base32Decode($secret);
        $time = pack('J', $timeSlice);
        $hash = hash_hmac('sha1', $time, $secret, true);
        $offset = ord($hash[strlen($hash) - 1]) & 0x0F;
        $binary = unpack('N', substr($hash, $offset, 4))[1] & 0x7FFFFFFF;
        return str_pad((string) ($binary % 1000000), 6, '0', STR_PAD_LEFT);
    }

    private function base32Decode(string $secret): string
    {
        $alphabet = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ234567';
        $secret = rtrim(strtoupper($secret), '=');
        $binary = '';
        foreach (str_split($secret) as $char) {
            $pos = strpos($alphabet, $char);
            if ($pos === false) continue;
            $binary .= str_pad(decbin($pos), 5, '0', STR_PAD_LEFT);
        }
        $result = '';
        foreach (str_split($binary, 8) as $byte) {
            if (strlen($byte) < 8) break;
            $result .= chr(bindec($byte));
        }
        return $result;
    }
}
