@extends('layouts.app')

@section('title', 'Detail Kuisioner')

@section('content')
<div class="max-w-3xl mx-auto px-6 py-10">

    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 px-8 py-7">

        {{-- Header --}}
        <div class="flex items-start justify-between mb-6">
            <div>
                <h1 class="text-2xl font-bold text-gray-900">{{ $isAdminView ? 'Detail Respon' : 'Detail Kuisioner' }}</h1>
                <p class="text-sm text-gray-400 mt-1">{{ $judul }}</p>
            </div>
            <a href="{{ $backUrl }}"
               class="text-gray-400 hover:text-gray-600 transition-colors">
                <i data-lucide="x" class="w-6 h-6"></i>
            </a>
        </div>

        {{-- Identitas responden (hanya tampil saat admin melihat respons pegawai lain) --}}
        @if ($isAdminView)
            <div class="bg-gray-50 border border-gray-100 rounded-xl px-6 py-5 mb-6">
                <p class="text-xs font-semibold text-gray-400 uppercase tracking-wide mb-2">Responden</p>
                <div class="flex flex-wrap items-center gap-x-6 gap-y-1">
                    <p class="text-sm font-bold text-gray-900">{{ $responden['nama'] }}</p>
                    <p class="text-sm text-gray-500">NIP: {{ $responden['nip'] }}</p>
                    <p class="text-sm text-gray-500">{{ $responden['posisi'] }}</p>
                    <p class="text-sm text-gray-500">{{ $responden['pusat_riset'] }}</p>
                </div>
            </div>
        @endif

        {{-- Stat cards: Tahun / Mode Kerja / Rata-rata Skor --}}
        <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 mb-8">
            <div class="bg-red-50 rounded-xl px-5 py-4">
                <p class="text-xs font-medium text-red-500">Tahun</p>
                <p class="text-xl font-extrabold text-gray-900 mt-1">{{ $tahun }}</p>
            </div>
            <div class="bg-purple-50 rounded-xl px-5 py-4">
                <p class="text-xs font-medium text-purple-600">Mode Kerja</p>
                <p class="text-xl font-extrabold text-purple-900 mt-1">{{ $modeKerja }}</p>
            </div>
            <div class="bg-emerald-50 rounded-xl px-5 py-4">
                <p class="text-xs font-medium text-emerald-600">Rata-rata Skor</p>
                <p class="text-xl font-extrabold text-emerald-900 mt-1">{{ $rataRata }}/5</p>
            </div>
        </div>

        {{-- Hasil Penilaian --}}
        <h2 class="text-base font-bold text-gray-900 mb-4">Hasil Penilaian</h2>

        @forelse ($hasil as $kategori => $pertanyaans)
            <div class="mb-6 last:mb-0">
                @if ($hasil->count() > 1)
                    <p class="text-xs font-semibold text-gray-400 uppercase tracking-wide mb-3">{{ $kategori }}</p>
                @endif

                <div class="space-y-4">
                    @foreach ($pertanyaans as $p)
                        <div class="bg-gray-50 rounded-xl px-6 py-5">
                            <div class="flex items-center justify-between gap-4 mb-3">
                                <p class="text-sm font-semibold text-gray-800">{{ $p['pertanyaan'] }}</p>
                                <span class="text-lg font-extrabold text-brand-600 flex-shrink-0">{{ $p['skor'] }}/5</span>
                            </div>
                            <div class="w-full h-2.5 bg-gray-200 rounded-full overflow-hidden">
                                <div class="h-full bg-brand-600 rounded-full" style="width: {{ $p['skor'] * 20 }}%"></div>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        @empty
            <p class="text-sm text-gray-400 italic mb-6">Belum ada jawaban penilaian.</p>
        @endforelse

        {{-- Komentar --}}
        <h2 class="text-base font-bold text-gray-900 mt-8 mb-4">Komentar</h2>

        @forelse ($komentar as $k)
            <div class="bg-gray-50 rounded-xl px-6 py-5 mb-3 last:mb-0">
                <p class="text-xs text-gray-400 mb-1.5">{{ $k['pertanyaan'] }}</p>
                <p class="text-sm text-gray-700">{{ $k['jawaban'] }}</p>
            </div>
        @empty
            <p class="text-sm text-gray-400 italic">Tidak ada komentar.</p>
        @endforelse

        {{-- Footer --}}
        <div class="border-t border-gray-100 mt-8 pt-6 flex items-center justify-between">
            <p class="text-xs text-gray-400">Dikirim pada: {{ $dikirim }}</p>
            <a href="{{ $backUrl }}"
               class="inline-flex items-center gap-2 bg-brand-600 hover:bg-brand-700 text-white text-sm font-semibold px-5 py-2.5 rounded-xl shadow-sm transition-colors">
                Kembali
            </a>
        </div>

    </div>
</div>
@endsection
