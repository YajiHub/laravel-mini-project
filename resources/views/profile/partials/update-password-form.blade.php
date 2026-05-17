@if(session('status') === 'password-updated')
<div class="mb-4 p-3 bg-green-50 border border-green-200 rounded-lg text-green-800 text-sm font-medium flex items-center gap-2">
    <i class="fas fa-check-circle text-green-600"></i> Password updated successfully.
</div>
@endif

<form method="post" action="{{ route('password.update') }}" class="space-y-4">
    @csrf
    @method('put')

    <div>
        <label for="current_password" class="block text-sm font-medium text-gray-700 mb-1">Current Password</label>
        <input type="password" id="current_password" name="current_password" autocomplete="current-password"
            class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 @error('current_password', 'updatePassword') border-red-400 @enderror">
        @error('current_password', 'updatePassword')
            <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
        @enderror
    </div>

    <div>
        <label for="update_password_password" class="block text-sm font-medium text-gray-700 mb-1">New Password <span class="text-xs text-gray-400">(min 8 chars, uppercase, number)</span></label>
        <input type="password" id="update_password_password" name="password" autocomplete="new-password"
            class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 @error('password', 'updatePassword') border-red-400 @enderror">
        @error('password', 'updatePassword')
            <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
        @enderror
    </div>

    <div>
        <label for="update_password_password_confirmation" class="block text-sm font-medium text-gray-700 mb-1">Confirm New Password</label>
        <input type="password" id="update_password_password_confirmation" name="password_confirmation" autocomplete="new-password"
            class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
    </div>

    <div>
        <button type="submit" class="px-5 py-2 bg-yellow-600 hover:bg-yellow-700 text-white font-semibold rounded-lg text-sm transition">
            <i class="fas fa-key mr-2"></i>Update Password
        </button>
    </div>
</form>
