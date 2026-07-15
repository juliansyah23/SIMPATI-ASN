@extends('layouts.app')

@section('title', 'Data')

@section('content')

    <div class="max-w-7xl mx-auto px-6 pt-10 pb-16">

        {{-- Page heading --}}
        <h1 class="text-3xl font-extrabold text-gray-900">Data Hasil Kuisioner Psikososial</h1>
        <p class="text-gray-500 mt-2">Analisis komprehensif kondisi psikososial ASN berdasarkan hasil kuisioner</p>

        {{-- indikator loading kecil saat AJAX jalan --}}
        <div id="data-loading" class="hidden fixed top-4 right-4 z-50 bg-gray-900 text-white text-xs font-semibold px-4 py-2 rounded-lg shadow-lg">
            Memuat data...
        </div>

        {{-- Filter Data --}}
        <form id="data-filter-form"
              class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6 md:p-8 mt-8">
            <div class="flex items-center gap-2 mb-5">
                <i data-lucide="filter" class="w-5 h-5 text-gray-700"></i>
                <h2 class="text-lg font-bold text-gray-900">Filter Data</h2>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-5 gap-4">
                <select name="tahun"
                        class="h-12 rounded-lg border border-gray-300 px-4 text-sm font-medium text-gray-700 focus:outline-none focus:ring-2 focus:ring-brand-500">
                    @foreach ($filters['tahun'] as $tahun)
                        @php $val = $tahun === 'Semua Tahun' ? '' : $tahun; @endphp
                        <option value="{{ $val }}" @selected(($selected['tahun'] ?? '') === $val)>{{ $tahun }}</option>
                    @endforeach
                </select>

                <select name="pusat_riset"
                        class="h-12 rounded-lg border border-gray-300 px-4 text-sm font-medium text-gray-700 focus:outline-none focus:ring-2 focus:ring-brand-500">
                    @foreach ($filters['pusat_riset'] as $value => $label)
                        <option value="{{ $value === 'Semua Pusat Riset' ? '' : $value }}"
                                @selected(($selected['pusat_riset'] ?? '') === ($value === 'Semua Pusat Riset' ? '' : $value))>
                            {{ $label }}
                        </option>
                    @endforeach
                </select>

                <select name="posisi"
                        class="h-12 rounded-lg border border-gray-300 px-4 text-sm font-medium text-gray-700 focus:outline-none focus:ring-2 focus:ring-brand-500">
                    @foreach ($filters['posisi'] as $value => $label)
                        <option value="{{ $value === 'Semua Posisi' ? '' : $value }}"
                                @selected(($selected['posisi'] ?? '') === ($value === 'Semua Posisi' ? '' : $value))>
                            {{ $label }}
                        </option>
                    @endforeach
                </select>

                <select name="mode_kerja"
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
        <div id="stats-container" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6 mt-8">
            @include('data.partials.stat-cards', ['stats' => $stats])
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
                <h3 id="trend-title" class="text-base font-bold text-gray-900 mb-4">Tren Psikososial {{ ($selected['tahun'] ?? '') ?: 'Semua Tahun' }}</h3>
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
                    <a id="export-pusatriset-excel" href="{{ route('data.export.pusatRiset.excel', array_filter($selected ?? [])) }}"
                       class="inline-flex items-center gap-2 px-4 h-11 rounded-lg border border-gray-200 hover:bg-gray-50 text-gray-700 text-sm font-semibold transition">
                        <i data-lucide="file-spreadsheet" class="w-4 h-4 text-emerald-600"></i> Excel
                    </a>
                    <a id="export-pusatriset-pdf" href="{{ route('data.export.pusatRiset.pdf', array_filter($selected ?? [])) }}" target="_blank"
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
                    <tbody id="centers-table-body" class="divide-y divide-gray-50">
                        @include('data.partials.centers-table', ['centers' => $centers])
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
                    <a id="export-responden-excel" href="{{ route('data.export.responden.excel', array_filter($selected ?? [])) }}"
                       class="inline-flex items-center gap-2 px-4 h-11 rounded-lg border border-gray-200 hover:bg-gray-50 text-gray-700 text-sm font-semibold transition">
                        <i data-lucide="file-spreadsheet" class="w-4 h-4 text-emerald-600"></i> Excel
                    </a>
                    <a id="export-responden-pdf" href="{{ route('data.export.responden.pdf', array_filter($selected ?? [])) }}" target="_blank"
                       class="inline-flex items-center gap-2 px-4 h-11 rounded-lg bg-brand-600 hover:bg-brand-700 text-white text-sm font-semibold shadow-sm transition">
                        <i data-lucide="file-text" class="w-4 h-4"></i> PDF
                    </a>
                </div>
            </div>

            <div id="detail-responden-container">
                @include('data.partials.detail-table', ['detailResponden' => $detailResponden])
            </div>
        </div>
        @endif
        @endauth
    </div>

@endsection

