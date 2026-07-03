@extends('layouts.app')

@section('title', 'Login')

@section('content')
<div class="min-h-screen bg-gradient-to-br from-brand-50 via-red-50 to-rose-100 flex flex-col items-center justify-center py-12 px-4">

    {{-- Icon --}}
    <div class="mb-6">
        <div class="w-16 h-16 bg-brand-600 rounded-2xl flex items-center justify-center shadow-lg">
            <i data-lucide="log-in" class="w-8 h-8 text-white"></i>
        </div>
    </div>

    {{-- Heading --}}
    <h1 class="text-2xl font-bold text-gray-900 mb-1">Selamat Datang Kembali</h1>
    <p class="text-gray-500 text-sm mb-8">Masuk ke SIMPATI ASN</p>

    {{-- Card --}}
    <div class="w-full max-w-md bg-white rounded-2xl shadow-md px-8 py-8">

        {{-- Session Error --}}
        @if (session('error'))
            <div class="mb-4 flex items-center gap-2 bg-red-50 border border-red-200 text-red-700 text-sm rounded-lg px-4 py-3">
                <i data-lucide="alert-circle" class="w-4 h-4 flex-shrink-0"></i>
                {{ session('error') }}
            </div>
        @endif

        {{-- Validation Errors --}}
        @if ($errors->any())
            <div class="mb-4 bg-red-50 border border-red-200 text-red-700 text-sm rounded-lg px-4 py-3">
                <ul class="list-disc list-inside space-y-1">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <form method="POST" action="{{ route('login.submit') }}" novalidate>
            @csrf

            {{-- Email --}}
            <div class="mb-5">
                <label for="email" class="block text-sm font-medium text-gray-700 mb-1.5">Email</label>
                <div class="relative">
                    <span class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                        <i data-lucide="mail" class="w-4 h-4 text-gray-400"></i>
                    </span>
                    <input
                        id="email"
                        type="email"
                        name="email"
                        value="{{ old('email') }}"
                        placeholder="nama@brin.go.id"
                        autocomplete="email"
                        class="w-full pl-10 pr-4 py-2.5 text-sm border @error('email') border-red-400 bg-red-50 @else border-gray-200 @enderror rounded-xl focus:outline-none focus:ring-2 focus:ring-brand-500 focus:border-transparent transition"
                    >
                </div>
                @error('email')
                    <p class="mt-1.5 text-xs text-red-600">{{ $message }}</p>
                @enderror
            </div>

            {{-- Password --}}
            <div class="mb-6">
                <div class="flex items-center justify-between mb-1.5">
                    <label for="password" class="block text-sm font-medium text-gray-700">Password</label>
                    <a href="{{ route('password.request') }}" class="text-xs text-brand-600 hover:underline">Lupa password?</a>
                </div>
                <div class="relative">
                    <span class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                        <i data-lucide="lock" class="w-4 h-4 text-gray-400"></i>
                    </span>
                    <input
                        id="password"
                        type="password"
                        name="password"
                        placeholder="••••••••"
                        autocomplete="current-password"
                        class="w-full pl-10 pr-10 py-2.5 text-sm border @error('password') border-red-400 bg-red-50 @else border-gray-200 @enderror rounded-xl focus:outline-none focus:ring-2 focus:ring-brand-500 focus:border-transparent transition"
                    >
                    {{-- Toggle show/hide --}}
                    <button type="button" id="togglePassword"
                        class="absolute inset-y-0 right-0 pr-3 flex items-center text-gray-400 hover:text-gray-600">
                        <i data-lucide="eye" class="w-4 h-4" id="eyeIcon"></i>
                    </button>
                </div>
                @error('password')
                    <p class="mt-1.5 text-xs text-red-600">{{ $message }}</p>
                @enderror
            </div>

            {{-- Submit --}}
            <button type="submit"
                class="w-full flex items-center justify-center gap-2 bg-brand-600 hover:bg-brand-700 active:bg-brand-800 text-white font-semibold py-2.5 rounded-xl shadow-sm transition-colors duration-150">
                <i data-lucide="log-in" class="w-4 h-4"></i>
                Masuk
            </button>
        </form>

        {{-- Divider --}}
        <div class="flex items-center my-6">
            <div class="flex-1 border-t border-gray-200"></div>
            <span class="px-3 text-xs text-gray-400">atau</span>
            <div class="flex-1 border-t border-gray-200"></div>
        </div>

        {{-- Register link --}}
        <div class="text-center">
            <p class="text-sm text-gray-500 mb-3">Belum punya akun?</p>
            <a href="{{ route('register') }}"
                class="inline-block border border-brand-600 text-brand-600 hover:bg-brand-50 font-semibold text-sm px-6 py-2 rounded-xl transition-colors duration-150">
                Daftar Sekarang
            </a>
        </div>
    </div>

    {{-- Demo Credentials --}}
    <div class="w-full max-w-md mt-4 bg-white/70 backdrop-blur-sm border border-gray-100 rounded-2xl px-6 py-4 shadow-sm">
        <p class="text-xs font-semibold text-gray-500 uppercase tracking-wide mb-3">Demo Credentials:</p>
        <div class="space-y-2 text-sm">
            <div class="flex items-center justify-between">
                <span class="text-gray-500 font-medium">Admin:</span>
                <span class="font-mono text-gray-700 text-xs bg-gray-100 px-2 py-0.5 rounded">
                    admin@brin.go.id / admin123
                </span>
            </div>
            <div class="flex items-center justify-between">
                <span class="text-gray-500 font-medium">Employee:</span>
                <span class="font-mono text-gray-700 text-xs bg-gray-100 px-2 py-0.5 rounded">
                    employee@brin.go.id / employee123
                </span>
            </div>
        </div>
    </div>

</div>
@endsection

@push('scripts')
<script>
    const toggleBtn = document.getElementById('togglePassword');
    const passwordInput = document.getElementById('password');
    const eyeIcon = document.getElementById('eyeIcon');

    toggleBtn.addEventListener('click', () => {
        const isHidden = passwordInput.type === 'password';
        passwordInput.type = isHidden ? 'text' : 'password';
        eyeIcon.setAttribute('data-lucide', isHidden ? 'eye-off' : 'eye');
        lucide.createIcons();
    });
</script>
@endpush