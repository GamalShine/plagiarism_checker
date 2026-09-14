<!DOCTYPE html>
<html lang="id" x-data="{ isDark: localStorage.getItem('naskahcek_dark_mode') === 'true' }" :class="{ 'dark': isDark }" class="scroll-smooth">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="theme-color" content="#0d1b3d">
    <title>@yield('title', 'Cek Plagiarisme') — NaskahCek</title>
    <link rel="icon" type="image/png" href="{{ asset('images/naskahceklogo.png') }}">
    <link rel="shortcut icon" href="{{ asset('images/naskahceklogo.png') }}">
    <link rel="apple-touch-icon" href="{{ asset('images/naskahceklogo.png') }}">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800;900&display=swap" rel="stylesheet">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <style>[x-cloak]{display:none!important}</style>
</head>
<body class="font-sans antialiased pc-app-bg min-h-screen">
    <header class="border-b" style="border-color: var(--pc-border); background: var(--pc-surface);">
        <div class="max-w-5xl mx-auto px-4 sm:px-6 py-4 flex items-center justify-between">
            <div class="flex items-center gap-3">
                <img src="{{ asset('images/naskahceklogo.png') }}" alt="NaskahCek logo" class="h-9 w-9 rounded-lg object-cover bg-white ring-1 ring-slate-200 shadow-sm" />
                <div>
                    <h1 class="text-base font-bold leading-none" style="color: var(--pc-text);">NaskahCek</h1>
                    <p class="text-xs mt-1" style="color: var(--pc-text-muted);">Akses Tamu — Sekali Pakai</p>
                </div>
            </div>
            <button type="button" class="pc-btn-soft pc-btn-sm"
                    @click="isDark = !isDark; localStorage.setItem('naskahcek_dark_mode', isDark); isDark ? document.documentElement.classList.add('dark') : document.documentElement.classList.remove('dark')">
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
