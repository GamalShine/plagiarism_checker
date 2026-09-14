@extends('layouts.user')

@section('title', 'Profil')
@section('page-title', 'Profil')
@section('page-subtitle', 'Kelola informasi akun Anda')

@section('content')
    <div class="pc-card p-6 sm:p-8">
        @include('profile.partials.update-profile-information-form')
    </div>

    <div class="pc-card p-6 sm:p-8">
        @include('profile.partials.update-password-form')
    </div>

    <div class="pc-card p-6 sm:p-8">
        @include('profile.partials.delete-user-form')
    </div>
@endsection
