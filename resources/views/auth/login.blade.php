<x-guest-layout>
    <div class="mb-8">
        <h2 class="text-2xl font-bold tracking-tight">Selamat datang kembali</h2>
        <p class="text-sm mt-1" style="color: var(--pc-text-muted);">Masuk ke akun PlagCheck Pro Anda</p>
    </div>

    <x-auth-session-status class="mb-4" :status="session('status')" />

    <form method="POST" action="{{ route('login') }}" class="space-y-5">
        @csrf

        <div>
            <x-input-label for="email" :value="__('Email')" />
            <x-text-input id="email" class="mt-1.5" type="email" name="email" :value="old('email')" required autofocus autocomplete="username" />
            <x-input-error :messages="$errors->get('email')" class="mt-2" />
        </div>

        <div>
            <x-input-label for="password" :value="__('Password')" />
            <x-text-input id="password" class="mt-1.5" type="password" name="password" required autocomplete="current-password" />
            <x-input-error :messages="$errors->get('password')" class="mt-2" />
        </div>

        <div class="flex items-center justify-between">
            <label for="remember_me" class="inline-flex items-center gap-2 cursor-pointer">
                <input id="remember_me" type="checkbox" class="rounded border-slate-300 text-indigo-600 focus:ring-indigo-500" name="remember">
                <span class="text-sm" style="color: var(--pc-text-muted);">{{ __('Remember me') }}</span>
            </label>
            @if (Route::has('password.request'))
                <a class="text-sm pc-link" href="{{ route('password.request') }}">{{ __('Forgot password?') }}</a>
            @endif
        </div>

        <x-primary-button>{{ __('Log in') }}</x-primary-button>

        <p class="text-center text-sm" style="color: var(--pc-text-muted);">
            Belum punya akun?
            <a href="{{ route('register') }}" class="pc-link">Daftar gratis</a>
        </p>
    </form>
</x-guest-layout>
