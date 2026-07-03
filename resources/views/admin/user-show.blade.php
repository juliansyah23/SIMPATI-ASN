@extends('layouts.app')

@section('title', 'Detail User')

@section('content')
<div class="max-w-4xl mx-auto px-6 py-8">

    {{-- ── Header Card ──────────────────────────────────────────────────── --}}
    <div class="bg-white rounded-2xl border border-gray-200 shadow-sm p-6 mb-6">
        <div class="flex items-start justify-between gap-4 flex-wrap">
            <div>
                <h1 class="text-2xl font-bold text-gray-900">{{ $u->name }}</h1>
                <p class="text-sm text-brand-600 mt-1 font-mono">{{ $u->nip }}</p>
            </div>

            <div class="flex items-center gap-2">
                <a href="{{ route('admin.index', ['tab' => 'users']) }}"
                   class="flex items-center gap-2 px-4 h-10 rounded-lg border border-gray-300 text-sm font-semibold text-gray-700 hover:bg-gray-50 transition">
                    <i data-lucide="arrow-left" class="w-4 h-4"></i> Kembali
                </a>
                <a href="{{ route('admin.user.edit', $u->id) }}"
                   class="flex items-center gap-2 px-5 h-10 rounded-lg bg-brand-600 hover:bg-brand-700 text-white text-sm font-semibold shadow-sm transition">
                    <i data-lucide="pencil" class="w-4 h-4"></i> Edit User
                </a>
            </div>
        </div>

        <div class="mt-3 flex items-center gap-2">
            <span class="text-xs font-medium px-2.5 py-1 rounded-full
                {{ $u->role === 'admin' ? 'bg-violet-100 text-violet-700' : 'bg-pink-100 text-pink-700' }}">
                {{ $u->role }}
            </span>
            <span class="text-xs font-medium px-2.5 py-1 rounded-full bg-green-100 text-green-700">
                active
            </span>
        </div>
    </div>

    {{-- ── Profil ──────────────────────────────────────────────────────── --}}
    <div class="bg-white rounded-2xl border border-gray-200 shadow-sm p-6 mb-6">
        <h2 class="text-base font-bold text-gray-800 mb-5">Profil</h2>

        <dl class="grid grid-cols-1 sm:grid-cols-2 gap-x-6 gap-y-5 text-sm">
            <div>
                <dt class="text-gray-500 mb-1">NIP</dt>
                <dd class="font-semibold text-gray-800 font-mono">{{ $u->nip }}</dd>
            </div>
            <div>
                <dt class="text-gray-500 mb-1">Nama Lengkap</dt>
                <dd class="font-semibold text-gray-800">{{ $u->name }}</dd>
            </div>
            <div>
                <dt class="text-gray-500 mb-1">Email</dt>
                <dd class="font-semibold text-gray-800">{{ $u->email }}</dd>
            </div>
            <div>
                <dt class="text-gray-500 mb-1">Institusi</dt>
                <dd class="font-semibold text-gray-800">{{ $u->institusi }}</dd>
            </div>
            <div>
                <dt class="text-gray-500 mb-1">Pusat Riset</dt>
                <dd class="font-semibold text-gray-800">{{ $u->pusat_riset }}</dd>
            </div>
            <div>
                <dt class="text-gray-500 mb-1">Posisi</dt>
                <dd class="font-semibold text-gray-800">{{ $u->posisi }}</dd>
            </div>
            <div>
                <dt class="text-gray-500 mb-1">Role</dt>
                <dd class="font-semibold text-gray-800 capitalize">{{ $u->role }}</dd>
            </div>
            <div>
                <dt class="text-gray-500 mb-1">Bergabung</dt>
                <dd class="font-semibold text-gray-800">{{ $u->created_at->translatedFormat('d F Y') }}</dd>
            </div>
        </dl>
    </div>

    {{-- ── Riwayat Pengisian Kuisioner ─────────────────────────────────── --}}
    <div class="bg-white rounded-2xl border border-gray-200 shadow-sm p-6">
        <h2 class="text-base font-bold text-gray-800 mb-5">Riwayat Pengisian Kuisioner</h2>

        @if ($riwayat->isEmpty())
            <div class="text-center py-10">
                <span class="inline-flex items-center justify-center w-14 h-14 rounded-full bg-gray-50 text-gray-300 mb-4">
                    <i data-lucide="inbox" class="w-6 h-6"></i>
                </span>
                <p class="text-sm font-semibold text-gray-500">Belum ada kuisioner yang diisi.</p>
            </div>
        @else
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead>
                        <tr class="border-b border-gray-100">
                            <th class="text-left py-3 px-2 text-xs font-semibold text-gray-500 uppercase tracking-wide">Kuisioner</th>
                            <th class="text-center py-3 px-2 text-xs font-semibold text-gray-500 uppercase tracking-wide">Rata-rata</th>
                            <th class="text-right py-3 px-2 text-xs font-semibold text-gray-500 uppercase tracking-wide">Dikirim</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-50">
                        @foreach ($riwayat as $r)
                            <tr class="hover:bg-gray-50 transition">
                                <td class="py-3.5 px-2 font-semibold text-gray-800">{{ $r['judul'] }}</td>
                                <td class="py-3.5 px-2 text-center font-bold text-gray-800">{{ $r['rata_rata'] }}</td>
                                <td class="py-3.5 px-2 text-right text-gray-500">{{ $r['dikirim'] }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif
    </div>

</div>
@endsection
