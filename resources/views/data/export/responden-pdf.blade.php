<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Data Detail Responden — SIMPATI ASN</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        @page { size: A4 landscape; margin: 14mm; }
        @media print {
            .no-print { display: none !important; }
            body { -webkit-print-color-adjust: exact; print-color-adjust: exact; }
        }
        body { font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif; }
        table { font-size: 10px; }
        th, td { white-space: nowrap; }
    </style>
</head>
<body class="bg-white text-gray-800 px-8 py-6">

    <div class="no-print flex justify-end gap-2 mb-6">
        <button onclick="window.print()"
            class="inline-flex items-center gap-2 px-5 h-10 rounded-lg bg-red-600 hover:bg-red-700 text-white text-sm font-semibold shadow-sm transition">
            Cetak / Simpan sebagai PDF
        </button>
    </div>

    <div class="flex items-center justify-between border-b border-gray-200 pb-4 mb-5">
        <div>
            <h1 class="text-lg font-extrabold text-gray-900">Data Detail Responden</h1>
            <p class="text-xs text-gray-500 mt-1">SIMPATI ASN — Sistem Monitoring Psikososial ASN</p>
        </div>
        <div class="text-right text-xs text-gray-400">
            <p>Dicetak: {{ $dicetak }}</p>
            @if (!empty($selected['tahun']))
                <p>Tahun: {{ $selected['tahun'] }}</p>
            @endif
        </div>
    </div>

    @if (count($rows) > 0)
        <table class="w-full border-collapse">
            <thead>
                <tr class="bg-gray-50 border-b-2 border-gray-200">
                    <th class="text-left font-semibold px-2 py-2">NIP</th>
                    <th class="text-left font-semibold px-2 py-2">Nama</th>
                    <th class="text-left font-semibold px-2 py-2">Pusat Riset</th>
                    <th class="text-left font-semibold px-2 py-2">Posisi</th>
                    <th class="text-center font-semibold px-2 py-2">Kelamin</th>
                    <th class="text-center font-semibold px-2 py-2">Usia</th>
                    <th class="text-center font-semibold px-2 py-2">Lama Kerja</th>
                    <th class="text-center font-semibold px-2 py-2">Mode Kerja</th>
                    <th class="text-center font-semibold px-2 py-2">I. Kebijakan</th>
                    <th class="text-center font-semibold px-2 py-2">II. Motivasi</th>
                    <th class="text-center font-semibold px-2 py-2">III. Kepuasan</th>
                    <th class="text-center font-semibold px-2 py-2">IV. Engagement</th>
                    <th class="text-center font-semibold px-2 py-2">V. Stres</th>
                    <th class="text-center font-semibold px-2 py-2">VI. Dukungan</th>
                    <th class="text-center font-semibold px-2 py-2">VII. WLB</th>
                    <th class="text-center font-semibold px-2 py-2">Rata-rata</th>
                    <th class="text-center font-semibold px-2 py-2">Tgl Submit</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($rows as $row)
                    <tr class="border-b border-gray-100">
                        <td class="px-2 py-1.5 font-mono">{{ $row['nip'] }}</td>
                        <td class="px-2 py-1.5 font-medium">{{ $row['nama'] }}</td>
                        <td class="px-2 py-1.5">{{ $row['pusat_riset'] }}</td>
                        <td class="px-2 py-1.5">{{ $row['posisi'] }}</td>
                        <td class="px-2 py-1.5 text-center">{{ $row['jenis_kelamin'] }}</td>
                        <td class="px-2 py-1.5 text-center">{{ $row['usia'] }}</td>
                        <td class="px-2 py-1.5 text-center">{{ $row['lama_bekerja'] }}</td>
                        <td class="px-2 py-1.5 text-center">{{ $row['mode_kerja'] }}</td>
                        @foreach ($row['per_kategori'] as $skor)
                            <td class="px-2 py-1.5 text-center">{{ $skor }}</td>
                        @endforeach
                        <td class="px-2 py-1.5 text-center font-bold">{{ $row['rata_rata'] }}</td>
                        <td class="px-2 py-1.5 text-center">{{ $row['submitted_at'] }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
        <p class="text-xs text-gray-400 mt-4">Total {{ count($rows) }} responden.</p>
    @else
        <p class="text-sm text-gray-400 italic">Tidak ada data responden sesuai filter yang dipilih.</p>
    @endif

    <script>
        window.addEventListener('load', () => window.print());
    </script>
</body>
</html>
