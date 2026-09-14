@extends('layouts.guest_check')

@section('title', 'Link Tidak Tersedia')
@section('page-title', 'Link Tidak Tersedia')
@section('page-subtitle', 'Halaman pengecekan sudah tidak bisa diakses')

@section('content')
<div class="pc-card p-8 text-center">
    <div class="pc-empty-icon mx-auto mb-4">
        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01M10.29 3.86L1.82 18a2 2 0 001.71 3h16.94a2 2 0 001.71-3L13.71 3.86a2 2 0 00-3.42 0z"/></svg>
    </div>
    <h3 class="text-lg font-bold mb-2">{{ $message ?? 'Link sudah tidak tersedia' }}</h3>
    <p class="text-sm" style="color: var(--pc-text-muted);">
        Link sekali pakai mungkin sudah dipakai, dinonaktifkan, atau tidak valid.
    </p>
</div>
@endsection
