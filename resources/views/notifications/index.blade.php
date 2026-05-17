@extends('layouts.app')

@section('content')
<div class="min-h-screen bg-gray-50 py-8 px-4 sm:px-6 lg:px-8">
  <div class="max-w-3xl mx-auto space-y-5">

    <div class="flex items-center justify-between">
      <div>
        <h1 class="text-2xl font-bold text-gray-900"><i class="fas fa-bell mr-2 text-blue-600"></i>Notifications</h1>
        <p class="text-sm text-gray-500 mt-1">Your system alerts and updates.</p>
      </div>
      @php $hasUnread = $notifications->where('read_at', null)->count() > 0; @endphp
      @if($hasUnread)
      <form method="POST" action="{{ route('notifications.read-all') }}">
        @csrf
        <button type="submit" class="px-4 py-2 bg-gray-100 hover:bg-gray-200 text-gray-600 font-semibold rounded-lg text-sm transition">
          <i class="fas fa-check-double mr-1"></i>Mark All as Read
        </button>
      </form>
      @endif
    </div>

    @if(session('success'))
    <div class="p-3 bg-green-50 border border-green-200 rounded-lg text-green-800 text-sm flex items-center gap-2">
      <i class="fas fa-check-circle text-green-600"></i>{{ session('success') }}
    </div>
    @endif

    <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden divide-y divide-gray-50">
      @forelse($notifications as $notif)
      <div class="flex items-start gap-4 p-5 {{ $notif->read_at ? 'opacity-60' : 'bg-blue-50' }}">
        <div class="mt-0.5 flex-shrink-0">
          @php
            $typeIcon = [
              'warning'  => ['fas fa-exclamation-triangle', 'text-yellow-500'],
              'success'  => ['fas fa-check-circle', 'text-green-500'],
              'error'    => ['fas fa-times-circle', 'text-red-500'],
              'info'     => ['fas fa-info-circle', 'text-blue-500'],
              'low_stock'=> ['fas fa-boxes', 'text-orange-500'],
            ];
            [$icon, $iconColor] = $typeIcon[$notif->type] ?? ['fas fa-bell', 'text-gray-400'];
          @endphp
          <i class="{{ $icon }} {{ $iconColor }} text-xl"></i>
        </div>
        <div class="flex-1 min-w-0">
          <p class="font-semibold text-gray-800 text-sm">{{ $notif->title }}</p>
          <p class="text-gray-600 text-sm mt-0.5">{{ $notif->message }}</p>
          <p class="text-gray-400 text-xs mt-1">{{ $notif->created_at->diffForHumans() }}</p>
        </div>
        @if(!$notif->read_at)
        <form method="POST" action="{{ route('notifications.read', $notif) }}">
          @csrf
          @method('PUT')
          <button type="submit" class="text-xs text-blue-500 hover:text-blue-700 font-medium whitespace-nowrap">Mark read</button>
        </form>
        @else
        <span class="text-xs text-gray-300 whitespace-nowrap">Read</span>
        @endif
      </div>
      @empty
      <div class="text-center py-16">
        <i class="fas fa-bell-slash text-4xl text-gray-300 mb-3 block"></i>
        <p class="text-gray-500 font-medium">No notifications</p>
        <p class="text-gray-400 text-sm mt-1">You're all caught up!</p>
      </div>
      @endforelse
    </div>

    @if($notifications->hasPages())
    <div>{{ $notifications->links() }}</div>
    @endif

  </div>
</div>
@endsection
