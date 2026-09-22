<!DOCTYPE html>
<html lang="id" x-data="appLayout()" :class="{ 'dark': isDark }" class="scroll-smooth">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'PlagCheck Pro') — Sistem Cek Plagiasi Akademik</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:ital,wght@0,400;0,500;0,600;0,700;0,800;1,400&display=swap" rel="stylesheet">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @stack('styles')
    <style>
        [x-cloak] {
            display: none !important;
        }
    </style>
</head>

<body class="font-sans antialiased pc-app-bg">

    <div id="toast-container" class="fixed top-4 right-4 z-[60] flex flex-col gap-2"></div>

    @auth
    <div x-show="sidebarOpen" x-cloak
        @click="sidebarOpen = false"
        class="fixed inset-0 z-40 bg-slate-900/60 backdrop-blur-sm lg:hidden"
        x-transition:enter="transition-opacity ease-out duration-200"
        x-transition:enter-start="opacity-0"
        x-transition:enter-end="opacity-100"
        x-transition:leave="transition-opacity ease-in duration-150"
        x-transition:leave-start="opacity-100"
        x-transition:leave-end="opacity-0"></div>

    <aside class="fixed left-0 top-0 z-50 h-full w-[17.5rem] pc-sidebar flex flex-col transition-transform duration-300 lg:translate-x-0"
        :class="sidebarOpen ? 'translate-x-0' : '-translate-x-full'">

        <div class="flex items-center gap-3 px-5 py-5 border-b border-white/10">
            <div class="pc-logo-mark">
                <svg class="w-5 h-5 text-white" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z" />
                </svg>
            </div>
            <div>
                <h1 class="text-[17px] font-bold text-white tracking-tight leading-none">PlagCheck</h1>
                <p class="text-[11px] text-slate-500 mt-0.5 font-medium">@yield('area-label', 'User Area')</p>
            </div>
            <button @click="sidebarOpen = false" class="ml-auto p-1.5 rounded-lg text-slate-400 hover:text-white hover:bg-white/10 lg:hidden">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                </svg>
            </button>
        </div>

        @yield('sidebar-nav')

        <div class="p-4 border-t border-white/10">
            <div class="flex items-center gap-3 p-2 rounded-xl bg-white/5">
                <div class="w-9 h-9 rounded-lg bg-gradient-to-br from-indigo-500 to-teal-500 flex items-center justify-center text-xs font-bold text-white shrink-0">
                    {{ strtoupper(substr(auth()->user()->name, 0, 1)) }}
                </div>
                <div class="flex-1 min-w-0">
                    <p class="text-sm font-semibold text-white truncate">{{ auth()->user()->name }}</p>
                    <p class="text-[11px] text-slate-500 truncate">{{ auth()->user()->email }}</p>
                </div>
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button type="submit" class="p-2 rounded-lg text-slate-400 hover:text-white hover:bg-white/10 transition-colors" title="Logout">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1" />
                        </svg>
                    </button>
                </form>
            </div>
        </div>
    </aside>
    @endauth

    <div class="{{ auth()->check() ? 'lg:ml-[17.5rem]' : '' }} min-h-screen flex flex-col">

        @auth
        <header class="sticky top-0 z-30 pc-header">
            <div class="pc-container flex items-center justify-between gap-4 py-4">
                <div class="flex items-center gap-3 min-w-0">
                    <button @click="sidebarOpen = true" class="pc-btn-ghost pc-btn-icon lg:hidden">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
                        </svg>
                    </button>
                    <div class="min-w-0">
                        <h2 class="pc-page-title truncate">@yield('page-title', 'Dashboard')</h2>
                        @hasSection('page-subtitle')
                        <p class="pc-page-subtitle truncate">@yield('page-subtitle')</p>
                        @endif
                    </div>
                </div>

                <div class="flex items-center gap-2 shrink-0">
                    @yield('header-actions')
                    <button @click="toggleDark()" class="pc-btn-ghost pc-btn-icon" title="Toggle tema">
                        <svg x-show="!isDark" class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20.354 15.354A9 9 0 018.646 3.646 9.003 9.003 0 0012 21a9.003 9.003 0 008.354-5.646z" />
                        </svg>
                        <svg x-show="isDark" x-cloak class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 3v1m0 16v1m9-9h-1M4 12H3m15.364 6.364l-.707-.707M6.343 6.343l-.707-.707m12.728 0l-.707.707M6.343 17.657l-.707.707M16 12a4 4 0 11-8 0 4 4 0 018 0z" />
                        </svg>
                    </button>
                </div>
            </div>
        </header>
        @endauth

        @if(session('success'))
        <div class="pc-container mt-4">
            <div x-data="{ show: true }" x-show="show" x-init="setTimeout(() => show = false, 5000)"
                class="pc-alert-success">
                <svg class="w-5 h-5 shrink-0" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" />
                </svg>
                <span>{{ session('success') }}</span>
            </div>
        </div>
        @endif

        @if($errors->any())
        <div class="pc-container mt-4">
            <div class="pc-alert-error">
                <svg class="w-5 h-5 shrink-0" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" />
                </svg>
                <ul class="list-disc list-inside">
                    @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        </div>
        @endif

        <main class="pc-main pc-fade-in">
            <div class="@yield('container', 'pc-container')">
                <div class="@yield('page-class', 'pc-page')">
                    @yield('content')
                </div>
            </div>
        </main>

        @auth
        <footer class="pc-container py-5 text-center text-xs border-t" style="border-color: var(--pc-border); color: var(--pc-text-subtle);">
            PlagCheck Pro &mdash; Sistem Deteksi Plagiasi Akademik
        </footer>
        @endauth
    </div>

    <script>
        function appLayout() {
            return {
                isDark: false,
                sidebarOpen: false,
                init: function() {
                    var stored = localStorage.getItem('plagcheck_dark_mode');
                    var serverDark = <?php
                                        $userDark = auth()->check() ? optional(optional(auth()->user())->settings)->dark_mode : null;
                                        echo json_encode((bool) ($userDark ?? false));
                                        ?>;
                    this.isDark = stored !== null ? (stored === 'true') : serverDark;
                    this.applyTheme();
                },
                toggleDark: function() {
                    this.isDark = !this.isDark;
                    localStorage.setItem('plagcheck_dark_mode', this.isDark);
                    this.applyTheme();
                    @auth
                    fetch('<?php echo route('user.settings.dark-mode'); ?>', {
                        method: 'POST',
                        headers: {
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                            'Accept': 'application/json'
                        },
                        body: JSON.stringify({
                            dark_mode: this.isDark
                        })
                    });
                    @endauth
                },
                applyTheme: function() {
                    if (this.isDark) {
                        document.documentElement.classList.add('dark');
                    } else {
                        document.documentElement.classList.remove('dark');
                    }
                }
            }
        }
    </script>
    @stack('scripts')
</body>

</html>
