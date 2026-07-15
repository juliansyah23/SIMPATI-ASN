<?php

namespace App\Http\Controllers;

use App\Models\Questionnaire;
use App\Models\SurveyAnswer;
use App\Models\SurveyResponse;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    private const LIKERT_LABELS = [
        1 => 'Sangat Tidak Setuju',
        2 => 'Tidak Setuju',
        3 => 'Netral',
        4 => 'Setuju',
        5 => 'Sangat Setuju',
    ];

    /**
     * Label "kualitas" per kategori (kode kategori => [skala => label]). Dipakai
     * untuk teks di tabel distribusi & ringkasan, persis seperti versi hardcode
     * sebelumnya. Kategori yang tidak ada di sini fallback ke LIKERT_LABELS biasa.
     */
    private const KUALITAS_LABELS = [
        'persepsi_kebijakan'  => [1 => 'Sangat Kurang Baik', 2 => 'Kurang Baik', 3 => 'Cukup Baik', 4 => 'Baik', 5 => 'Sangat Baik'],
        'motivasi_kerja'      => [1 => 'Sangat Rendah', 2 => 'Rendah', 3 => 'Cukup', 4 => 'Tinggi', 5 => 'Sangat Tinggi'],
        'kepuasan_kerja'      => [1 => 'Sangat Tidak Puas', 2 => 'Tidak Puas', 3 => 'Cukup Puas', 4 => 'Puas', 5 => 'Sangat Puas'],
        'engagement_pegawai'  => [1 => 'Sangat Rendah', 2 => 'Rendah', 3 => 'Cukup', 4 => 'Tinggi', 5 => 'Sangat Tinggi'],
        'stres_kerja'         => [1 => 'Sangat Tidak Setuju', 2 => 'Tidak Setuju', 3 => 'Netral', 4 => 'Setuju', 5 => 'Sangat Setuju'],
        'dukungan_organisasi' => [1 => 'Sangat Kurang', 2 => 'Kurang', 3 => 'Cukup', 4 => 'Baik', 5 => 'Sangat Baik'],
        'wlb'                 => [1 => 'Sangat Buruk', 2 => 'Buruk', 3 => 'Cukup', 4 => 'Baik', 5 => 'Sangat Baik'],
    ];

    /** Label tampilan dropdown "Pilih Kategori", per kode kategori. */
    private const CATEGORY_DISPLAY_LABELS = [
        'persepsi_kebijakan'  => 'I. Persepsi terhadap Kebijakan WFO/WFA',
        'motivasi_kerja'      => 'II. Motivasi Kerja',
        'kepuasan_kerja'      => 'III. Kepuasan Kerja',
        'engagement_pegawai'  => 'IV. Engagement Pegawai',
        'stres_kerja'         => 'V. Stres Kerja',
        'dukungan_organisasi' => 'VI. Persepsi Dukungan Organisasi',
        'wlb'                 => 'VII. Work–Life Balance',
    ];

    private const CHART_COLORS = [
        1 => '#ef4444',
        2 => '#f97316',
        3 => '#facc15',
        4 => '#10b981',
        5 => '#047857',
    ];

    /**
     * Kuisioner aktif yang dipakai sebagai sumber data dashboard. Mengambil
     * kuisioner berstatus 'aktif' yang paling baru dibuat — sejalan dengan
     * asumsi di KuisionerController bahwa hanya ada satu kuisioner aktif
     * pada satu waktu untuk pengisian pegawai.
     */
    private function activeQuestionnaire(): ?Questionnaire
    {
        return Questionnaire::where('status', 'aktif')->latest('id')->first();
    }

    /**
     * Ambil kategori-kategori tipe 'likert' milik kuisioner aktif, beserta
     * distribusi skor (1-5) dari seluruh jawaban submitted untuk kategori
     * tersebut. Hasil: ['kode' => ['label'=>.., 'labels_likert'=>.., 'labels_kualitas'=>.., 'distribution'=>[1..5=>count]]]
     */
    private function categoriesWithDistribution(?Questionnaire $questionnaire): array
    {
        if (! $questionnaire) {
            return [];
        }

        $categories = $questionnaire->categories()
            ->where('type', 'likert')
            ->orderBy('urutan')
            ->get();

        $result = [];

        foreach ($categories as $category) {
            // Hitung distribusi skor per RESPONDEN UNIK (bukan per jawaban).
            // Caranya: ambil rata-rata skor tiap responden dalam kategori ini,
            // lalu bulatkan ke integer untuk menentukan skala 1–5-nya.
            // Dengan begitu, 1 responden = 1 suara, meskipun kategori punya banyak pertanyaan.
            $rows = SurveyAnswer::query()
                ->join('questions', 'questions.id', '=', 'survey_answers.question_id')
                ->join('survey_responses', 'survey_responses.id', '=', 'survey_answers.survey_response_id')
                ->where('questions.category_id', $category->id)
                ->where('survey_responses.status', 'submitted')
                ->selectRaw('survey_responses.id as response_id, ROUND(AVG(survey_answers.skor)) as skor_rata')
                ->groupBy('survey_responses.id')
                ->get()
                ->groupBy('skor_rata')
                ->map(fn ($group) => $group->count());

            $distribution = [];
            foreach (range(1, 5) as $scale) {
                $distribution[$scale] = (int) ($rows[$scale] ?? 0);
            }

            $result[$category->kode] = [
                'label'           => self::CATEGORY_DISPLAY_LABELS[$category->kode] ?? $category->title,
                'labels_likert'   => self::LIKERT_LABELS,
                'labels_kualitas' => self::KUALITAS_LABELS[$category->kode] ?? self::LIKERT_LABELS,
                'distribution'    => $distribution,
            ];
        }

        return $result;
    }

    public function index(Request $request)
    {
        return view('dashboard.index', $this->buildPayload($request));
    }

    /**
     * Endpoint AJAX: dipanggil saat dropdown kategori berganti. Mengembalikan
     * data chart mentah (untuk Chart.js) + HTML partial hasil render Blade
     * untuk daftar distribusi & tabel frekuensi.
     */
    public function data(Request $request)
    {
        $payload = $this->buildPayload($request);

        return response()->json([
            'panelHtml' => view('dashboard.partials.category-panel', [
                'activeCategory' => $payload['activeCategory'],
                'colors'         => $payload['colors'],
                'table'          => $payload['table'],
                'total'          => $payload['total'],
            ])->render(),
            'distribution'  => $payload['activeCategory']['distribution'],
            'labelsLikert'  => $payload['activeCategory']['labels_likert'],
            'colors'        => $payload['colors'],
        ]);
    }

    /**
     * Menyusun seluruh data yang dibutuhkan halaman Dashboard sesuai kategori
     * yang dipilih. Dipakai bersama oleh index() (render halaman penuh) dan
     * data() (AJAX, dipanggil saat dropdown kategori berganti).
     */
    private function buildPayload(Request $request): array
    {
        $questionnaire = $this->activeQuestionnaire();
        $categories    = $this->categoriesWithDistribution($questionnaire);

        $selected = $request->query('kategori');
        if (! $selected || ! array_key_exists($selected, $categories)) {
            $selected = array_key_first($categories) ?? 'persepsi_kebijakan';
        }

        $activeCategory = $categories[$selected] ?? [
            'label'           => 'Belum ada data',
            'labels_likert'   => self::LIKERT_LABELS,
            'labels_kualitas' => self::LIKERT_LABELS,
            'distribution'    => array_fill_keys(range(1, 5), 0),
        ];

        // Total = jumlah responden unik (bukan jumlah jawaban)
        $total = array_sum($activeCategory['distribution']);

        $table = [];
        foreach ($activeCategory['distribution'] as $scale => $count) {
            $table[] = [
                'scale'   => $scale,
                'label'   => $activeCategory['labels_kualitas'][$scale],
                'count'   => $count,
                'percent' => $total > 0 ? round($count / $total * 100, 1) : 0,
            ];
        }

        $stats    = $this->buildStats($questionnaire);
        $insights = $this->buildInsights($questionnaire);

        return [
            'stats'            => $stats,
            'colors'           => self::CHART_COLORS,
            'categories'       => $categories,
            'selectedCategory' => $selected,
            'activeCategory'   => $activeCategory,
            'table'            => $table,
            'total'            => $total,
            'insights'         => $insights,
        ];
    }

    /**
     * Kartu statistik atas: total responden (submitted) + rata-rata skor per
     * dimensi kunci (produktivitas diwakili oleh 'motivasi_kerja', WLB oleh
     * 'wlb', stres oleh 'stres_kerja' — sesuai dimensi yang memang ada di
     * instrumen kuisioner).
     */
    private function buildStats(?Questionnaire $questionnaire): array
    {
        if (! $questionnaire) {
            return [
                ['icon' => 'users',       'color' => 'red',    'label' => 'Total Responden',        'value' => '0',   'change' => '', 'positive' => true],
                ['icon' => 'trending-up', 'color' => 'green',  'label' => 'Produktivitas Rata-rata', 'value' => '-',   'change' => '', 'positive' => true],
                ['icon' => 'award',       'color' => 'purple', 'label' => 'Work-Life Balance',       'value' => '-',   'change' => '', 'positive' => true],
                ['icon' => 'target',      'color' => 'orange', 'label' => 'Tingkat Stres',           'value' => '-',   'change' => '', 'positive' => false],
            ];
        }

        $totalResponden = SurveyResponse::where('questionnaire_id', $questionnaire->id)
            ->where('status', 'submitted')
            ->count();

        $avgByKode = $this->avgScoreByCategoryKode($questionnaire);

        $produktivitas = $avgByKode['motivasi_kerja'] ?? null;
        $wlb           = $avgByKode['wlb'] ?? null;
        $stres         = $avgByKode['stres_kerja'] ?? null;

        return [
            ['icon' => 'users',       'color' => 'red',    'label' => 'Total Responden',        'value' => (string) $totalResponden, 'change' => '', 'positive' => true],
            ['icon' => 'trending-up', 'color' => 'green',  'label' => 'Produktivitas Rata-rata', 'value' => $produktivitas !== null ? number_format($produktivitas, 1) . '/5' : '-', 'change' => '', 'positive' => true],
            ['icon' => 'award',       'color' => 'purple', 'label' => 'Work-Life Balance',       'value' => $wlb !== null ? number_format($wlb, 1) . '/5' : '-', 'change' => '', 'positive' => true],
            ['icon' => 'target',      'color' => 'orange', 'label' => 'Tingkat Stres',           'value' => $stres !== null ? number_format($stres, 1) . '/5' : '-', 'change' => '', 'positive' => false],
        ];
    }

    /** Rata-rata skor likert per kode kategori, dari jawaban submitted kuisioner ini. */
    private function avgScoreByCategoryKode(Questionnaire $questionnaire): array
    {
        $rows = SurveyAnswer::query()
            ->join('questions', 'questions.id', '=', 'survey_answers.question_id')
            ->join('categories', 'categories.id', '=', 'questions.category_id')
            ->join('survey_responses', 'survey_responses.id', '=', 'survey_answers.survey_response_id')
            ->where('categories.questionnaire_id', $questionnaire->id)
            ->where('survey_responses.status', 'submitted')
            ->selectRaw('categories.kode as kode, avg(survey_answers.skor) as rata')
            ->groupBy('categories.kode')
            ->pluck('rata', 'kode');

        return $rows->map(fn ($v) => round((float) $v, 2))->all();
    }

    /**
     * Insight ringkas dihasilkan dari perbandingan skor rata-rata dimensi kunci.
     * Kalau belum ada data submitted sama sekali, tampilkan pesan netral
     * (bukan klaim tren palsu) supaya tidak menyesatkan.
     */
    private function buildInsights(?Questionnaire $questionnaire): array
    {
        if (! $questionnaire) {
            return [[
                'title' => 'Belum Ada Kuisioner Aktif',
                'color' => 'red',
                'text'  => 'Belum ada kuisioner berstatus aktif, sehingga insight psikososial belum dapat dihitung.',
            ]];
        }

        $avg = $this->avgScoreByCategoryKode($questionnaire);

        if (empty($avg)) {
            return [[
                'title' => 'Belum Ada Data',
                'color' => 'red',
                'text'  => 'Belum ada pengisian kuisioner yang submitted, sehingga insight psikososial belum dapat dihitung.',
            ]];
        }

        $insights = [];

        if (isset($avg['persepsi_kebijakan'])) {
            $insights[] = [
                'title' => 'Persepsi Kebijakan WFO/WFA',
                'color' => 'red',
                'text'  => "Rata-rata skor persepsi terhadap kebijakan WFO/WFA saat ini {$avg['persepsi_kebijakan']}/5, dari seluruh responden yang sudah submit kuisioner.",
            ];
        }

        if (isset($avg['kepuasan_kerja']) && isset($avg['dukungan_organisasi'])) {
            $insights[] = [
                'title' => 'Kepuasan & Dukungan Organisasi',
                'color' => 'green',
                'text'  => "Rata-rata kepuasan kerja {$avg['kepuasan_kerja']}/5, sementara persepsi dukungan organisasi {$avg['dukungan_organisasi']}/5.",
            ];
        }

        if (isset($avg['stres_kerja'])) {
            $insights[] = [
                'title' => 'Tingkat Stres Kerja',
                'color' => 'purple',
                'text'  => "Rata-rata skor stres kerja saat ini {$avg['stres_kerja']}/5 (semakin rendah semakin baik).",
            ];
        }

        return $insights ?: [[
            'title' => 'Data Terbatas',
            'color' => 'red',
            'text'  => 'Jumlah data yang submitted masih terbatas untuk menghasilkan insight yang representatif.',
        ]];
    }
}