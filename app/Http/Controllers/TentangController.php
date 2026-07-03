<?php

namespace App\Http\Controllers;

use App\Models\Questionnaire;
use App\Models\SurveyResponse;
use Illuminate\Support\Facades\DB;

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

        $temuanUtama = $this->temuanUtama();

        $kontak = [
            'email' => ['simpati.asn@brin.go.id', 'hrd@brin.go.id'],
            'telepon' => ['(021) 316-0000', '(021) 316-0001'],
            'alamat' => ['Gedung BRIN', 'Jl. M.H. Thamrin No. 8', 'Jakarta Pusat, Indonesia'],
        ];

        return view('tentang.index', compact(
            'tujuan', 'latarBelakang', 'metodologi', 'temuanUtama', 'kontak'
        ));
    }

    /** Kuisioner aktif dipakai sebagai sumber data "Temuan Utama". Duplikat dari DataController::activeQuestionnaire(). */
    private function activeQuestionnaire(): ?Questionnaire
    {
        return Questionnaire::where('status', 'aktif')->latest('id')->first();
    }

    /**
     * Map nilai field demografi 'pola_kehadiran' ke bucket WFA/WFO/Hybrid.
     * Duplikat dari DataController::modeKerjaBucket() (pola duplikasi ini sudah
     * dipakai di beberapa controller lain di project ini).
     */
    private function modeKerjaBucket(?string $polaKehadiran): string
    {
        if (! $polaKehadiran) {
            return 'Hybrid';
        }

        $value = strtolower($polaKehadiran);

        if (str_contains($value, 'wfa')) {
            return 'WFA';
        }

        if (str_contains($value, 'wfo 5x')) {
            return 'WFO';
        }

        return 'Hybrid';
    }

    /** Bucket mode kerja per survey_response_id, dari kuisioner aktif. Duplikat dari DataController::modeKerjaByResponseId(). */
    private function modeKerjaByResponseId(Questionnaire $questionnaire): array
    {
        $rows = DB::table('survey_demografi')
            ->join('demografi_fields', 'demografi_fields.id', '=', 'survey_demografi.demografi_field_id')
            ->join('categories', 'categories.id', '=', 'demografi_fields.category_id')
            ->where('categories.questionnaire_id', $questionnaire->id)
            ->where('demografi_fields.field_key', 'pola_kehadiran')
            ->select('survey_demografi.survey_response_id', 'survey_demografi.value')
            ->get();

        $map = [];
        foreach ($rows as $row) {
            $map[$row->survey_response_id] = $this->modeKerjaBucket($row->value);
        }

        return $map;
    }

    /**
     * Rata-rata skor (kolom survey_responses.rata_rata, skala 1-5) untuk kuisioner
     * aktif, dibatasi ke tahun submit tertentu dan (opsional) bucket mode kerja
     * tertentu, dikonversi ke persen (0-100). Null kalau tidak ada respons yang cocok.
     */
    private function avgSkorPersen(Questionnaire $questionnaire, string $tahun, array $modeKerjaMap, ?string $bucket = null): ?float
    {
        $responses = SurveyResponse::where('questionnaire_id', $questionnaire->id)
            ->where('status', 'submitted')
            ->whereYear('submitted_at', $tahun)
            ->whereNotNull('rata_rata')
            ->get(['id', 'rata_rata']);

        if ($bucket !== null) {
            $responses = $responses->filter(
                fn ($r) => ($modeKerjaMap[$r->id] ?? null) === $bucket
            )->values();
        }

        if ($responses->isEmpty()) {
            return null;
        }

        return round(((float) $responses->avg('rata_rata') / 5) * 100, 1);
    }

    /**
     * Hitung 3 kartu "Temuan Utama" dari data respons submitted sungguhan (bukan
     * hardcode), dibandingkan antara tahun submit paling awal & paling akhir yang
     * benar-benar ada di database — bukan diasumsikan tetap 2022 & 2024, supaya
     * tetap valid berapa pun rentang tahun data yang tersedia (termasuk kalau
     * baru ada data satu tahun).
     */
    private function temuanUtama(): array
    {
        $questionnaire = $this->activeQuestionnaire();

        if (! $questionnaire) {
            return $this->temuanUtamaKosong();
        }

        $tahunList = SurveyResponse::where('questionnaire_id', $questionnaire->id)
            ->where('status', 'submitted')
            ->whereNotNull('submitted_at')
            ->selectRaw('YEAR(submitted_at) as tahun')
            ->distinct()
            ->orderBy('tahun')
            ->pluck('tahun');

        if ($tahunList->isEmpty()) {
            return $this->temuanUtamaKosong();
        }

        $tahunAwal  = (string) $tahunList->first();
        $tahunAkhir = (string) $tahunList->last();
        $adaTren    = $tahunAwal !== $tahunAkhir;

        $modeKerjaMap = $this->modeKerjaByResponseId($questionnaire);

        $wfaAwal    = $this->avgSkorPersen($questionnaire, $tahunAwal, $modeKerjaMap, 'WFA');
        $wfaAkhir   = $this->avgSkorPersen($questionnaire, $tahunAkhir, $modeKerjaMap, 'WFA');
        $wfoAwal    = $this->avgSkorPersen($questionnaire, $tahunAwal, $modeKerjaMap, 'WFO');
        $wfoAkhir   = $this->avgSkorPersen($questionnaire, $tahunAkhir, $modeKerjaMap, 'WFO');
        $semuaAkhir = $this->avgSkorPersen($questionnaire, $tahunAkhir, $modeKerjaMap);

        // Kartu 1: perubahan (atau kondisi terkini kalau baru ada 1 tahun data) skor WFA.
        if ($adaTren && $wfaAwal !== null && $wfaAkhir !== null) {
            $delta = round($wfaAkhir - $wfaAwal, 1);
            $kartu1 = [
                'value' => ($delta >= 0 ? '+' : '') . number_format($delta, 1) . '%',
                'color' => 'red',
                'desc'  => "Perubahan kondisi psikososial WFA dari {$tahunAwal} ke {$tahunAkhir}",
            ];
        } else {
            $kartu1 = [
                'value' => $wfaAkhir !== null ? number_format($wfaAkhir, 1) . '%' : '-',
                'color' => 'red',
                'desc'  => "Kondisi psikososial WFA saat ini ({$tahunAkhir})",
            ];
        }

        // Kartu 2: kesenjangan WFA-WFO di tahun terakhir, dibanding tahun awal (kalau ada).
        $gapAkhir = ($wfaAkhir !== null && $wfoAkhir !== null) ? round(abs($wfaAkhir - $wfoAkhir), 1) : null;
        $gapAwal  = ($wfaAwal !== null && $wfoAwal !== null) ? round(abs($wfaAwal - $wfoAwal), 1) : null;

        if ($adaTren && $gapAkhir !== null && $gapAwal !== null) {
            $arah = match (true) {
                $gapAkhir < $gapAwal => "turun dari " . number_format($gapAwal, 1) . "% di {$tahunAwal}",
                $gapAkhir > $gapAwal => "naik dari " . number_format($gapAwal, 1) . "% di {$tahunAwal}",
                default              => "sama seperti di {$tahunAwal}",
            };
            $kartu2 = [
                'value' => number_format($gapAkhir, 1) . '%',
                'color' => 'green',
                'desc'  => "Kesenjangan psikososial WFA-WFO di {$tahunAkhir} ({$arah})",
            ];
        } else {
            $kartu2 = [
                'value' => $gapAkhir !== null ? number_format($gapAkhir, 1) . '%' : '-',
                'color' => 'green',
                'desc'  => "Kesenjangan psikososial WFA-WFO saat ini ({$tahunAkhir})",
            ];
        }

        // Kartu 3: rata-rata skor kesejahteraan psikososial seluruh responden di tahun terakhir.
        $kartu3 = [
            'value' => $semuaAkhir !== null ? number_format($semuaAkhir, 1) . '%' : '-',
            'color' => 'purple',
            'desc'  => "Rata-rata skor kesejahteraan psikososial ASN {$tahunAkhir}",
        ];

        return [$kartu1, $kartu2, $kartu3];
    }

    /** Fallback kalau belum ada kuisioner aktif / belum ada respons submitted sama sekali. */
    private function temuanUtamaKosong(): array
    {
        return [
            ['value' => '-', 'color' => 'red',    'desc' => 'Perubahan kondisi psikososial WFA — data belum tersedia'],
            ['value' => '-', 'color' => 'green',  'desc' => 'Kesenjangan psikososial WFA-WFO — data belum tersedia'],
            ['value' => '-', 'color' => 'purple', 'desc' => 'Rata-rata skor kesejahteraan psikososial ASN — data belum tersedia'],
        ];
    }
}