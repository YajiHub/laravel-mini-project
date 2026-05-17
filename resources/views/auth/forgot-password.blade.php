<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Forgot Password — QueenBuilders IMS</title>
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
        .info-box {
            background: #eff6ff; border: 1px solid #bfdbfe; border-radius: 10px;
            padding: 14px 16px; margin-bottom: 24px; font-size: 13px; color: #1e40af;
        }
        label { display: block; font-size: 13px; font-weight: 600; color: #374151; margin-bottom: 6px; }
        input {
            width: 100%; padding: 11px 14px; border: 1.5px solid #e2e8f0;
            border-radius: 8px; font-size: 14px; outline: none; transition: border-color .15s;
        }
        input:focus { border-color: #3b82f6; box-shadow: 0 0 0 3px rgba(59,130,246,.12); }
        .error { color: #ef4444; font-size: 12px; margin-top: 4px; }
        .btn {
            width: 100%; padding: 12px; background: linear-gradient(135deg,#1d4ed8,#3b82f6);
            color: #fff; border: none; border-radius: 8px; font-size: 15px; font-weight: 700;
            cursor: pointer; margin-top: 20px; transition: opacity .15s;
        }
        .btn:hover { opacity: 0.92; }
        .back-link { text-align: center; margin-top: 20px; font-size: 13px; }
        .back-link a { color: #3b82f6; font-weight: 600; text-decoration: none; }
        .back-link a:hover { text-decoration: underline; }
        .alert-success {
            background: #f0fdf4; border: 1px solid #bbf7d0; border-radius: 10px;
            padding: 14px 16px; margin-bottom: 20px; font-size: 13px; color: #166534;
        }
    </style>
</head>
<body>
<div class="card">
    <div class="logo">
        <div class="logo-icon"><i class="fas fa-hard-hat"></i></div>
        <h1>QueenBuilders IMS</h1>
        <p>Reset your password</p>
    </div>

    @if(session('status'))
    <div class="alert-success">
        <i class="fas fa-check-circle" style="margin-right:6px"></i>{{ session('status') }}
    </div>
    @endif

    @if(!session('status'))
    <div class="info-box">
        <i class="fas fa-info-circle" style="margin-right:6px"></i>
        Enter your email address and we'll send you a password reset link.
    </div>

    <form method="POST" action="{{ route('password.email') }}">
        @csrf
        <div style="margin-bottom:4px">
            <label for="email">Email Address</label>
            <input type="email" id="email" name="email" value="{{ old('email') }}"
                   placeholder="you@queensbuilders.com" autocomplete="email" autofocus required>
            @error('email')
                <p class="error">{{ $message }}</p>
            @enderror
        </div>
        <button type="submit" class="btn">
            <i class="fas fa-paper-plane" style="margin-right:8px"></i>Send Reset Link
        </button>
    </form>
    @endif

    <div class="back-link">
        <a href="{{ route('login') }}"><i class="fas fa-arrow-left" style="margin-right:4px"></i>Back to Login</a>
    </div>
</div>
</body>
</html>
