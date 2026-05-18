<!DOCTYPE html>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login — QueenBuilders IMS</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <style>body{font-family:'Inter',sans-serif;}</style>
</head>
<body class="min-h-screen bg-gradient-to-br from-blue-900 via-blue-800 to-indigo-900 flex items-center justify-center px-4">

<div class="w-full max-w-md">
    {{-- Logo/Brand --}}
    <div class="text-center mb-8">
        <div class="inline-flex items-center justify-center w-16 h-16 bg-white bg-opacity-20 rounded-2xl mb-4">
            <i class="fas fa-building text-white text-2xl"></i>
        </div>
        <h1 class="text-3xl font-bold text-white">QueenBuilders IMS</h1>
        <p class="text-blue-200 text-sm mt-1">Inventory Management System</p>
    </div>

    {{-- Card --}}
    <div class="bg-white rounded-2xl shadow-2xl p-8">
        <h2 class="text-xl font-bold text-gray-800 mb-1">Welcome back</h2>
        <p class="text-sm text-gray-500 mb-6">Sign in to your account to continue.</p>

        @if(session('info'))
        <div class="mb-4 p-3 bg-blue-50 border border-blue-200 rounded-lg text-blue-800 text-sm flex items-center gap-2">
            <i class="fas fa-info-circle text-blue-500"></i>{{ session('info') }}
        </div>
        @endif

        @if(session('success'))
        <div class="mb-4 p-3 bg-green-50 border border-green-200 rounded-lg text-green-800 text-sm flex items-center gap-2">
            <i class="fas fa-check-circle text-green-500"></i>{{ session('success') }}
        </div>
        @endif

        @if($errors->any())
        <div class="mb-4 p-3 bg-red-50 border border-red-200 rounded-lg text-red-800 text-sm flex items-center gap-2">
            <i class="fas fa-exclamation-circle text-red-500"></i>{{ $errors->first() }}
        </div>
        @endif

        <form method="POST" action="{{ route('authenticate') }}" class="space-y-4">
            @csrf
            <div>
                <label for="email" class="block text-sm font-medium text-gray-700 mb-1">Email Address</label>
                <div class="relative">
                    <i class="fas fa-envelope absolute left-3 top-1/2 -translate-y-1/2 text-gray-400 text-sm"></i>
                    <input type="email" id="email" name="email" value="{{ old('email') }}" required autofocus
                        class="w-full pl-9 pr-4 py-2.5 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 @error('email') border-red-400 @enderror"
                        placeholder="you@example.com">
                </div>
            </div>

            <div>
                <label for="password" class="block text-sm font-medium text-gray-700 mb-1">Password</label>
                <div class="relative">
                    <i class="fas fa-lock absolute left-3 top-1/2 -translate-y-1/2 text-gray-400 text-sm z-10"></i>
                    <x-password-input id="password" name="password" required
                        class="w-full pl-9 pr-10 py-2.5 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                        placeholder="••••••••" />
                </div>
            </div>

            <div class="flex items-center justify-between">
                <label class="flex items-center gap-2 text-sm text-gray-600 cursor-pointer">
                    <input type="checkbox" name="remember" class="rounded border-gray-300">
                    Remember me
                </label>
            </div>

            @if(session()->has('captcha'))
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">
                    Security Check: {{ session('captcha.question') }} = ?
                </label>
                <input type="number" name="captcha_answer" required
                    class="w-full px-4 py-2.5 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                    placeholder="Enter answer">
                @error('captcha_answer')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
            </div>
            @endif

            <button type="submit"
                class="w-full py-3 bg-blue-600 hover:bg-blue-700 text-white font-semibold rounded-lg text-sm transition focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2">
                <i class="fas fa-sign-in-alt mr-2"></i>Sign In
            </button>
        </form>
    </div>

    <p class="text-center text-blue-300 text-xs mt-6">
        &copy; {{ date('Y') }} QueenBuilders Hardware. All rights reserved.
    </p>
</div>

</body>
</html>
