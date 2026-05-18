<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Reset Password — QueenBuilders IMS</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }
        body {
            font-family: 'Segoe UI', system-ui, sans-serif;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            background: linear-gradient(135deg, #0f172a 0%, #1e3a5f 50%, #1e40af 100%);
        }
        .card {
            background: rgba(255,255,255,0.97);
            border-radius: 16px;
            padding: 44px 40px;
            width: 100%;
            max-width: 420px;
            box-shadow: 0 24px 60px rgba(0,0,0,0.3);
        }
        .logo { text-align: center; margin-bottom: 28px; }
        .logo-icon { font-size: 40px; color: #1d4ed8; margin-bottom: 10px; }
        .logo h1 { font-size: 22px; font-weight: 800; color: #0f172a; letter-spacing: -0.5px; }
        .logo p { font-size: 13px; color: #64748b; margin-top: 4px; }
        label { display: block; font-size: 13px; font-weight: 600; color: #374151; margin-bottom: 6px; margin-top: 16px; }
        input {
            width: 100%; padding: 11px 14px; border: 1.5px solid #e2e8f0;
            border-radius: 8px; font-size: 14px; outline: none; transition: border-color .15s;
        }
        input:focus { border-color: #3b82f6; box-shadow: 0 0 0 3px rgba(59,130,246,.12); }
        .error { color: #ef4444; font-size: 12px; margin-top: 4px; }
        .btn {
            width: 100%; padding: 12px; background: linear-gradient(135deg,#1d4ed8,#3b82f6);
            color: #fff; border: none; border-radius: 8px; font-size: 15px; font-weight: 700;
            cursor: pointer; margin-top: 24px; transition: opacity .15s;
        }
        .btn:hover { opacity: 0.92; }
        .back-link { text-align: center; margin-top: 20px; font-size: 13px; }
        .back-link a { color: #3b82f6; font-weight: 600; text-decoration: none; }
        .back-link a:hover { text-decoration: underline; }
    </style>
</head>
<body>
<div class="card">
    <div class="logo">
        <div class="logo-icon"><i class="fas fa-lock"></i></div>
        <h1>Reset Password</h1>
        <p>QueenBuilders IMS</p>
    </div>

    @if($errors->any())
    <div class="p-3 mb-4 bg-red-50 border border-red-200 rounded-md text-red-800 text-sm">
        <ul class="list-disc ml-4 space-y-1">
            @foreach($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
    @endif

    <form method="POST" action="{{ route('password.store') }}">
        @csrf
        <input type="hidden" name="token" value="{{ $token }}">
        <input type="hidden" name="email" value="{{ request('email') }}">

        <label for="password">New Password</label>
        <input type="password" id="password" name="password" autocomplete="new-password" required minlength="8" placeholder="Min 8 chars, mixed case, numbers">
        @error('password')
            <p class="error">{{ $message }}</p>
        @enderror

        <label for="password_confirmation">Confirm Password</label>
        <x-password-input id="password_confirmation" name="password_confirmation" autocomplete="new-password" required placeholder="Re-enter your new password" style="width:100%; padding:11px 14px; padding-right:40px; border:1.5px solid #e2e8f0; border-radius:8px; font-size:14px; outline:none" />

        <button type="submit" class="btn">
            <i class="fas fa-key" style="margin-right:8px"></i>Reset Password
        </button>
    </form>

    <div class="back-link">
        <a href="{{ route('login') }}"><i class="fas fa-arrow-left" style="margin-right:4px"></i>Back to Login</a>
    </div>
</div>
</body>
</html>
