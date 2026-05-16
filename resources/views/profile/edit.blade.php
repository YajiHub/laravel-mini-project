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
    </div>
</div>
@endsection
