<x-guest-layout>
    <div class="mb-8">
        <h2 class="text-2xl font-bold tracking-tight">Buat akun baru</h2>
        <p class="text-sm mt-1" style="color: var(--pc-text-muted);">Gratis, tanpa kartu kredit</p>
    </div>

    <form method="POST" action="{{ route('register') }}" class="space-y-5">
        @csrf

        <div>
            <x-input-label for="email" :value="__('Email')" />
            <x-text-input id="email" class="mt-1.5" type="email" name="email" :value="old('email')" required autofocus autocomplete="username" />
            <x-input-error :messages="$errors->get('email')" class="mt-2" />
        </div>

        <div>
            <x-input-label for="password" :value="__('Password')" />
            <x-text-input id="password" class="mt-1.5" type="password" name="password" required autocomplete="new-password" />
            <x-input-error :messages="$errors->get('password')" class="mt-2" />
        </div>

        <div>
            <x-input-label for="password_confirmation" :value="__('Confirm Password')" />
            <x-text-input id="password_confirmation" class="mt-1.5" type="password" name="password_confirmation" required autocomplete="new-password" />
            <x-input-error :messages="$errors->get('password_confirmation')" class="mt-2" />
        </div>

        <x-primary-button>{{ __('Register') }}</x-primary-button>

        <p class="text-center text-sm" style="color: var(--pc-text-muted);">
            Sudah punya akun?
            <a href="{{ route('login') }}" class="pc-link">Login</a>
        </p>
    </form>
</x-guest-layout>
