@extends('layouts.app')

@section('title', 'Data')

@section('content')

    <div class="max-w-7xl mx-auto px-6 pt-10 pb-16">

        {{-- Page heading --}}
        <h1 class="text-3xl font-extrabold text-gray-900">Data Hasil Kuisioner Psikososial</h1>
        <p class="text-gray-500 mt-2">Analisis komprehensif kondisi psikososial ASN berdasarkan hasil kuisioner</p>

        {{-- Filter Data --}}
        <form method="GET" action="{{ route('data') }}"
              class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6 md:p-8 mt-8">
            <div class="flex items-center gap-2 mb-5">
                <i data-lucide="filter" class="w-5 h-5 text-gray-700"></i>
                <h2 class="text-lg font-bold text-gray-900">Filter Data</h2>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-5 gap-4">
                <select name="tahun" onchange="this.form.submit()"
                        class="h-12 rounded-lg border border-gray-300 px-4 text-sm font-medium text-gray-700 focus:outline-none focus:ring-2 focus:ring-brand-500">
                    @foreach ($filters['tahun'] as $tahun)
                        @php $val = $tahun === 'Semua Tahun' ? '' : $tahun; @endphp
                        <option value="{{ $val }}" @selected(($selected['tahun'] ?? '') === $val)>{{ $tahun }}</option>
                    @endforeach
                </select>

                <select name="pusat_riset" onchange="this.form.submit()"
                        class="h-12 rounded-lg border border-gray-300 px-4 text-sm font-medium text-gray-700 focus:outline-none focus:ring-2 focus:ring-brand-500">
                    @foreach ($filters['pusat_riset'] as $value => $label)
                        <option value="{{ $value === 'Semua Pusat Riset' ? '' : $value }}"
                                @selected(($selected['pusat_riset'] ?? '') === ($value === 'Semua Pusat Riset' ? '' : $value))>
                            {{ $label }}
                        </option>
                    @endforeach
                </select>

                <select name="posisi" onchange="this.form.submit()"
                        class="h-12 rounded-lg border border-gray-300 px-4 text-sm font-medium text-gray-700 focus:outline-none focus:ring-2 focus:ring-brand-500">
                    @foreach ($filters['posisi'] as $value => $label)
                        <option value="{{ $value === 'Semua Posisi' ? '' : $value }}"
                                @selected(($selected['posisi'] ?? '') === ($value === 'Semua Posisi' ? '' : $value))>
                            {{ $label }}
                        </option>
                    @endforeach
                </select>

                <select name="mode_kerja" onchange="this.form.submit()"
                        class="h-12 rounded-lg border border-gray-300 px-4 text-sm font-medium text-gray-700 focus:outline-none focus:ring-2 focus:ring-brand-500">
                    @foreach ($filters['mode_kerja'] as $value => $label)
                        <option value="{{ $value === 'Semua Mode Kerja' ? '' : $value }}"
                                @selected(($selected['mode_kerja'] ?? '') === ($value === 'Semua Mode Kerja' ? '' : $value))>
                            {{ $label }}
                        </option>
                    @endforeach
                </select>

                <div class="relative">
                    <i data-lucide="search" class="w-4 h-4 text-gray-400 absolute left-4 top-1/2 -translate-y-1/2"></i>
                    <input type="text" name="q" value="{{ $selected['q'] ?? '' }}" placeholder="Cari NIP/Nama"
                           class="w-full h-12 rounded-lg border border-gray-300 pl-11 pr-4 text-sm text-gray-700 focus:outline-none focus:ring-2 focus:ring-brand-500">
                </div>
            </div>
        </form>

        {{-- Stat cards --}}
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6 mt-8">
            @foreach ($stats as $stat)
                <x-data-stat-card
                    :icon="$stat['icon']"
                    :color="$stat['color']"
                    :label="$stat['label']"
                    :value="$stat['value']"
                    :unit="$stat['unit']"
                    :subtitle="$stat['subtitle']"
                />
            @endforeach
        </div>

        {{-- Row: Bar chart + Pie chart --}}
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mt-8">
            <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6">
                <h3 class="text-base font-bold text-gray-900 mb-4">Perbandingan Mode Kerja</h3>
                <div class="relative h-72"><canvas id="modeKerjaChart"></canvas></div>
                <div class="flex flex-wrap gap-x-6 gap-y-2 mt-4 text-sm text-gray-600">
                    @foreach ($modeKerjaChart['datasets'] as $ds)
                        <span class="flex items-center gap-2">
                            <span class="w-3 h-3 rounded-sm" style="background-color: {{ $ds['color'] }}"></span>{{ $ds['label'] }}
                        </span>
                    @endforeach
                </div>
            </div>

            <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6">
                <h3 class="text-base font-bold text-gray-900 mb-4">Distribusi Respons per Pusat Riset</h3>
                <div class="relative h-72"><canvas id="pieChart"></canvas></div>
            </div>
        </div>

        {{-- Row: Radar chart + Line chart --}}
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mt-8">
            <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6">
                <h3 class="text-base font-bold text-gray-900 mb-4">Profil Psikososial WFA vs WFO</h3>
                <div class="relative h-80"><canvas id="radarChart"></canvas></div>
                <div class="flex justify-center gap-6 mt-4 text-sm text-gray-600">
                    @foreach ($radarChart['datasets'] as $ds)
                        <span class="flex items-center gap-2">
                            <span class="w-3 h-3 rounded-sm" style="background-color: {{ $ds['color'] }}"></span>{{ $ds['label'] }}
                        </span>
                    @endforeach
                </div>
            </div>

            <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6">
                <h3 class="text-base font-bold text-gray-900 mb-4">Tren Psikososial {{ ($selected['tahun'] ?? '') ?: 'Semua Tahun' }}</h3>
                <div class="relative h-80"><canvas id="trendChart"></canvas></div>
                <div class="flex flex-wrap justify-center gap-x-6 gap-y-2 mt-4 text-sm text-gray-600">
                    @foreach ($trendChart['datasets'] as $ds)
                        <span class="flex items-center gap-2">
                            <span class="w-3 h-3 rounded-sm" style="background-color: {{ $ds['color'] }}"></span>{{ $ds['label'] }}
                        </span>
                    @endforeach
                </div>
            </div>
        </div>

        {{-- Performa per Pusat Riset --}}
        <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6 md:p-8 mt-8">
            <div class="flex items-center justify-between mb-5">
                <h2 class="text-lg font-bold text-gray-900">Performa per Pusat Riset</h2>
                <div class="flex items-center gap-2">
                    <a href="{{ route('data.export.pusatRiset.excel', array_filter($selected ?? [])) }}"
                       class="inline-flex items-center gap-2 px-4 h-11 rounded-lg border border-gray-200 hover:bg-gray-50 text-gray-700 text-sm font-semibold transition">
                        <i data-lucide="file-spreadsheet" class="w-4 h-4 text-emerald-600"></i> Excel
                    </a>
                    <a href="{{ route('data.export.pusatRiset.pdf', array_filter($selected ?? [])) }}" target="_blank"
                       class="inline-flex items-center gap-2 px-4 h-11 rounded-lg bg-brand-600 hover:bg-brand-700 text-white text-sm font-semibold shadow-sm transition">
                        <i data-lucide="file-text" class="w-4 h-4"></i> PDF
                    </a>
                </div>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead class="text-gray-500 border-b border-gray-100">
                        <tr>
                            <th class="text-left font-semibold px-3 py-3">Pusat Riset</th>
                            <th class="text-center font-semibold px-3 py-3">Respons</th>
                            <th class="text-center font-semibold px-3 py-3">Produktivitas</th>
                            <th class="text-center font-semibold px-3 py-3">Kolaborasi</th>
                            <th class="text-center font-semibold px-3 py-3">Work-Life Balance</th>
                            <th class="text-center font-semibold px-3 py-3">Stres</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-50">
                        @foreach ($centers as $center)
                            <tr>
                                <td class="px-3 py-4 font-semibold text-gray-800">{{ $center['name'] }}</td>
                                <td class="px-3 py-4 text-center text-blue-600 font-semibold">{{ $center['respons'] }}</td>
                                <td class="px-3 py-4 text-center">
                                    <span class="inline-flex px-3 py-1 rounded-full text-xs font-bold {{ $center['produktivitas'] >= 4.5 ? 'bg-emerald-100 text-emerald-700' : 'bg-amber-100 text-amber-700' }}">
                                        {{ number_format($center['produktivitas'], 1) }}
                                    </span>
                                </td>
                                <td class="px-3 py-4 text-center">
                                    <span class="inline-flex px-3 py-1 rounded-full text-xs font-bold {{ $center['kolaborasi'] >= 4.5 ? 'bg-emerald-100 text-emerald-700' : 'bg-amber-100 text-amber-700' }}">
                                        {{ number_format($center['kolaborasi'], 1) }}
                                    </span>
                                </td>
                                <td class="px-3 py-4 text-center">
                                    <span class="inline-flex px-3 py-1 rounded-full text-xs font-bold {{ $center['wlb'] >= 4.5 ? 'bg-emerald-100 text-emerald-700' : 'bg-amber-100 text-amber-700' }}">
                                        {{ number_format($center['wlb'], 1) }}
                                    </span>
                                </td>
                                <td class="px-3 py-4 text-center">
                                    <span class="inline-flex px-3 py-1 rounded-full text-xs font-bold {{ $center['stres'] <= 2.0 ? 'bg-emerald-100 text-emerald-700' : 'bg-orange-100 text-orange-700' }}">
                                        {{ number_format($center['stres'], 1) }}
                                    </span>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>

        {{-- Data Detail Responden --}}
        @auth
                @if(auth()->user()->isAdmin())
        <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6 md:p-8 mt-8">
            <div class="flex items-center justify-between mb-6">
                <h2 class="text-lg font-bold text-gray-900">Data Detail Responden</h2>
                <div class="flex items-center gap-2">
                    <a href="{{ route('data.export.responden.excel', array_filter($selected ?? [])) }}"
                       class="inline-flex items-center gap-2 px-4 h-11 rounded-lg border border-gray-200 hover:bg-gray-50 text-gray-700 text-sm font-semibold transition">
                        <i data-lucide="file-spreadsheet" class="w-4 h-4 text-emerald-600"></i> Excel
                    </a>
                    <a href="{{ route('data.export.responden.pdf', array_filter($selected ?? [])) }}" target="_blank"
                       class="inline-flex items-center gap-2 px-4 h-11 rounded-lg bg-brand-600 hover:bg-brand-700 text-white text-sm font-semibold shadow-sm transition">
                        <i data-lucide="file-text" class="w-4 h-4"></i> PDF
                    </a>
                </div>
            </div>

            
                    @if(count($detailResponden) > 0)
                        <div class="overflow-x-auto">
                            <table class="w-full text-xs">
                                <thead class="text-gray-500 border-b border-gray-100 bg-gray-50">
                                    <tr>
                                        <th class="text-left font-semibold px-3 py-3 whitespace-nowrap">NIP</th>
                                        <th class="text-left font-semibold px-3 py-3 whitespace-nowrap">Nama</th>
                                        <th class="text-left font-semibold px-3 py-3 whitespace-nowrap">Pusat Riset</th>
                                        <th class="text-left font-semibold px-3 py-3 whitespace-nowrap">Posisi</th>
                                        <th class="text-center font-semibold px-3 py-3 whitespace-nowrap">Kelamin</th>
                                        <th class="text-center font-semibold px-3 py-3 whitespace-nowrap">Usia</th>
                                        <th class="text-center font-semibold px-3 py-3 whitespace-nowrap">Lama Kerja</th>
                                        <th class="text-center font-semibold px-3 py-3 whitespace-nowrap">Mode Kerja</th>
                                        <th class="text-center font-semibold px-3 py-3 whitespace-nowrap">I. Kebijakan</th>
                                        <th class="text-center font-semibold px-3 py-3 whitespace-nowrap">II. Motivasi</th>
                                        <th class="text-center font-semibold px-3 py-3 whitespace-nowrap">III. Kepuasan</th>
                                        <th class="text-center font-semibold px-3 py-3 whitespace-nowrap">IV. Engagement</th>
                                        <th class="text-center font-semibold px-3 py-3 whitespace-nowrap">V. Stres</th>
                                        <th class="text-center font-semibold px-3 py-3 whitespace-nowrap">VI. Dukungan</th>
                                        <th class="text-center font-semibold px-3 py-3 whitespace-nowrap">VII. WLB</th>
                                        <th class="text-center font-semibold px-3 py-3 whitespace-nowrap">Rata-rata</th>
                                        <th class="text-center font-semibold px-3 py-3 whitespace-nowrap">Tgl Submit</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-gray-50">
                                    @foreach ($detailResponden as $row)
                                        <tr class="hover:bg-gray-50">
                                            <td class="px-3 py-3 font-mono text-gray-600 whitespace-nowrap">{{ $row['nip'] }}</td>
                                            <td class="px-3 py-3 font-semibold text-gray-800 whitespace-nowrap">{{ $row['nama'] }}</td>
                                            <td class="px-3 py-3 text-gray-600 whitespace-nowrap max-w-[160px] truncate" title="{{ $row['pusat_riset'] }}">{{ $row['pusat_riset'] }}</td>
                                            <td class="px-3 py-3 text-gray-600 whitespace-nowrap">{{ $row['posisi'] }}</td>
                                            <td class="px-3 py-3 text-center text-gray-600">{{ $row['jenis_kelamin'] }}</td>
                                            <td class="px-3 py-3 text-center text-gray-600 whitespace-nowrap">{{ $row['usia'] }}</td>
                                            <td class="px-3 py-3 text-center text-gray-600 whitespace-nowrap">{{ $row['lama_bekerja'] }}</td>
                                            <td class="px-3 py-3 text-center">
                                                <span class="inline-flex px-2 py-0.5 rounded-full text-xs font-semibold
                                                    {{ $row['mode_kerja'] === 'WFA' ? 'bg-blue-100 text-blue-700' : ($row['mode_kerja'] === 'WFO' ? 'bg-green-100 text-green-700' : 'bg-amber-100 text-amber-700') }}">
                                                    {{ $row['mode_kerja'] }}
                                                </span>
                                            </td>
                                            @foreach ($row['per_kategori'] as $kode => $skor)
                                                <td class="px-3 py-3 text-center">
                                                    @if($skor !== '-')
                                                        <span class="font-semibold {{ (float)$skor >= 4.0 ? 'text-emerald-600' : ((float)$skor >= 3.0 ? 'text-amber-600' : 'text-red-500') }}">
                                                            {{ $skor }}
                                                        </span>
                                                    @else
                                                        <span class="text-gray-300">-</span>
                                                    @endif
                                                </td>
                                            @endforeach
                                            <td class="px-3 py-3 text-center font-bold text-gray-800">{{ $row['rata_rata'] }}</td>
                                            <td class="px-3 py-3 text-center text-gray-500 whitespace-nowrap">{{ $row['submitted_at'] }}</td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                        <p class="text-xs text-gray-400 mt-4">Menampilkan {{ count($detailResponden) }} responden sesuai filter aktif.</p>
                    @else
                        <div class="text-center py-12">
                            <span class="inline-flex items-center justify-center w-14 h-14 rounded-full bg-gray-50 text-gray-300 mb-4">
                                <i data-lucide="inbox" class="w-6 h-6"></i>
                            </span>
                            <p class="text-sm font-semibold text-gray-500">Belum ada data responden</p>
                            <p class="text-xs text-gray-400 mt-1">Coba ubah filter atau tunggu pengisian kuisioner.</p>
                        </div>
                    @endif
            
        </div>
        @endif
        @endauth
    </div>

