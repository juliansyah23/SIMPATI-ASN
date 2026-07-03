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
                <button type="button"
                        onclick="window.location.href='{{ route('data') }}'"
                        class="inline-flex items-center gap-2 px-5 h-11 rounded-lg bg-emerald-500 hover:bg-emerald-600 text-white text-sm font-semibold shadow-sm transition shrink-0">
                    <i data-lucide="download" class="w-4 h-4"></i> Unduh Data
                </button>
            </div>

            {{-- Filters --}}
            <form method="GET" action="{{ route('dashboard') }}" class="grid grid-cols-1 md:grid-cols-2 gap-6 mt-8">
                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-2">Pilih Kategori:</label>
                    <select name="kategori" onchange="this.form.submit()"
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
            </form>

            <div class="grid grid-cols-1 lg:grid-cols-2 gap-10 mt-10">

                {{-- Chart + legend --}}
                <div>
                    <h3 class="text-base font-bold text-gray-900 mb-4">Distribusi Respon</h3>
                    <div class="relative h-80">
                        <canvas id="distributionChart"></canvas>
                    </div>

                    <div class="flex flex-wrap gap-x-6 gap-y-2 mt-6 text-sm text-gray-600">
                        @foreach ($activeCategory['labels_likert'] as $scale => $label)
                            <span class="flex items-center gap-2">
                                <span class="w-3 h-3 rounded-sm" style="background-color: {{ $colors[$scale] ?? '#ccc' }}"></span>
                                {{ $label }} ({{ $scale }})
                            </span>
                        @endforeach
                    </div>
                </div>

                {{-- Distribution list + table --}}
                <div>
                    <h3 class="text-base font-bold text-gray-900 mb-4">Distribusi Respon</h3>
                    <div class="space-y-3">
                        @foreach ($table as $row)
                            <div class="flex items-center justify-between bg-gray-50 rounded-xl px-5 py-4">
                                <span class="flex items-center gap-3 text-sm font-semibold text-gray-700">
                                    <span class="w-3 h-3 rounded-sm shrink-0" style="background-color: {{ $colors[$row['scale']] ?? '#ccc' }}"></span>
                                    {{ $activeCategory['labels_likert'][$row['scale']] }} ({{ $row['scale'] }})
                                </span>
                                <span class="text-right">
                                    <span class="block text-sm font-bold text-gray-900">{{ $row['count'] }} responden</span>
                                    <span class="block text-xs text-gray-500">{{ $row['percent'] }}%</span>
                                </span>
                            </div>
                        @endforeach
                    </div>

                    <h3 class="text-base font-bold text-gray-900 mt-10 mb-4">Tabel Distribusi Frekuensi</h3>
                    <div class="overflow-x-auto rounded-xl border border-gray-100">
                        <table class="w-full text-sm">
                            <thead class="bg-gray-50 text-gray-600">
                                <tr>
                                    <th class="text-left font-semibold px-5 py-3">Skala</th>
                                    <th class="text-center font-semibold px-5 py-3">Frekuensi (n)</th>
                                    <th class="text-center font-semibold px-5 py-3">Persentase (%)</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-100">
                                @foreach ($table as $row)
                                    <tr>
                                        <td class="px-5 py-3 text-gray-700">{{ $row['label'] }} ({{ $row['scale'] }})</td>
                                        <td class="px-5 py-3 text-center font-semibold text-gray-900">{{ $row['count'] }}</td>
                                        <td class="px-5 py-3 text-center text-gray-600">{{ $row['percent'] }}%</td>
                                    </tr>
                                @endforeach
                            </tbody>
                            <tfoot>
                                <tr class="bg-gray-50 font-bold text-gray-900">
                                    <td class="px-5 py-3">Total</td>
                                    <td class="px-5 py-3 text-center">{{ $total }}</td>
                                    <td class="px-5 py-3 text-center">100.0%</td>
                                </tr>
                            </tfoot>
                        </table>
                    </div>

                    <div class="bg-red-50 rounded-xl px-5 py-4 mt-6">
                        <p class="text-sm font-bold text-red-700">Ringkasan</p>
                        <p class="text-sm text-red-600 mt-1">Total Responden: {{ $total }} pegawai</p>
                    </div>
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
        const distribution = @json($activeCategory['distribution']);
        const likertLabels = @json($activeCategory['labels_likert']);
        const colors = @json($colors);

        const labels = Object.keys(distribution).map(k => `${likertLabels[k]} (${k})`);
        const data = Object.values(distribution);
        const backgroundColors = Object.keys(distribution).map(k => colors[k]);

        let chart;
        const ctx = document.getElementById('distributionChart');

        function renderChart(type) {
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
                                    const pct   = (raw / total * 100).toFixed(1);
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
    </script>
@endpush
