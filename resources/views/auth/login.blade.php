@extends('layouts.app')

@section('title', 'Masuk')

@section('content')
<div class="container min-vh-100 d-flex flex-column justify-content-center align-items-center py-4">
    <div class="w-100" style="max-width: 420px;">

        {{-- Brand Header --}}
        <div class="text-center mb-4">
            <a href="{{ url('/') }}" class="text-decoration-none text-dark">
                <span class="fs-3 fw-bold tracking-tight">Spendly<span class="text-primary">.</span></span>
            </a>
            <p class="text-muted small mt-1 mb-0">Atur duit, biar nggak boncos.</p>
        </div>

        {{-- Auth Card --}}
        <div class="card border shadow-sm rounded-4">
            <div class="card-body p-4 p-sm-5">

                <div class="mb-4">
                    <h1 class="h4 fw-bold text-dark mb-1">Selamat datang kembali</h1>
                    <p class="text-muted small mb-0">
                        Masuk ke akun Spendly kamu untuk melanjutkan.
                    </p>
                </div>

                <x-alert type="success">
                    {{ session('success') }}
                </x-alert>

                <x-validation-errors />

                <form action="{{ route('login') }}" method="POST">
                    @csrf

                    <div class="mb-3">
                        <label for="email" class="form-label small fw-medium text-dark mb-1">
                            Email
                        </label>
                        <input
                            type="email"
                            class="form-control py-2 px-3"
                            id="email"
                            name="email"
                            value="{{ old('email') }}"
                            placeholder="nama@email.com"
                            autocomplete="email"
                            required
                        >
                    </div>

                    <div class="mb-4">
                        <label for="password" class="form-label small fw-medium text-dark mb-1">
                            Password
                        </label>
                        <input
                            type="password"
                            class="form-control py-2 px-3"
                            id="password"
                            name="password"
                            placeholder="Masukkan password"
                            autocomplete="current-password"
                            required
                        >
                    </div>

                    <button type="submit" class="btn btn-primary w-100 py-2 fw-semibold">
                        Masuk
                    </button>
                </form>

                <div class="text-center mt-4 pt-2 border-top">
                    <p class="text-muted small mb-0">
                        Belum punya akun?
                        <a href="{{ route('register') }}" class="text-primary fw-semibold text-decoration-none">
                            Daftar sekarang
                        </a>
                    </p>
                </div>

            </div>
        </div>

        <p class="text-center text-muted mt-4 mb-0" style="font-size: 0.75rem;">
            &copy; {{ date('Y') }} Spendly. Kelola keuangan dengan lebih baik.
        </p>

    </div>
</div>
@endsection