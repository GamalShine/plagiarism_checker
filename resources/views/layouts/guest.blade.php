<!DOCTYPE html>
<html lang="id" x-data="guestTheme()" :class="{ 'dark': isDark }">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="theme-color" content="#0d1b3d">
    <title>{{ config('app.name', 'NaskahCek') }}</title>
    <link rel="icon" type="image/png" href="{{ asset('images/naskahceklogo.png') }}">
    <link rel="shortcut icon" href="{{ asset('images/naskahceklogo.png') }}">
    <link rel="apple-touch-icon" href="{{ asset('images/naskahceklogo.png') }}">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&display=swap" rel="stylesheet">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="font-sans antialiased pc-app-bg min-h-screen">
    <div class="min-h-screen flex flex-col lg:flex-row">
        {{-- Left panel --}}
        <div class="hidden lg:flex lg:w-[45%] xl:w-[42%] pc-sidebar flex-col justify-between p-12 relative overflow-hidden">
            <div class="absolute inset-0 opacity-30" style="background: radial-gradient(circle at 30% 20%, rgb(79 70 229 / 0.4) 0%, transparent 50%), radial-gradient(circle at 80% 80%, rgb(13 148 136 / 0.3) 0%, transparent 50%);"></div>

            <div class="relative z-10">
                <a href="{{ route('welcome') }}" class="flex items-center gap-3">
                    <img src="{{ asset('images/naskahceklogo.png') }}" alt="NaskahCek logo" class="h-10 w-10 rounded-xl object-cover bg-white/90 ring-1 ring-white/20 shadow-sm" />
                    <span class="text-xl font-bold text-white">NaskahCek</span>
                </a>
            </div>

            <div class="relative z-10 space-y-6">
                <h1 class="text-4xl xl:text-5xl font-extrabold text-white leading-tight tracking-tight">
                    Deteksi plagiarisme<br>
                    <span class="text-blue-300">akademik terpercaya</span>
                </h1>
                <p class="text-slate-400 text-lg leading-relaxed max-w-md">
                    Periksa keaslian naskah akademik dengan cepat, akurat, dan aman. Dilengkapi fitur analisis similarity mendalam dan bantuan penyempurnaan teks.
                </p>
                <div class="flex flex-wrap gap-3">
                    <span class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-full text-xs font-semibold bg-white/10 text-slate-200 border border-white/10">
                        <span class="h-1.5 w-1.5 rounded-full bg-blue-400"></span> Pengecekan Cepat
                    </span>
                    <span class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-full text-xs font-semibold bg-white/10 text-slate-200 border border-white/10">
                        <span class="h-1.5 w-1.5 rounded-full bg-emerald-400"></span> Hasil Akurat
                    </span>
                    <span class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-full text-xs font-semibold bg-white/10 text-slate-200 border border-white/10">
                        <span class="h-1.5 w-1.5 rounded-full bg-indigo-400"></span> 100% Privat
                    </span>
                </div>
            </div>

            <p class="relative z-10 text-sm text-slate-600">&copy; {{ date('Y') }} NaskahCek</p>
        </div>

        {{-- Right panel --}}
        <div class="flex-1 flex flex-col justify-center items-center px-6 py-10 sm:px-10">
            <div class="lg:hidden mb-8 text-center">
                <a href="{{ route('welcome') }}" class="inline-flex items-center gap-2">
                    <img src="{{ asset('images/naskahceklogo.png') }}" alt="NaskahCek logo" class="h-8 w-8 rounded-lg object-cover bg-white ring-1 ring-slate-200 shadow-sm" />
                    <span class="text-lg font-bold" style="color: var(--pc-text)">NaskahCek</span>
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
            isDark: localStorage.getItem('naskahcek_dark_mode') === 'true',
            init() {
                this.isDark ? document.documentElement.classList.add('dark') : document.documentElement.classList.remove('dark');
            }
        }
    }
    </script>
</body>
</html>
