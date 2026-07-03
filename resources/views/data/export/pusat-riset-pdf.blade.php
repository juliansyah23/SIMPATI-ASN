<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Performa per Pusat Riset — SIMPATI ASN</title>
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
            <h1 class="text-xl font-extrabold text-gray-900">Performa per Pusat Riset</h1>
            <p class="text-sm text-gray-500 mt-1">SIMPATI ASN — Sistem Monitoring Psikososial ASN</p>
        </div>
        <div class="text-right text-xs text-gray-400">
            <p>Dicetak: {{ $dicetak }}</p>
            @if (!empty($selected['tahun']))
                <p>Tahun: {{ $selected['tahun'] }}</p>
            @endif
        </div>
    </div>

    <table class="w-full text-sm border-collapse">
        <thead>
            <tr class="bg-gray-50 border-b-2 border-gray-200">
                <th class="text-left font-semibold px-3 py-2.5">Pusat Riset</th>
                <th class="text-center font-semibold px-3 py-2.5">Respons</th>
                <th class="text-center font-semibold px-3 py-2.5">Produktivitas</th>
                <th class="text-center font-semibold px-3 py-2.5">Kolaborasi</th>
                <th class="text-center font-semibold px-3 py-2.5">Work-Life Balance</th>
                <th class="text-center font-semibold px-3 py-2.5">Stres</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($centers as $center)
                <tr class="border-b border-gray-100">
                    <td class="px-3 py-2.5 font-medium">{{ $center['name'] }}</td>
                    <td class="px-3 py-2.5 text-center">{{ $center['respons'] }}</td>
                    <td class="px-3 py-2.5 text-center">{{ number_format($center['produktivitas'], 2) }}</td>
                    <td class="px-3 py-2.5 text-center">{{ number_format($center['kolaborasi'], 2) }}</td>
                    <td class="px-3 py-2.5 text-center">{{ number_format($center['wlb'], 2) }}</td>
                    <td class="px-3 py-2.5 text-center">{{ number_format($center['stres'], 2) }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>

    <p class="text-xs text-gray-400 mt-6">Dokumen ini dibuat otomatis oleh sistem SIMPATI ASN.</p>

    <script>
        window.addEventListener('load', () => window.print());
    </script>
</body>
</html>
