@extends('layouts.app')

@section('title', 'Kuisioner')

@section('content')
<div class="max-w-5xl mx-auto px-6 py-10 space-y-8">

    {{-- Page header --}}
    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 px-8 py-6">
        <h1 class="text-2xl font-bold text-gray-900">Kuisioner Psikososial</h1>
        <p class="mt-1 text-sm text-gray-500">Isi kuisioner evaluasi kondisi psikososial ASN dalam mode WFA dan WFO</p>
    </div>

    {{-- Flash messages --}}
    @if (session('success'))
        <div class="flex items-center gap-3 bg-green-50 border border-green-200 text-green-700 text-sm rounded-xl px-5 py-3.5">
            <i data-lucide="check-circle-2" class="w-5 h-5 flex-shrink-0"></i>
            {{ session('success') }}
        </div>
    @endif
    @if (session('error'))
        <div class="flex items-center gap-3 bg-red-50 border border-red-200 text-red-700 text-sm rounded-xl px-5 py-3.5">
            <i data-lucide="alert-circle" class="w-5 h-5 flex-shrink-0"></i>
            {{ session('error') }}
        </div>
    @endif

    {{-- ── Kuisioner Tersedia ─────────────────────────────────────────── --}}
    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 px-8 py-6">
        <h2 class="text-base font-bold text-gray-800 mb-5">Kuisioner Tersedia</h2>

        <div class="space-y-4">
            @foreach ($questionnaires as $q)
            <div class="flex items-center justify-between gap-4 border border-gray-100 rounded-xl px-6 py-5 hover:bg-gray-50 transition">
                {{-- Info --}}
                <div class="flex-1 min-w-0">
                    <div class="flex flex-wrap items-center gap-2 mb-1">
                        <span class="text-sm font-semibold text-gray-900">{{ $q['judul'] }}</span>

                        {{-- Status badge --}}
                        @if ($q['status'] === 'aktif' && !$q['sudah_diisi'])
                            <span class="inline-flex items-center gap-1 text-xs font-medium bg-amber-50 text-amber-600 border border-amber-200 rounded-full px-2.5 py-0.5">
                                <i data-lucide="clock" class="w-3 h-3"></i> Belum Diisi
                            </span>
                        @elseif ($q['status'] === 'aktif' && $q['sudah_diisi'])
                            <span class="inline-flex items-center gap-1 text-xs font-medium bg-green-50 text-green-700 border border-green-200 rounded-full px-2.5 py-0.5">
                                <i data-lucide="check-circle-2" class="w-3 h-3"></i> Sudah Diisi
                            </span>
                        @else
                            <span class="inline-flex items-center text-xs font-medium bg-gray-100 text-gray-500 rounded-full px-2.5 py-0.5">
                                Ditutup
                            </span>
                        @endif
                    </div>
                    <p class="text-xs text-gray-400">Tahun: {{ $q['tahun'] }} &nbsp;·&nbsp; Dibuat oleh: {{ $q['dibuat_oleh'] }}</p>
                </div>

                {{-- Action --}}
                <div class="flex-shrink-0">
                    @if ($q['status'] === 'aktif' && !$q['sudah_diisi'])
                        <a href="{{ route('kuisioner.show', $q['id']) }}"
                           class="inline-flex items-center gap-2 bg-brand-600 hover:bg-brand-700 text-white text-sm font-semibold px-5 py-2.5 rounded-xl shadow-sm transition-colors">
                            <i data-lucide="file-edit" class="w-4 h-4"></i>
                            Isi Kuisioner
                        </a>
                    @elseif ($q['status'] === 'aktif' && $q['sudah_diisi'])
                        <span class="text-sm text-gray-400 italic">Terima kasih sudah mengisi</span>
                    @else
                        <span class="text-sm text-gray-400 italic">Terima kasih sudah mengisi</span>
                    @endif
                </div>
            </div>
            @endforeach
        </div>
    </div>

    {{-- ── Riwayat Pengisian ──────────────────────────────────────────── --}}
    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 px-8 py-6">
        <h2 class="text-base font-bold text-gray-800 mb-5">Riwayat Pengisian Saya</h2>

        @if (count($history) > 0)
            <div class="space-y-4">
                @foreach ($history as $h)
                <div class="flex items-center justify-between gap-4 border border-gray-100 rounded-xl px-6 py-5 hover:bg-gray-50 transition">
                    <div class="flex-1 min-w-0">
                        <div class="flex flex-wrap items-center gap-2 mb-1">
                            <span class="text-sm font-semibold text-gray-900">{{ $h['judul'] }}</span>

                            {{-- Mode kerja badge --}}
                            <span class="text-xs font-medium bg-purple-100 text-purple-700 rounded-full px-2.5 py-0.5">
                                {{ $h['mode_kerja'] }}
                            </span>

                            {{-- Rata-rata badge --}}
                            <span class="text-xs font-medium bg-green-100 text-green-700 rounded-full px-2.5 py-0.5">
                                Avg: {{ $h['rata_rata'] }}/5
                            </span>
                        </div>
                        <p class="text-xs text-gray-400 mb-1">Dikirim: {{ $h['dikirim'] }}</p>
                        @if (!empty($h['komentar']))
                            <p class="text-xs text-gray-500 italic">"{{ $h['komentar'] }}"</p>
                        @endif
                    </div>

                    <div class="flex-shrink-0">
                        <a href="{{ route('kuisioner.riwayat.detail', $h['id']) }}"
                            class="inline-flex items-center gap-2 bg-brand-600 hover:bg-brand-700 text-white text-sm font-semibold px-5 py-2.5 rounded-xl shadow-sm transition-colors">
                            <i data-lucide="eye" class="w-4 h-4"></i>
                            Lihat Detail
                        </a>
                    </div>
                </div>
                @endforeach
            </div>
        @else
            <div class="flex flex-col items-center justify-center py-12 text-gray-400">
                <i data-lucide="inbox" class="w-12 h-12 mb-3 opacity-40"></i>
                <p class="text-sm">Belum ada riwayat pengisian.</p>
            </div>
        @endif
    </div>

</div>
@endsection
