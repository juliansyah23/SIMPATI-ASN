@extends('layouts.app')

@section('title', 'Lupa Password')

@section('content')
<div class="min-h-screen bg-gradient-to-br from-brand-50 via-red-50 to-rose-100 flex flex-col items-center justify-center py-12 px-4">

    {{-- Icon --}}
    <div class="mb-6">
        <div class="w-16 h-16 bg-brand-600 rounded-2xl flex items-center justify-center shadow-lg">
            <i data-lucide="key-round" class="w-8 h-8 text-white"></i>
        </div>
    </div>

    {{-- Heading --}}
    <h1 class="text-2xl font-bold text-gray-900 mb-1">Lupa Password?</h1>
    <p class="text-gray-500 text-sm mb-8 text-center max-w-sm">
        Masukkan email akun Anda, kami akan kirimkan link untuk mengatur ulang password.
    </p>

    {{-- Card --}}
    <div class="w-full max-w-md bg-white rounded-2xl shadow-md px-8 py-8">

        {{-- Session Success --}}
        @if (session('success'))
            <div class="mb-4 flex items-center gap-2 bg-emerald-50 border border-emerald-200 text-emerald-700 text-sm rounded-lg px-4 py-3">
                <i data-lucide="check-circle" class="w-4 h-4 flex-shrink-0"></i>
                {{ session('success') }}
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

        <form method="POST" action="{{ route('password.email') }}" novalidate>
            @csrf

            {{-- Email --}}
            <div class="mb-6">
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
                        autofocus
                        class="w-full pl-10 pr-4 py-2.5 text-sm border @error('email') border-red-400 bg-red-50 @else border-gray-200 @enderror rounded-xl focus:outline-none focus:ring-2 focus:ring-brand-500 focus:border-transparent transition"
                    >
                </div>
                @error('email')
                    <p class="mt-1.5 text-xs text-red-600">{{ $message }}</p>
                @enderror
            </div>

            {{-- Submit --}}
            <button type="submit"
                class="w-full flex items-center justify-center gap-2 bg-brand-600 hover:bg-brand-700 active:bg-brand-800 text-white font-semibold py-2.5 rounded-xl shadow-sm transition-colors duration-150">
                <i data-lucide="send" class="w-4 h-4"></i>
                Kirim Link Reset
            </button>
        </form>

        {{-- Back to login --}}
        <div class="text-center mt-6">
            <a href="{{ route('login') }}" class="inline-flex items-center gap-1.5 text-sm text-brand-600 hover:underline font-medium">
                <i data-lucide="arrow-left" class="w-4 h-4"></i>
                Kembali ke Login
            </a>
        </div>
    </div>

</div>
@endsection
