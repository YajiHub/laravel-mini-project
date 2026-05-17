@extends('layouts.app')

@section('content')
<div class="min-h-screen bg-gray-50 py-8 px-4 sm:px-6 lg:px-8">
    <div class="max-w-2xl mx-auto space-y-6">
        <div>
            <h1 class="text-2xl font-bold text-gray-900"><i class="fas fa-user-circle mr-2 text-blue-600"></i>My Profile</h1>
            <p class="text-sm text-gray-500 mt-1">Manage your account information and password.</p>
        </div>

        {{-- Profile Info --}}
        <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
            <div class="px-6 py-4 border-b border-gray-100 bg-gray-50">
                <h2 class="text-base font-semibold text-gray-800"><i class="fas fa-id-card mr-2 text-blue-500"></i>Profile Information</h2>
            </div>
            <div class="p-6">
                @include('profile.partials.update-profile-information-form')
            </div>
        </div>

        {{-- Password --}}
        <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
            <div class="px-6 py-4 border-b border-gray-100 bg-gray-50">
                <h2 class="text-base font-semibold text-gray-800"><i class="fas fa-lock mr-2 text-yellow-500"></i>Update Password</h2>
            </div>
            <div class="p-6">
                @include('profile.partials.update-password-form')
            </div>
        </div>

        {{-- MFA / Two-Factor Auth --}}
        <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
            <div class="px-6 py-4 border-b border-gray-100 bg-gray-50">
                <h2 class="text-base font-semibold text-gray-800"><i class="fas fa-shield-alt mr-2 text-green-500"></i>Two-Factor Authentication</h2>
            </div>
            <div class="p-6">
                @if(auth()->user()->mfa_enabled)
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-sm font-medium text-green-700"><i class="fas fa-check-circle mr-1"></i>Two-factor authentication is enabled</p>
                            <p class="text-xs text-gray-500 mt-1">Your account is protected with an authenticator app.</p>
                        </div>
                        <form method="POST" action="{{ route('profile.mfa.disable') }}" onsubmit="return confirm('Disable two-factor authentication?')">
                            @csrf
                            <button type="submit" class="px-4 py-2 bg-red-100 text-red-700 rounded-lg text-sm font-medium hover:bg-red-200 transition">Disable</button>
                        </form>
                    </div>
                @else
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-sm font-medium text-gray-700">Two-factor authentication is not enabled</p>
                            <p class="text-xs text-gray-500 mt-1">Add an extra layer of security to your account.</p>
                        </div>
                        <a href="{{ route('profile.mfa') }}" class="px-4 py-2 bg-blue-600 text-white rounded-lg text-sm font-medium hover:bg-blue-700 transition">Setup</a>
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>
@endsection
