<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\DemografiField;
use App\Models\Question;
use App\Models\Questionnaire;
use App\Models\SurveyResponse;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;

class AdminController extends Controller
{
    // ──────────────────────────────────────────────────────────────────────
    //  Stats dari DB (bukan hardcode)
    // ──────────────────────────────────────────────────────────────────────

    private function stats(): array
    {
        $totalPegawai    = User::where('role', 'pegawai')->count();
        $totalKuisioner  = Questionnaire::count();
        $totalEntries    = SurveyResponse::where('status', 'submitted')->count();

        // Response rate: submitted / total pegawai terdaftar (hindari div-by-zero)
        $responseRate = $totalPegawai > 0
            ? round($totalEntries / $totalPegawai * 100, 1)
            : 0;

        // Perubahan bulan ini vs bulan lalu
        $now      = now();
        $thisMonth = $now->month;
        $thisYear  = $now->year;
        $lastMonth = $now->copy()->subMonth()->month;
        $lastYear  = $now->copy()->subMonth()->year;

        $pegawaiBulanIni  = User::where('role', 'pegawai')
            ->whereMonth('created_at', $thisMonth)->whereYear('created_at', $thisYear)->count();
        $submissBulanIni  = SurveyResponse::where('status', 'submitted')
            ->whereMonth('submitted_at', $thisMonth)->whereYear('submitted_at', $thisYear)->count();
        $submissBulanLalu = SurveyResponse::where('status', 'submitted')
            ->whereMonth('submitted_at', $lastMonth)->whereYear('submitted_at', $lastYear)->count();

        $submissDelta = $submissBulanIni - $submissBulanLalu;
        $submissLabel = ($submissDelta >= 0 ? '+' : '') . $submissDelta . ' vs bulan lalu';

        return [
            ['icon' => 'users-2',     'color' => 'red',    'label' => 'Total Pegawai',     'value' => (string) $totalPegawai,       'change' => "+{$pegawaiBulanIni} bulan ini",  'positive' => true],
            ['icon' => 'file-text',   'color' => 'green',  'label' => 'Total Kuisioner',   'value' => (string) $totalKuisioner,     'change' => '',                               'positive' => true],
            ['icon' => 'database',    'color' => 'purple', 'label' => 'Total Pengisian',   'value' => (string) $totalEntries,       'change' => $submissLabel,                    'positive' => $submissDelta >= 0],
            ['icon' => 'trending-up', 'color' => 'orange', 'label' => 'Response Rate',     'value' => $responseRate . '%',          'change' => '',                               'positive' => true],
        ];
    }

    // ──────────────────────────────────────────────────────────────────────
    //  Activity chart dari DB — submissions per bulan (6 bulan terakhir)
    // ──────────────────────────────────────────────────────────────────────

    private function activityChart(): array
    {
        $months      = collect();
        $submissions = collect();

        for ($i = 5; $i >= 0; $i--) {
            $date  = now()->subMonths($i);
            $label = $date->translatedFormat('M');
            $count = SurveyResponse::where('status', 'submitted')
                ->whereYear('submitted_at', $date->year)
                ->whereMonth('submitted_at', $date->month)
                ->count();

            $months->push($label);
            $submissions->push($count);
        }

        // Login tracking tidak ada di DB standar Laravel (butuh paket audit/activity log).
        // Untuk sekarang diisi 0 semua agar tidak menyesatkan.
        $logins = array_fill(0, 6, 0);

        return [
            'labels'      => $months->all(),
            'logins'      => $logins,
            'submissions' => $submissions->all(),
        ];
    }

    // ──────────────────────────────────────────────────────────────────────
    //  Users dari DB (dengan filter pencarian)
    // ──────────────────────────────────────────────────────────────────────

