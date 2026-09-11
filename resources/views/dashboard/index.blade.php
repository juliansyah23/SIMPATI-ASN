@extends('layouts.app')

@section('title', 'Beranda')

@section('content')
    @include('dashboard.partials.analysis-styles')

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
                    <label for="diagram-select" class="block text-sm font-semibold text-gray-700 mb-2">Jenis Diagram:</label>
                    <select id="diagram-select" aria-controls="category-panel" class="w-full h-12 rounded-lg border border-gray-300 px-4 text-sm font-medium text-gray-700 focus:outline-none focus:ring-2 focus:ring-brand-500">
                        <option value="pie">Diagram Lingkaran</option>
                        <option value="bar" selected>Diagram Batang</option>
                        <option value="tree">Diagram Pohon</option>
                    </select>
                </div>
            </div>
            <p id="category-error" class="text-sm text-red-600 mt-4" role="alert" hidden></p>
            <div id="category-panel" class="mt-8">
                @include('dashboard.partials.analysis')
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
        const kategoriSelect = document.getElementById('kategori-select');
        const categoryPanel = document.getElementById('category-panel');
        const categoryError = document.getElementById('category-error');
        let selectedCategory = kategoriSelect.value;
        const diagramSelect = document.getElementById('diagram-select');
        function updateDiagrams() {
            categoryPanel.querySelectorAll('[data-diagram]').forEach((diagram) => {
                diagram.hidden = diagram.dataset.diagram !== diagramSelect.value;
            });
        }
        diagramSelect.addEventListener('change', updateDiagrams);
        updateDiagrams();
        kategoriSelect.addEventListener('change', async (event) => {
            kategoriSelect.disabled = true;
            categoryPanel.setAttribute('aria-busy', 'true');
            categoryError.hidden = true;
            try {
                const response = await fetch(`{{ route('dashboard.data') }}?kategori=${encodeURIComponent(event.target.value)}`, {
                    headers: { 'X-Requested-With': 'XMLHttpRequest' },
                });
                if (!response.ok) throw new Error('Request gagal');
                const payload = await response.json();
                categoryPanel.innerHTML = payload.panelHtml;
                updateDiagrams();
                selectedCategory = kategoriSelect.value;
            } catch (error) {
                kategoriSelect.value = selectedCategory;
                categoryError.textContent = 'Gagal memuat data kategori. Silakan coba kembali.';
                categoryError.hidden = false;
            } finally {
                kategoriSelect.disabled = false;
                categoryPanel.removeAttribute('aria-busy');
            }
        });
    </script>
@endpush
