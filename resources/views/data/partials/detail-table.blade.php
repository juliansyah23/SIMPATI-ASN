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