    private function users(string $search = ''): array
    {
        $query = User::orderByDesc('created_at');

        if ($search !== '') {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%")
                  ->orWhere('nip', 'like', "%{$search}%");
            });
        }

        return $query->get()->map(fn ($u) => [
            'id'        => $u->id,
            'nip'       => $u->nip,
            'name'      => $u->name,
            'email'     => $u->email,
            'role'      => $u->role,
            'status'    => 'active',   // kolom status belum ada di tabel; semua aktif
            'bergabung' => $u->created_at->format('d/m/Y'),
        ])->all();
    }

    // ──────────────────────────────────────────────────────────────────────
    //  Quick summary dari DB
    // ──────────────────────────────────────────────────────────────────────

    private function quickSummary(): array
    {
        $now = now();
        return [
            'pengisian_bulan_ini' => SurveyResponse::where('status', 'submitted')
                ->whereMonth('submitted_at', $now->month)
                ->whereYear('submitted_at', $now->year)
                ->count(),
            'user_aktif' => User::where('role', 'pegawai')->count(),
            // Login tracking belum tersedia tanpa paket tambahan
            'login_bulan_ini' => '-',
        ];
    }

    // ──────────────────────────────────────────────────────────────────────
    //  Questionnaires dari DB
    // ──────────────────────────────────────────────────────────────────────

    private function questionnaires(): array
    {
        return Questionnaire::withCount([
                'surveyResponses as respons' => fn ($q) => $q->where('status', 'submitted'),
            ])
            ->latest()
            ->get()
            ->map(fn ($q) => [
                'id'      => $q->id,
                'judul'   => $q->judul,
                'tahun'   => $q->tahun,
                'status'  => $q->status,
                'respons' => $q->respons,
                'dibuat'  => $q->created_at->translatedFormat('j M Y'),
            ])
            ->toArray();
    }

    // ──────────────────────────────────────────────────────────────────────
    //  Actions
    // ──────────────────────────────────────────────────────────────────────

    public function index(Request $request)
    {
        $tab        = $request->query('tab', 'overview');
        $searchUser = $request->query('q', '');

        return view('admin.index', [
            'tab'            => $tab,
            'stats'          => $this->stats(),
            'activityChart'  => $this->activityChart(),
            'users'          => $this->users($searchUser),
            'questionnaires' => $this->questionnaires(),
            'searchUser'     => $searchUser,
            'quickSummary'   => $this->quickSummary(),
        ]);
    }

    public function toggleKuisioner(Request $request, int $id)
    {
        $kuisioner = Questionnaire::findOrFail($id);
        $action    = $request->input('action', 'tutup');
        $kuisioner->update(['status' => $action === 'buka' ? 'aktif' : 'ditutup']);

        $pesan = $action === 'buka' ? 'Kuisioner berhasil dibuka.' : 'Kuisioner berhasil ditutup.';

        return redirect()->route('admin.index', ['tab' => 'kuisioner'])->with('success', $pesan);
    }

    /**
     * Map nilai field demografi 'pola_kehadiran' ke salah satu dari tiga bucket
     * mode kerja: WFA, WFO, Hybrid. Duplikat dari KuisionerController::modeKerjaBucket()
     * (di sana private) supaya label "Mode Kerja" pada daftar respons admin konsisten
     * dengan yang dipakai di halaman riwayat/detail milik pegawai.
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

    private function kuisionerResponsData(Request $request, int $id): array
    {
        $search = trim((string) $request->query('q', ''));

        $query = SurveyResponse::where('questionnaire_id', $id)
            ->where('status', 'submitted')
            ->with(['user', 'demografi.field'])
            ->latest('submitted_at');

        if ($search !== '') {
            $query->whereHas('user', function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('nip', 'like', "%{$search}%");
            });
        }

        $responses = $query->get()->map(function ($r) {
            $modeKerjaRaw = $r->demografi
                ->first(fn ($d) => $d->field?->field_key === 'pola_kehadiran')?->value;

            return [
                'response_id' => $r->id,
                'nama'        => $r->user->name ?? '-',
                'nip'         => $r->user->nip ?? '-',
                'posisi'      => $r->user->posisi ?? '-',
                'pusat_riset' => $r->user->pusat_riset ?? '-',
                'mode_kerja'  => $this->modeKerjaBucket($modeKerjaRaw),
                'rata_rata'   => $r->rata_rata !== null ? number_format((float) $r->rata_rata, 1) : '-',
                'dikirim'     => $r->submitted_at?->translatedFormat('d F Y') ?? '-',
            ];
        });

        return ['responses' => $responses, 'search' => $search];
    }

    public function kuisionerRespons(Request $request, int $id)
    {
        $kuisioner = Questionnaire::findOrFail($id);
        $data      = $this->kuisionerResponsData($request, $id);

        return view('admin.kuisioner-respons', [
            'kuisioner' => [
                'id'    => $kuisioner->id,
                'judul' => $kuisioner->judul,
                'tahun' => $kuisioner->tahun,
            ],
            'responses' => $data['responses'],
            'search'    => $data['search'],
        ]);
    }

    public function exportKuisionerResponsExcel(Request $request, int $id)
    {
        $kuisioner = Questionnaire::findOrFail($id);
        $responses = $this->kuisionerResponsData($request, $id)['responses'];

        $filename = 'respons-' . Str::slug($kuisioner->judul) . '-' . now()->format('Y-m-d_His') . '.csv';

        return response()->streamDownload(function () use ($responses) {
            $out = fopen('php://output', 'w');
            fwrite($out, "\xEF\xBB\xBF");
            fputcsv($out, ['Nama', 'NIP', 'Posisi', 'Pusat Riset', 'Mode Kerja', 'Rata-rata Skor', 'Tanggal Dikirim']);
            foreach ($responses as $r) {
                fputcsv($out, [
                    $r['nama'], $r['nip'], $r['posisi'], $r['pusat_riset'],
                    $r['mode_kerja'], $r['rata_rata'], $r['dikirim'],
                ]);
            }
            fclose($out);
        }, $filename, ['Content-Type' => 'text/csv; charset=UTF-8']);
    }

    public function exportKuisionerResponsPdf(Request $request, int $id)
    {
        $kuisioner = Questionnaire::findOrFail($id);
        $responses = $this->kuisionerResponsData($request, $id)['responses'];

        return view('admin.export.kuisioner-respons-pdf', [
            'kuisioner' => [
                'id'    => $kuisioner->id,
                'judul' => $kuisioner->judul,
                'tahun' => $kuisioner->tahun,
            ],
            'responses' => $responses,
            'dicetak'   => now()->translatedFormat('d F Y, H:i'),
        ]);
    }

    public function showUser(int $id)
    {
        $user = User::findOrFail($id);

        $riwayat = SurveyResponse::where('user_id', $user->id)
            ->where('status', 'submitted')
            ->with('questionnaire')
            ->latest('submitted_at')
            ->get()
            ->map(fn ($r) => [
                'judul'     => $r->questionnaire?->judul ?? '-',
                'rata_rata' => $r->rata_rata !== null ? number_format((float) $r->rata_rata, 1) : '-',
                'dikirim'   => $r->submitted_at?->translatedFormat('d F Y') ?? '-',
            ]);

        return view('admin.user-show', [
            'u'       => $user,
            'riwayat' => $riwayat,
        ]);
    }

    public function editUser(int $id)
    {
        $user = User::findOrFail($id);

        return view('admin.user-edit', [
            'u'          => $user,
            'pusatRiset' => config('options.pusat_riset'),
            'posisiList' => config('options.posisi'),
        ]);
    }

    public function updateUser(Request $request, int $id)
    {
        $user = User::findOrFail($id);

        $validated = $request->validate([
            'nip'         => ['required', 'digits_between:15,18', Rule::unique('users', 'nip')->ignore($user->id)],
            'name'        => ['required', 'string', 'max:255'],
            'email'       => ['required', 'email', 'ends_with:brin.go.id', Rule::unique('users', 'email')->ignore($user->id)],
            'institusi'   => ['required', 'string', 'max:100'],
            'pusat_riset' => ['required', 'string', 'in:' . implode(',', config('options.pusat_riset'))],
            'posisi'      => ['required', 'string', 'in:' . implode(',', config('options.posisi'))],
            'role'        => ['required', 'string', 'in:admin,pegawai'],
            'password'    => ['nullable', 'confirmed', Password::min(6)],
        ], [
            'nip.required'          => 'NIP wajib diisi.',
            'nip.digits_between'    => 'NIP harus terdiri dari 15–18 digit angka.',
            'nip.unique'            => 'NIP ini sudah dipakai user lain.',
            'name.required'         => 'Nama lengkap wajib diisi.',
            'email.required'        => 'Email wajib diisi.',
            'email.email'           => 'Format email tidak valid.',
            'email.ends_with'       => 'Email harus menggunakan domain @brin.go.id.',
            'email.unique'          => 'Email ini sudah dipakai user lain.',
            'institusi.required'    => 'Institusi wajib diisi.',
            'pusat_riset.required'  => 'Pusat Riset wajib dipilih.',
            'pusat_riset.in'        => 'Pusat Riset yang dipilih tidak valid.',
            'posisi.required'       => 'Posisi wajib dipilih.',
            'posisi.in'             => 'Posisi yang dipilih tidak valid.',
            'role.required'         => 'Role wajib dipilih.',
            'role.in'               => 'Role yang dipilih tidak valid.',
            'password.confirmed'    => 'Konfirmasi password tidak cocok.',
        ]);

        if ($user->id === auth()->id() && $validated['role'] !== 'admin') {
            return back()->withInput()
                ->with('error', 'Anda tidak dapat mengubah role akun Anda sendiri keluar dari admin.');
        }

        $user->fill([
            'nip'         => $validated['nip'],
            'name'        => $validated['name'],
            'email'       => $validated['email'],
            'institusi'   => $validated['institusi'],
            'pusat_riset' => $validated['pusat_riset'],
            'posisi'      => $validated['posisi'],
            'role'        => $validated['role'],
        ]);

        if (! empty($validated['password'])) {
            $user->password = Hash::make($validated['password']);
        }

        $user->save();

        return redirect()->route('admin.index', ['tab' => 'users'])
            ->with('success', "Data user \"{$user->name}\" berhasil diperbarui.");
    }


    public function deleteKuisioner(int $id)
    {
        $kuisioner = Questionnaire::findOrFail($id);

        $kuisioner->delete(); // soft delete

        return redirect()->route('admin.index', ['tab' => 'kuisioner'])
            ->with('success', "Kuisioner \"{$kuisioner->judul}\" berhasil dihapus.");
    }


    private function validateKuisionerPayload(Request $request): array
    {
        $request->validate([
            'judul'          => ['required', 'string', 'max:255'],
            'tahun'          => ['required', 'digits:4'],
            'deskripsi'      => ['nullable', 'string'],
            'kategoris_json' => ['required', 'string'],
        ]);

        $kategoris = json_decode($request->input('kategoris_json'), true);

        if (! is_array($kategoris)) {
            $kategoris = [];
        }

        $validator = Validator::make(['kategoris' => $kategoris], [
            'kategoris'                   => ['required', 'array', 'min:1'],
            'kategoris.*.nama'            => ['required', 'string', 'max:255'],
            'kategoris.*.deskripsi'       => ['nullable', 'string'],
            'kategoris.*.tipe'            => ['required', 'in:likert,demografi'],
            'kategoris.*.items'           => ['array'],
            'kategoris.*.items.*.jenis'   => ['required', 'string'],
            'kategoris.*.items.*.teks'    => ['nullable', 'string'],
            'kategoris.*.items.*.label'   => ['nullable', 'string'],
            'kategoris.*.items.*.options' => ['nullable', 'array'],
        ], [
            'kategoris.required' => 'Minimal harus ada 1 kategori.',
            'kategoris.min'      => 'Minimal harus ada 1 kategori.',
        ]);

        $validator->validate();

        return $kategoris;
    }

    private function syncKategoris(Questionnaire $kuisioner, array $kategoris): void
    {
        $kuisioner->categories()->delete();

        foreach ($kategoris as $i => $kat) {
            $slug = Str::slug($kat['nama'], '_') ?: 'kategori';
            $kode = $slug . '_' . ($i + 1);

            $kategori = Category::create([
                'questionnaire_id' => $kuisioner->id,
                'kode'             => $kode,
                'title'            => $kat['nama'],
                'subtitle'         => $kat['deskripsi'] ?? null,
                'type'             => $kat['tipe'],
                'urutan'           => $i + 1,
            ]);

            $items = $kat['items'] ?? [];

            if ($kat['tipe'] === 'demografi') {
                foreach ($items as $j => $item) {
                    $label = $item['label'] ?? ($item['teks'] ?? 'Field ' . ($j + 1));
                    DemografiField::create([
                        'category_id' => $kategori->id,
                        'field_key'   => (Str::slug($label, '_') ?: 'field') . '_' . ($j + 1),
                        'label'       => $label,
                        'type'        => ($item['jenis'] ?? 'choice') === 'text' ? 'text' : 'choice',
                        'options'     => $item['options'] ?? [],
                        'urutan'      => $j + 1,
                    ]);
                }
            } else {
                foreach ($items as $j => $item) {
                    Question::create([
                        'category_id' => $kategori->id,
                        'type'        => ($item['jenis'] ?? 'likert') === 'esai' ? 'esai' : 'likert',
                        'pertanyaan'  => $item['teks'] ?? '',
                        'urutan'      => $j + 1,
                    ]);
                }
            }
        }
    }


    public function storeKuisioner(Request $request)
    {
        return redirect()->route('admin.index', ['tab' => 'kuisioner'])
            ->with('error', 'Pembuatan kuisioner baru sedang dinonaktifkan sementara.');
    }


    public function updateKuisioner(Request $request, int $id)
    {
        $kuisioner = Questionnaire::findOrFail($id);

        $adaResponden = $kuisioner->surveyResponses()
            ->where('status', 'submitted')
            ->exists();

        if ($adaResponden) {
            return redirect()->route('admin.index', ['tab' => 'kuisioner'])
                ->with('error', 'Kuisioner tidak dapat diedit karena sudah memiliki responden. Tutup kuisioner ini dan buat yang baru jika perlu perubahan struktur.');
        }

        $kategoris = $this->validateKuisionerPayload($request);

        DB::transaction(function () use ($request, $kuisioner, $kategoris) {
            $kuisioner->update([
                'judul'     => $request->input('judul'),
                'tahun'     => $request->input('tahun'),
                'deskripsi' => $request->input('deskripsi'),
            ]);

            $this->syncKategoris($kuisioner, $kategoris);
        });

        return redirect()->route('admin.index', ['tab' => 'kuisioner'])
            ->with('success', "Kuisioner \"{$kuisioner->judul}\" berhasil diperbarui.");
    }
}