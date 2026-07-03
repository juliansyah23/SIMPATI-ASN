@extends('layouts.app')

@section('title', $title)

@section('content')
    <section class="max-w-3xl mx-auto px-6 py-24 text-center">
        <span class="inline-flex items-center justify-center w-14 h-14 rounded-2xl bg-brand-100 text-brand-600 mb-6">
            <i data-lucide="construction" class="w-7 h-7"></i>
        </span>
        <h1 class="text-2xl font-bold text-gray-900">Halaman {{ $title }}</h1>
        <p class="text-gray-500 mt-2">Halaman ini belum diimplementasikan. Hubungkan ke controller dan view yang sesuai.</p>
        <a href="{{ route('dashboard') }}" class="inline-flex items-center gap-2 mt-8 px-5 h-11 rounded-lg bg-brand-600 hover:bg-brand-700 text-white text-sm font-semibold transition">
            <i data-lucide="arrow-left" class="w-4 h-4"></i> Kembali ke Beranda
        </a>
    </section>
@endsection
