@extends('layouts.app')
@section('content')
<div class="min-h-screen bg-gray-50 py-10 px-4 sm:px-6 lg:px-8">
  <div class="max-w-lg mx-auto">
    <div class="mb-6">
      <a href="{{ route('profile.edit') }}" class="text-gray-400 hover:text-gray-600 transition"><i class="fas fa-arrow-left text-lg"></i></a>
      <h1 class="text-2xl font-bold text-gray-900 mt-2">Setup Two-Factor Authentication</h1>
      <p class="text-sm text-gray-500 mt-1">Add an extra layer of security to your account</p>
    </div>

    @if($errors->any())
    <div class="mb-5 p-4 bg-red-50 border border-red-200 rounded-xl text-red-800 text-sm">
      <ul class="ml-4 list-disc space-y-1">
        @foreach($errors->all() as $error)
        <li>{{ $error }}</li>
        @endforeach
      </ul>
    </div>
    @endif

    <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6 space-y-6">
      <div class="text-center">
        <p class="text-sm text-gray-600 mb-4">Scan this QR code with your authenticator app (Google Authenticator, Authy, etc.)</p>
        <div class="inline-block bg-white p-3 border border-gray-200 rounded-lg">
          {!! $qrSvg !!}
        </div>
        <p class="text-xs text-gray-400 mt-3">Or enter this key manually in your app:</p>
        <code class="inline-block mt-1 px-3 py-1 bg-gray-100 rounded text-sm font-mono text-blue-700 break-all">{{ auth()->user()->mfa_secret }}</code>
      </div>

      <form method="POST" action="{{ route('profile.mfa.enable') }}" class="space-y-4">
        @csrf
        <div>
          <label class="block text-sm font-medium text-gray-700 mb-1">Verification Code</label>
          <input type="text" name="code" maxlength="6" minlength="6" required
                 class="w-full px-4 py-3 text-center text-2xl tracking-widest font-mono border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                 placeholder="000000" autocomplete="one-time-code" autofocus>
          <p class="text-xs text-gray-400 mt-1">Enter the 6-digit code from your authenticator app</p>
        </div>
        <button type="submit" class="w-full py-3 bg-blue-600 hover:bg-blue-700 text-white font-semibold rounded-lg text-sm transition">
          <i class="fas fa-shield-alt mr-2"></i>Enable Two-Factor Auth
        </button>
      </form>
    </div>
  </div>
</div>
@endsection
