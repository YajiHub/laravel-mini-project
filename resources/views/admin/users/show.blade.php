@extends('layouts.app')

@section('content')
<div class="min-h-screen bg-gray-50 py-12 px-4 sm:px-6 lg:px-8">
    <div class="max-w-2xl mx-auto">
        <div class="bg-white shadow rounded-lg">
            <div class="px-6 py-4 border-b border-gray-200 flex items-center justify-between">
                <h2 class="text-2xl font-bold text-gray-900">
                    <i class="fas fa-user mr-2"></i>{{ $user->name }}
                </h2>
                <div class="flex gap-2">
                    <a href="{{ route('admin.users.edit', $user) }}" class="px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700 text-sm font-medium transition">
                        <i class="fas fa-edit mr-1"></i>Edit
                    </a>
                    <a href="{{ route('admin.users.index') }}" class="px-4 py-2 bg-gray-300 text-gray-700 rounded-md hover:bg-gray-400 text-sm font-medium transition">
                        <i class="fas fa-arrow-left mr-1"></i>Back
                    </a>
                </div>
            </div>

            <div class="p-6 space-y-6">
                <!-- Basic Information -->
                <div class="grid grid-cols-2 gap-6">
                    <div>
                        <p class="text-xs font-semibold text-gray-500 uppercase">Full Name</p>
                        <p class="mt-1 text-lg font-semibold text-gray-900">{{ $user->name }}</p>
                    </div>
                    <div>
                        <p class="text-xs font-semibold text-gray-500 uppercase">Email Address</p>
                        <p class="mt-1 text-lg font-semibold text-gray-900">{{ $user->email }}</p>
                    </div>
                </div>

                <hr>

                <!-- Role & Status -->
                <div class="grid grid-cols-2 gap-6">
                    <div>
                        <p class="text-xs font-semibold text-gray-500 uppercase">Role</p>
                        <p class="mt-1">
                            <span class="px-3 py-1 inline-flex text-sm leading-5 font-semibold rounded-full bg-blue-100 text-blue-800">
                                {{ $user->role->display_name }}
                            </span>
                        </p>
                    </div>
                    <div>
                        <p class="text-xs font-semibold text-gray-500 uppercase">Status</p>
                        <p class="mt-1">
                            @if($user->deactivated_at)
                                <span class="px-3 py-1 inline-flex text-sm leading-5 font-semibold rounded-full bg-red-100 text-red-800">
                                    Inactive
                                </span>
                            @else
                                <span class="px-3 py-1 inline-flex text-sm leading-5 font-semibold rounded-full bg-green-100 text-green-800">
                                    Active
                                </span>
                            @endif
                        </p>
                    </div>
                </div>

                <hr>

                <!-- Timestamps -->
                <div class="grid grid-cols-2 gap-6">
                    <div>
                        <p class="text-xs font-semibold text-gray-500 uppercase">Created At</p>
                        <p class="mt-1 text-gray-900">{{ $user->created_at->format('M d, Y H:i A') }}</p>
                    </div>
                    <div>
                        <p class="text-xs font-semibold text-gray-500 uppercase">Last Updated</p>
                        <p class="mt-1 text-gray-900">{{ $user->updated_at->format('M d, Y H:i A') }}</p>
                    </div>
                </div>

                @if($user->deactivated_at)
                    <hr>
                    <div>
                        <p class="text-xs font-semibold text-gray-500 uppercase">Deactivated At</p>
                        <p class="mt-1 text-red-600 font-semibold">{{ \Carbon\Carbon::parse($user->deactivated_at)->format('M d, Y H:i A') }}</p>
                    </div>
                @endif

                <!-- Actions -->
                @if($user->id !== auth()->id())
                    <hr>
                    <div class="flex gap-3">
                        <form method="POST" action="{{ route('admin.users.toggle-status', $user) }}" class="inline">
                            @csrf
                            @method('PATCH')
                            @if($user->deactivated_at)
                                <button type="submit" class="px-6 py-2 bg-green-600 text-white rounded-md hover:bg-green-700 font-medium transition">
                                    <i class="fas fa-check-circle mr-2"></i>Activate User
                                </button>
                            @else
                                <button type="submit" class="px-6 py-2 bg-yellow-600 text-white rounded-md hover:bg-yellow-700 font-medium transition">
                                    <i class="fas fa-times-circle mr-2"></i>Deactivate User
                                </button>
                            @endif
                        </form>
                        <form method="POST" action="{{ route('admin.users.destroy', $user) }}" onsubmit="return confirm('Are you sure? This action cannot be undone.')" class="inline">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="px-6 py-2 bg-red-600 text-white rounded-md hover:bg-red-700 font-medium transition">
                                <i class="fas fa-trash mr-2"></i>Delete User
                            </button>
                        </form>
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>
@endsection
