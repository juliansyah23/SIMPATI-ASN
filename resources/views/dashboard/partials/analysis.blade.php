@php
    $names = [1 => 'Sangat Kurang', 'Kurang', 'Cukup Baik', 'Baik', 'Sangat Baik'];
    $palette = [1 => '#dc2626', '#f97316', '#facc15', '#8cc63f', '#25a65a'];
    $ranges = [1 => '1,00 – 1,80', '1,81 – 2,60', '2,61 – 3,40', '3,41 – 4,20', '4,21 – 5,00'];
@endphp
<div class="analysis-layout">
    <aside class="analysis-card criteria-card">
        <h3>Kriteria Kelas</h3>
        <table class="criteria-table">
            <thead><tr><th>Kriteria Kelas</th><th>Rata-rata Skor</th></tr></thead>
            <tbody>@foreach ($table as $row)<tr><td><span class="criteria-label"><i class="criteria-marker" aria-hidden="true" style="background: {{ $palette[$row['scale']] }}"></i>{{ $names[$row['scale']] }}</span></td><td>{{ $ranges[$row['scale']] }}</td></tr>@endforeach</tbody>
        </table>
        <p class="scale-note">Catatan: Semakin tinggi skor, semakin baik kondisi psikososial ASN.</p>
    </aside>
    <div class="analysis-main">
        <section class="analysis-card distribution-card"><h3>Distribusi Frekuensi</h3><div class="frequency-grid">
            @foreach (['count' => '1. Frekuensi Tiap Kelas', 'percent' => '2. Persentase Tiap Kelas'] as $metric => $heading)
                <div class="chart-cell"><h4>{{ $heading }}</h4>
                    @foreach (['pie', 'bar', 'tree'] as $diagramType)
                        <div data-diagram="{{ $diagramType }}" @if ($diagramType !== 'bar') hidden @endif>
                            @include('dashboard.partials.analysis-chart', ['type' => $diagramType, 'metric' => $metric])
                        </div>
                    @endforeach
                </div>
            @endforeach
        </div></section>
        <div class="analysis-summary" role="status" aria-label="Ringkasan kategori terpilih">
            <p class="summary-line">
                <span>Total Responden: <strong>{{ number_format($total, 0, ',', '.') }} pegawai</strong></span>
                <span class="summary-separator" aria-hidden="true">·</span>
                <span class="summary-average">Rata-rata Skor:
                @if ($total > 0)
                    <strong>{{ number_format($activeCategory['rata_rata'], 2, ',', '.') }} / 5,00</strong>
                @else
                    <span class="summary-empty">Belum ada data</span>
                @endif
                </span>
                @if ($total > 0)
                    <span class="summary-class"><i aria-hidden="true" style="background: {{ $palette[$activeCategory['kelas']] }}"></i>{{ $names[$activeCategory['kelas']] }}</span>
                @endif
            </p>
        </div>
    </div>
</div>