@push('scripts')
    <script>
        const initialCharts = {
            modeKerjaChart: @json($modeKerjaChart),
            pieChart:       @json($pieChart),
            radarChart:     @json($radarChart),
            trendChart:     @json($trendChart),
        };

        // ---- Bar: Perbandingan Mode Kerja ----
        const modeKerjaChartInstance = new Chart(document.getElementById('modeKerjaChart'), {
            type: 'bar',
            data: {
                labels: initialCharts.modeKerjaChart.labels,
                datasets: initialCharts.modeKerjaChart.datasets.map(ds => ({
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
        const pieChartInstance = new Chart(document.getElementById('pieChart'), {
            type: 'pie',
            data: {
                labels: initialCharts.pieChart.labels,
                datasets: [{
                    data: initialCharts.pieChart.data,
                    backgroundColor: initialCharts.pieChart.colors,
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
        const radarChartInstance = new Chart(document.getElementById('radarChart'), {
            type: 'radar',
            data: {
                labels: initialCharts.radarChart.labels,
                datasets: initialCharts.radarChart.datasets.map(ds => ({
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
        const trendChartInstance = new Chart(document.getElementById('trendChart'), {
            type: 'line',
            data: {
                labels: initialCharts.trendChart.labels,
                datasets: initialCharts.trendChart.datasets.map(ds => ({
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

        // ─────────────────────────────────────────────────────────────
        //  AJAX: ambil ulang data setiap filter berubah, tanpa reload halaman
        // ─────────────────────────────────────────────────────────────
        const filterForm   = document.getElementById('data-filter-form');
        const loadingBadge = document.getElementById('data-loading');
        const dataUrl      = '{{ route('data.ajax') }}';

        async function refreshData() {
            const params = new URLSearchParams(new FormData(filterForm)).toString();
            loadingBadge.classList.remove('hidden');

            try {
                const res = await fetch(`${dataUrl}?${params}`, {
                    headers: { 'X-Requested-With': 'XMLHttpRequest' },
                });
                if (!res.ok) throw new Error('Request gagal');
                const json = await res.json();

                // Stat cards, tabel pusat riset, tabel detail responden (HTML partial dari server)
                document.getElementById('stats-container').innerHTML = json.statsHtml;
                document.getElementById('centers-table-body').innerHTML = json.centersHtml;
                const detailContainer = document.getElementById('detail-responden-container');
                if (detailContainer && json.detailHtml !== null) {
                    detailContainer.innerHTML = json.detailHtml;
                }

                // Judul chart tren ikut update sesuai filter tahun
                const trendTitle = document.getElementById('trend-title');
                if (trendTitle) trendTitle.textContent = json.trendTitle;

                // Update 4 chart Chart.js
                modeKerjaChartInstance.data.labels = json.modeKerjaChart.labels;
                modeKerjaChartInstance.data.datasets.forEach((ds, i) => ds.data = json.modeKerjaChart.datasets[i].data);
                modeKerjaChartInstance.update();

                pieChartInstance.data.labels = json.pieChart.labels;
                pieChartInstance.data.datasets[0].data = json.pieChart.data;
                pieChartInstance.update();

                radarChartInstance.data.labels = json.radarChart.labels;
                radarChartInstance.data.datasets.forEach((ds, i) => ds.data = json.radarChart.datasets[i].data);
                radarChartInstance.update();

                trendChartInstance.data.labels = json.trendChart.labels;
                trendChartInstance.data.datasets.forEach((ds, i) => ds.data = json.trendChart.datasets[i].data);
                trendChartInstance.update();

                // Tombol export ikut bawa filter yang sedang aktif
                const exportLinks = {
                    'export-pusatriset-excel': '{{ route('data.export.pusatRiset.excel') }}',
                    'export-pusatriset-pdf':   '{{ route('data.export.pusatRiset.pdf') }}',
                    'export-responden-excel':  '{{ route('data.export.responden.excel') }}',
                    'export-responden-pdf':    '{{ route('data.export.responden.pdf') }}',
                };
                Object.entries(exportLinks).forEach(([id, base]) => {
                    const el = document.getElementById(id);
                    if (el) el.href = json.exportQuery ? `${base}?${json.exportQuery}` : base;
                });

                // Render ulang ikon lucide di dalam HTML yang baru disuntikkan
                if (window.lucide) lucide.createIcons();
            } catch (err) {
                console.error('Gagal memuat data:', err);
            } finally {
                loadingBadge.classList.add('hidden');
            }
        }

        // Dropdown langsung memicu refresh
        filterForm.querySelectorAll('select').forEach(el => {
            el.addEventListener('change', refreshData);
        });

        // Input pencarian pakai debounce 400ms supaya tidak fetch di setiap ketikan
        let searchTimeout;
        filterForm.querySelector('input[name="q"]').addEventListener('input', () => {
            clearTimeout(searchTimeout);
            searchTimeout = setTimeout(refreshData, 400);
        });

        // Cegah submit form biasa (misalnya kalau user pencet Enter di kolom pencarian)
        filterForm.addEventListener('submit', (e) => {
            e.preventDefault();
            refreshData();
        });
    </script>
@endpush