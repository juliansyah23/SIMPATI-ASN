<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Respons Kuisioner — SIMPATI ASN</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        @media print {
            .no-print { display: none !important; }
            body { -webkit-print-color-adjust: exact; print-color-adjust: exact; }
        }
        body { font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif; }
    </style>
</head>
<body class="bg-white text-gray-800 px-10 py-8">

    <div class="no-print flex justify-end gap-2 mb-6">
        <button onclick="window.print()"
            class="inline-flex items-center gap-2 px-5 h-10 rounded-lg bg-red-600 hover:bg-red-700 text-white text-sm font-semibold shadow-sm transition">
            Cetak / Simpan sebagai PDF
        </button>
    </div>

    <div class="flex items-center justify-between border-b border-gray-200 pb-4 mb-6">
        <div>
            <h1 class="text-xl font-extrabold text-gray-900">Respons Kuisioner</h1>
            <p class="text-sm text-gray-500 mt-1">{{ $kuisioner['judul'] }} &middot; Tahun {{ $kuisioner['tahun'] }}</p>
        </div>
        <div class="text-right text-xs text-gray-400">
            <p>Dicetak: {{ $dicetak }}</p>
            <p>Total: {{ $responses->count() }} responden</p>
        </div>
    </div>

    @if ($responses->count() > 0)
        <table class="w-full text-sm border-collapse">
            <thead>
                <tr class="bg-gray-50 border-b-2 border-gray-200">
                    <th class="text-left font-semibold px-3 py-2.5">Nama</th>
                    <th class="text-left font-semibold px-3 py-2.5">NIP</th>
                    <th class="text-left font-semibold px-3 py-2.5">Posisi</th>
                    <th class="text-left font-semibold px-3 py-2.5">Pusat Riset</th>
                    <th class="text-center font-semibold px-3 py-2.5">Mode Kerja</th>
                    <th class="text-center font-semibold px-3 py-2.5">Rata-rata Skor</th>
                    <th class="text-center font-semibold px-3 py-2.5">Tanggal Dikirim</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($responses as $r)
                    <tr class="border-b border-gray-100">
                        <td class="px-3 py-2.5 font-medium">{{ $r['nama'] }}</td>
                        <td class="px-3 py-2.5 font-mono">{{ $r['nip'] }}</td>
                        <td class="px-3 py-2.5">{{ $r['posisi'] }}</td>
                        <td class="px-3 py-2.5">{{ $r['pusat_riset'] }}</td>
                        <td class="px-3 py-2.5 text-center">{{ $r['mode_kerja'] }}</td>
                        <td class="px-3 py-2.5 text-center font-bold">{{ $r['rata_rata'] }}/5</td>
                        <td class="px-3 py-2.5 text-center">{{ $r['dikirim'] }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    @else
        <p class="text-sm text-gray-400 italic">Belum ada responden untuk kuisioner ini.</p>
    @endif

    <p class="text-xs text-gray-400 mt-6">Dokumen ini dibuat otomatis oleh sistem SIMPATI ASN.</p>

    <script>
        window.addEventListener('load', () => window.print());
    </script>
</body>
</html>
