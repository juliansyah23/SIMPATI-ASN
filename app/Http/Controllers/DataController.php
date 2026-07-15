<?php

namespace App\Http\Controllers;

use App\Models\Questionnaire;
use App\Models\SurveyResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class DataController extends Controller
{
    private const PUSAT_RISET = [
        'Pusat Riset Elektronika',
        'Pusat Riset Geoinformatika',
        'Pusat Riset Kecerdasan Artifisial dan Keamanan Siber',
        'Pusat Riset Komputasi',
        'Pusat Riset Mekatronika Cerdas',
        'Pusat Riset Sains Data dan Informasi',
        'Pusat Riset Telekomunikasi',
    ];

    private const PIE_COLORS = ['#a855f7', '#ef4444', '#10b981', '#f97316', '#3b82f6', '#ec4899', '#14b8a6'];

    /** Kuisioner aktif dipakai sebagai sumber data halaman ini. */
    private function activeQuestionnaire(): ?Questionnaire
    {
        return Questionnaire::where('status', 'aktif')->latest('id')->first();
    }

    /**
     * Map nilai field demografi 'pola_kehadiran' (mis. "WFA penuh", "WFO 3x/minggu")
     * ke salah satu dari tiga bucket mode kerja dipakai chart: WFA, WFO, Hybrid.
     * "WFO 5x/minggu" dianggap WFO penuh; selain itu (2x/3x per minggu) dianggap Hybrid.
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

        if (str_contains($value, 'wfo')) {
            return 'Hybrid';
        }

        return 'Hybrid';
    }

    /**
     * Query dasar: survey_responses submitted untuk kuisioner aktif, join ke users,
     * dengan filter pusat_riset/posisi/q (cari NIP/Nama) diterapkan di level SQL.
     * Filter 'tahun' diterapkan terpisah (lihat trendChart) karena submitted_at
     * dipakai untuk grouping bulanan, bukan filter exclusive di sini, supaya
     * stat cards & tabel performa tetap konsisten dengan tahun yang dipilih.
     */
    private function baseQuery(Questionnaire $questionnaire, array $filters)
    {
        $query = SurveyResponse::query()
            ->join('users', 'users.id', '=', 'survey_responses.user_id')
            ->where('survey_responses.questionnaire_id', $questionnaire->id)
            ->where('survey_responses.status', 'submitted');

        if (! empty($filters['tahun'])) {
            $query->whereYear('survey_responses.submitted_at', $filters['tahun']);
        }

        if (! empty($filters['pusat_riset'])) {
            $query->where('users.pusat_riset', $filters['pusat_riset']);
        }

        if (! empty($filters['posisi'])) {
            $query->where('users.posisi', $filters['posisi']);
        }

        if (! empty($filters['q'])) {
            $q = $filters['q'];
            $query->where(function ($w) use ($q) {
                $w->where('users.nip', 'like', "%{$q}%")
                  ->orWhere('users.name', 'like', "%{$q}%");
            });
        }

        return $query;
    }

    /**
     * Ambil bucket mode kerja per survey_response_id (dari jawaban demografi
     * field_key='pola_kehadiran'), untuk kuisioner aktif. Dipakai untuk filter
     * 'mode_kerja' (yang tidak ada kolomnya langsung di users/survey_responses)
     * dan untuk grouping chart mode kerja / radar WFA vs WFO.
     */
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
     * OPTIMISASI: ambil rata-rata skor per kategori UNTUK SETIAP survey_response_id
     * sekaligus, dalam SATU query (bukan satu query per pusat riset / mode kerja /
     * bulan seperti sebelumnya). Hasilnya: [response_id => ['kode_kategori' => rata]].
     *
     * Semua agregasi lain (per pusat riset, per mode kerja, per bulan, dst) tinggal
     * mengelompokkan response_id yang relevan lalu me-rata-rata nilai dari peta ini
     * di PHP — tanpa perlu query baru ke `survey_answers` sama sekali.
     */
    private function scoresByResponse(Questionnaire $questionnaire, array $responseIds): Collection
    {
        if (empty($responseIds)) {
            return collect();
        }

        return DB::table('survey_answers')
            ->join('questions', 'questions.id', '=', 'survey_answers.question_id')
            ->join('categories', 'categories.id', '=', 'questions.category_id')
            ->where('categories.questionnaire_id', $questionnaire->id)
            ->whereIn('survey_answers.survey_response_id', $responseIds)
            ->selectRaw('survey_answers.survey_response_id, categories.kode, AVG(survey_answers.skor) as rata')
            ->groupBy('survey_answers.survey_response_id', 'categories.kode')
            ->get()
            ->groupBy('survey_response_id')
            ->map(fn ($rows) => $rows->pluck('rata', 'kode')->map(fn ($v) => (float) $v)->all());
    }

    /**
     * Rata-rata per kode kategori untuk sekumpulan response_id, dihitung dari peta
     * skor yang sudah diambil sekali lewat scoresByResponse() — tanpa query baru.
     * (Rata-rata dari rata-rata per-response valid karena jumlah pertanyaan per
     * kategori sama untuk semua responden — 4 soal Likert per dimensi.)
     */
    private function aggregateScores(Collection $scores, array $ids): array
    {
        if (empty($ids) || $scores->isEmpty()) {
            return [];
        }

        $sums = [];
        $counts = [];

        foreach ($ids as $id) {
            foreach ($scores[$id] ?? [] as $kode => $value) {
                $sums[$kode] = ($sums[$kode] ?? 0) + $value;
                $counts[$kode] = ($counts[$kode] ?? 0) + 1;
            }
        }

        $out = [];
        foreach ($sums as $kode => $sum) {
            $out[$kode] = round($sum / $counts[$kode], 2);
        }

        return $out;
    }

    public function index(Request $request)
    {
        return view('data.index', $this->buildPayload($request));
    }

    /**
     * Endpoint AJAX: menerima filter yang sama persis dengan index(), tapi
     * mengembalikan JSON berisi data chart mentah (untuk di-render ulang oleh
     * Chart.js) + HTML partial hasil render Blade (untuk tabel/kartu yang
     * markup-nya kompleks, supaya tidak perlu ditulis ulang di JS).
     */
    public function data(Request $request)
    {
        $payload = $this->buildPayload($request);
        $isAdmin = auth()->check() && auth()->user()->isAdmin();

        return response()->json([
            'statsHtml'   => view('data.partials.stat-cards', ['stats' => $payload['stats']])->render(),
            'centersHtml' => view('data.partials.centers-table', ['centers' => $payload['centers']])->render(),
            'detailHtml'  => $isAdmin
                ? view('data.partials.detail-table', ['detailResponden' => $payload['detailResponden']])->render()
                : null,
            'detailCount'    => count($payload['detailResponden']),
            'modeKerjaChart' => $payload['modeKerjaChart'],
            'pieChart'       => $payload['pieChart'],
            'radarChart'     => $payload['radarChart'],
            'trendChart'     => $payload['trendChart'],
            'trendTitle'     => 'Tren Psikososial ' . ((($payload['selected']['tahun'] ?? '')) ?: 'Semua Tahun'),
            'exportQuery'    => http_build_query(array_filter($payload['selected'] ?? [])),
        ]);
    }

    /**
     * Menyusun seluruh data yang dibutuhkan halaman Data (stat cards, chart,
     * tabel performa, detail responden) sesuai filter yang dikirim.
     * Dipakai bersama oleh index() (render halaman penuh) dan data() (AJAX).
     */
    private function buildPayload(Request $request): array
    {
        $questionnaire = $this->activeQuestionnaire();

        $filters_selected = $request->only(['tahun', 'pusat_riset', 'posisi', 'mode_kerja', 'q']);
        // Default tahun: kosong = semua tahun (tidak filter), bukan hardcode 2025
        $tahun = $filters_selected['tahun'] ?? '';

        if (! $questionnaire) {
            return $this->emptyPayload($request, $tahun);
        }

        $modeKerjaMap = $this->modeKerjaByResponseId($questionnaire);

        // Responses matching SQL-level filters (tahun/pusat_riset/posisi/q).
        $responses = $this->baseQuery($questionnaire, $filters_selected)
            ->select(
                'survey_responses.id',
                'survey_responses.rata_rata',
                'survey_responses.submitted_at',
                'users.pusat_riset',
                'users.nip',
                'users.name',
                'users.posisi'
            )
            ->get();

        // Apply mode_kerja filter in PHP (value lives in survey_demografi, not a column).
        if (! empty($filters_selected['mode_kerja'])) {
            $responses = $responses->filter(
                fn ($r) => ($modeKerjaMap[$r->id] ?? null) === $filters_selected['mode_kerja']
            )->values();
        }

        // Set responden yang lebih luas (tanpa filter pusat_riset) untuk tabel
        // perbandingan "Performa per Pusat Riset".
        $filtersWithoutPusatRiset = $filters_selected;
        unset($filtersWithoutPusatRiset['pusat_riset']);
        $allCenterResponses = $this->baseQuery($questionnaire, $filtersWithoutPusatRiset)
            ->select('survey_responses.id', 'users.pusat_riset')
            ->get();

        // Set responden untuk trend chart (filter tahun diperlakukan khusus, lihat trendChart()).
        $filtersForTrend = $filters_selected;
        if (! empty($tahun)) {
            $filtersForTrend['tahun'] = $tahun;
        } else {
            unset($filtersForTrend['tahun']);
        }
        $trendResponses = $this->baseQuery($questionnaire, $filtersForTrend)
            ->whereNotNull('survey_responses.submitted_at')
            ->select('survey_responses.id', 'survey_responses.submitted_at')
            ->get();

        // ── Query tunggal untuk SEMUA kebutuhan rata-rata skor di halaman ini ──
        // Union id dari ketiga set di atas, lalu ambil skornya sekali saja.
        // Sebelumnya bagian ini memicu ~25+ query JOIN terpisah (per pusat riset,
        // per mode kerja, per bulan, dst). Sekarang tinggal 1 query.
        $allIds = $responses->pluck('id')
            ->merge($allCenterResponses->pluck('id'))
            ->merge($trendResponses->pluck('id'))
            ->unique()
            ->values()
            ->all();

        $scores = $this->scoresByResponse($questionnaire, $allIds);

        $totalRespons = $responses->count();
        $avgByCategory = $this->aggregateScores($scores, $responses->pluck('id')->all());

        $stats = [
            [
                'icon' => 'users', 'color' => 'red', 'label' => 'Total Respons',
                'value' => (string) $totalRespons, 'unit' => '', 'subtitle' => 'Kuisioner terisi',
            ],
            [
                'icon' => 'zap', 'color' => 'green', 'label' => 'Rata-rata Produktivitas',
                'value' => isset($avgByCategory['motivasi_kerja']) ? number_format($avgByCategory['motivasi_kerja'], 1) : '-',
                'unit' => '/5', 'subtitle' => 'Skala 1-5',
            ],
            [
                'icon' => 'heart', 'color' => 'purple', 'label' => 'Work-Life Balance',
                'value' => isset($avgByCategory['wlb']) ? number_format($avgByCategory['wlb'], 1) : '-',
                'unit' => '/5', 'subtitle' => 'Keseimbangan hidup-kerja',
            ],
            [
                'icon' => 'brain', 'color' => 'orange', 'label' => 'Tingkat Stres',
                'value' => isset($avgByCategory['stres_kerja']) ? number_format($avgByCategory['stres_kerja'], 1) : '-',
                'unit' => '/5', 'subtitle' => 'Semakin rendah semakin baik',
            ],
        ];

        $centers          = $this->researchCenterPerformance($allCenterResponses, $scores);
        $modeKerjaChart    = $this->modeKerjaChart($responses, $modeKerjaMap, $scores);
        $pieChart          = $this->pieChartByPusatRiset($responses);
        $radarChart        = $this->radarWfaVsWfo($responses, $modeKerjaMap, $scores);
        $trendChart        = $this->trendChart($trendResponses, $scores, $tahun);

        $filtersOptions   = $this->filterOptions();
        $detailResponden  = $this->detailResponden($questionnaire, $responses, $modeKerjaMap, $scores);

        return [
            'stats' => $stats,
            'centers' => $centers,
            'modeKerjaChart' => $modeKerjaChart,
            'pieChart' => $pieChart,
            'radarChart' => $radarChart,
            'trendChart' => $trendChart,
            'filters' => $filtersOptions,
            'selected' => $filters_selected,
            'detailResponden' => $detailResponden,
        ];
    }

    /**
     * Tabel "Performa per Pusat Riset": jumlah respons + rata-rata skor
     * (produktivitas/kolaborasi/wlb/stres) per pusat riset, dari kuisioner aktif,
     * dengan filter tahun/posisi/q ikut diterapkan (kecuali filter pusat_riset
     * sendiri, supaya tabel tetap menampilkan semua pusat riset untuk dibandingkan).
     */
    private function researchCenterPerformance(Collection $allCenterResponses, Collection $scores): array
    {
        $byPusat = $allCenterResponses->groupBy('pusat_riset');

        $centers = [];
        foreach (self::PUSAT_RISET as $name) {
            $group = $byPusat->get($name, collect());
            $ids   = $group->pluck('id')->all();
            $avg   = $this->aggregateScores($scores, $ids);

            $centers[] = [
                'name'          => $name,
                'respons'       => $group->count(),
                'produktivitas' => $avg['motivasi_kerja'] ?? 0.0,
                'kolaborasi'    => $avg['dukungan_organisasi'] ?? 0.0,
                'wlb'           => $avg['wlb'] ?? 0.0,
                'stres'         => $avg['stres_kerja'] ?? 0.0,
            ];
        }

        return $centers;
    }

    /** Bar chart: rata-rata Produktivitas/WLB/Kolaborasi per bucket mode kerja (WFA/WFO/Hybrid). */
    private function modeKerjaChart(Collection $responses, array $modeKerjaMap, Collection $scores): array
    {
        $buckets = ['WFA' => [], 'WFO' => [], 'Hybrid' => []];

        foreach ($responses as $r) {
            $bucket = $modeKerjaMap[$r->id] ?? 'Hybrid';
            $buckets[$bucket][] = $r->id;
        }

        $produktivitas = [];
        $wlb = [];
        $kolaborasi = [];

        foreach (['WFA', 'WFO', 'Hybrid'] as $label) {
            $avg = $this->aggregateScores($scores, $buckets[$label]);
            $produktivitas[] = $avg['motivasi_kerja'] ?? 0.0;
            $wlb[]           = $avg['wlb'] ?? 0.0;
            $kolaborasi[]    = $avg['dukungan_organisasi'] ?? 0.0;
        }

        return [
            'labels' => ['WFA', 'WFO', 'Hybrid'],
            'datasets' => [
                ['label' => 'Produktivitas', 'color' => '#10b981', 'data' => $produktivitas],
                ['label' => 'Work-Life Balance', 'color' => '#a855f7', 'data' => $wlb],
                ['label' => 'Kolaborasi', 'color' => '#3b82f6', 'data' => $kolaborasi],
            ],
        ];
    }

    /** Pie chart: distribusi jumlah respons (yang lolos filter) per pusat riset. */
    private function pieChartByPusatRiset(Collection $responses): array
    {
        $counts = $responses->groupBy('pusat_riset')->map->count();

        $labels = [];
        $data   = [];
        foreach (self::PUSAT_RISET as $name) {
            $labels[] = $name;
            $data[]   = (int) ($counts[$name] ?? 0);
        }

        return [
            'labels' => $labels,
            'data'   => $data,
            'colors' => self::PIE_COLORS,
        ];
    }

    /** Radar chart: rata-rata skor 5 dimensi terdekat, dibandingkan antara bucket WFA vs WFO. */
    private function radarWfaVsWfo(Collection $responses, array $modeKerjaMap, Collection $scores): array
    {
        $wfaIds = [];
        $wfoIds = [];

        foreach ($responses as $r) {
            $bucket = $modeKerjaMap[$r->id] ?? null;
            if ($bucket === 'WFA') {
                $wfaIds[] = $r->id;
            } elseif ($bucket === 'WFO') {
                $wfoIds[] = $r->id;
            }
        }

        $avgWfa = $this->aggregateScores($scores, $wfaIds);
        $avgWfo = $this->aggregateScores($scores, $wfoIds);

        // Dimensi radar dipetakan ke kode kategori yang tersedia di instrumen.
        $dimensions = [
            'Produktivitas'         => 'motivasi_kerja',
            'Kolaborasi'            => 'dukungan_organisasi',
            'Work-Life Balance'     => 'wlb',
            'Dukungan Teknis'       => 'persepsi_kebijakan',
            'Kesejahteraan Mental'  => 'engagement_pegawai',
        ];

        $wfaData = [];
        $wfoData = [];
        foreach ($dimensions as $kode) {
            $wfaData[] = $avgWfa[$kode] ?? 0.0;
            $wfoData[] = $avgWfo[$kode] ?? 0.0;
        }

        return [
            'labels' => array_keys($dimensions),
            'datasets' => [
                ['label' => 'WFA', 'color' => '#ef4444', 'data' => $wfaData],
                ['label' => 'WFO', 'color' => '#a855f7', 'data' => $wfoData],
            ],
        ];
    }

    /**
     * Line chart: tren rata-rata skor per bulan dalam tahun yang dipilih, dari
     * kuisioner aktif, dipecah per dimensi (Produktivitas='motivasi_kerja',
     * Work-Life Balance='wlb', Kolaborasi='dukungan_organisasi') — bukan
     * memakai satu nilai 'rata_rata' yang sama untuk ketiga garis, supaya
     * tren antar dimensi benar-benar bisa dibandingkan.
     * Filter pusat_riset/posisi/q tetap diterapkan; filter mode_kerja tidak
     * (perlu join tambahan per bulan) — cukup konsisten dengan chart lain
     * yang sudah scoped ke tahun terpilih.
     *
     * Menerima $rows (hasil query response id + submitted_at) dan $scores
     * (peta skor per response, sudah diambil sekali di index()) — tidak lagi
     * melakukan query baru per bulan seperti sebelumnya.
     */
    private function trendChart(Collection $rows, Collection $scores, string $tahun): array
    {
        $grouped = $rows->groupBy(fn ($r) => date('Y-m', strtotime($r->submitted_at)));

        $monthLabels = ['01' => 'Jan', '02' => 'Feb', '03' => 'Mar', '04' => 'Apr',
                        '05' => 'May', '06' => 'Jun', '07' => 'Jul', '08' => 'Aug',
                        '09' => 'Sep', '10' => 'Oct', '11' => 'Nov', '12' => 'Dec'];

        $keys = $grouped->keys()->sort()->values();

        if ($keys->isEmpty()) {
            return [
                'labels' => [],
                'datasets' => [
                    ['label' => 'Produktivitas', 'color' => '#10b981', 'data' => []],
                    ['label' => 'Work-Life Balance', 'color' => '#a855f7', 'data' => []],
                    ['label' => 'Kolaborasi', 'color' => '#3b82f6', 'data' => []],
                ],
            ];
        }

        $produktivitas = [];
        $wlb = [];
        $kolaborasi = [];
        $labels = [];

        foreach ($keys as $yearMonth) {
            $ids = $grouped->get($yearMonth, collect())->pluck('id')->all();
            $avg = $this->aggregateScores($scores, $ids);

            // Label: "Jan 2025" jika lintas tahun, cukup "Jan" jika satu tahun
            [$yr, $mo] = explode('-', $yearMonth);
            $labels[] = ! empty($tahun) ? ($monthLabels[$mo] ?? $mo) : (($monthLabels[$mo] ?? $mo) . ' ' . $yr);

            $produktivitas[] = $avg['motivasi_kerja'] ?? 0.0;
            $wlb[]           = $avg['wlb'] ?? 0.0;
            $kolaborasi[]    = $avg['dukungan_organisasi'] ?? 0.0;
        }

        return [
            'labels' => $labels,
            'datasets' => [
                ['label' => 'Produktivitas', 'color' => '#10b981', 'data' => $produktivitas],
                ['label' => 'Work-Life Balance', 'color' => '#a855f7', 'data' => $wlb],
                ['label' => 'Kolaborasi', 'color' => '#3b82f6', 'data' => $kolaborasi],
            ],
        ];
    }

    private function filterOptions(): array
    {
        // Ambil daftar tahun dari data submitted yang ada di DB (dinamis)
        $tahunList = SurveyResponse::where('status', 'submitted')
            ->whereNotNull('submitted_at')
            ->selectRaw('YEAR(submitted_at) as tahun')
            ->distinct()
            ->orderByDesc('tahun')
            ->pluck('tahun')
            ->map(fn ($t) => (string) $t)
            ->all();

        // Fallback jika belum ada data
        if (empty($tahunList)) {
            $tahunList = [(string) date('Y')];
        }

        return [
            'tahun' => array_merge(['Semua Tahun'], $tahunList),
            'pusat_riset' => ['Semua Pusat Riset' => 'Semua Pusat Riset']
                + collect(config('options.pusat_riset'))->mapWithKeys(fn ($name) => [$name => $this->shortName($name)])->all(),
            'posisi' => ['Semua Posisi' => 'Semua Posisi']
                + array_combine(config('options.posisi'), config('options.posisi')),
            'mode_kerja' => [
                'Semua Mode Kerja' => 'Semua Mode Kerja',
                'WFA' => 'WFA',
                'WFO' => 'WFO',
                'Hybrid' => 'Hybrid',
            ],
        ];
    }

    /** Nama singkat pusat riset untuk label dropdown (mis. "Pusat Riset Komputasi" -> "Komputasi"). */
    private function shortName(string $fullName): string
    {
        $map = [
            'Pusat Riset Elektronika' => 'Elektronika',
            'Pusat Riset Geoinformatika' => 'Geoinformatika',
            'Pusat Riset Kecerdasan Artifisial dan Keamanan Siber' => 'AI & Keamanan Siber',
            'Pusat Riset Komputasi' => 'Komputasi',
            'Pusat Riset Mekatronika Cerdas' => 'Mekatronika Cerdas',
            'Pusat Riset Sains Data dan Informasi' => 'Sains Data & Informasi',
            'Pusat Riset Telekomunikasi' => 'Telekomunikasi',
        ];

        return $map[$fullName] ?? $fullName;
    }

    /**
     * Tabel detail responden: satu baris per response yang lolos filter,
     * berisi data user + demografi + rata-rata skor per kategori.
     * Hanya ditampilkan untuk admin (dicek di view).
     *
     * Menerima $scores (peta skor per response, sudah diambil sekali di index())
     * — tidak lagi melakukan query AVG(...) sendiri seperti sebelumnya.
     */
    private function detailResponden(Questionnaire $questionnaire, Collection $responses, array $modeKerjaMap, Collection $scores): array
    {
        if ($responses->isEmpty()) {
            return [];
        }

        $responseIds = $responses->pluck('id')->all();

        // Ambil data demografi (field_key => value) per response_id — ini query
        // terpisah dari `survey_answers` (tabel & data berbeda), jadi tetap perlu
        // query sendiri, tapi tetap hanya 1 query untuk semua response.
        $demografiRows = DB::table('survey_demografi')
            ->join('demografi_fields', 'demografi_fields.id', '=', 'survey_demografi.demografi_field_id')
            ->join('categories', 'categories.id', '=', 'demografi_fields.category_id')
            ->where('categories.questionnaire_id', $questionnaire->id)
            ->whereIn('survey_demografi.survey_response_id', $responseIds)
            ->select('survey_demografi.survey_response_id', 'demografi_fields.field_key', 'survey_demografi.value')
            ->get()
            ->groupBy('survey_response_id')
            ->map(fn ($rows) => $rows->pluck('value', 'field_key')->all());

        $result = [];
        foreach ($responses as $r) {
            $demografi = $demografiRows[$r->id] ?? [];
            $avg       = $scores[$r->id] ?? [];

            $result[] = [
                'response_id'  => $r->id,
                'nip'          => $r->nip ?? '-',
                'nama'         => $r->name ?? '-',
                'pusat_riset'  => $r->pusat_riset ?? '-',
                'posisi'       => $r->posisi ?? '-',
                'mode_kerja'   => $modeKerjaMap[$r->id] ?? '-',
                'jenis_kelamin'=> $demografi['jenis_kelamin'] ?? '-',
                'usia'         => $demografi['usia'] ?? '-',
                'lama_bekerja' => $demografi['lama_bekerja'] ?? '-',
                'submitted_at' => $r->submitted_at ? date('d/m/Y', strtotime($r->submitted_at)) : '-',
                'rata_rata'    => $r->rata_rata ? number_format((float) $r->rata_rata, 2) : '-',
                'per_kategori' => [
                    'persepsi_kebijakan'  => isset($avg['persepsi_kebijakan'])  ? number_format($avg['persepsi_kebijakan'], 2)  : '-',
                    'motivasi_kerja'      => isset($avg['motivasi_kerja'])      ? number_format($avg['motivasi_kerja'], 2)      : '-',
                    'kepuasan_kerja'      => isset($avg['kepuasan_kerja'])      ? number_format($avg['kepuasan_kerja'], 2)      : '-',
                    'engagement_pegawai'  => isset($avg['engagement_pegawai'])  ? number_format($avg['engagement_pegawai'], 2)  : '-',
                    'stres_kerja'         => isset($avg['stres_kerja'])         ? number_format($avg['stres_kerja'], 2)         : '-',
                    'dukungan_organisasi' => isset($avg['dukungan_organisasi']) ? number_format($avg['dukungan_organisasi'], 2) : '-',
                    'wlb'                 => isset($avg['wlb'])                 ? number_format($avg['wlb'], 2)                 : '-',
                ],
            ];
        }

        return $result;
    }

    // ──────────────────────────────────────────────────────────────────────
    //  Export — Performa per Pusat Riset & Data Detail Responden
    //  Excel diekspor sebagai CSV (dibuka langsung oleh Excel, tanpa perlu
    //  dependency tambahan seperti PhpSpreadsheet). PDF diekspor sebagai
    //  halaman cetak (print-friendly HTML + auto window.print()) yang dibuka
    //  di tab baru — pengguna tinggal pilih "Save as PDF" di dialog cetak
    //  browser, tanpa perlu dependency seperti dompdf.
    // ──────────────────────────────────────────────────────────────────────

    /** Data "Performa per Pusat Riset" sesuai filter aktif, dipakai bersama oleh export Excel & PDF. */
    private function pusatRisetExportData(Request $request): array
    {
        $questionnaire = $this->activeQuestionnaire();
        $filters       = $request->only(['tahun', 'pusat_riset', 'posisi', 'mode_kerja', 'q']);

        if (! $questionnaire) {
            return collect(self::PUSAT_RISET)->map(fn ($name) => [
                'name' => $name, 'respons' => 0, 'produktivitas' => 0.0, 'kolaborasi' => 0.0, 'wlb' => 0.0, 'stres' => 0.0,
            ])->all();
        }

        $filtersWithoutPusatRiset = $filters;
        unset($filtersWithoutPusatRiset['pusat_riset']);

        $allCenterResponses = $this->baseQuery($questionnaire, $filtersWithoutPusatRiset)
            ->select('survey_responses.id', 'users.pusat_riset')
            ->get();

        $scores = $this->scoresByResponse($questionnaire, $allCenterResponses->pluck('id')->all());

        return $this->researchCenterPerformance($allCenterResponses, $scores);
    }

    public function exportPusatRisetExcel(Request $request)
    {
        $centers  = $this->pusatRisetExportData($request);
        $filename = 'performa-pusat-riset-' . now()->format('Y-m-d_His') . '.csv';

        return response()->streamDownload(function () use ($centers) {
            $out = fopen('php://output', 'w');
            fwrite($out, "\xEF\xBB\xBF"); // BOM supaya karakter Excel terbaca benar
            fputcsv($out, ['Pusat Riset', 'Respons', 'Produktivitas', 'Kolaborasi', 'Work-Life Balance', 'Stres']);
            foreach ($centers as $c) {
                fputcsv($out, [
                    $c['name'],
                    $c['respons'],
                    number_format($c['produktivitas'], 2),
                    number_format($c['kolaborasi'], 2),
                    number_format($c['wlb'], 2),
                    number_format($c['stres'], 2),
                ]);
            }
            fclose($out);
        }, $filename, ['Content-Type' => 'text/csv; charset=UTF-8']);
    }

    public function exportPusatRisetPdf(Request $request)
    {
        return view('data.export.pusat-riset-pdf', [
            'centers'  => $this->pusatRisetExportData($request),
            'selected' => $request->only(['tahun', 'pusat_riset', 'posisi', 'mode_kerja', 'q']),
            'dicetak'  => now()->translatedFormat('d F Y, H:i'),
        ]);
    }

    /** Data "Data Detail Responden" sesuai filter aktif, dipakai bersama oleh export Excel & PDF. */
    private function respondenExportData(Request $request): array
    {
        $questionnaire = $this->activeQuestionnaire();
        if (! $questionnaire) {
            return [];
        }

        $filters      = $request->only(['tahun', 'pusat_riset', 'posisi', 'mode_kerja', 'q']);
        $modeKerjaMap = $this->modeKerjaByResponseId($questionnaire);

        $responses = $this->baseQuery($questionnaire, $filters)
            ->select(
                'survey_responses.id',
                'survey_responses.rata_rata',
                'survey_responses.submitted_at',
                'users.pusat_riset',
                'users.nip',
                'users.name',
                'users.posisi'
            )
            ->get();

        if (! empty($filters['mode_kerja'])) {
            $responses = $responses->filter(
                fn ($r) => ($modeKerjaMap[$r->id] ?? null) === $filters['mode_kerja']
            )->values();
        }

        $scores = $this->scoresByResponse($questionnaire, $responses->pluck('id')->all());

        return $this->detailResponden($questionnaire, $responses, $modeKerjaMap, $scores);
    }

    public function exportRespondenExcel(Request $request)
    {
        abort_unless(auth()->check() && auth()->user()->isAdmin(), 403);

        $rows     = $this->respondenExportData($request);
        $filename = 'data-detail-responden-' . now()->format('Y-m-d_His') . '.csv';

        return response()->streamDownload(function () use ($rows) {
            $out = fopen('php://output', 'w');
            fwrite($out, "\xEF\xBB\xBF");
            fputcsv($out, [
                'NIP', 'Nama', 'Pusat Riset', 'Posisi', 'Jenis Kelamin', 'Usia', 'Lama Bekerja', 'Mode Kerja',
                'I. Kebijakan', 'II. Motivasi', 'III. Kepuasan', 'IV. Engagement', 'V. Stres', 'VI. Dukungan', 'VII. WLB',
                'Rata-rata', 'Tanggal Submit',
            ]);
            foreach ($rows as $r) {
                fputcsv($out, [
                    $r['nip'], $r['nama'], $r['pusat_riset'], $r['posisi'], $r['jenis_kelamin'], $r['usia'],
                    $r['lama_bekerja'], $r['mode_kerja'],
                    $r['per_kategori']['persepsi_kebijakan'], $r['per_kategori']['motivasi_kerja'],
                    $r['per_kategori']['kepuasan_kerja'], $r['per_kategori']['engagement_pegawai'],
                    $r['per_kategori']['stres_kerja'], $r['per_kategori']['dukungan_organisasi'],
                    $r['per_kategori']['wlb'],
                    $r['rata_rata'], $r['submitted_at'],
                ]);
            }
            fclose($out);
        }, $filename, ['Content-Type' => 'text/csv; charset=UTF-8']);
    }

    public function exportRespondenPdf(Request $request)
    {
        abort_unless(auth()->check() && auth()->user()->isAdmin(), 403);

        return view('data.export.responden-pdf', [
            'rows'     => $this->respondenExportData($request),
            'selected' => $request->only(['tahun', 'pusat_riset', 'posisi', 'mode_kerja', 'q']),
            'dicetak'  => now()->translatedFormat('d F Y, H:i'),
        ]);
    }

    /** Fallback data kalau belum ada kuisioner aktif sama sekali (DB baru/kosong). */
    private function emptyPayload(Request $request, string $tahun): array
    {
        $tahun = $tahun ?: '';
        $stats = [
            ['icon' => 'users', 'color' => 'red', 'label' => 'Total Respons', 'value' => '0', 'unit' => '', 'subtitle' => 'Kuisioner terisi'],
            ['icon' => 'zap', 'color' => 'green', 'label' => 'Rata-rata Produktivitas', 'value' => '-', 'unit' => '/5', 'subtitle' => 'Skala 1-5'],
            ['icon' => 'heart', 'color' => 'purple', 'label' => 'Work-Life Balance', 'value' => '-', 'unit' => '/5', 'subtitle' => 'Keseimbangan hidup-kerja'],
            ['icon' => 'brain', 'color' => 'orange', 'label' => 'Tingkat Stres', 'value' => '-', 'unit' => '/5', 'subtitle' => 'Semakin rendah semakin baik'],
        ];

        $centers = collect(self::PUSAT_RISET)->map(fn ($name) => [
            'name' => $name, 'respons' => 0, 'produktivitas' => 0.0, 'kolaborasi' => 0.0, 'wlb' => 0.0, 'stres' => 0.0,
        ])->all();

        $empty3 = ['WFA' => 0.0, 'WFO' => 0.0, 'Hybrid' => 0.0];

        return [
            'stats' => $stats,
            'centers' => $centers,
            'modeKerjaChart' => [
                'labels' => ['WFA', 'WFO', 'Hybrid'],
                'datasets' => [
                    ['label' => 'Produktivitas', 'color' => '#10b981', 'data' => array_values($empty3)],
                    ['label' => 'Work-Life Balance', 'color' => '#a855f7', 'data' => array_values($empty3)],
                    ['label' => 'Kolaborasi', 'color' => '#3b82f6', 'data' => array_values($empty3)],
                ],
            ],
            'pieChart' => [
                'labels' => self::PUSAT_RISET,
                'data' => array_fill(0, count(self::PUSAT_RISET), 0),
                'colors' => self::PIE_COLORS,
            ],
            'radarChart' => [
                'labels' => ['Produktivitas', 'Kolaborasi', 'Work-Life Balance', 'Dukungan Teknis', 'Kesejahteraan Mental'],
                'datasets' => [
                    ['label' => 'WFA', 'color' => '#ef4444', 'data' => [0, 0, 0, 0, 0]],
                    ['label' => 'WFO', 'color' => '#a855f7', 'data' => [0, 0, 0, 0, 0]],
                ],
            ],
            'trendChart' => [
                'labels' => [],
                'datasets' => [
                    ['label' => 'Produktivitas', 'color' => '#10b981', 'data' => []],
                    ['label' => 'Work-Life Balance', 'color' => '#a855f7', 'data' => []],
                    ['label' => 'Kolaborasi', 'color' => '#3b82f6', 'data' => []],
                ],
            ],
            'filters' => $this->filterOptions(),
            'selected' => $request->only(['tahun', 'pusat_riset', 'posisi', 'mode_kerja', 'q']),
            'detailResponden' => [],
        ];
    }
}