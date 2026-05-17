<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>{{ config('app.name', 'Queen Builders IMS') }}</title>

        <!-- Fonts -->
        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />
        
        <!-- Font Awesome -->
        <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

        <!-- Scripts -->
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="font-sans antialiased bg-gray-50">
        <div class="min-h-screen">
            @include('layouts.navigation')

            <!-- Flash Messages -->
            @if(session('success'))
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 mt-4">
                <div class="p-3 bg-green-50 border border-green-200 rounded-lg text-green-800 text-sm font-medium flex items-center gap-2">
                    <i class="fas fa-check-circle text-green-500"></i>{{ session('success') }}
                </div>
            </div>
            @endif
            @if(session('error'))
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 mt-4">
                <div class="p-3 bg-red-50 border border-red-200 rounded-lg text-red-800 text-sm font-medium flex items-center gap-2">
                    <i class="fas fa-exclamation-circle text-red-500"></i>{{ session('error') }}
                </div>
            </div>
            @endif

            <!-- Page Content -->
            @yield('content')
        </div>

        <!-- Notification Polling (Real-time updates every 30s) -->
        <script>
        (function(){
            const badge = document.getElementById('notification-badge');
            let lastCount = 0;

            function fetchNotifications() {
                fetch('{{ route("notifications.poll") }}', {
                    headers: {
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                    }
                })
                .then(res => res.json())
                .then(data => {
                    const count = data.unread_count;
                    if (badge) {
                        if (count > 0) {
                            badge.textContent = count > 9 ? '9+' : count;
                            badge.classList.remove('hidden');
                            if (count > lastCount) {
                                badge.classList.add('animate-pulse');
                                setTimeout(() => badge.classList.remove('animate-pulse'), 1000);
                            }
                        } else {
                            badge.classList.add('hidden');
                        }
                        lastCount = count;
                    }
                })
                .catch(() => {});
            }

            fetchNotifications();
            setInterval(fetchNotifications, 30000);
        })();
        </script>

        <!-- Session Timeout Warning (25 min idle → warn, 30 min → logout) -->
        <div id="session-warning" style="display:none;position:fixed;bottom:24px;right:24px;z-index:9999;background:#1e3a5f;color:#fff;border-radius:12px;padding:16px 20px;box-shadow:0 8px 24px rgba(0,0,0,.3);max-width:320px;font-family:inherit">
            <div style="font-weight:700;margin-bottom:6px"><i class="fas fa-clock" style="margin-right:6px"></i>Session Expiring</div>
            <div style="font-size:13px;color:#bfdbfe">You will be logged out in <span id="session-countdown">5:00</span> due to inactivity.</div>
            <button onclick="resetSessionTimer()" style="margin-top:10px;width:100%;padding:8px;background:#3b82f6;color:#fff;border:none;border-radius:6px;font-weight:600;cursor:pointer;font-size:13px">Stay Logged In</button>
        </div>

        <script>
        (function(){
            const WARN_AT  = 25 * 60 * 1000; // 25 min
            const LOGOUT_AT = 30 * 60 * 1000; // 30 min
            let warnTimer, logoutTimer, countdownInterval;
            const warning = document.getElementById('session-warning');
            const countdown = document.getElementById('session-countdown');

            function startTimers() {
                clearTimeout(warnTimer);
                clearTimeout(logoutTimer);
                clearInterval(countdownInterval);
                warning.style.display = 'none';

                warnTimer = setTimeout(function(){
                    let secsLeft = 5 * 60;
                    warning.style.display = 'block';
                    countdownInterval = setInterval(function(){
                        secsLeft--;
                        const m = Math.floor(secsLeft / 60);
                        const s = secsLeft % 60;
                        countdown.textContent = m + ':' + (s < 10 ? '0' : '') + s;
                        if (secsLeft <= 0) clearInterval(countdownInterval);
                    }, 1000);
                }, WARN_AT);

                logoutTimer = setTimeout(function(){
                    document.querySelector('form[action*="logout"]')?.submit();
                    window.location.href = '{{ route("logout") }}';
                }, LOGOUT_AT);
            }

            window.resetSessionTimer = function() {
                startTimers();
            };

            // Reset on any user interaction
            ['mousemove','keydown','click','scroll','touchstart'].forEach(function(evt){
                document.addEventListener(evt, function(){ startTimers(); }, { passive: true });
            });

            startTimers();
        })();
        </script>
    </body>
</html>