@endsection

@push('scripts')
    <script>
        // ---- Bar: Perbandingan Mode Kerja ----
        new Chart(document.getElementById('modeKerjaChart'), {
            type: 'bar',
            data: {
                labels: @json($modeKerjaChart['labels']),
                datasets: @json($modeKerjaChart['datasets']).map(ds => ({
                    label: ds.label,
                    data: ds.data,
                    backgroundColor: ds.color,
                    borderRadius: 6,
                })),
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: { legend: { display: false } },
                scales: { y: { beginAtZero: true, max: 5 } },
            },
        });

        // ---- Pie: Distribusi Respons per Pusat Riset ----
        new Chart(document.getElementById('pieChart'), {
            type: 'pie',
            data: {
                labels: @json($pieChart['labels']),
                datasets: [{
                    data: @json($pieChart['data']),
                    backgroundColor: @json($pieChart['colors']),
                    borderWidth: 2,
                    borderColor: '#fff',
                }],
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: { display: false },
                    tooltip: {
                        callbacks: {
                            label: (ctx) => `${ctx.label}: ${ctx.raw} responden`,
                        },
                    },
                },
            },
        });

        // ---- Radar: Profil Psikososial WFA vs WFO ----
        new Chart(document.getElementById('radarChart'), {
            type: 'radar',
            data: {
                labels: @json($radarChart['labels']),
                datasets: @json($radarChart['datasets']).map(ds => ({
                    label: ds.label,
                    data: ds.data,
                    borderColor: ds.color,
                    backgroundColor: ds.color + '55',
                    pointBackgroundColor: ds.color,
                })),
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: { legend: { display: false } },
                scales: { r: { beginAtZero: true, max: 5 } },
            },
        });

        // ---- Line: Tren Psikososial ----
        new Chart(document.getElementById('trendChart'), {
            type: 'line',
            data: {
                labels: @json($trendChart['labels']),
                datasets: @json($trendChart['datasets']).map(ds => ({
                    label: ds.label,
                    data: ds.data,
                    borderColor: ds.color,
                    backgroundColor: ds.color,
                    tension: 0.35,
                    pointRadius: 4,
                })),
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: { legend: { display: false } },
                scales: { y: { beginAtZero: true, max: 5 } },
            },
        });
    </script>
@endpush