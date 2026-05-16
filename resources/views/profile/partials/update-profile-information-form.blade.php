@if(session('status') === 'profile-updated')
<div class="mb-4 p-3 bg-green-50 border border-green-200 rounded-lg text-green-800 text-sm font-medium flex items-center gap-2">
    <i class="fas fa-check-circle text-green-600"></i> Profile updated successfully.
</div>
@endif

<form method="post" action="{{ route('profile.update') }}" class="space-y-4">
    @csrf
    @method('patch')

    <div>
        <label for="name" class="block text-sm font-medium text-gray-700 mb-1">Full Name</label>
        <input type="text" id="name" name="name" value="{{ old('name', $user->name) }}" required
            class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
        @error('name')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
    </div>

    <div>
        <label for="email" class="block text-sm font-medium text-gray-700 mb-1">Email Address</label>
        <input type="email" id="email" name="email" value="{{ old('email', $user->email) }}" required
            class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
        @error('email')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
    </div>

    <div>
        <button type="submit" class="px-5 py-2 bg-blue-600 hover:bg-blue-700 text-white font-semibold rounded-lg text-sm transition">
            <i class="fas fa-save mr-2"></i>Save Changes
        </button>
    </div>
</form>
