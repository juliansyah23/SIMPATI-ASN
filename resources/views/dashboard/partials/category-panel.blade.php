<h3 class="text-base font-bold text-gray-900 mb-4">Tabel Distribusi Frekuensi</h3>
<div class="overflow-x-auto rounded-xl border border-gray-100">
    <table class="w-full text-sm">
        <thead class="bg-gray-50 text-gray-600">
            <tr>
                <th class="text-left font-semibold px-5 py-3">Kriteria kelas</th>
                <th class="text-center font-semibold px-5 py-3">Frekuensi</th>
                <th class="text-center font-semibold px-5 py-3">Persentase</th>
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
<h3 class="text-base font-bold text-gray-900 mt-10 mb-4">Distribusi Respon</h3>
<div class="space-y-3">
    @foreach ($table as $row)
        <div class="flex items-center justify-between bg-gray-50 rounded-xl px-5 py-4">
            <span class="flex items-center gap-3 text-sm font-semibold text-gray-700">
                <span class="w-3 h-3 rounded-sm shrink-0" style="background-color: {{ $colors[$row['scale']] ?? '#ccc' }}"></span>
                {{ $activeCategory['labels_kualitas'][$row['scale']] }} ({{ $row['scale'] }})
            </span>
            <span class="text-right">
                <span class="block text-sm font-bold text-gray-900">{{ $row['count'] }} responden</span>
                <span class="block text-xs text-gray-500">{{ $row['percent'] }}%</span>
            </span>
        </div>
    @endforeach
</div>
<div class="bg-red-50 rounded-xl px-5 py-4 mt-6">
    <p class="text-sm font-bold text-red-700">Ringkasan</p>
    <p class="text-sm text-red-600 mt-1">Total Responden: {{ $total }} pegawai</p>
    <p class="text-sm text-red-600 mt-1">
        Rata-rata Skor: {{ number_format($activeCategory['rata_rata'], 2, ',', '.') }} / 5,00
        — {{ $activeCategory['kelas_label'] }}
    </p>
</div>
