<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Verify — QueenBuilders IMS</title>
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
        .logo h1 { font-size: 22px; font-weight: 800; color: #0f172a; }
        .logo p { font-size: 13px; color: #64748b; margin-top: 4px; }
        label { display: block; font-size: 13px; font-weight: 600; color: #374151; margin-bottom: 6px; }
        input {
            width: 100%; padding: 14px; border: 1.5px solid #e2e8f0;
            border-radius: 8px; font-size: 24px; text-align: center; letter-spacing: 8px;
            font-family: monospace; outline: none; transition: border-color .15s;
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
    </style>
</head>
<body>
<div class="card">
    <div class="logo">
        <div class="logo-icon"><i class="fas fa-shield-alt"></i></div>
        <h1>Two-Factor Authentication</h1>
        <p>Enter the code from your authenticator app</p>
    </div>

    @if($errors->any())
    <div class="p-3 mb-4 bg-red-50 border border-red-200 rounded-md text-red-800 text-sm">
        {{ $errors->first() }}
    </div>
    @endif

    <form method="POST" action="{{ route('mfa.verify') }}">
        @csrf
        <label for="code">6-Digit Code</label>
        <input type="text" id="code" name="code" maxlength="6" minlength="6" required
               placeholder="000000" autocomplete="one-time-code" autofocus>

        <button type="submit" class="btn">
            <i class="fas fa-check-circle" style="margin-right:8px"></i>Verify
        </button>
    </form>

    <div class="back-link">
        <a href="{{ route('login') }}"><i class="fas fa-arrow-left" style="margin-right:4px"></i>Back to Login</a>
    </div>
</div>
</body>
</html>
