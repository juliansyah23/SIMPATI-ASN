@extends('layouts.app')

@section('title', 'Admin Panel')

@push('styles')
<style>
    .tab-btn { transition: color .15s, border-color .15s; }
    .tab-btn.active { color: #dc2626; border-bottom: 2px solid #dc2626; }
    .tab-btn:not(.active) { color: #6b7280; border-bottom: 2px solid transparent; }
    .tab-btn:not(.active):hover { color: #374151; }
    .tab-content { display: none; }
    .tab-content.active { display: block; }

    /* Status badges */
    .badge-active   { background:#dcfce7; color:#16a34a; }
    .badge-inactive { background:#fee2e2; color:#dc2626; }
    .badge-admin    { background:#ede9fe; color:#7c3aed; }
    .badge-employee { background:#fce7f3; color:#be185d; }
    .badge-aktif    { background:#dcfce7; color:#16a34a; }
    .badge-ditutup  { background:#fef3c7; color:#d97706; }
</style>
@endpush

@section('content')
<div class="max-w-7xl mx-auto px-6 py-8 space-y-6">

    {{-- ── Hero Banner ──────────────────────────────────────────────────── --}}
    <div class="relative bg-gradient-to-r from-brand-700 to-brand-600 rounded-2xl px-8 py-7 text-white shadow-md overflow-hidden">
        <div class="absolute right-8 top-1/2 -translate-y-1/2 opacity-20">
            <i data-lucide="settings" class="w-20 h-20"></i>
        </div>
        <h1 class="text-2xl font-extrabold">Admin Dashboard</h1>
        <p class="mt-1 text-brand-100 text-sm">Selamat datang, Admin User</p>
    </div>

    {{-- Flash --}}
    @if (session('success'))
        <div class="flex items-center gap-3 bg-green-50 border border-green-200 text-green-700 text-sm rounded-xl px-5 py-3">
            <i data-lucide="check-circle-2" class="w-4 h-4 flex-shrink-0"></i>
            {{ session('success') }}
        </div>
    @endif
    @if (session('error'))
        <div class="flex items-center gap-3 bg-red-50 border border-red-200 text-red-700 text-sm rounded-xl px-5 py-3">
            <i data-lucide="alert-circle" class="w-4 h-4 flex-shrink-0"></i>
            {{ session('error') }}
        </div>
    @endif

    {{-- ── Stat Cards ────────────────────────────────────────────────────── --}}
    <div class="grid grid-cols-2 lg:grid-cols-4 gap-4">
        @foreach ($stats as $s)
            <x-stat-card
                icon="{{ $s['icon'] }}"
                color="{{ $s['color'] }}"
                label="{{ $s['label'] }}"
                value="{{ $s['value'] }}"
                change="{{ $s['change'] }}"
                :positive="$s['positive']"
            />
        @endforeach
    </div>

    {{-- ── Tabs ──────────────────────────────────────────────────────────── --}}
    <div class="bg-white rounded-2xl shadow-sm border border-gray-100">

        {{-- Tab bar --}}
        <div class="flex border-b border-gray-100 px-6 gap-1 overflow-x-auto">
            @php
                $tabs = [
                    'overview'  => ['icon' => 'trending-up',      'label' => 'Overview'],
                    'users'     => ['icon' => 'users-2',           'label' => 'Manajemen User'],
                    'kuisioner' => ['icon' => 'file-text',         'label' => 'Kuisioner'],
                    'settings'  => ['icon' => 'settings',          'label' => 'Pengaturan'],
                ];
            @endphp
            @foreach ($tabs as $key => $t)
            <button
                type="button"
                data-tab="{{ $key }}"
                class="tab-btn flex items-center gap-2 py-4 px-4 text-sm font-semibold whitespace-nowrap {{ $tab === $key ? 'active' : '' }}">
                <i data-lucide="{{ $t['icon'] }}" class="w-4 h-4"></i>
                {{ $t['label'] }}
            </button>
            @endforeach
        </div>

        {{-- ════════════════  TAB: OVERVIEW  ════════════════ --}}
        <div id="tab-overview" class="tab-content p-6 {{ $tab === 'overview' ? 'active' : '' }}">
            <h2 class="text-base font-bold text-gray-800 mb-5">Activity Analytics</h2>
            <div class="w-full" style="height: 320px;">
                <canvas id="activityChart"></canvas>
            </div>

            {{-- Quick summary row --}}
            <div class="grid grid-cols-3 gap-4 mt-6">
                <div class="rounded-xl border border-gray-100 bg-gray-50 p-4 text-center">
                    <p class="text-xs text-gray-500 mb-1">Total Login Bulan Ini</p>
                    <p class="text-xl font-extrabold text-gray-900">{{ $quickSummary['login_bulan_ini'] }}</p>
                </div>
                <div class="rounded-xl border border-gray-100 bg-gray-50 p-4 text-center">
                    <p class="text-xs text-gray-500 mb-1">Pengisian Bulan Ini</p>
                    <p class="text-xl font-extrabold text-gray-900">{{ $quickSummary['pengisian_bulan_ini'] }}</p>
                </div>
                <div class="rounded-xl border border-gray-100 bg-gray-50 p-4 text-center">
                    <p class="text-xs text-gray-500 mb-1">User Aktif</p>
                    <p class="text-xl font-extrabold text-gray-900">{{ $quickSummary['user_aktif'] }}</p>
                </div>
            </div>
        </div>

        {{-- ════════════════  TAB: MANAJEMEN USER  ════════════════ --}}
        <div id="tab-users" class="tab-content p-6 {{ $tab === 'users' ? 'active' : '' }}">
            <div class="flex items-center justify-between mb-5">
                <h2 class="text-base font-bold text-gray-800">Manajemen User</h2>
                <button type="button"
                    class="inline-flex items-center gap-2 bg-brand-600 hover:bg-brand-700 text-white text-sm font-semibold px-4 py-2 rounded-xl shadow-sm transition-colors">
                    <i data-lucide="plus" class="w-4 h-4"></i> Tambah User
                </button>
            </div>

            {{-- Search --}}
            <div class="relative mb-5">
                <span class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none">
                    <i data-lucide="search" class="w-4 h-4 text-gray-400"></i>
                </span>
                <input
                    type="text"
                    id="userSearch"
                    placeholder="Cari user berdasarkan nama, email, atau NIP..."
                    value="{{ $searchUser }}"
                    class="w-full pl-10 pr-4 py-2.5 text-sm border border-gray-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-brand-500 focus:border-transparent transition"
                >
            </div>

            {{-- Table --}}
            <div class="overflow-x-auto">
                <table class="w-full text-sm" id="userTable">
                    <thead>
                        <tr class="border-b border-gray-100">
                            <th class="text-left py-3 px-2 text-xs font-semibold text-gray-500 uppercase tracking-wide">NIP</th>
                            <th class="text-left py-3 px-2 text-xs font-semibold text-gray-500 uppercase tracking-wide">Nama</th>
                            <th class="text-left py-3 px-2 text-xs font-semibold text-gray-500 uppercase tracking-wide">Email</th>
                            <th class="text-left py-3 px-2 text-xs font-semibold text-gray-500 uppercase tracking-wide">Role</th>
                            <th class="text-left py-3 px-2 text-xs font-semibold text-gray-500 uppercase tracking-wide">Status</th>
                            <th class="text-left py-3 px-2 text-xs font-semibold text-gray-500 uppercase tracking-wide">Bergabung</th>
                            <th class="text-right py-3 px-2 text-xs font-semibold text-gray-500 uppercase tracking-wide">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-50" id="userTableBody">
                        @foreach ($users as $u)
                        <tr class="hover:bg-gray-50 transition user-row"
                            data-name="{{ strtolower($u['name']) }}"
                            data-email="{{ strtolower($u['email']) }}"
                            data-nip="{{ $u['nip'] }}">
                            <td class="py-3.5 px-2 text-brand-600 font-mono text-xs">{{ $u['nip'] }}</td>
                            <td class="py-3.5 px-2 font-semibold text-gray-800">{{ $u['name'] }}</td>
                            <td class="py-3.5 px-2 text-gray-500">{{ $u['email'] }}</td>
                            <td class="py-3.5 px-2">
                                <span class="text-xs font-medium px-2.5 py-1 rounded-full
                                    {{ $u['role'] === 'admin' ? 'badge-admin' : 'badge-employee' }}">
                                    {{ $u['role'] }}
                                </span>
                            </td>
                            <td class="py-3.5 px-2">
                                <span class="text-xs font-medium px-2.5 py-1 rounded-full
                                    {{ $u['status'] === 'active' ? 'badge-active' : 'badge-inactive' }}">
                                    {{ $u['status'] }}
                                </span>
                            </td>
                            <td class="py-3.5 px-2 text-gray-500">{{ $u['bergabung'] }}</td>
                            <td class="py-3.5 px-2">
                                <div class="flex items-center justify-end gap-2">
                                    {{-- View --}}
                                    <a href="{{ route('admin.user.show', $u['id']) }}" title="Lihat Detail"
                                        class="text-brand-600 hover:text-brand-800 transition p-1">
                                        <i data-lucide="eye" class="w-4 h-4"></i>
                                    </a>
                                    {{-- Edit --}}
                                    <a href="{{ route('admin.user.edit', $u['id']) }}" title="Edit"
                                        class="text-green-600 hover:text-green-800 transition p-1">
                                        <i data-lucide="pencil" class="w-4 h-4"></i>
                                    </a>
                                </div>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
                <p id="noUserResult" class="hidden text-center text-sm text-gray-400 py-8">Tidak ada user yang cocok.</p>
            </div>
        </div>

        {{-- ════════════════  TAB: KUISIONER  ════════════════ --}}
        <div id="tab-kuisioner" class="tab-content p-6 {{ $tab === 'kuisioner' ? 'active' : '' }}">
            <div class="flex items-start justify-between mb-5">
                <div>
                    <h2 class="text-base font-bold text-gray-800">Manajemen Kuisioner</h2>
                    <p class="text-xs text-gray-500 mt-0.5">Buat dan kelola kuisioner seperti Google Forms</p>
                </div>
                <button type="button" disabled title="Pembuatan kuisioner baru sedang dinonaktifkan sementara"
                    class="inline-flex items-center gap-2 bg-gray-200 text-gray-400 text-sm font-semibold px-4 py-2 rounded-xl cursor-not-allowed">
                    <i data-lucide="plus" class="w-4 h-4"></i> Buat Kuisioner Baru
                </button>
            </div>

            {{-- Mini stat row --}}
            @php
                $totalK   = count($questionnaires);
                $aktifK   = collect($questionnaires)->where('status','aktif')->count();
                $totalR   = collect($questionnaires)->sum('respons');
                $ditutupK = collect($questionnaires)->where('status','ditutup')->count();
            @endphp
            <div class="grid grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
                <div class="rounded-xl bg-red-50 border border-red-100 px-4 py-4 flex items-center justify-between">
                    <div>
                        <p class="text-xs text-red-500 font-semibold">Total Kuisioner</p>
                        <p class="text-2xl font-extrabold text-gray-900 mt-1">{{ $totalK }}</p>
                    </div>
                    <i data-lucide="file-text" class="w-8 h-8 text-red-300"></i>
                </div>
                <div class="rounded-xl bg-green-50 border border-green-100 px-4 py-4 flex items-center justify-between">
                    <div>
                        <p class="text-xs text-green-600 font-semibold">Aktif</p>
                        <p class="text-2xl font-extrabold text-gray-900 mt-1">{{ $aktifK }}</p>
                    </div>
                    <i data-lucide="check-circle-2" class="w-8 h-8 text-green-300"></i>
                </div>
                <div class="rounded-xl bg-purple-50 border border-purple-100 px-4 py-4 flex items-center justify-between">
                    <div>
                        <p class="text-xs text-purple-600 font-semibold">Total Respons</p>
                        <p class="text-2xl font-extrabold text-gray-900 mt-1">{{ $totalR }}</p>
                    </div>
                    <i data-lucide="bar-chart-2" class="w-8 h-8 text-purple-300"></i>
                </div>
                <div class="rounded-xl bg-amber-50 border border-amber-100 px-4 py-4 flex items-center justify-between">
                    <div>
                        <p class="text-xs text-amber-600 font-semibold">Ditutup</p>
                        <p class="text-2xl font-extrabold text-gray-900 mt-1">{{ $ditutupK }}</p>
                    </div>
                    <i data-lucide="clock" class="w-8 h-8 text-amber-300"></i>
                </div>
            </div>

            {{-- Kuisioner cards grid --}}
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                @foreach ($questionnaires as $q)
                <div class="border-l-4 {{ $q['status'] === 'aktif' ? 'border-green-500' : 'border-gray-300' }}
                            border border-gray-100 rounded-xl p-5 flex flex-col gap-4 bg-white">

                    {{-- Card header --}}
                    <div class="flex items-start justify-between gap-3">
                        <div class="flex-1">
                            <p class="text-sm font-bold text-gray-900 leading-snug">{{ $q['judul'] }}</p>
                            <div class="flex flex-wrap items-center gap-2 mt-1.5">
                                <span class="text-xs font-medium px-2 py-0.5 rounded-full
                                    {{ $q['status'] === 'aktif' ? 'badge-aktif' : 'badge-ditutup' }}
                                    flex items-center gap-1">
                                    @if ($q['status'] === 'aktif')
                                        <i data-lucide="check-circle-2" class="w-3 h-3"></i> Aktif
                                    @else
                                        <i data-lucide="clock" class="w-3 h-3"></i> Ditutup
                                    @endif
                                </span>
                                <span class="text-xs text-gray-400">Tahun {{ $q['tahun'] }}</span>
                            </div>
                        </div>
                        <i data-lucide="file-text" class="w-5 h-5 text-gray-300 flex-shrink-0"></i>
                    </div>

                    {{-- Stats row --}}
                    <div class="grid grid-cols-2 gap-3 text-xs">
                        <div>
                            <p class="text-gray-400">Respons</p>
                            <p class="font-bold text-gray-800 text-base mt-0.5">{{ $q['respons'] }}</p>
                        </div>
                        <div>
                            <p class="text-gray-400">Dibuat</p>
                            <p class="font-semibold text-gray-700 mt-0.5">{{ $q['dibuat'] }}</p>
                        </div>
                    </div>

                    {{-- Primary actions --}}
                    <div class="flex gap-2">
                        <a href="{{ route('admin.kuisioner.edit', $q['id']) }}"
                            class="flex-1 inline-flex items-center justify-center gap-1.5 border border-brand-200 text-brand-600 hover:bg-brand-50 text-xs font-semibold py-2 rounded-lg transition-colors">
                            <i data-lucide="pencil" class="w-3.5 h-3.5"></i> Edit
                        </a>
                        <a href="{{ route('admin.kuisioner.respons', $q['id']) }}"
                            class="flex-1 inline-flex items-center justify-center gap-1.5 border border-purple-200 text-purple-600 hover:bg-purple-50 text-xs font-semibold py-2 rounded-lg transition-colors">
                            <i data-lucide="bar-chart-2" class="w-3.5 h-3.5"></i> Respons
                        </a>
                    </div>

                    {{-- Secondary actions --}}
                    <div class="flex items-center justify-between pt-1 border-t border-gray-50">
                        <div class="flex items-center gap-2">
                            {{-- Duplicate --}}
                            <button type="button" title="Duplikat"
                                class="text-gray-400 hover:text-gray-600 transition p-1">
                                <i data-lucide="copy" class="w-4 h-4"></i>
                            </button>
                            {{-- Download --}}
                            <button type="button" title="Unduh"
                                class="text-gray-400 hover:text-gray-600 transition p-1">
                                <i data-lucide="download" class="w-4 h-4"></i>
                            </button>
                            {{-- Delete --}}
                            <form method="POST" action="{{ route('admin.kuisioner.delete', $q['id']) }}"
                                onsubmit="return confirm('Hapus kuisioner ini?')">
                                @csrf @method('DELETE')
                                <button type="submit" title="Hapus"
                                    class="text-red-400 hover:text-red-600 transition p-1">
                                    <i data-lucide="trash-2" class="w-4 h-4"></i>
                                </button>
                            </form>
                        </div>

                        {{-- Toggle status --}}
                        <form method="POST" action="{{ route('admin.kuisioner.toggle', $q['id']) }}">
                            @csrf
                            <input type="hidden" name="action" value="{{ $q['status'] === 'aktif' ? 'tutup' : 'buka' }}">
                            <button type="submit"
                                class="text-xs font-semibold px-3 py-1.5 rounded-lg transition-colors
                                    {{ $q['status'] === 'aktif'
                                        ? 'bg-amber-100 text-amber-700 hover:bg-amber-200'
                                        : 'bg-green-100 text-green-700 hover:bg-green-200' }}">
                                {{ $q['status'] === 'aktif' ? 'Tutup' : 'Buka' }}
                            </button>
                        </form>
                    </div>
                </div>
                @endforeach
            </div>
        </div>

        {{-- ════════════════  TAB: PENGATURAN  ════════════════ --}}
        <div id="tab-settings" class="tab-content p-6 {{ $tab === 'settings' ? 'active' : '' }}">
            <h2 class="text-base font-bold text-gray-800 mb-6">Pengaturan Sistem</h2>

            <div class="space-y-5 max-w-xl">
                {{-- Nama Sistem --}}
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1.5">Nama Sistem</label>
                    <input type="text" value="SIMPATI ASN"
                        class="w-full px-4 py-2.5 text-sm border border-gray-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-brand-500 transition">
                </div>
                {{-- Institusi --}}
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1.5">Institusi</label>
                    <input type="text" value="BRIN"
                        class="w-full px-4 py-2.5 text-sm border border-gray-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-brand-500 transition">
                </div>
                {{-- Domain email --}}
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1.5">Domain Email yang Diizinkan</label>
                    <input type="text" value="brin.go.id"
                        class="w-full px-4 py-2.5 text-sm border border-gray-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-brand-500 transition">
                </div>
                {{-- Toggle registrasi --}}
                <div class="flex items-center justify-between py-3 border-t border-gray-100">
                    <div>
                        <p class="text-sm font-medium text-gray-700">Izinkan Registrasi Publik</p>
                        <p class="text-xs text-gray-400 mt-0.5">Pegawai dapat mendaftar sendiri tanpa perlu diundang admin</p>
                    </div>
                    <label class="relative inline-flex items-center cursor-pointer">
                        <input type="checkbox" class="sr-only peer" checked>
                        <div class="w-10 h-5 bg-gray-200 rounded-full peer peer-checked:bg-brand-600
                                    peer-focus:outline-none transition-colors duration-200 after:content-['']
                                    after:absolute after:top-0.5 after:left-0.5 after:bg-white after:rounded-full
                                    after:h-4 after:w-4 after:transition-all peer-checked:after:translate-x-5"></div>
                    </label>
                </div>
                {{-- Save --}}
                <button type="button"
                    class="inline-flex items-center gap-2 bg-brand-600 hover:bg-brand-700 text-white text-sm font-semibold px-6 py-2.5 rounded-xl shadow-sm transition-colors">
                    <i data-lucide="save" class="w-4 h-4"></i> Simpan Pengaturan
                </button>
            </div>
        </div>

    </div>{{-- /tabs wrapper --}}
</div>
@endsection

@push('scripts')
<script>
// ── Tab switching ──────────────────────────────────────────────────────────
(function () {
    const btns     = document.querySelectorAll('.tab-btn');
    const contents = document.querySelectorAll('.tab-content');

    btns.forEach(btn => {
        btn.addEventListener('click', () => {
            btns.forEach(b => b.classList.remove('active'));
            contents.forEach(c => c.classList.remove('active'));
            btn.classList.add('active');
            document.getElementById('tab-' + btn.dataset.tab).classList.add('active');

            // Re-render lucide icons inside newly-shown tabs
            if (window.lucide) lucide.createIcons();
        });
    });
})();

// ── Activity Chart ─────────────────────────────────────────────────────────
(function () {
    const ctx = document.getElementById('activityChart');
    if (!ctx) return;

    new Chart(ctx, {
        type: 'bar',
        data: {
            labels: {!! json_encode($activityChart['labels']) !!},
            datasets: [
                {
                    label: 'Total Logins',
                    data: {!! json_encode($activityChart['logins']) !!},
                    backgroundColor: '#3b82f6',
                    borderRadius: 6,
                    barPercentage: 0.5,
                },
                {
                    label: 'Submissions',
                    data: {!! json_encode($activityChart['submissions']) !!},
                    backgroundColor: '#10b981',
                    borderRadius: 6,
                    barPercentage: 0.5,
                },
            ],
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: { legend: { position: 'bottom', labels: { usePointStyle: true, pointStyle: 'rect' } } },
            scales: {
                x: { grid: { display: false } },
                y: { beginAtZero: true, grid: { color: '#f3f4f6' } },
            },
        },
    });
})();

// ── Live user search ───────────────────────────────────────────────────────
(function () {
    const input   = document.getElementById('userSearch');
    const rows    = document.querySelectorAll('.user-row');
    const noResult= document.getElementById('noUserResult');
    if (!input) return;

    input.addEventListener('input', () => {
        const q = input.value.toLowerCase().trim();
        let visible = 0;
        rows.forEach(row => {
            const match = !q
                || row.dataset.name.includes(q)
                || row.dataset.email.includes(q)
                || row.dataset.nip.includes(q);
            row.style.display = match ? '' : 'none';
            if (match) visible++;
        });
        noResult.classList.toggle('hidden', visible > 0);
    });
})();
</script>
@endpush