<?php

namespace App\Http\Controllers;

class TentangController extends Controller
{
    public function index()
    {
        $tujuan = [
            'SIMPATI ASN (Sistem Monitoring Psikososial ASN di Era WFO/WFA) dirancang untuk memantau, menganalisis, dan mengevaluasi kondisi psikososial Aparatur Sipil Negara di lingkungan Badan Riset dan Inovasi Nasional (BRIN) dalam konteks perubahan pola kerja dari Work From Office (WFO) ke Work From Anywhere (WFA).',
            'Sistem ini mengumpulkan dan menganalisis data psikososial pegawai dari tahun 2022 hingga 2024, memberikan wawasan mendalam tentang dampak perubahan pola kerja terhadap produktivitas, kolaborasi tim, work-life balance, kepuasan kerja, dan dukungan teknis yang diterima pegawai.',
            'Melalui dashboard interaktif dan visualisasi data yang komprehensif, SIMPATI ASN mendukung pengambilan keputusan berbasis data untuk menciptakan lingkungan kerja yang lebih kondusif dan mendukung kesejahteraan psikososial ASN.',
        ];

        $latarBelakang = [
            'Pandemi COVID-19 telah mengubah paradigma kerja secara global, termasuk di Indonesia. BRIN sebagai lembaga riset nasional terdepan mengadopsi kebijakan Work From Anywhere (WFA) untuk menjaga produktivitas sambil memastikan keselamatan dan kesejahteraan pegawai.',
            'Perubahan mendadak dari WFO ke WFA membawa dampak signifikan terhadap aspek psikososial pegawai, termasuk stres kerja, isolasi sosial, work-life balance, serta dinamika kolaborasi tim. Setelah lebih dari tiga tahun implementasi, penting untuk memantau dan mengevaluasi kondisi psikososial ASN secara berkelanjutan.',
            'SIMPATI ASN hadir sebagai solusi monitoring berbasis data untuk memahami dampak psikososial perubahan pola kerja, mengidentifikasi tantangan yang dihadapi pegawai, dan memberikan rekomendasi kebijakan yang mendukung kesehatan mental dan kesejahteraan ASN di era kerja hybrid.',
        ];

        $metodologi = [
            [
                'title' => 'Pengumpulan Data',
                'color' => 'red',
                'items' => [
                    'Kuisioner psikososial online untuk pegawai WFA dan WFO',
                    'Data kinerja dan kesejahteraan dari sistem manajemen SDM',
                    'Wawancara mendalam tentang kesehatan mental pegawai',
                    'Observasi pola kerja dan work-life balance',
                ],
            ],
            [
                'title' => 'Analisis Data',
                'color' => 'green',
                'items' => [
                    'Analisis statistik deskriptif dan inferensial',
                    'Komparasi tren kinerja tahunan (2022-2024)',
                    'Segmentasi berdasarkan institusi dan posisi',
                    'Analisis kualitatif feedback pegawai',
                ],
            ],
            [
                'title' => 'Partisipan',
                'color' => 'purple',
                'items' => [
                    '200 pegawai dari berbagai pusat riset',
                    'Peneliti, teknisi, dan analis',
                    'Distribusi seimbang WFA dan WFO',
                    'Data longitudinal 3 tahun (2022-2024)',
                ],
            ],
            [
                'title' => 'Metrik Psikososial',
                'color' => 'orange',
                'items' => [
                    'Tingkat produktivitas dan stres kerja',
                    'Efektivitas kolaborasi tim virtual/fisik',
                    'Work-life balance dan kesejahteraan mental',
                    'Kepuasan kerja dan dukungan organisasi',
                ],
            ],
        ];

        $temuanUtama = [
            [
                'value' => '+12.3%',
                'color' => 'red',
                'desc' => 'Peningkatan kondisi psikososial WFA dari 2022 ke 2024',
            ],
            [
                'value' => '1.3%',
                'color' => 'green',
                'desc' => 'Kesenjangan psikososial WFA-WFO di 2024 (turun dari 11.5% di 2022)',
            ],
            [
                'value' => '85.5%',
                'color' => 'purple',
                'desc' => 'Rata-rata skor kesejahteraan psikososial ASN 2024',
            ],
        ];

        $kontak = [
            'email' => ['simpati.asn@brin.go.id', 'hrd@brin.go.id'],
            'telepon' => ['(021) 316-0000', '(021) 316-0001'],
            'alamat' => ['Gedung BRIN', 'Jl. M.H. Thamrin No. 8', 'Jakarta Pusat, Indonesia'],
        ];

        return view('tentang.index', compact(
            'tujuan', 'latarBelakang', 'metodologi', 'temuanUtama', 'kontak'
        ));
    }
}
