@extends('layouts.app')

@section('title', 'Isi Kuisioner')

@push('styles')
<style>
    /* ── Likert 1-5 boxes ─────────────────────────────────────────────── */
    .likert-radio { display: none; }
    .likert-radio:checked + .likert-label {
        background-color: #fff;
        color: #111827;
        border-color: #dc2626;
        box-shadow: 0 0 0 3px rgba(220,38,38,.15);
    }
    .likert-label {
        display: flex;
        align-items: center;
        justify-content: center;
        height: 3.25rem;
        border: 2px solid #e5e7eb;
        border-radius: .75rem;
        font-size: .95rem;
        font-weight: 600;
        color: #374151;
        cursor: pointer;
        background: #fff;
        transition: background .15s, color .15s, border-color .15s, box-shadow .15s;
        user-select: none;
    }
    .likert-label:hover { border-color: #dc2626; color: #dc2626; }

    /* ── Demografi pilihan kartu ──────────────────────────────────────── */
    .choice-radio { display: none; }
    .choice-radio:checked + .choice-label {
        border-color: #dc2626;
        background-color: #fef2f2;
        color: #111827;
    }
    .choice-radio:checked + .choice-label .choice-dot {
        border-color: #dc2626;
        background-color: #dc2626;
        box-shadow: inset 0 0 0 3px #fff;
    }
    .choice-label {
        display: flex;
        align-items: center;
        gap: .75rem;
        width: 100%;
        padding: .9rem 1.25rem;
        border: 1.5px solid #e5e7eb;
        border-radius: .75rem;
        font-size: .9rem;
        font-weight: 600;
        color: #374151;
        cursor: pointer;
        transition: border-color .15s, background-color .15s, color .15s;
        user-select: none;
    }
    .choice-label:hover { border-color: #dc2626; }
    .choice-dot {
        flex-shrink: 0;
        width: 1.1rem;
        height: 1.1rem;
        border-radius: 9999px;
        border: 2px solid #9ca3af;
        background: #fff;
        transition: border-color .15s, background-color .15s, box-shadow .15s;
    }

    #progress-fill { transition: width .3s ease; }
</style>
@endpush

@section('content')
<div class="max-w-3xl mx-auto px-6 py-10 space-y-6">

    {{-- Header --}}
    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 px-8 py-6">
        <div class="flex items-start gap-4">
            <span class="flex items-center justify-center w-12 h-12 rounded-xl bg-red-50 text-brand-600 shrink-0">
                <i data-lucide="file-text" class="w-6 h-6"></i>
            </span>
            <div>
                <h1 class="text-xl font-extrabold text-gray-900">{{ $kuesioner['judul'] }}</h1>
                <p class="mt-1 text-sm text-gray-500">{{ $kuesioner['deskripsi'] ?? '' }}</p>
            </div>
        </div>

        {{-- Progress bar --}}
        <div class="mt-6">
            <div class="flex justify-between text-sm text-gray-500 mb-1.5">
                <span>Kategori {{ $step }} dari {{ $totalSteps }}</span>
                <span>{{ $progress }}% Selesai</span>
            </div>
            <div class="w-full bg-gray-100 rounded-full h-2">
                <div id="progress-fill" class="bg-brand-600 h-2 rounded-full" style="width: {{ $progress }}%"></div>
            </div>
        </div>
    </div>

    {{-- Validation errors --}}
    @if ($errors->any())
        <div class="bg-red-50 border border-red-200 text-red-700 text-sm rounded-xl px-5 py-4">
            <p class="font-semibold mb-1">Harap lengkapi semua jawaban:</p>
            <ul class="list-disc list-inside space-y-0.5">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    {{-- Form ini selalu menyimpan kategori AKTIF; tombol "Kirim Kuisioner" muncul di kategori terakhir --}}
    <form method="POST"
          action="{{ $step === $totalSteps ? route('kuisioner.submit', $kuesioner['id']) : route('kuisioner.step', $kuesioner['id']) }}"
          id="kuisioner-form">
        @csrf
        <input type="hidden" name="step" value="{{ $step }}">
        <input type="hidden" name="action" id="form-action" value="next">

        {{-- ════════════════════════════════════════════════════════════
             KATEGORI 1 — DATA DEMOGRAFI RESPONDEN
        ════════════════════════════════════════════════════════════ --}}
        @if ($current['type'] === 'demografi')
            <div class="bg-white rounded-2xl shadow-sm border border-gray-100 px-8 py-6">
                <h2 class="text-lg font-bold text-gray-900">{{ $current['title'] }}</h2>
                <p class="mt-1 text-sm text-gray-500">{{ $current['subtitle'] }}</p>
            </div>

            @foreach ($current['fields'] as $field)
                <div class="bg-white rounded-2xl shadow-sm border border-gray-100 px-8 py-6">
                    <p class="text-sm font-bold text-gray-800 mb-4">
                        {{ $field['label'] }} <span class="text-red-500">*</span>
                    </p>

                    @if ($field['field_key'] === 'kategori_jabatan')
                        {{-- Dropdown untuk Kategori Jabatan (13 opsi, lebih nyaman pakai select) --}}
                        @php $selected = $draft['demografi'][$field['id']] ?? old("demografi.{$field['id']}"); @endphp
                        <div class="relative">
                            <select name="demografi[{{ $field['id'] }}]"
                                    class="w-full h-11 pl-4 pr-10 rounded-xl border border-gray-300 text-sm text-gray-800
                                           focus:outline-none focus:ring-2 focus:ring-brand-500 focus:border-transparent
                                           transition bg-white appearance-none cursor-pointer">
                                <option value="" disabled {{ $selected ? '' : 'selected' }}>— Pilih Kategori Jabatan —</option>
                                @foreach ($field['options'] as $opt)
                                    <option value="{{ $opt }}" {{ $selected === $opt ? 'selected' : '' }}>{{ $opt }}</option>
                                @endforeach
                            </select>
                            <span class="pointer-events-none absolute inset-y-0 right-3 flex items-center text-gray-400">
                                <i data-lucide="chevron-down" class="w-4 h-4"></i>
                            </span>
                        </div>
                    @else
                        {{-- Radio button pill untuk field lainnya --}}
                        <div class="space-y-2.5">
                            @foreach ($field['options'] as $opt)
                                @php
                                    $checked = ($draft['demografi'][$field['id']] ?? old("demografi.{$field['id']}")) === $opt;
                                @endphp
                                <label class="block cursor-pointer">
                                    <input type="radio"
                                           class="choice-radio"
                                           name="demografi[{{ $field['id'] }}]"
                                           value="{{ $opt }}"
                                           {{ $checked ? 'checked' : '' }}>
                                    <span class="choice-label">
                                        <span class="choice-dot"></span>
                                        {{ $opt }}
                                    </span>
                                </label>
                            @endforeach
                        </div>
                    @endif

                    @error("demografi.{$field['id']}")
                        <p class="mt-2 text-xs text-red-600">{{ $message }}</p>
                    @enderror
                </div>
            @endforeach

        {{-- ════════════════════════════════════════════════════════════
             KATEGORI 2-8 — DIMENSI PSIKOSOSIAL (LIKERT + ESAI)
        ════════════════════════════════════════════════════════════ --}}
        @else
            <div class="bg-white rounded-2xl shadow-sm border border-gray-100 px-8 py-6">
                <h2 class="text-lg font-bold text-gray-900">{{ $current['title'] }}</h2>
                <p class="mt-1 text-sm text-gray-500">{{ $current['subtitle'] }}</p>
            </div>

            @foreach ($current['questions'] as $q)
                @php $jawabanLama = $draft['jawaban'][$q['id']] ?? old("jawaban.{$q['id']}"); @endphp
                <div class="bg-white rounded-2xl shadow-sm border border-gray-100 px-8 py-6">
                    <p class="text-sm font-semibold text-gray-800 mb-4">
                        {{ $q['pertanyaan'] }} <span class="text-red-500">*</span>
                    </p>

                    @php
                        $likertLabels = [
                            1 => 'Sangat Tidak Setuju',
                            2 => 'Tidak Setuju',
                            3 => 'Cukup Setuju',
                            4 => 'Setuju',
                            5 => 'Sangat Setuju',
                        ];
                    @endphp
                    <div class="grid grid-cols-5 gap-2 sm:gap-3">
                        @for ($val = 1; $val <= 5; $val++)
                            <div class="flex flex-col items-center">
                                <input type="radio"
                                       id="q{{ $q['id'] }}_v{{ $val }}"
                                       name="jawaban[{{ $q['id'] }}]"
                                       value="{{ $val }}"
                                       class="likert-radio"
                                       {{ (string) $jawabanLama === (string) $val ? 'checked' : '' }}>
                                <label for="q{{ $q['id'] }}_v{{ $val }}" class="likert-label w-full">{{ $val }}</label>
                                <span class="mt-1.5 text-[11px] leading-tight text-center text-gray-400">
                                    {{ $likertLabels[$val] }}
                                </span>
                            </div>
                        @endfor
                    </div>

                    @error("jawaban.{$q['id']}")
                        <p class="mt-2 text-xs text-red-600">{{ $message }}</p>
                    @enderror
                </div>
            @endforeach

            @foreach ($current['esai'] as $e)
                <div class="bg-white rounded-2xl shadow-sm border border-gray-100 px-8 py-6">
                    <label for="esai_{{ $e['id'] }}" class="block text-sm font-semibold text-gray-800 mb-3">
                        {{ $e['pertanyaan'] }}
                    </label>
                    <textarea
                        id="esai_{{ $e['id'] }}"
                        name="esai[{{ $e['id'] }}]"
                        rows="4"
                        placeholder="Masukkan jawaban Anda"
                        class="w-full text-sm border border-gray-200 rounded-xl px-4 py-3 focus:outline-none focus:ring-2 focus:ring-brand-500 focus:border-transparent transition resize-none"
                    >{{ $draft['esai'][$e['id']] ?? old("esai.{$e['id']}") }}</textarea>
                </div>
            @endforeach
        @endif

        {{-- ════════════════════════════════════════════════════════════
             NAVIGASI
        ════════════════════════════════════════════════════════════ --}}
        <div class="bg-white rounded-2xl shadow-sm border border-gray-100 px-6 py-4 flex items-center justify-between">
            @if ($step > 1)
                <button type="submit" onclick="document.getElementById('form-action').value='prev'"
                    class="inline-flex items-center gap-1.5 text-sm font-semibold text-gray-500 hover:text-gray-700 px-4 py-2 rounded-lg transition">
                    <i data-lucide="chevron-left" class="w-4 h-4"></i> Sebelumnya
                </button>
            @else
                <span class="inline-flex items-center gap-1.5 text-sm font-semibold text-gray-300 px-4 py-2">
                    <i data-lucide="chevron-left" class="w-4 h-4"></i> Sebelumnya
                </span>
            @endif

            <span class="text-sm font-semibold text-amber-500">{{ $step }} / {{ $totalSteps }}</span>

            @if ($step < $totalSteps)
                <button type="submit" onclick="document.getElementById('form-action').value='next'"
                    class="inline-flex items-center gap-1.5 bg-brand-600 hover:bg-brand-700 active:bg-brand-800 text-white text-sm font-semibold px-6 py-2.5 rounded-xl shadow-sm transition-colors">
                    Selanjutnya <i data-lucide="chevron-right" class="w-4 h-4"></i>
                </button>
            @else
                <button type="submit"
                    class="inline-flex items-center gap-1.5 bg-brand-600 hover:bg-brand-700 active:bg-brand-800 text-white text-sm font-semibold px-6 py-2.5 rounded-xl shadow-sm transition-colors">
                    <i data-lucide="send" class="w-4 h-4"></i> Kirim Kuisioner
                </button>
            @endif
        </div>

    </form>
</div>
@endsection