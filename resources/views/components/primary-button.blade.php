<button {{ $attributes->merge(['type' => 'submit', 'class' => 'pc-btn-primary w-full']) }}>
    {{ $slot }}
</button>
