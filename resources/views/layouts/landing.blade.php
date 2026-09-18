<!DOCTYPE html>
<html lang="id" class="scroll-smooth">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="theme-color" content="#0d1b3d">
    <title>@yield('title', 'NaskahCek — AI Academic Integrity & Plagiarism Platform')</title>
    <link rel="icon" type="image/png" href="{{ asset('images/naskahceklogo.png') }}">
    <link rel="shortcut icon" href="{{ asset('images/naskahceklogo.png') }}">
    <link rel="apple-touch-icon" href="{{ asset('images/naskahceklogo.png') }}">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&display=swap" rel="stylesheet">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @stack('styles')
    <style>
        [x-cloak] {
            display: none !important;
        }
        body {
            font-family: 'Poppins', sans-serif;
            background-color: #ffffff;
            color: #0f172a;
        }
        .landing-nav {
            top: 42px;
        }
        /* Fade-up animation */
        .fade-up {
            opacity: 0;
            transform: translateY(32px);
            transition: opacity 0.7s cubic-bezier(.22,1,.36,1), transform 0.7s cubic-bezier(.22,1,.36,1);
        }
        .fade-up.is-visible {
            opacity: 1;
            transform: translateY(0);
        }
        /* Subtle float for mockup */
        @keyframes gentle-float {
            0%, 100% { transform: translateY(0); }
            50% { transform: translateY(-10px); }
        }
        .float-animation {
            animation: gentle-float 5s ease-in-out infinite;
        }
        /* Smooth accordion */
        .accordion-body {
            max-height: 0;
            overflow: hidden;
            transition: max-height 0.4s cubic-bezier(.22,1,.36,1), padding 0.3s ease;
        }
        .accordion-body.open {
            max-height: 500px;
        }
    </style>
</head>

<body class="min-h-screen antialiased bg-white text-slate-900 selection:bg-blue-600 selection:text-white">
    <div class="promo-marquee-wrapper">
        <div class="promo-marquee-track">
            <span>Promo Launching: Cek plagiarisme lebih cepat dengan analisis mendalam dan aman</span>
            <span>•</span>
            <span>Diskon khusus untuk mahasiswa, dosen, dan peneliti</span>
            <span>•</span>
            <span>Gratis review awal untuk naskah akademik yang ingin diperbaiki</span>
            <span>•</span>
            <span>Promo Launching: Cek plagiarisme lebih cepat dengan analisis mendalam dan aman</span>
            <span>•</span>
            <span>Diskon khusus untuk mahasiswa, dosen, dan peneliti</span>
            <span>•</span>
            <span>Gratis review awal untuk naskah akademik yang ingin diperbaiki</span>
        </div>
    </div>
    <style>
        .promo-marquee-wrapper {
            position: sticky;
            top: 0;
            z-index: 40;
            overflow: hidden;
            background: #1e3a8a;
            color: #ffffff;
            border-bottom: 1px solid rgba(255,255,255,0.08);
        }
        .promo-marquee-track {
            display: flex;
            align-items: center;
            gap: 1.5rem;
            white-space: nowrap;
            min-width: max-content;
            width: max-content;
            padding: 0.6rem 0;
            font-size: 0.75rem;
            font-weight: 400;
            letter-spacing: 0.02em;
            animation: promo-marquee 20s linear infinite;
        }
        .promo-marquee-track span { display: inline-block; }
        @keyframes promo-marquee {
            from { transform: translateX(0); }
            to { transform: translateX(-50%); }
        }
    </style>
    @yield('content')

    {{-- Fade-up IntersectionObserver --}}
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const observer = new IntersectionObserver((entries) => {
                entries.forEach(entry => {
                    if (entry.isIntersecting) {
                        entry.target.classList.add('is-visible');
                    }
                });
            }, { threshold: 0.08, rootMargin: '0px 0px -40px 0px' });
            document.querySelectorAll('.fade-up').forEach(el => observer.observe(el));
        });
    </script>
    @stack('scripts')
</body>

</html>
