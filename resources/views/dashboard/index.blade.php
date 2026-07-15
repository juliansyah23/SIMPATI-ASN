@extends('layouts.app')

@section('title', 'Beranda')

@section('content')

    {{-- HERO --}}
    <section class="bg-gradient-to-b from-brand-700 via-brand-600 to-brand-600 text-white">
        <div class="max-w-5xl mx-auto px-6 pt-16 pb-12 text-center">
            <h1 class="text-4xl md:text-5xl font-extrabold tracking-tight">SIMPATI ASN</h1>
            <p class="mt-4 text-lg md:text-xl font-medium text-white/90">
                Sistem Monitoring Psikososial ASN di Era WFO/WFA
            </p>
            <p class="mt-3 max-w-3xl mx-auto text-sm md:text-base text-white/75">
                Dashboard analisis dan monitoring kondisi psikososial Aparatur Sipil Negara dalam
                lingkungan kerja Work From Office (WFO) dan Work From Anywhere (WFA)
            </p>
        </div>
    </section>

    {{-- STAT CARDS (overlapping the hero) --}}
    <section class="max-w-7xl mx-auto px-6 -mt-10 relative z-10">
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6">
            @foreach ($stats as $stat)
                <x-stat-card
                    :icon="$stat['icon']"
                    :color="$stat['color']"
                    :label="$stat['label']"
                    :value="$stat['value']"
                    :change="$stat['change']"
                    :positive="$stat['positive']"
                />
            @endforeach
        </div>
    </section>

    {{-- PERSEPSI KEBIJAKAN: filter + chart + table --}}
    <section class="max-w-7xl mx-auto px-6 mt-10">
        <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6 md:p-8">

            <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
                <div>
                    <h2 class="text-xl font-bold text-gray-900">Persepsi terhadap Kebijakan WFO/WFA</h2>
                    <p class="text-sm text-gray-500 mt-1">Distribusi respon berdasarkan skala Likert (1-5)</p>
                </div>
            </div>

            {{-- Filters --}}
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mt-8">
                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-2">Pilih Kategori:</label>
                    <select id="kategori-select"
                            class="w-full h-12 rounded-lg border border-gray-300 px-4 text-sm font-medium text-gray-700 focus:outline-none focus:ring-2 focus:ring-brand-500 focus:border-brand-500">
                        @foreach ($categories as $key => $cat)
                            <option value="{{ $key }}" @selected($selectedCategory === $key)>
                                {{ $cat['label'] }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-2">Jenis Diagram:</label>
                    <select id="chartType"
                            class="w-full h-12 rounded-lg border border-gray-300 px-4 text-sm font-medium text-gray-700 focus:outline-none focus:ring-2 focus:ring-brand-500 focus:border-brand-500">
                        <option value="pie">Diagram Lingkaran</option>
                        <option value="bar">Diagram Batang</option>
                        <option value="polarArea">Diagram Pohon</option>
                    </select>
                </div>
            </div>

            <div class="grid grid-cols-1 lg:grid-cols-2 gap-10 mt-10">

                {{-- Chart canvas tetap statis di sini (bukan bagian AJAX) karena
                     Chart.js perlu instance yang menempel ke elemen <canvas> ini
                     terus-menerus; datanya di-update lewat chart.update(), bukan
                     lewat penggantian innerHTML. --}}
                <div>
                    <h3 class="text-base font-bold text-gray-900 mb-4">Distribusi Respon</h3>
                    <div class="relative h-80">
                        <canvas id="distributionChart"></canvas>
                    </div>
                </div>

                {{-- Legend + distribution list + table + ringkasan: semuanya
                     diganti sekaligus lewat AJAX saat kategori berganti. --}}
                <div id="category-panel">
                    @include('dashboard.partials.category-panel', [
                        'activeCategory' => $activeCategory,
                        'colors'         => $colors,
                        'table'          => $table,
                        'total'          => $total,
                    ])
                </div>
            </div>
        </div>
    </section>

    {{-- INSIGHTS --}}
    <section class="max-w-7xl mx-auto px-6 mt-10 mb-16">
        <h2 class="text-xl font-bold text-gray-900 mb-5">Insight Psikososial ASN</h2>
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
            @foreach ($insights as $insight)
                @php
                    $bg = match ($insight['color']) {
                        'red'    => 'bg-red-50 border-red-100',
                        'green'  => 'bg-emerald-50 border-emerald-100',
                        'purple' => 'bg-purple-50 border-purple-100',
                        default  => 'bg-gray-50 border-gray-100',
                    };
                    $title = match ($insight['color']) {
                        'red'    => 'text-red-700',
                        'green'  => 'text-emerald-700',
                        'purple' => 'text-purple-700',
                        default  => 'text-gray-700',
                    };
                    $body = match ($insight['color']) {
                        'red'    => 'text-red-600',
                        'green'  => 'text-emerald-600',
                        'purple' => 'text-purple-600',
                        default  => 'text-gray-600',
                    };
                @endphp
                <div class="rounded-2xl border {{ $bg }} p-6">
                    <p class="font-bold {{ $title }}">{{ $insight['title'] }}</p>
                    <p class="text-sm {{ $body }} mt-2 leading-relaxed">{{ $insight['text'] }}</p>
                </div>
            @endforeach
        </div>
    </section>

@endsection

@push('scripts')
    <script>
        // Data chart bersifat "current" (bisa diganti saat AJAX refresh),
        // makanya pakai `let` bukan `const`.
        let distribution  = @json($activeCategory['distribution']);
        let likertLabels  = @json($activeCategory['labels_likert']);
        const colors      = @json($colors);

        let labels           = Object.keys(distribution).map(k => `${likertLabels[k]} (${k})`);
        let data             = Object.values(distribution);
        let backgroundColors = Object.keys(distribution).map(k => colors[k]);

        let chart;
        let currentChartType = 'pie';
        const ctx = document.getElementById('distributionChart');

        function renderChart(type) {
            currentChartType = type;
            if (chart) chart.destroy();

            const isCartesian = type === 'bar';
            const isPolar     = type === 'polarArea';

            chart = new Chart(ctx, {
                type: type,
                data: {
                    labels: labels,
                    datasets: [{
                        data: data,
                        backgroundColor: backgroundColors,
                        borderWidth: isCartesian ? 0 : 2,
                        borderColor: '#ffffff',
                    }],
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: { display: isPolar },
                        tooltip: {
                            callbacks: {
                                label: (ctx) => {
                                    const total = data.reduce((a, b) => a + b, 0);
                                    const raw   = ctx.raw ?? 0;
                                    const pct   = total > 0 ? (raw / total * 100).toFixed(1) : '0.0';
                                    return `${ctx.label}: ${raw} responden (${pct}%)`;
                                },
                            },
                        },
                    },
                    scales: isCartesian
                        ? { y: { beginAtZero: true, grid: { color: '#f3f4f6' } } }
                        : {},
                },
            });
        }

        renderChart('pie');

        document.getElementById('chartType').addEventListener('change', (e) => {
            renderChart(e.target.value);
        });

        // ─────────────────────────────────────────────────────────────
        //  AJAX: ambil ulang data setiap dropdown kategori berganti
        // ─────────────────────────────────────────────────────────────
        const kategoriSelect = document.getElementById('kategori-select');
        const dashboardDataUrl = '{{ route('dashboard.data') }}';

        kategoriSelect.addEventListener('change', async (e) => {
            try {
                const res = await fetch(`${dashboardDataUrl}?kategori=${encodeURIComponent(e.target.value)}`, {
                    headers: { 'X-Requested-With': 'XMLHttpRequest' },
                });
                if (!res.ok) throw new Error('Request gagal');
                const json = await res.json();

                // Ganti HTML legend + distribusi + tabel + ringkasan
                document.getElementById('category-panel').innerHTML = json.panelHtml;
                if (window.lucide) lucide.createIcons();

                // Hitung ulang data chart lalu render ulang dengan tipe yang sedang dipilih
                distribution     = json.distribution;
                likertLabels     = json.labelsLikert;
                labels           = Object.keys(distribution).map(k => `${likertLabels[k]} (${k})`);
                data             = Object.values(distribution);
                backgroundColors = Object.keys(distribution).map(k => json.colors[k]);

                renderChart(currentChartType);
            } catch (err) {
                console.error('Gagal memuat data dashboard:', err);
            }
        });
    </script>
@endpush
