@extends('layouts.app')

@section('title', 'Profil Saya')

@section('content')
<div class="max-w-3xl mx-auto px-6 py-8">

    @if ($errors->any())
        <div class="bg-red-50 border border-red-200 text-red-700 text-sm rounded-xl p-4 mb-6">
            <p class="font-semibold mb-1">Periksa kembali isian Anda:</p>
            <ul class="list-disc list-inside space-y-0.5">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    @if (session('success'))
        <div class="flex items-center gap-3 bg-green-50 border border-green-200 text-green-700 text-sm rounded-xl px-5 py-3 mb-6">
            <i data-lucide="check-circle-2" class="w-4 h-4 flex-shrink-0"></i>
            {{ session('success') }}
        </div>
    @endif

    <form method="POST" action="{{ route('profile.update') }}">
        @csrf
        @method('PUT')

        {{-- ── Header Card ──────────────────────────────────────────────── --}}
        <div class="bg-white rounded-2xl border border-gray-200 shadow-sm p-6 mb-6">
            <div class="flex items-start justify-between gap-4 flex-wrap">
                <div>
                    <h1 class="text-2xl font-bold text-gray-900">Profil Saya</h1>
                    <p class="text-sm text-brand-600 mt-1">{{ $u->name }}</p>
                </div>

                <button type="submit"
                        class="flex items-center gap-2 px-5 h-10 rounded-lg bg-brand-600 hover:bg-brand-700 text-white text-sm font-semibold shadow-sm transition">
                    <i data-lucide="save" class="w-4 h-4"></i> Simpan Perubahan
                </button>
            </div>
        </div>

        {{-- ── Form Fields ──────────────────────────────────────────────── --}}
        <div class="bg-white rounded-2xl border border-gray-200 shadow-sm p-6 space-y-5">

            <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-1.5">NIP</label>
                    <input type="text" name="nip" value="{{ old('nip', $u->nip) }}" placeholder="Isi kalau belum ada"
                        class="w-full h-11 px-4 rounded-xl border @error('nip') border-red-400 bg-red-50 @else border-gray-300 @enderror text-sm text-gray-800 focus:outline-none focus:ring-2 focus:ring-brand-500 focus:border-transparent transition">
                    @error('nip') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-1.5">Nama Lengkap <span class="text-brand-600">*</span></label>
                    <input type="text" name="name" value="{{ old('name', $u->name) }}"
                        class="w-full h-11 px-4 rounded-xl border @error('name') border-red-400 bg-red-50 @else border-gray-300 @enderror text-sm text-gray-800 focus:outline-none focus:ring-2 focus:ring-brand-500 focus:border-transparent transition">
                    @error('name') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
                </div>

                <div class="md:col-span-2">
                    <label class="block text-sm font-semibold text-gray-700 mb-1.5">Email <span class="text-brand-600">*</span></label>
                    <input type="email" name="email" value="{{ old('email', $u->email) }}"
                        class="w-full h-11 px-4 rounded-xl border @error('email') border-red-400 bg-red-50 @else border-gray-300 @enderror text-sm text-gray-800 focus:outline-none focus:ring-2 focus:ring-brand-500 focus:border-transparent transition">
                    @error('email') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
                </div>

                <div class="md:col-span-2">
                    <label class="block text-sm font-semibold text-gray-700 mb-1.5">Institusi <span class="text-brand-600">*</span></label>
                    <input type="text" name="institusi" value="{{ old('institusi', $u->institusi) }}"
                        class="w-full h-11 px-4 rounded-xl border @error('institusi') border-red-400 bg-red-50 @else border-gray-300 @enderror text-sm text-gray-800 focus:outline-none focus:ring-2 focus:ring-brand-500 focus:border-transparent transition">
                    @error('institusi') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-1.5">Pusat Riset <span class="text-brand-600">*</span></label>
                    <select name="pusat_riset"
                        class="w-full h-11 px-4 rounded-xl border @error('pusat_riset') border-red-400 bg-red-50 @else border-gray-300 @enderror text-sm text-gray-800 bg-white focus:outline-none focus:ring-2 focus:ring-brand-500 focus:border-transparent transition">
                        @foreach ($pusatRiset as $pr)
                            <option value="{{ $pr }}" {{ old('pusat_riset', $u->pusat_riset) === $pr ? 'selected' : '' }}>{{ $pr }}</option>
                        @endforeach
                    </select>
                    @error('pusat_riset') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-1.5">Posisi <span class="text-brand-600">*</span></label>
                    <select name="posisi"
                        class="w-full h-11 px-4 rounded-xl border @error('posisi') border-red-400 bg-red-50 @else border-gray-300 @enderror text-sm text-gray-800 bg-white focus:outline-none focus:ring-2 focus:ring-brand-500 focus:border-transparent transition">
                        @foreach ($posisiList as $p)
                            <option value="{{ $p }}" {{ old('posisi', $u->posisi) === $p ? 'selected' : '' }}>{{ $p }}</option>
                        @endforeach
                    </select>
                    @error('posisi') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-1.5">Role</label>
                    <input type="text" value="{{ $u->role }}" disabled
                        class="w-full h-11 px-4 rounded-xl border border-gray-300 bg-gray-100 text-sm text-gray-400">
                    <p class="text-xs text-gray-400 mt-1">Role hanya bisa diubah oleh admin.</p>
                </div>
            </div>

            <hr class="border-gray-100">

            <div>
                <p class="text-sm font-semibold text-gray-700 mb-1.5">Ubah Password <span class="text-gray-400 font-normal">(opsional)</span></p>
                <p class="text-xs text-gray-400 mb-3">Kosongkan semua field di bawah kalau tidak ingin mengubah password.</p>
                <div class="grid grid-cols-1 md:grid-cols-3 gap-5">
                    <div>
                        <label class="block text-xs font-medium text-gray-500 mb-1.5">Password Saat Ini</label>
                        <input type="password" name="current_password"
                            class="w-full h-11 px-4 rounded-xl border @error('current_password') border-red-400 bg-red-50 @else border-gray-300 @enderror text-sm text-gray-800 focus:outline-none focus:ring-2 focus:ring-brand-500 focus:border-transparent transition">
                        @error('current_password') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-gray-500 mb-1.5">Password Baru</label>
                        <input type="password" name="password"
                            class="w-full h-11 px-4 rounded-xl border @error('password') border-red-400 bg-red-50 @else border-gray-300 @enderror text-sm text-gray-800 focus:outline-none focus:ring-2 focus:ring-brand-500 focus:border-transparent transition">
                        @error('password') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-gray-500 mb-1.5">Konfirmasi Password Baru</label>
                        <input type="password" name="password_confirmation"
                            class="w-full h-11 px-4 rounded-xl border border-gray-300 text-sm text-gray-800 focus:outline-none focus:ring-2 focus:ring-brand-500 focus:border-transparent transition">
                    </div>
                </div>
            </div>
        </div>
    </form>
</div>
@endsection
