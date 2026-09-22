<!DOCTYPE html>
<html lang="id" x-data="guestTheme()" :class="{ 'dark': isDark }">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ config('app.name', 'PlagCheck Pro') }}</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:ital,wght@0,400;0,500;0,600;0,700;0,800;1,400&display=swap" rel="stylesheet">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="font-sans antialiased pc-app-bg min-h-screen">
    <div class="min-h-screen flex flex-col lg:flex-row">
        {{-- Left panel --}}
        <div class="hidden lg:flex lg:w-[45%] xl:w-[42%] pc-sidebar flex-col justify-between p-12 relative overflow-hidden">
            <div class="absolute inset-0 opacity-30" style="background: radial-gradient(circle at 30% 20%, rgb(79 70 229 / 0.4) 0%, transparent 50%), radial-gradient(circle at 80% 80%, rgb(13 148 136 / 0.3) 0%, transparent 50%);"></div>

            <div class="relative z-10">
                <a href="{{ route('welcome') }}" class="flex items-center gap-3">
                    <div class="pc-logo-mark">
                        <svg class="w-5 h-5 text-white" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/>
                        </svg>
                    </div>
                    <span class="text-xl font-bold text-white">PlagCheck Pro</span>
                </a>
            </div>

            <div class="relative z-10 space-y-6">
                <h1 class="text-4xl xl:text-5xl font-extrabold text-white leading-tight tracking-tight">
                    Deteksi plagiasi<br>
                    <span class="text-indigo-300">akademik terpercaya</span>
                </h1>
                <p class="text-slate-400 text-lg leading-relaxed max-w-md">
                    Multi-sumber pengecekan ke Google Scholar, Crossref, OpenAlex, dan web. Lengkap dengan parafrase cerdas dan generator jurnal.
                </p>
                <div class="flex flex-wrap gap-3">
                    <span class="px-3 py-1.5 rounded-full text-xs font-semibold bg-white/10 text-slate-300 border border-white/10">Google Scholar</span>
                    <span class="px-3 py-1.5 rounded-full text-xs font-semibold bg-white/10 text-slate-300 border border-white/10">Crossref</span>
                    <span class="px-3 py-1.5 rounded-full text-xs font-semibold bg-white/10 text-slate-300 border border-white/10">OpenAlex</span>
                </div>
            </div>

            <p class="relative z-10 text-sm text-slate-600">&copy; {{ date('Y') }} PlagCheck Pro</p>
        </div>

        {{-- Right panel --}}
        <div class="flex-1 flex flex-col justify-center items-center px-6 py-10 sm:px-10">
            <div class="lg:hidden mb-8 text-center">
                <a href="{{ route('welcome') }}" class="inline-flex items-center gap-2">
                    <div class="pc-logo-mark w-8 h-8">
                        <svg class="w-4 h-4 text-white" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/>
                        </svg>
                    </div>
                    <span class="text-lg font-bold" style="color: var(--pc-text)">PlagCheck Pro</span>
                </a>
            </div>

            <div class="w-full max-w-md">
                <div class="pc-card p-8 sm:p-10">
                    {{ $slot }}
                </div>
            </div>
        </div>
    </div>

    <script>
    function guestTheme() {
        return {
            isDark: localStorage.getItem('plagcheck_dark_mode') === 'true',
            init() {
                this.isDark ? document.documentElement.classList.add('dark') : document.documentElement.classList.remove('dark');
            }
        }
    }
    </script>
</body>
</html>
