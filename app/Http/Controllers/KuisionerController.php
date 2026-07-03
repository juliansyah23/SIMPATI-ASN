<?php

namespace App\Http\Controllers;

use App\Models\Questionnaire;
use App\Models\SurveyResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class KuisionerController extends Controller
{
    /**
     * Field-field untuk kategori Data Demografi Responden.
     * Dipakai HANYA oleh builder admin (create/edit) sebagai referensi opsi default
     * saat membuat kategori demografi baru dari nol — bukan dipakai lagi oleh sisi
     * pengisian pegawai (show/step/submit), yang sekarang baca dari DB.
     */
    private function demografiFields(): array
    {
        return [
            [
                'id'      => 'jenis_kelamin',
                'label'   => 'Jenis Kelamin',
                'type'    => 'choice',
                'options' => ['Laki-laki', 'Perempuan'],
            ],
            [
                'id'      => 'usia',
                'label'   => 'Usia',
                'type'    => 'choice',
                'options' => ['<30 tahun', '30–39 tahun', '40–49 tahun', '≥50 tahun'],
            ],
            [
                'id'      => 'kategori_jabatan',
                'label'   => 'Kategori Jabatan',
                'type'    => 'choice',
                'options' => config('options.posisi'),
            ],
            [
                'id'      => 'lama_bekerja',
                'label'   => 'Lama Bekerja',
                'type'    => 'choice',
                'options' => ['<5 tahun', '5–10 tahun', '11–20 tahun', '>20 tahun'],
            ],
            [
                'id'      => 'pola_kehadiran',
                'label'   => 'Pola Kehadiran',
                'type'    => 'choice',
                'options' => ['WFA penuh', 'WFO 2x/minggu', 'WFO 3x/minggu', 'WFO 5x/minggu'],
            ],
        ];
    }

    /**
     * Ambil struktur kategori (+ pertanyaan/field demografi) milik satu kuisioner
     * dari DB, lalu bentuk ulang ke array dengan shape yang SAMA seperti dulu di
     * categories() hardcode, supaya kuisioner/show.blade.php tidak perlu diubah:
     *
     *   [1 => ['kode'=>.., 'title'=>.., 'subtitle'=>.., 'type'=>'demografi', 'fields' => [...]],
     *    2 => ['kode'=>.., 'title'=>.., 'subtitle'=>.., 'type'=>'likert',    'questions'=>[...], 'esai'=>[...]],
     *    ...]
     *
     * Key array dimulai dari 1 dan berurutan sesuai `urutan` kategori di DB, supaya
     * tetap bisa diakses langsung lewat $categories[$step] seperti sebelumnya.
     * 'id' pada setiap question/field memakai id ASLI dari tabel questions/demografi_fields
     * (bukan dummy 101/102/... lagi), karena id inilah yang dipakai untuk menyimpan
     * jawaban ke survey_answers/survey_essays/survey_demografi.
     */
    private function categoriesFor(Questionnaire $questionnaire): array
    {
        $categories = $questionnaire->categories()
            ->with(['questions', 'demografiFields'])
            ->orderBy('urutan')
            ->get();

        $result = [];
        $step   = 1;

        foreach ($categories as $kategori) {
            if ($kategori->type === 'demografi') {
                $result[$step] = [
                    'id'       => $kategori->id,
                    'kode'     => $kategori->kode,
                    'title'    => $kategori->title,
                    'subtitle' => $kategori->subtitle,
                    'type'     => 'demografi',
                    'fields'   => $kategori->demografiFields->sortBy('urutan')->map(fn ($f) => [
                        'id'        => $f->id,
                        'field_key' => $f->field_key,
                        'label'     => $f->label,
                        'type'      => $f->type,
                        'options'   => $f->options ?? [],
                    ])->values()->all(),
                ];
            } else {
                $questions = $kategori->questions->sortBy('urutan')->values();

                $result[$step] = [
                    'id'        => $kategori->id,
                    'kode'      => $kategori->kode,
                    'title'     => $kategori->title,
                    'subtitle'  => $kategori->subtitle,
                    'type'      => 'likert',
                    'questions' => $questions->where('type', 'likert')->map(fn ($q) => [
                        'id'         => $q->id,
                        'pertanyaan' => $q->pertanyaan,
                    ])->values()->all(),
                    'esai' => $questions->where('type', 'esai')->map(fn ($q) => [
                        'id'         => $q->id,
                        'pertanyaan' => $q->pertanyaan,
                    ])->values()->all(),
                ];
            }

            $step++;
        }

        return $result;
    }

    /**
     * Cari (atau buat baru) baris draft survey_responses milik user yang sedang
     * login untuk kuisioner tertentu. Menggantikan mekanisme session lama —
     * progres pengisian sekarang tersimpan permanen di DB, tidak hilang walau
     * browser ditutup di tengah jalan.
     */
    private function findOrCreateDraft(Questionnaire $questionnaire): SurveyResponse
    {
        return SurveyResponse::firstOrCreate(
            [
                'user_id'          => auth()->id(),
                'questionnaire_id' => $questionnaire->id,
            ],
            [
                'status'       => 'draft',
                'current_step' => 1,
            ]
        );
    }

    /**
     * Bentuk array $draft dengan shape yang sama seperti dulu disimpan di session
     * (['demografi' => [...], 'jawaban' => [...], 'esai' => [...]]), dibaca dari
     * jawaban yang sudah tersimpan di DB untuk SurveyResponse ini. Dipakai supaya
     * kuisioner/show.blade.php (yang mengakses $draft['demografi'][$field['id']]
     * dst.) tidak perlu diubah sama sekali.
     */
    private function draftArrayFor(SurveyResponse $response): array
    {
        $demografi = $response->demografi()->pluck('value', 'demografi_field_id')->all();
        $jawaban   = $response->answers()->pluck('skor', 'question_id')->all();
        $esai      = $response->essays()->pluck('jawaban', 'question_id')->all();

        return [
            'demografi' => $demografi,
            'jawaban'   => $jawaban,
            'esai'      => $esai,
        ];
    }

    /**
     * Halaman daftar kuisioner + riwayat pengisian milik user yang sedang login.
     */
    public function index()
    {
        $userId = auth()->id();

        $questionnaires = Questionnaire::orderByDesc('tahun')->get()->map(fn ($q) => [
            'id'          => $q->id,
            'judul'       => $q->judul,
            'tahun'       => $q->tahun,
            'dibuat_oleh' => $q->creator?->name ?? 'Admin',
            'status'      => $q->status,
            'sudah_diisi' => $q->sudahDiisiOleh($userId),
            'deskripsi'   => $q->deskripsi,
        ])->all();

        $history = SurveyResponse::where('user_id', $userId)
            ->where('status', 'submitted')
            ->with('questionnaire')
            ->latest('submitted_at')
            ->get()
            ->map(function ($response) {
                // "Mode Kerja" & "komentar" pada riwayat diambil dari jawaban demografi
                // 'pola_kehadiran' & esai pertama yang tersedia pada pengisian ini —
                // mengikuti makna data yang sama seperti dulu dipakai di data dummy.
                $modeKerja = $response->demografi()
                    ->whereHas('field', fn ($q) => $q->where('field_key', 'pola_kehadiran'))
                    ->value('value');

                $komentar = $response->essays()->whereNotNull('jawaban')->value('jawaban');

                return [
                    'id'         => $response->id,
                    'judul'      => $response->questionnaire->judul,
                    'mode_kerja' => $modeKerja ?? '-',
                    'rata_rata'  => $response->rata_rata !== null ? number_format((float) $response->rata_rata, 1) : '-',
                    'dikirim'    => $response->submitted_at?->translatedFormat('d F Y') ?? '-',
                    'komentar'   => $komentar,
                ];
            })
            ->all();

        return view('kuisioner.index', [
            'questionnaires' => $questionnaires,
            'history'        => $history,
        ]);
    }

    /**
     * Form isi kuisioner — menampilkan kategori sesuai query string ?kategori=.
     * Progres pengisian dibaca/ditulis ke baris survey_responses (status=draft)
     * milik user yang sedang login, bukan session lagi.
     */
    public function show(Request $request, int $id)
    {
        $questionnaire = Questionnaire::find($id);

        if (!$questionnaire || !$questionnaire->isActive()) {
            return redirect()->route('kuisioner')->with('error', 'Kuisioner tidak tersedia.');
        }

        if ($questionnaire->sudahDiisiOleh(auth()->id())) {
            return redirect()->route('kuisioner')->with('error', 'Anda sudah mengisi kuisioner ini.');
        }

        $categories = $this->categoriesFor($questionnaire);
        $totalSteps = count($categories);

        $step = (int) $request->query('kategori', 1);
        if ($step < 1 || $step > $totalSteps) {
            $step = 1;
        }

        $response = $this->findOrCreateDraft($questionnaire);
        $draft    = $this->draftArrayFor($response);
        $current  = $categories[$step];

        return view('kuisioner.show', [
            'kuesioner'  => [
                'id'        => $questionnaire->id,
                'judul'     => $questionnaire->judul,
                'deskripsi' => $questionnaire->deskripsi,
            ],
            'categories' => $categories,
            'totalSteps' => $totalSteps,
            'step'       => $step,
            'current'    => $current,
            'draft'      => $draft,
            'progress'   => round(($step / $totalSteps) * 100),
        ]);
    }

    /**
     * Simpan jawaban kategori yang sedang dikerjakan ke DB (upsert per pertanyaan/
     * field, bukan tulis ulang ke session), lalu pindah ke kategori berikutnya/
     * sebelumnya sesuai tombol navigasi yang ditekan.
     */
    public function step(Request $request, int $id)
    {
        $questionnaire = Questionnaire::find($id);

        if (!$questionnaire || !$questionnaire->isActive()) {
            return redirect()->route('kuisioner')->with('error', 'Kuisioner tidak tersedia.');
        }

        if ($questionnaire->sudahDiisiOleh(auth()->id())) {
            return redirect()->route('kuisioner')->with('error', 'Anda sudah mengisi kuisioner ini.');
        }

        $categories = $this->categoriesFor($questionnaire);
        $totalSteps = count($categories);

        $step = (int) $request->input('step', 1);
        if ($step < 1 || $step > $totalSteps) {
            $step = 1;
        }
        $current = $categories[$step];

        // ── Validasi sesuai tipe kategori ───────────────────────────────────
        if ($current['type'] === 'demografi') {
            $rules    = [];
            $messages = [];
            foreach ($current['fields'] as $field) {
                $rules["demografi.{$field['id']}"] = ['required', 'string'];
                $messages["demografi.{$field['id']}.required"] = "{$field['label']} wajib dipilih.";
            }
            $request->validate($rules, $messages);
        } else {
            $rules    = [];
            $messages = [];
            foreach ($current['questions'] as $q) {
                $rules["jawaban.{$q['id']}"] = ['required', 'integer', 'min:1', 'max:5'];
                $messages["jawaban.{$q['id']}.required"] = 'Semua pertanyaan wajib dijawab.';
            }
            foreach ($current['esai'] as $e) {
                $rules["esai.{$e['id']}"] = ['nullable', 'string'];
            }
            $request->validate($rules, $messages);
        }

        $response = $this->findOrCreateDraft($questionnaire);

        // ── Simpan jawaban kategori ini ke DB ────────────────────────────────
        DB::transaction(function () use ($current, $request, $response, $step) {
            if ($current['type'] === 'demografi') {
                foreach ($request->input('demografi', []) as $fieldId => $value) {
                    $response->demografi()->updateOrCreate(
                        ['demografi_field_id' => $fieldId],
                        ['value' => $value]
                    );
                }
            } else {
                foreach ($request->input('jawaban', []) as $questionId => $skor) {
                    $response->answers()->updateOrCreate(
                        ['question_id' => $questionId],
                        ['skor' => $skor]
                    );
                }
                foreach ($request->input('esai', []) as $questionId => $jawaban) {
                    $response->essays()->updateOrCreate(
                        ['question_id' => $questionId],
                        ['jawaban' => $jawaban]
                    );
                }
            }

            $response->update(['current_step' => $step]);
        });

        // ── Tentukan kategori tujuan ────────────────────────────────────────
        $action = $request->input('action', 'next'); // next | prev
        $target = $action === 'prev' ? $step - 1 : $step + 1;
        $target = max(1, min($totalSteps, $target));

        return redirect()->route('kuisioner.show', ['id' => $id, 'kategori' => $target]);
    }

    /**
     * Simpan jawaban kategori terakhir, lalu finalisasi seluruh kuisioner:
     * hitung rata-rata skor likert dan ubah status draft -> submitted.
     */
    public function submit(Request $request, int $id)
    {
        $questionnaire = Questionnaire::find($id);

        if (!$questionnaire || !$questionnaire->isActive()) {
            return redirect()->route('kuisioner')->with('error', 'Kuisioner tidak tersedia.');
        }

        if ($questionnaire->sudahDiisiOleh(auth()->id())) {
            return redirect()->route('kuisioner')->with('error', 'Anda sudah mengisi kuisioner ini.');
        }

        $categories = $this->categoriesFor($questionnaire);
        $totalSteps = count($categories);
        $last       = $categories[$totalSteps];

        $rules    = [];
        $messages = [];
        foreach ($last['questions'] as $q) {
            $rules["jawaban.{$q['id']}"] = ['required', 'integer', 'min:1', 'max:5'];
            $messages["jawaban.{$q['id']}.required"] = 'Semua pertanyaan wajib dijawab.';
        }
        foreach ($last['esai'] as $e) {
            $rules["esai.{$e['id']}"] = ['nullable', 'string'];
        }
        $request->validate($rules, $messages);

        $response = $this->findOrCreateDraft($questionnaire);

        DB::transaction(function () use ($last, $request, $response, $totalSteps) {
            foreach ($request->input('jawaban', []) as $questionId => $skor) {
                $response->answers()->updateOrCreate(
                    ['question_id' => $questionId],
                    ['skor' => $skor]
                );
            }
            foreach ($request->input('esai', []) as $questionId => $jawaban) {
                $response->essays()->updateOrCreate(
                    ['question_id' => $questionId],
                    ['jawaban' => $jawaban]
                );
            }

            $response->update([
                'current_step' => $totalSteps,
                'status'       => 'submitted',
                'submitted_at' => now(),
            ]);

            $response->hitungRataRata();
        });

        return redirect()->route('kuisioner')
            ->with('success', 'Kuisioner berhasil dikirim. Terima kasih!');
    }

    /**
     * Struktur default kategori & pertanyaan yang dipakai untuk mengisi builder
     * admin (kuisioner/create.blade.php) secara otomatis saat klik "Buat Kuisioner
     * Baru" — supaya admin tinggal sunting/tambah dari template ini, tidak mulai
     * dari kosong. Shape tiap item HARUS sama dengan yang dipakai JS builder:
     *   - kategori demografi -> items = [{jenis: 'choice'|'text', label, options:[]}]
     *   - kategori likert    -> items = [{jenis: 'likert'|'esai', teks}]
     *
     * Catatan: ini HANYA dipakai builder saat create() (kategori belum pernah ada
     * di DB). Saat edit(), builder tetap di-bootstrap dari struktur DB asli, bukan
     * dari default ini.
     */
    private function defaultKategoris(): array
    {
        return [
            [
                'nama'      => 'A. Data Demografi Responden',
                'deskripsi' => 'Informasi dasar tentang responden',
                'tipe'      => 'demografi',
                'items'     => [
                    ['jenis' => 'choice', 'label' => 'Jenis Kelamin', 'options' => ['Laki-laki', 'Perempuan']],
                    ['jenis' => 'choice', 'label' => 'Usia', 'options' => ['<30 tahun', '30–39 tahun', '40–49 tahun', '≥50 tahun']],
                    ['jenis' => 'choice', 'label' => 'Jenjang Pendidikan', 'options' => ['D3', 'S1', 'S2', 'S3']],
                    ['jenis' => 'choice', 'label' => 'Jabatan Fungsional', 'options' => config('options.posisi')],
                    ['jenis' => 'choice', 'label' => 'Lama Bekerja', 'options' => ['<5 tahun', '5–10 tahun', '11–20 tahun', '>20 tahun']],
                    ['jenis' => 'choice', 'label' => 'Pola Kehadiran', 'options' => ['WFA penuh', 'WFO 2x/minggu', 'WFO 3x/minggu', 'WFO 5x/minggu']],
                ],
            ],
            [
                'nama'      => 'I. Persepsi terhadap Kebijakan WFO/WFA (Job Resources: Clarity & Flexibility)',
                'deskripsi' => 'Persepsi pegawai terhadap kebijakan dan fleksibilitas kerja',
                'tipe'      => 'likert',
                'items'     => [
                    ['jenis' => 'likert', 'teks' => 'Aturan WFO/WFA mudah dipahami dan konsisten.'],
                    ['jenis' => 'likert', 'teks' => 'Kebijakan WFO/WFA berlaku adil bagi semua pegawai.'],
                    ['jenis' => 'likert', 'teks' => 'Kebijakan WFO/WFA mendukung aktivitas penelitian/pekerjaan saya.'],
                    ['jenis' => 'likert', 'teks' => 'Kebijakan WFO/WFA memberi fleksibilitas untuk kebutuhan kerja.'],
                    ['jenis' => 'likert', 'teks' => 'Kebijakan WFO/WFA berpengaruh positif terhadap produktivitas saya.'],
                    ['jenis' => 'esai', 'teks' => 'Apa tantangan terbesar yang Anda hadapi dalam memahami atau mengimplementasikan aturan WFO/WFA?'],
                    ['jenis' => 'esai', 'teks' => 'Jika Anda bisa mengubah satu hal dari kebijakan WFO/WFA BRIN, apa itu dan mengapa?'],
                ],
            ],
            [
                'nama'      => 'II. Motivasi Kerja (Keterkaitan dengan Commitment & Vigor)',
                'deskripsi' => 'Tingkat motivasi dan semangat kerja pegawai',
                'tipe'      => 'likert',
                'items'     => [
                    ['jenis' => 'likert', 'teks' => 'Saya merasa antusias dalam menyelesaikan tugas.'],
                    ['jenis' => 'likert', 'teks' => 'Saya memiliki komitmen tinggi untuk mencapai target kerja.'],
                    ['jenis' => 'likert', 'teks' => 'Saya bersedia melakukan usaha ekstra demi keberhasilan pekerjaan.'],
                    ['jenis' => 'likert', 'teks' => 'Saya merasa termotivasi secara intrinsik saat bekerja dengan pola WFO/WFA.'],
                    ['jenis' => 'likert', 'teks' => 'Saya berkeinginan kuat untuk tetap menjadi bagian dari organisasi BRIN dalam jangka panjang.'],
                    ['jenis' => 'esai', 'teks' => 'Jelaskan bagaimana pola kerja (WFO/WFA) saat ini memengaruhi tingkat antusiasme Anda dalam memulai tugas sehari-hari.'],
                    ['jenis' => 'esai', 'teks' => 'Apa faktor (selain gaji) yang membuat Anda berkeinginan kuat untuk tetap bekerja di BRIN dalam jangka panjang?'],
                ],
            ],
            [
                'nama'      => 'III. Kepuasan Kerja (Dampak Job Resources)',
                'deskripsi' => 'Tingkat kepuasan pegawai terhadap pekerjaan dan lingkungan kerja',
                'tipe'      => 'likert',
                'items'     => [
                    ['jenis' => 'likert', 'teks' => 'Saya puas dengan jenis tugas yang saya kerjakan.'],
                    ['jenis' => 'likert', 'teks' => 'Hubungan dengan atasan/rekan kerja mendukung kenyamanan kerja.'],
                    ['jenis' => 'likert', 'teks' => 'Fasilitas kerja yang tersedia sudah memadai.'],
                    ['jenis' => 'likert', 'teks' => 'Saya merasa kompensasi dan tunjangan yang saya terima sebanding dengan fleksibilitas kerja ini.'],
                    ['jenis' => 'likert', 'teks' => 'Saya merasa memiliki otonomi yang cukup dalam menentukan cara terbaik untuk menyelesaikan tugas saya.'],
                    ['jenis' => 'esai', 'teks' => 'Dalam pola kerja WFA/WFO saat ini, sebutkan dan jelaskan satu aspek tugas atau lingkungan kerja yang paling memuaskan Anda.'],
                    ['jenis' => 'esai', 'teks' => 'Apakah Anda merasa hubungan dengan rekan kerja/atasan lebih mudah atau lebih sulit dikelola di bawah kebijakan fleksibel? Jelaskan alasannya.'],
                ],
            ],
            [
                'nama'      => 'IV. Engagement Pegawai (Work Engagement: Vigor, Dedication, Absorption)',
                'deskripsi' => 'Tingkat keterlibatan dan keterikatan pegawai terhadap organisasi',
                'tipe'      => 'likert',
                'items'     => [
                    ['jenis' => 'likert', 'teks' => 'Saya merasa berdedikasi penuh pada pekerjaan.'],
                    ['jenis' => 'likert', 'teks' => 'Saya tenggelam (absorbed) ketika mengerjakan tugas.'],
                    ['jenis' => 'likert', 'teks' => 'Saya memiliki semangat (vigour) tinggi saat bekerja.'],
                    ['jenis' => 'likert', 'teks' => 'Lingkungan kerja (WFA/WFO) memungkinkan saya mempertahankan fokus yang mendalam pada tugas-tugas saya.'],
                    ['jenis' => 'likert', 'teks' => 'Saya secara proaktif memberikan bantuan kepada rekan kerja atau tim meskipun saya tidak berada di kantor.'],
                    ['jenis' => 'esai', 'teks' => 'Deskripsikan momen atau situasi spesifik (WFO atau WFA) ketika Anda merasa paling tenggelam (absorbed) dalam pekerjaan.'],
                    ['jenis' => 'esai', 'teks' => 'Apa yang organisasi (BRIN) bisa lakukan untuk lebih meningkatkan semangat (vigor) dan dedikasi Anda terhadap pekerjaan?'],
                ],
            ],
            [
                'nama'      => 'V. Stres Kerja (Job Demands)',
                'deskripsi' => 'Tingkat tekanan dan stres yang dirasakan dalam bekerja',
                'tipe'      => 'likert',
                'items'     => [
                    ['jenis' => 'likert', 'teks' => 'Beban kerja saya sering terasa berlebihan.'],
                    ['jenis' => 'likert', 'teks' => 'Saya mengalami konflik peran akibat kebijakan WFO/WFA.'],
                    ['jenis' => 'likert', 'teks' => 'Tekanan waktu membuat saya merasa tertekan dalam bekerja.'],
                    ['jenis' => 'likert', 'teks' => 'Saya merasa terisolasi atau terputus dari rekan kerja dan budaya kantor saat WFA.'],
                    ['jenis' => 'likert', 'teks' => 'Saya merasa harus selalu siaga atau merespon pekerjaan di luar jam kerja normal.'],
                    ['jenis' => 'esai', 'teks' => 'Apa sumber stres terbesar Anda: tuntutan pekerjaan (beban kerja, tekanan waktu) atau gangguan dari luar (keluarga, isolasi)? Jelaskan.'],
                    ['jenis' => 'esai', 'teks' => 'Berikan contoh spesifik konflik peran (misalnya, kerja vs. keluarga) yang pernah Anda alami karena kebijakan WFO/WFA.'],
                ],
            ],
            [
                'nama'      => 'VI. Persepsi Dukungan Organisasi (POS) (Job Resources: Support)',
                'deskripsi' => 'Persepsi pegawai terhadap dukungan yang diberikan organisasi',
                'tipe'      => 'likert',
                'items'     => [
                    ['jenis' => 'likert', 'teks' => 'Organisasi menyediakan dukungan teknologi informasi yang memadai.'],
                    ['jenis' => 'likert', 'teks' => 'Organisasi memberikan dukungan administratif yang membantu pekerjaan saya.'],
                    ['jenis' => 'likert', 'teks' => 'Organisasi memiliki kebijakan fleksibel yang mendukung kebutuhan kerja pegawai.'],
                    ['jenis' => 'likert', 'teks' => 'Atasan langsung saya memberikan instruksi dan komunikasi yang jelas mengenai ekspektasi WFO/WFA.'],
                    ['jenis' => 'likert', 'teks' => 'Lingkungan kantor (WFO) menyediakan ruang yang memadai untuk privasi dan mengurangi gangguan.'],
                    ['jenis' => 'esai', 'teks' => 'Jelaskan satu kelemahan terbesar dalam dukungan teknologi informasi (IT) atau administratif BRIN yang menghambat produktivitas WFA/WFH Anda.'],
                    ['jenis' => 'esai', 'teks' => 'Bagaimana atasan langsung Anda secara efektif mendukung Anda dalam menyeimbangkan WFO dan WFA? (Berikan contoh perilaku spesifik).'],
                ],
            ],
            [
                'nama'      => 'VII. Work–Life Balance (WLB) (Job Resources/Outcomes)',
                'deskripsi' => 'Keseimbangan antara kehidupan kerja dan kehidupan pribadi',
                'tipe'      => 'likert',
                'items'     => [
                    ['jenis' => 'likert', 'teks' => 'Saya memiliki cukup waktu untuk keluarga meskipun WFO/WFA diterapkan.'],
                    ['jenis' => 'likert', 'teks' => 'Saya memiliki waktu istirahat yang cukup di luar pekerjaan.'],
                    ['jenis' => 'likert', 'teks' => 'Saya memiliki kesempatan untuk mengembangkan diri di luar pekerjaan.'],
                    ['jenis' => 'likert', 'teks' => 'Saya mampu mengendalikan dan membatasi waktu kerja agar tidak mengganggu kehidupan pribadi.'],
                    ['jenis' => 'likert', 'teks' => 'Pengaturan WFO/WFA membantu saya memanfaatkan energi dan keterampilan dari satu peran (kerja/keluarga) untuk meningkatkan peran yang lain.'],
                    ['jenis' => 'esai', 'teks' => 'Bagaimana Anda mengelola atau menetapkan batas yang jelas antara waktu kerja dan waktu pribadi saat WFA? (Jelaskan metodenya).'],
                    ['jenis' => 'esai', 'teks' => 'Selain jam tidur/istirahat, kegiatan non-kerja apa yang menurut Anda paling penting untuk pemulihan energi, dan apakah WFO/WFA mendukung hal tersebut?'],
                ],
            ],
        ];
    }

    /**
     * Map nilai field demografi 'pola_kehadiran' ke salah satu dari tiga bucket
     * mode kerja: WFA, WFO, Hybrid. Logikanya sengaja disamakan dengan
     * DataController::modeKerjaBucket() supaya label "Mode Kerja" yang
     * ditampilkan di halaman detail riwayat konsisten dengan yang dipakai di
     * chart/filter halaman Data.
     */
    private function modeKerjaBucket(?string $polaKehadiran): string
    {
        if (!$polaKehadiran) {
            return '-';
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

    /**
     * Halaman detail satu pengisian kuisioner (riwayat) milik user yang sedang
     * login — diakses lewat tombol "Lihat Detail" di /kuisioner. Menampilkan
     * seluruh jawaban likert (dikelompokkan per kategori/dimensi) beserta skor
     * & progress bar, jawaban esai (komentar), serta info ringkas (tahun, mode
     * kerja, rata-rata skor, tanggal dikirim).
     *
     * Hanya pemilik pengisian atau admin yang boleh mengakses — kalau bukan,
     * 403. Hanya pengisian berstatus 'submitted' yang punya halaman detail
     * (draft belum final, belum ada rata-rata/submitted_at).
     */
    public function riwayatDetail(int $id)
    {
        $response = SurveyResponse::with([
                'questionnaire',
                'user',
                'answers.question.category',
                'essays.question',
                'demografi.field',
            ])
            ->where('status', 'submitted')
            ->findOrFail($id);

        if ($response->user_id !== auth()->id() && !auth()->user()->isAdmin()) {
            abort(403);
        }

        $modeKerjaRaw = $response->demografi
            ->first(fn ($d) => $d->field?->field_key === 'pola_kehadiran')?->value;

        // Urutkan jawaban likert sesuai urutan kategori lalu urutan pertanyaan
        // (sortBy stabil: sort sekunder dulu baru primer, supaya urutan dalam
        // kategori tetap benar), lalu kelompokkan per kategori untuk ditampilkan
        // dengan sub-judul.
        $hasil = $response->answers
            ->sortBy(fn ($a) => $a->question->urutan ?? 0)
            ->sortBy(fn ($a) => $a->question->category->urutan ?? 0)
            ->groupBy(fn ($a) => $a->question->category->title ?? 'Lainnya')
            ->map(fn ($group) => $group->map(fn ($a) => [
                'pertanyaan' => $a->question->pertanyaan,
                'skor'       => (int) $a->skor,
            ])->values());

        $komentar = $response->essays
            ->filter(fn ($e) => filled($e->jawaban))
            ->sortBy(fn ($e) => $e->question->urutan ?? 0)
            ->map(fn ($e) => [
                'pertanyaan' => $e->question->pertanyaan,
                'jawaban'    => $e->jawaban,
            ])
            ->values();

        // Halaman ini dipakai 2 konteks: pegawai melihat riwayat pengisiannya
        // sendiri, ATAU admin melihat detail respons milik pegawai lain (dari
        // daftar responden kuisioner). Bedakan supaya identitas responden &
        // tombol kembali ditampilkan sesuai konteksnya.
        $isAdminView = auth()->user()->isAdmin() && $response->user_id !== auth()->id();

        return view('kuisioner.detail', [
            'judul'       => $response->questionnaire->judul,
            'tahun'       => $response->questionnaire->tahun,
            'modeKerja'   => $this->modeKerjaBucket($modeKerjaRaw),
            'rataRata'    => $response->rata_rata !== null ? number_format((float) $response->rata_rata, 1) : '-',
            'hasil'       => $hasil,
            'komentar'    => $komentar,
            'dikirim'     => $response->submitted_at?->translatedFormat('d F Y') ?? '-',
            'isAdminView' => $isAdminView,
            'responden'   => $isAdminView ? [
                'nama'        => $response->user->name ?? '-',
                'nip'         => $response->user->nip ?? '-',
                'posisi'      => $response->user->posisi ?? '-',
                'pusat_riset' => $response->user->pusat_riset ?? '-',
            ] : null,
            'backUrl' => $isAdminView
                ? route('admin.kuisioner.respons', $response->questionnaire_id)
                : route('kuisioner'),
        ]);
    }

    /**
     * Halaman form buat kuisioner baru (admin builder, Tahap 3).
     * Builder di-bootstrap dengan struktur default (defaultKategoris()) supaya
     * admin tinggal sunting/tambah dari template, bukan mulai dari kosong.
     */
    /**
     * Halaman form buat kuisioner baru (admin builder, Tahap 3).
     *
     * NONAKTIF SEMENTARA atas permintaan user — admin tidak bisa membuat
     * kuisioner baru dulu untuk saat ini (lihat juga catatan di
     * AdminController::storeKuisioner()). edit() TIDAK terpengaruh, admin
     * tetap bisa mengedit kuisioner yang sudah ada.
     */
    public function create()
    {
        return redirect()->route('admin.index', ['tab' => 'kuisioner'])
            ->with('error', 'Pembuatan kuisioner baru sedang dinonaktifkan sementara.');
    }

    /**
     * Halaman form edit kuisioner — builder yang sama dengan create(), tapi
     * di-bootstrap dengan struktur kategori/pertanyaan/field yang sudah ada di DB.
     */
    public function edit(int $id)
    {
        $questionnaire = Questionnaire::with([
            'categories.questions',
            'categories.demografiFields',
        ])->findOrFail($id);

        $kategoris = $questionnaire->categories->map(function ($kategori) {
            if ($kategori->type === 'demografi') {
                $items = $kategori->demografiFields->map(fn ($f) => [
                    'jenis'   => $f->type,                 // choice | text
                    'label'   => $f->label,
                    'options' => $f->options ?? [],
                ])->values();
            } else {
                $items = $kategori->questions->sortBy('urutan')->map(fn ($q) => [
                    'jenis' => $q->type,                    // likert | esai
                    'teks'  => $q->pertanyaan,
                ])->values();
            }

            return [
                'nama'      => $kategori->title,
                'deskripsi' => $kategori->subtitle,
                'tipe'      => $kategori->type,             // demografi | likert
                'items'     => $items,
            ];
        })->values();

        return view('kuisioner.create', [
            'questionnaire' => $questionnaire,
            'initialData'   => [
                'judul'     => $questionnaire->judul,
                'tahun'     => $questionnaire->tahun,
                'deskripsi' => $questionnaire->deskripsi,
                'kategoris' => $kategoris,
            ],
        ]);
    }
}
