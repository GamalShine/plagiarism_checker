<!DOCTYPE html>
<html lang="id" x-data="{ isDark: localStorage.getItem('plagcheck_dark_mode') === 'true' }" :class="{ 'dark': isDark }" class="scroll-smooth">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Cek Plagiasi') — PlagCheck Pro</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:ital,wght@0,400;0,500;0,600;0,700;0,800;1,400&display=swap" rel="stylesheet">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <style>[x-cloak]{display:none!important}</style>
</head>
<body class="font-sans antialiased pc-app-bg min-h-screen">
    <header class="border-b" style="border-color: var(--pc-border); background: var(--pc-surface);">
        <div class="max-w-5xl mx-auto px-4 sm:px-6 py-4 flex items-center justify-between">
            <div class="flex items-center gap-3">
                <div class="pc-logo-mark w-9 h-9">
                    <svg class="w-4 h-4 text-white" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/>
                    </svg>
                </div>
                <div>
                    <h1 class="text-base font-bold leading-none" style="color: var(--pc-text);">PlagCheck Pro</h1>
                    <p class="text-xs mt-1" style="color: var(--pc-text-muted);">Akses Tamu — Sekali Pakai</p>
                </div>
            </div>
            <button type="button" class="pc-btn-soft pc-btn-sm"
                    @click="isDark = !isDark; localStorage.setItem('plagcheck_dark_mode', isDark); isDark ? document.documentElement.classList.add('dark') : document.documentElement.classList.remove('dark')">
                Tema
            </button>
        </div>
    </header>

    <main class="max-w-5xl mx-auto px-4 sm:px-6 py-8">
        <div class="mb-6">
            <h2 class="text-2xl font-bold" style="color: var(--pc-text);">@yield('page-title')</h2>
            @hasSection('page-subtitle')
                <p class="text-sm mt-1" style="color: var(--pc-text-muted);">@yield('page-subtitle')</p>
            @endif
        </div>

        @if(session('success'))
            <div class="mb-4 p-3 rounded-xl text-sm bg-emerald-50 text-emerald-700 border border-emerald-200">{{ session('success') }}</div>
        @endif

        @yield('content')
    </main>
    @stack('scripts')
</body>
</html>
