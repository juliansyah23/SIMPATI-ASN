@extends('layouts.app')

@section('title', 'Respons Kuisioner')

@section('content')
<div class="max-w-5xl mx-auto px-6 py-10 space-y-6">

    {{-- Header --}}
    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 px-8 py-6">
        <div class="flex items-start justify-between gap-4">
            <div class="flex items-start gap-4">
                <span class="flex items-center justify-center w-12 h-12 rounded-xl bg-purple-50 text-purple-600 shrink-0">
                    <i data-lucide="bar-chart-2" class="w-6 h-6"></i>
                </span>
                <div>
                    <h1 class="text-xl font-extrabold text-gray-900">Respons Kuisioner</h1>
                    <p class="mt-1 text-sm text-gray-500">{{ $kuisioner['judul'] }} &middot; Tahun {{ $kuisioner['tahun'] }}</p>
                </div>
            </div>
            <a href="{{ route('admin.index', ['tab' => 'kuisioner']) }}"
                class="inline-flex items-center gap-1.5 text-sm font-semibold text-gray-500 hover:text-gray-700 px-3 py-2 rounded-lg transition">
                <i data-lucide="arrow-left" class="w-4 h-4"></i> Kembali
            </a>
        </div>

        <div class="mt-5 flex items-center justify-between gap-3">
            <span class="text-xs font-medium bg-purple-100 text-purple-700 rounded-full px-3 py-1">
                {{ $responses->count() }} Responden
            </span>
            <div class="flex items-center gap-2">
                <a href="{{ route('admin.kuisioner.respons.export.excel', array_filter(['id' => $kuisioner['id'], 'q' => $search])) }}"
                   class="inline-flex items-center gap-2 px-4 h-10 rounded-lg border border-gray-200 hover:bg-gray-50 text-gray-700 text-xs font-semibold transition">
                    <i data-lucide="file-spreadsheet" class="w-4 h-4 text-emerald-600"></i> Excel
                </a>
                <a href="{{ route('admin.kuisioner.respons.export.pdf', array_filter(['id' => $kuisioner['id'], 'q' => $search])) }}" target="_blank"
                   class="inline-flex items-center gap-2 px-4 h-10 rounded-lg bg-brand-600 hover:bg-brand-700 text-white text-xs font-semibold shadow-sm transition">
                    <i data-lucide="file-text" class="w-4 h-4"></i> PDF
                </a>
            </div>
        </div>
    </div>

    {{-- Search --}}
    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 px-8 py-5">
        <form method="GET" action="{{ route('admin.kuisioner.respons', $kuisioner['id']) }}" class="relative">
            <span class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none">
                <i data-lucide="search" class="w-4 h-4 text-gray-400"></i>
            </span>
            <input
                type="text"
                name="q"
                value="{{ $search }}"
                placeholder="Cari responden berdasarkan nama atau NIP..."
                class="w-full pl-10 pr-4 py-2.5 text-sm border border-gray-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-brand-500 focus:border-transparent transition"
            >
        </form>
    </div>

    {{-- Daftar responden --}}
    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 px-8 py-6">
        @forelse ($responses as $r)
            <div class="flex items-center justify-between gap-4 border border-gray-100 rounded-xl px-6 py-5 mb-3 last:mb-0 hover:bg-gray-50 transition">
                <div class="flex-1 min-w-0">
                    <div class="flex flex-wrap items-center gap-2 mb-1">
                        <span class="text-sm font-semibold text-gray-900">{{ $r['nama'] }}</span>
                        <span class="text-xs font-medium bg-gray-100 text-gray-600 rounded-full px-2.5 py-0.5">
                            {{ $r['nip'] }}
                        </span>
                        <span class="text-xs font-medium bg-purple-100 text-purple-700 rounded-full px-2.5 py-0.5">
                            {{ $r['mode_kerja'] }}
                        </span>
                        <span class="text-xs font-medium bg-green-100 text-green-700 rounded-full px-2.5 py-0.5">
                            Avg: {{ $r['rata_rata'] }}/5
                        </span>
                    </div>
                    <p class="text-xs text-gray-400">{{ $r['posisi'] }} &middot; {{ $r['pusat_riset'] }}</p>
                    <p class="text-xs text-gray-400 mt-0.5">Dikirim: {{ $r['dikirim'] }}</p>
                </div>

                <div class="flex-shrink-0">
                    <a href="{{ route('kuisioner.riwayat.detail', $r['response_id']) }}"
                        class="inline-flex items-center gap-2 bg-brand-600 hover:bg-brand-700 text-white text-sm font-semibold px-5 py-2.5 rounded-xl shadow-sm transition-colors">
                        <i data-lucide="eye" class="w-4 h-4"></i>
                        Detail Respon
                    </a>
                </div>
            </div>
        @empty
            <div class="flex flex-col items-center justify-center py-12 text-gray-400">
                <i data-lucide="inbox" class="w-12 h-12 mb-3 opacity-40"></i>
                <p class="text-sm">
                    @if ($search !== '')
                        Tidak ada responden yang cocok dengan pencarian "{{ $search }}".
                    @else
                        Belum ada responden yang mengisi kuisioner ini.
                    @endif
                </p>
            </div>
        @endforelse
    </div>

</div>
@endsection
