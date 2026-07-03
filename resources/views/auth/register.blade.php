@extends('layouts.app')

@section('title', 'Daftar Akun')

@section('content')
<div class="min-h-screen bg-gradient-to-br from-brand-50 via-red-50 to-rose-100 flex flex-col items-center justify-center py-12 px-4">

    {{-- Icon --}}
    <div class="mb-6">
        <div class="w-16 h-16 bg-brand-600 rounded-2xl flex items-center justify-center shadow-lg">
            <i data-lucide="user-plus" class="w-8 h-8 text-white"></i>
        </div>
    </div>

    {{-- Heading --}}
    <h1 class="text-2xl font-bold text-gray-900 mb-1">Buat Akun Baru</h1>
    <p class="text-gray-500 text-sm mb-8">Daftar untuk mengakses SIMPATI ASN</p>

    {{-- Card --}}
    <div class="w-full max-w-lg bg-white rounded-2xl shadow-md px-8 py-8">

        {{-- Validation Errors --}}
        @if ($errors->any())
            <div class="mb-5 bg-red-50 border border-red-200 text-red-700 text-sm rounded-lg px-4 py-3">
                <ul class="list-disc list-inside space-y-1">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <form method="POST" action="{{ route('register.submit') }}" novalidate>
            @csrf

            {{-- NIP --}}
            <div class="mb-5">
                <label for="nip" class="block text-sm font-medium text-gray-700 mb-1.5">
                    NIP <span class="text-brand-600">*</span>
                </label>
                <div class="relative">
                    <span class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                        <i data-lucide="hash" class="w-4 h-4 text-gray-400"></i>
                    </span>
                    <input
                        id="nip"
                        type="text"
                        name="nip"
                        value="{{ old('nip') }}"
                        placeholder="199001012015041001"
                        maxlength="18"
                        class="w-full pl-10 pr-4 py-2.5 text-sm border @error('nip') border-red-400 bg-red-50 @else border-gray-200 @enderror rounded-xl focus:outline-none focus:ring-2 focus:ring-brand-500 focus:border-transparent transition"
                    >
                </div>
                @error('nip')
                    <p class="mt-1.5 text-xs text-red-600">{{ $message }}</p>
                @enderror
            </div>

            {{-- Nama Lengkap --}}
            <div class="mb-5">
                <label for="name" class="block text-sm font-medium text-gray-700 mb-1.5">
                    Nama Lengkap <span class="text-brand-600">*</span>
                </label>
                <div class="relative">
                    <span class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                        <i data-lucide="user" class="w-4 h-4 text-gray-400"></i>
                    </span>
                    <input
                        id="name"
                        type="text"
                        name="name"
                        value="{{ old('name') }}"
                        placeholder="Ahmad Hidayat"
                        class="w-full pl-10 pr-4 py-2.5 text-sm border @error('name') border-red-400 bg-red-50 @else border-gray-200 @enderror rounded-xl focus:outline-none focus:ring-2 focus:ring-brand-500 focus:border-transparent transition"
                    >
                </div>
                @error('name')
                    <p class="mt-1.5 text-xs text-red-600">{{ $message }}</p>
                @enderror
            </div>

            {{-- Email --}}
            <div class="mb-5">
                <label for="email" class="block text-sm font-medium text-gray-700 mb-1.5">
                    Email <span class="text-brand-600">*</span>
                </label>
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

            {{-- Institusi + Pusat Riset --}}
            <div class="grid grid-cols-2 gap-4 mb-5">
                {{-- Institusi --}}
                <div>
                    <label for="institusi" class="block text-sm font-medium text-gray-700 mb-1.5">
                        Institusi <span class="text-brand-600">*</span>
                    </label>
                    <div class="relative">
                        <span class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                            <i data-lucide="building-2" class="w-4 h-4 text-gray-400"></i>
                        </span>
                        <input
                            id="institusi"
                            type="text"
                            name="institusi"
                            value="{{ old('institusi', 'BRIN') }}"
                            placeholder="BRIN"
                            class="w-full pl-10 pr-4 py-2.5 text-sm border @error('institusi') border-red-400 bg-red-50 @else border-gray-200 @enderror rounded-xl focus:outline-none focus:ring-2 focus:ring-brand-500 focus:border-transparent transition"
                        >
                    </div>
                    @error('institusi')
                        <p class="mt-1.5 text-xs text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                {{-- Pusat Riset --}}
                <div>
                    <label for="pusat_riset" class="block text-sm font-medium text-gray-700 mb-1.5">
                        Pusat Riset <span class="text-brand-600">*</span>
                    </label>
                    <div class="relative">
                        <select
                            id="pusat_riset"
                            name="pusat_riset"
                            class="w-full px-4 py-2.5 text-sm border @error('pusat_riset') border-red-400 bg-red-50 @else border-gray-200 @enderror rounded-xl focus:outline-none focus:ring-2 focus:ring-brand-500 focus:border-transparent transition appearance-none bg-white pr-8"
                        >
                            <option value="" disabled {{ old('pusat_riset') ? '' : 'selected' }}>Pilih Pusat Riset</option>
                            @php
                                // Label singkat khusus untuk tampilan dropdown (value tetap nama lengkap dari config)
                                $pusatRisetLabels = [
                                    'Pusat Riset Kecerdasan Artifisial dan Keamanan Siber' => 'PR Kecerdasan Artifisial & Keamanan Siber',
                                    'Pusat Riset Sains Data dan Informasi' => 'Pusat Riset Sains Data & Informasi',
                                ];
                            @endphp
                            @foreach (config('options.pusat_riset') as $pr)
                                <option value="{{ $pr }}" {{ old('pusat_riset') === $pr ? 'selected' : '' }}>{{ $pusatRisetLabels[$pr] ?? $pr }}</option>
                            @endforeach
                        </select>
                        {{-- Custom chevron --}}
                        <span class="pointer-events-none absolute inset-y-0 right-3 flex items-center">
                            <i data-lucide="chevron-down" class="w-4 h-4 text-gray-400"></i>
                        </span>
                    </div>
                    @error('pusat_riset')
                        <p class="mt-1.5 text-xs text-red-600">{{ $message }}</p>
                    @enderror
                </div>
            </div>

            {{-- Posisi --}}
            <div class="mb-5">
                <label for="posisi" class="block text-sm font-medium text-gray-700 mb-1.5">
                    Posisi <span class="text-brand-600">*</span>
                </label>
                <div class="relative">
                    <span class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                        <i data-lucide="briefcase" class="w-4 h-4 text-gray-400"></i>
                    </span>
                    <select
                        id="posisi"
                        name="posisi"
                        class="w-full pl-10 pr-8 py-2.5 text-sm border @error('posisi') border-red-400 bg-red-50 @else border-gray-200 @enderror rounded-xl focus:outline-none focus:ring-2 focus:ring-brand-500 focus:border-transparent transition appearance-none bg-white"
                    >
                        <option value="" disabled {{ old('posisi') ? '' : 'selected' }}>Pilih Posisi</option>
                        @foreach (config('options.posisi') as $p)
                            <option value="{{ $p }}" {{ old('posisi') === $p ? 'selected' : '' }}>{{ $p }}</option>
                        @endforeach
                    </select>
                    <span class="pointer-events-none absolute inset-y-0 right-3 flex items-center">
                        <i data-lucide="chevron-down" class="w-4 h-4 text-gray-400"></i>
                    </span>
                </div>
                @error('posisi')
                    <p class="mt-1.5 text-xs text-red-600">{{ $message }}</p>
                @enderror
            </div>

            {{-- Password + Konfirmasi --}}
            <div class="grid grid-cols-2 gap-4 mb-6">
                {{-- Password --}}
                <div>
                    <label for="password" class="block text-sm font-medium text-gray-700 mb-1.5">
                        Password <span class="text-brand-600">*</span>
                    </label>
                    <div class="relative">
                        <span class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                            <i data-lucide="lock" class="w-4 h-4 text-gray-400"></i>
                        </span>
                        <input
                            id="password"
                            type="password"
                            name="password"
                            placeholder="Minimal 6 karakter"
                            autocomplete="new-password"
                            class="w-full pl-10 pr-4 py-2.5 text-sm border @error('password') border-red-400 bg-red-50 @else border-gray-200 @enderror rounded-xl focus:outline-none focus:ring-2 focus:ring-brand-500 focus:border-transparent transition"
                        >
                    </div>
                    @error('password')
                        <p class="mt-1.5 text-xs text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                {{-- Konfirmasi Password --}}
                <div>
                    <label for="password_confirmation" class="block text-sm font-medium text-gray-700 mb-1.5">
                        Konfirmasi Password <span class="text-brand-600">*</span>
                    </label>
                    <div class="relative">
                        <span class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                            <i data-lucide="lock" class="w-4 h-4 text-gray-400"></i>
                        </span>
                        <input
                            id="password_confirmation"
                            type="password"
                            name="password_confirmation"
                            placeholder="Ulangi password"
                            autocomplete="new-password"
                            class="w-full pl-10 pr-4 py-2.5 text-sm border border-gray-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-brand-500 focus:border-transparent transition"
                        >
                    </div>
                </div>
            </div>

            {{-- Submit --}}
            <button type="submit"
                class="w-full flex items-center justify-center gap-2 bg-brand-600 hover:bg-brand-700 active:bg-brand-800 text-white font-semibold py-2.5 rounded-xl shadow-sm transition-colors duration-150">
                <i data-lucide="user-plus" class="w-4 h-4"></i>
                Daftar
            </button>
        </form>

        {{-- Divider --}}
        <div class="flex items-center my-6">
            <div class="flex-1 border-t border-gray-200"></div>
            <span class="px-3 text-xs text-gray-400">atau</span>
            <div class="flex-1 border-t border-gray-200"></div>
        </div>

        {{-- Login link --}}
        <div class="text-center">
            <p class="text-sm text-gray-500 mb-3">Sudah punya akun?</p>
            <a href="{{ route('login') }}"
                class="inline-block border border-brand-600 text-brand-600 hover:bg-brand-50 font-semibold text-sm px-6 py-2 rounded-xl transition-colors duration-150">
                Masuk
            </a>
        </div>
    </div>

</div>
@endsection
