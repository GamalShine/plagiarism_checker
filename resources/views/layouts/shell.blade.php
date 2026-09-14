<!DOCTYPE html>
<html lang="id" x-data="appLayout()" :class="{ 'dark': isDark }" class="scroll-smooth">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="theme-color" content="#0d1b3d">
    <title>@yield('title', 'NaskahCek') — Sistem Cek Plagiarisme Akademik</title>
    <link rel="icon" type="image/png" href="{{ asset('images/naskahceklogo.png') }}">
    <link rel="shortcut icon" href="{{ asset('images/naskahceklogo.png') }}">
    <link rel="apple-touch-icon" href="{{ asset('images/naskahceklogo.png') }}">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800;900&display=swap"
        rel="stylesheet">
    @php
    $assetQuery = request()->getHost() === 'nuzzle-humpback-showroom.ngrok-free.dev'
    ? '?ngrok-skip-browser-warning=true'
    : '';
    @endphp
    @if($assetQuery)
    <script>
    (async () => {
        const cssResponse = await fetch('{{ Vite::asset('resources/css/app.css') }}', {
                headers: {
                    'ngrok-skip-browser-warning': 'true'
                }
            });
        if (cssResponse.ok) {
            const style = document.createElement('style');
            style.textContent = await cssResponse.text();
            document.head.appendChild(style);
        }
    })();
    </script>
    <script type="module">
    const jsResponse = await fetch('{{ Vite::asset('resources/js/app.js') }}', {
            headers: {
                'ngrok-skip-browser-warning': 'true'
            }
        });
    if (jsResponse.ok) {
        const source = await jsResponse.text();
        const moduleUrl = URL.createObjectURL(new Blob([source], {
            type: 'text/javascript'
        }));
        await import(moduleUrl);
        URL.revokeObjectURL(moduleUrl);
    }
    </script>
    @else
    <link rel="stylesheet" href="{{ Vite::asset('resources/css/app.css') }}">
    <script type="module" src="{{ Vite::asset('resources/js/app.js') }}"></script>
    @endif
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
    <div x-show="sidebarOpen" x-cloak @click="sidebarOpen = false"
        class="fixed inset-0 z-40 bg-slate-900/60 backdrop-blur-sm lg:hidden"
        x-transition:enter="transition-opacity ease-out duration-200" x-transition:enter-start="opacity-0"
        x-transition:enter-end="opacity-100" x-transition:leave="transition-opacity ease-in duration-150"
        x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0"></div>

    <aside
        class="fixed left-0 top-0 z-50 h-full w-[17.5rem] pc-sidebar flex flex-col transition-transform duration-300 {{ request()->routeIs('user.*') ? 'lg:hidden' : 'lg:translate-x-0' }}"
        :class="sidebarOpen ? 'translate-x-0' : '-translate-x-full'">

        <div class="flex items-center gap-3 px-5 py-5 border-b border-white/10">
            <img src="{{ asset('images/naskahceklogo.png') }}" alt="NaskahCek logo"
                class="h-9 w-9 rounded-lg object-cover bg-white ring-1 ring-white/20 shadow-sm" />
            <div>
                <h1 class="text-[17px] font-bold text-white tracking-tight leading-none">NaskahCek</h1>
                <p class="text-[11px] text-slate-500 mt-0.5 font-medium">@yield('area-label', 'User Area')</p>
            </div>
            <button @click="sidebarOpen = false"
                class="ml-auto p-1.5 rounded-lg text-slate-400 hover:text-white hover:bg-white/10 lg:hidden">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                </svg>
            </button>
        </div>

        @yield('sidebar-nav')

        <div class="p-4 border-t border-white/10">
            <div class="flex items-center gap-3 p-2 rounded-xl bg-white/5">
                <div
                    class="w-9 h-9 rounded-lg bg-gradient-to-br from-indigo-500 to-teal-500 flex items-center justify-center text-xs font-bold text-white shrink-0">
                    {{ strtoupper(substr(auth()->user()->name, 0, 1)) }}
                </div>
                <div class="flex-1 min-w-0">
                    <p class="text-sm font-semibold text-white truncate">{{ auth()->user()->name }}</p>
                    <p class="text-[11px] text-slate-500 truncate">{{ auth()->user()->email }}</p>
                </div>
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button type="submit"
                        class="p-2 rounded-lg text-slate-400 hover:text-white hover:bg-white/10 transition-colors"
                        title="Logout">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1" />
                        </svg>
                    </button>
                </form>
            </div>
        </div>
    </aside>
    @endauth

    <div
        class="{{ auth()->check() && !request()->routeIs('user.*') ? 'lg:ml-[17.5rem]' : '' }} min-h-screen flex flex-col">

        @auth
        <header class="sticky top-0 z-30 pc-header">
            <div class="pc-container flex items-center justify-between gap-4 py-4">
                <div class="flex items-center gap-3 min-w-0">
                    <button @click="sidebarOpen = true" class="pc-btn-ghost pc-btn-icon lg:hidden">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M4 6h16M4 12h16M4 18h16" />
                        </svg>
                    </button>
                    <div class="min-w-0">
                        <div class="flex min-w-0 items-center gap-1.5 sm:hidden" aria-label="Breadcrumb">
                            <a href="{{ auth()->user()->isAdmin() ? route('admin.dashboard') : route('user.dashboard') }}"
                                class="shrink-0 text-slate-500 transition hover:text-blue-600" aria-label="Beranda">
                                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="m3 10 9-7 9 7v9a2 2 0 0 1-2 2h-3.5v-6h-7v6H5a2 2 0 0 1-2-2v-9Z" />
                                </svg>
                            </a>
                            <span class="text-xs text-slate-400" aria-hidden="true">›</span>
                            <span class="truncate text-sm font-semibold text-slate-700 dark:text-slate-200">@yield('page-title', 'Dashboard')</span>
                            @hasSection('page-subtitle')
                            <span class="text-xs text-slate-400" aria-hidden="true">›</span>
                            <span class="truncate text-xs text-slate-500 dark:text-slate-400">@yield('page-subtitle')</span>
                            @endif
                        </div>
                        <h2 class="pc-page-title hidden truncate sm:block">@yield('page-title', 'Dashboard')</h2>
                        @hasSection('page-subtitle')
                        <p class="pc-page-subtitle hidden truncate sm:block">@yield('page-subtitle')</p>
                        @endif
                    </div>
                </div>

                <div class="flex items-center gap-2 shrink-0">
                    @if(request()->routeIs('user.*'))
                    <nav class="hidden items-center gap-1 md:flex">
                        <a href="{{ route('user.dashboard') }}"
                            class="pc-btn-ghost pc-btn-sm uppercase tracking-[0.12em] {{ request()->routeIs('user.dashboard') ? 'bg-blue-50 text-blue-600 ring-1 ring-blue-200 dark:bg-blue-500/10 dark:text-blue-300 dark:ring-blue-500/30' : '' }}">Dashboard</a>
                        <a href="{{ route('user.plagiarism.index') }}"
                            class="pc-btn-ghost pc-btn-sm uppercase tracking-[0.12em] {{ request()->routeIs('user.plagiarism.*') ? 'bg-blue-50 text-blue-600 ring-1 ring-blue-200 dark:bg-blue-500/10 dark:text-blue-300 dark:ring-blue-500/30' : '' }}">Cek</a>
                        <a href="{{ route('user.history.index') }}"
                            class="pc-btn-ghost pc-btn-sm uppercase tracking-[0.12em] {{ request()->routeIs('user.history.*') ? 'bg-blue-50 text-blue-600 ring-1 ring-blue-200 dark:bg-blue-500/10 dark:text-blue-300 dark:ring-blue-500/30' : '' }}">History</a>
                        <a href="{{ route('user.settings.index') }}"
                            class="pc-btn-ghost pc-btn-sm uppercase tracking-[0.12em] {{ request()->routeIs('user.settings.*') ? 'bg-blue-50 text-blue-600 ring-1 ring-blue-200 dark:bg-blue-500/10 dark:text-blue-300 dark:ring-blue-500/30' : '' }}">Setting</a>
                    </nav>
                    @endif
                    @yield('header-actions')
                    <button @click="toggleDark()" class="pc-btn-ghost pc-btn-icon" title="Toggle tema">
                        <svg x-show="!isDark" class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M20.354 15.354A9 9 0 018.646 3.646 9.003 9.003 0 0012 21a9.003 9.003 0 008.354-5.646z" />
                        </svg>
                        <svg x-show="isDark" x-cloak class="w-5 h-5" fill="none" stroke="currentColor"
                            viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M12 3v1m0 16v1m9-9h-1M4 12H3m15.364 6.364l-.707-.707M6.343 6.343l-.707-.707m12.728 0l-.707.707M6.343 17.657l-.707.707M16 12a4 4 0 11-8 0 4 4 0 018 0z" />
                        </svg>
                    </button>

                    @auth
                    <div x-data="{ open: false }" class="relative">
                        <button type="button" @click="open = !open" class="pc-btn-ghost pc-btn-icon"
                            aria-label="Profil pengguna">
                            <div
                                class="flex h-8 w-8 items-center justify-center rounded-full bg-gradient-to-br from-indigo-500 to-teal-500 text-white shadow-sm">
                                <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"
                                    aria-hidden="true">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14c-4.418 0-8 2.239-8 5v1h16v-1c0-2.761-3.582-5-8-5z" />
                                </svg>
                            </div>
                        </button>

                        <div x-show="open" x-cloak @click.outside="open = false"
                            x-transition:enter="transition ease-out duration-150"
                            x-transition:enter-start="opacity-0 translate-y-1"
                            x-transition:enter-end="opacity-100 translate-y-0"
                            x-transition:leave="transition ease-in duration-100"
                            x-transition:leave-start="opacity-100 translate-y-0"
                            x-transition:leave-end="opacity-0 translate-y-1"
                            class="absolute right-0 top-12 z-50 w-56 overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-xl dark:border-slate-700 dark:bg-slate-900">
                            <div class="border-b border-slate-200 px-4 py-3 dark:border-slate-700">
                                <p class="text-sm font-semibold text-slate-900 dark:text-white">
                                    {{ auth()->user()->name }}</p>
                                <p class="mt-1 text-xs text-slate-500 dark:text-slate-400">{{ auth()->user()->email }}
                                </p>
                            </div>

                            @php
                            $profileSettingsRoute = auth()->user()->isAdmin() && request()->routeIs('admin.*') ?
                            route('admin.settings.index') : route('user.settings.index');
                            @endphp

                            <div class="p-2">
                                <a href="{{ $profileSettingsRoute }}"
                                    class="flex items-center gap-2 rounded-xl px-3 py-2 text-sm text-slate-700 transition hover:bg-slate-100 dark:text-slate-200 dark:hover:bg-slate-800">Profil</a>
                                <a href="{{ $profileSettingsRoute }}"
                                    class="flex items-center gap-2 rounded-xl px-3 py-2 text-sm text-slate-700 transition hover:bg-slate-100 dark:text-slate-200 dark:hover:bg-slate-800">Pengaturan</a>
                                <form method="POST" action="{{ route('logout') }}">
                                    @csrf
                                    <button type="submit"
                                        class="flex w-full items-center gap-2 rounded-xl px-3 py-2 text-left text-sm text-red-600 transition hover:bg-red-50 dark:text-red-400 dark:hover:bg-red-500/10">Keluar</button>
                                </form>
                            </div>
                        </div>
                    </div>
                    @endauth
                </div>
            </div>
        </header>
        @endauth

        @if(session('success'))
        <div id="flash-success" data-message="{{ e(session('success')) }}" style="display:none"></div>
        @endif

        @if($errors->any())
        <div id="flash-errors" style="display:none">
            @foreach($errors->all() as $error)
            <span data-message="{{ e($error) }}"></span>
            @endforeach
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
        <footer class="pc-container py-5 text-center text-xs border-t"
            style="border-color: var(--pc-border); color: var(--pc-text-subtle);">
            NaskahCek &mdash; Sistem Deteksi Plagiarisme Akademik
        </footer>
        @endauth
    </div>

    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script>
    function appLayout() {
        return {
            isDark: false,
            sidebarOpen: false,
            init: function() {
                var stored = localStorage.getItem('naskahcek_dark_mode');
                var serverDark = <?php
                                        $userDark = auth()->check() ? optional(optional(auth()->user())->settings)->dark_mode : null;
                                        echo json_encode((bool) ($userDark ?? false));
                                        ?>;
                this.isDark = stored !== null ? (stored === 'true') : serverDark;
                this.applyTheme();
            },
            toggleDark: function() {
                this.isDark = !this.isDark;
                localStorage.setItem('naskahcek_dark_mode', this.isDark);
                this.applyTheme();
                @auth
                var darkModeUrl =
                    <?php echo json_encode(auth()->user()->isAdmin() && request()->routeIs('admin.*') ? route('admin.settings.dark-mode') : route('user.settings.dark-mode')); ?>;
                fetch(darkModeUrl, {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                        'Accept': 'application/json',
                        'Content-Type': 'application/json'
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

    document.addEventListener('DOMContentLoaded', function() {
        document.querySelectorAll('.delete-confirm-form').forEach(function(form) {
            form.addEventListener('submit', function(event) {
                event.preventDefault();

                Swal.fire({
                    icon: 'warning',
                    title: form.dataset.confirmTitle || 'Yakin ingin melanjutkan?',
                    text: form.dataset.confirmText ||
                        'Tindakan ini tidak bisa dibatalkan.',
                    showCancelButton: true,
                    confirmButtonColor: '#dc2626',
                    cancelButtonColor: '#64748b',
                    confirmButtonText: form.dataset.confirmButton || 'Ya, lanjutkan',
                    cancelButtonText: 'Batal'
                }).then(function(result) {
                    if (result.isConfirmed) {
                        form.submit();
                    }
                });
            });
        });

        const successNode = document.getElementById('flash-success');
        if (successNode && successNode.dataset.message) {
            Swal.fire({
                icon: 'success',
                title: 'Berhasil',
                text: successNode.dataset.message,
                toast: true,
                position: 'top-end',
                timer: 2600,
                timerProgressBar: true,
                showConfirmButton: false,
                background: document.documentElement.classList.contains('dark') ? '#111827' : '#ffffff',
                color: document.documentElement.classList.contains('dark') ? '#e5e7eb' : '#0f172a'
            });
        }

        const errorNodes = document.querySelectorAll('#flash-errors [data-message]');
        const errorMessages = Array.from(errorNodes).map(node => node.dataset.message).filter(Boolean);
        if (errorMessages.length) {
            Swal.fire({
                icon: 'error',
                title: 'Perhatian',
                html: errorMessages.map(msg => '<div style="text-align:left; margin: 4px 0;">• ' + msg +
                    '</div>').join(''),
                confirmButtonText: 'OK',
                background: document.documentElement.classList.contains('dark') ? '#111827' : '#ffffff',
                color: document.documentElement.classList.contains('dark') ? '#e5e7eb' : '#0f172a'
            });
        }
    });
    </script>
    @stack('scripts')
</body>

</html>
