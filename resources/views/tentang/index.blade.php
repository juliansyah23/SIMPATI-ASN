@extends('layouts.app')

@section('title', 'Tentang')

@section('content')

    {{-- HERO --}}
    <section class="bg-gradient-to-b from-brand-700 via-brand-600 to-brand-600 text-white">
        <div class="max-w-5xl mx-auto px-6 pt-16 pb-12 text-center">
            <h1 class="text-4xl md:text-5xl font-extrabold tracking-tight">Tentang SIMPATI ASN</h1>
            <p class="mt-4 text-lg md:text-xl font-medium text-white/90">
                Sistem Monitoring Psikososial ASN di Era WFO/WFA
            </p>
        </div>
    </section>

    <div class="max-w-5xl mx-auto px-6 py-10 space-y-8">

        {{-- Tujuan Sistem --}}
        <section class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6 md:p-8">
            <x-section-heading icon="target" title="Tujuan Sistem" />
            <div class="space-y-4 text-gray-600 leading-relaxed">
                @foreach ($tujuan as $paragraf)
                    <p>{{ $paragraf }}</p>
                @endforeach
            </div>
        </section>

        {{-- Latar Belakang --}}
        <section class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6 md:p-8">
            <x-section-heading icon="book-open" title="Latar Belakang" />
            <div class="space-y-4 text-gray-600 leading-relaxed">
                @foreach ($latarBelakang as $paragraf)
                    <p>{{ $paragraf }}</p>
                @endforeach
            </div>
        </section>

        {{-- Metodologi --}}
        <section class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6 md:p-8">
            <x-section-heading icon="users" title="Metodologi" />
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                @foreach ($metodologi as $blok)
                    @php
                        $bg = match ($blok['color']) {
                            'red'    => 'bg-red-50',
                            'green'  => 'bg-emerald-50',
                            'purple' => 'bg-purple-50',
                            'orange' => 'bg-orange-50',
                            default  => 'bg-gray-50',
                        };
                        $title = match ($blok['color']) {
                            'red'    => 'text-red-700',
                            'green'  => 'text-emerald-700',
                            'purple' => 'text-purple-700',
                            'orange' => 'text-orange-700',
                            default  => 'text-gray-700',
                        };
                        $item = match ($blok['color']) {
                            'red'    => 'text-red-600',
                            'green'  => 'text-emerald-600',
                            'purple' => 'text-purple-600',
                            'orange' => 'text-orange-600',
                            default  => 'text-gray-600',
                        };
                    @endphp
                    <div class="rounded-xl {{ $bg }} p-6">
                        <p class="font-bold {{ $title }} mb-3">{{ $blok['title'] }}</p>
                        <ul class="space-y-2">
                            @foreach ($blok['items'] as $item_text)
                                <li class="flex items-start gap-2 text-sm {{ $item }}">
                                    <span class="mt-1.5 w-1.5 h-1.5 rounded-full {{ $item }} bg-current shrink-0"></span>
                                    <span>{{ $item_text }}</span>
                                </li>
                            @endforeach
                        </ul>
                    </div>
                @endforeach
            </div>
        </section>

        {{-- Temuan Utama --}}
        <section class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6 md:p-8">
            <x-section-heading icon="award" title="Temuan Utama" />
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                @foreach ($temuanUtama as $temuan)
                    @php
                        $bg = match ($temuan['color']) {
                            'red'    => 'bg-red-50',
                            'green'  => 'bg-emerald-50',
                            'purple' => 'bg-purple-50',
                            default  => 'bg-gray-50',
                        };
                        $valueColor = match ($temuan['color']) {
                            'red'    => 'text-red-600',
                            'green'  => 'text-emerald-600',
                            'purple' => 'text-purple-600',
                            default  => 'text-gray-700',
                        };
                    @endphp
                    <div class="rounded-xl {{ $bg }} p-8 text-center">
                        <p class="text-3xl font-extrabold {{ $valueColor }}">{{ $temuan['value'] }}</p>
                        <p class="text-sm text-gray-600 mt-3 leading-relaxed">{{ $temuan['desc'] }}</p>
                    </div>
                @endforeach
            </div>
        </section>

        {{-- Kontak & Informasi --}}
        <section class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6 md:p-8">
            <h2 class="text-xl font-bold text-gray-900 mb-6">Kontak &amp; Informasi</h2>
            <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
                <div class="flex items-start gap-3">
                    <i data-lucide="mail" class="w-5 h-5 text-brand-600 mt-0.5"></i>
                    <div>
                        <p class="font-semibold text-gray-900">Email</p>
                        @foreach ($kontak['email'] as $email)
                            <a href="mailto:{{ $email }}" class="block text-sm text-gray-500 hover:text-brand-600 transition">{{ $email }}</a>
                        @endforeach
                    </div>
                </div>
                <div class="flex items-start gap-3">
                    <i data-lucide="phone" class="w-5 h-5 text-brand-600 mt-0.5"></i>
                    <div>
                        <p class="font-semibold text-gray-900">Telepon</p>
                        @foreach ($kontak['telepon'] as $telp)
                            <span class="block text-sm text-gray-500">{{ $telp }}</span>
                        @endforeach
                    </div>
                </div>
                <div class="flex items-start gap-3">
                    <i data-lucide="map-pin" class="w-5 h-5 text-brand-600 mt-0.5"></i>
                    <div>
                        <p class="font-semibold text-gray-900">Alamat</p>
                        @foreach ($kontak['alamat'] as $baris)
                            <span class="block text-sm text-gray-500">{{ $baris }}</span>
                        @endforeach
                    </div>
                </div>
            </div>
        </section>

    </div>

@endsection
