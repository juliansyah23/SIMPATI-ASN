<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

/**
 * Impor data pegawai dari export lama (database/seeders/data/orei_users.csv)
 * hasil pencocokan dengan SK Kepala OREI No. 27/III.6/HK/2026 tentang
 * Kelompok Riset di Lingkungan OREI (KepOREI_2026_27_Kelompok Riset di
 * OREI.pdf) — sumber PDF hanya dipakai untuk verifikasi selama analisis,
 * bukan diparse langsung oleh seeder ini.
 *
 * Skema sumber tidak cocok 1:1 dengan tabel users SIMPATI-ASN, jadi field
 * disesuaikan dengan aturan berikut (lihat masing-masing method):
 * - pusat_riset: diturunkan dari `unit_kerja_id` (UUID), yang terbukti
 *   berkorespondensi 1:1 dengan 7 Pusat Riset resmi di config('options.pusat_riset').
 *   Sebagian kecil baris tanpa unit_kerja_id dicocokkan manual by name; sisanya
 *   (kolaborator eksternal / akun tanpa unit yang tidak dikenali) di-skip.
 * - posisi: digabung dari jenis_fungsional + tingkat_fungsional, dipetakan
 *   ke salah satu dari 22 nilai resmi di config('options.posisi') — CSV
 *   sumber punya kategori (penelaah/lektor/dll) yang tidak match persis,
 *   jadi dipetakan ke kategori terdekat (lihat mapJenisToCategory()).
 * - nip: tidak tersedia di sumber manapun -> NULL (kolom sudah dibuat
 *   nullable lewat migration make_nip_nullable_in_users_table).
 * - email & password: SENGAJA digenerate ulang dari nama (bukan dipakai
 *   dari kolom csv), karena data sumber ada yang rusak (domain typo
 *   "@brin.co.id", akun duplikat per kelompok riset dengan email berbeda).
 *   Format diminta eksplisit: email = nama (spasi->titik) + "@brin.go.id",
 *   password = 5 karakter awal nama (tanpa spasi/tanda baca) + "123".
 * - role: selalu "pegawai" — 3 baris admin/superadmin di sumber adalah
 *   akun dummy/sistem (superadmin user, dummy, admin prsdi) dan di-skip,
 *   bukan pegawai riil.
 */
class OreiUserSeeder extends Seeder
{
    /** unit_kerja_id (UUID) -> Pusat Riset, diverifikasi 1:1 lewat cross-check roster SK. */
    private const UNIT_TO_PUSAT_RISET = [
        '019e33b2-e0aa-7300-8ef0-8aa19e640420' => 'Pusat Riset Kecerdasan Artifisial dan Keamanan Siber',
        'ad700036-a925-40e2-96c4-8ddef0e32ad2' => 'Pusat Riset Sains Data dan Informasi',
        '1e23bf7c-6921-4355-beab-15c72059c7ce' => 'Pusat Riset Geoinformatika',
        '61dad47e-7148-49f2-bada-123b65354d75' => 'Pusat Riset Telekomunikasi',
        '782ebbef-1aed-45ed-a71c-b08a34e2c604' => 'Pusat Riset Mekatronika Cerdas',
        '655a80f7-8c98-425d-8a87-cba00912614f' => 'Pusat Riset Komputasi',
        '900a9d5d-a2b7-4f9f-86d3-e8a594814dbc' => 'Pusat Riset Elektronika',
    ];

    /**
     * Fallback untuk baris tanpa unit_kerja_id yang namanya masih dikenali
     * di lampiran SK (dicek manual satu per satu, bukan roster penuh).
     */
    private const NAME_FALLBACK_PUSAT_RISET = [
        'muh hafizh izzaturrahim' => 'Pusat Riset Sains Data dan Informasi',
        'al hafiz akbar maulana siagian' => 'Pusat Riset Kecerdasan Artifisial dan Keamanan Siber',
        'retno anggreini dyah ayuningtias' => 'Pusat Riset Sains Data dan Informasi',
    ];

    /** Akun dummy/sistem di data sumber, bukan pegawai riil — di-skip total. */
    private const EXCLUDED_NAMES = [
        'superadmin user',
        'dummy',
        'admin prsdi',
        'monev user',
        'head user',
        'researcher user',
    ];

    /** @var array<string,bool> pelacak email yang sudah dipakai pada proses import ini. */
    private array $usedEmails = [];

    public function run(): void
    {
        $csvPath = database_path('seeders/data/orei_users.csv');

        if (! file_exists($csvPath)) {
            $this->command?->warn("OreiUserSeeder: file tidak ditemukan di {$csvPath}, dilewati.");
            return;
        }

        User::where('email', 'like', '%@brin.go.id')
            ->pluck('email')
            ->each(fn (string $email) => $this->usedEmails[$email] = true);

        $handle = fopen($csvPath, 'r');
        $header = fgetcsv($handle, 0, ';');

        $seenNames = [];
        $imported = 0;
        $skippedExcluded = 0;
        $skippedNoAffiliation = 0;
        $skippedDuplicate = 0;
        $matchedByUnit = 0;
        $matchedByNameFallback = 0;

        while (($row = fgetcsv($handle, 0, ';')) !== false) {
            if (count($row) !== count($header)) {
                continue;
            }
            $data = array_combine($header, $row);

            $rawName = trim($data['name']);
            $normalized = $this->normalizeName($rawName);

            if ($normalized === '' || in_array($normalized, self::EXCLUDED_NAMES, true)) {
                $skippedExcluded++;
                continue;
            }

            if (isset($seenNames[$normalized])) {
                // Orang yang sama tergabung di >1 kelompok riset -> muncul >1 baris di CSV.
                $skippedDuplicate++;
                continue;
            }

            $unitKerjaId = $this->cleanNull($data['unit_kerja_id'] ?? null);
            $pusatRiset = null;

            if ($unitKerjaId !== null && isset(self::UNIT_TO_PUSAT_RISET[$unitKerjaId])) {
                $pusatRiset = self::UNIT_TO_PUSAT_RISET[$unitKerjaId];
                $matchedByUnit++;
            } elseif (isset(self::NAME_FALLBACK_PUSAT_RISET[$normalized])) {
                $pusatRiset = self::NAME_FALLBACK_PUSAT_RISET[$normalized];
                $matchedByNameFallback++;
            }

            if ($pusatRiset === null) {
                // Tidak ada unit_kerja_id maupun kecocokan nama di SK -> kemungkinan
                // kolaborator eksternal, bukan pegawai OREI. Jangan menebak pusat
                // riset-nya, lebih baik dilewati daripada data salah.
                $skippedNoAffiliation++;
                continue;
            }

            $seenNames[$normalized] = true;

            $email = $this->uniqueEmail($this->generateEmail($rawName));
            $password = $this->generatePassword($rawName);
            $posisi = $this->buildPosisi(
                $this->cleanNull($data['jenis_fungsional'] ?? null),
                $this->cleanNull($data['tingkat_fungsional'] ?? null)
            );

            User::create([
                'nip' => null,
                'name' => $this->titleCase($rawName),
                'email' => $email,
                'institusi' => 'BRIN',
                'pusat_riset' => $pusatRiset,
                'posisi' => $posisi,
                'role' => 'pegawai',
                'password' => Hash::make($password),
            ]);

            $imported++;
        }

        fclose($handle);

        $this->command?->info(sprintf(
            'OreiUserSeeder: %d user diimpor (%d via unit_kerja_id, %d via fallback nama). Dilewati: %d duplikat, %d akun dummy/sistem, %d tanpa afiliasi yang bisa dipastikan.',
            $imported,
            $matchedByUnit,
            $matchedByNameFallback,
            $skippedDuplicate,
            $skippedExcluded,
            $skippedNoAffiliation
        ));
    }

    /** Kategori fungsional CSV sumber -> kategori resmi di config('options.posisi'). */
    private function mapJenisToCategory(?string $jenis): string
    {
        $jenis = $jenis === null ? '' : strtolower(trim($jenis));

        return match (true) {
            $jenis === 'peneliti' => 'Peneliti',
            in_array($jenis, ['perekayasa', 'pengembang teknologi nuklir'], true) => 'Perekayasa',
            in_array($jenis, ['analis', 'analis data ilmiah'], true) => 'Analis Data Ilmiah',
            // "penelaah" (penelaah kebijakan) tidak ada padanan persis di list resmi;
            // kategori terdekat secara konsep adalah analis pemanfaatan iptek/kebijakan.
            $jenis === 'penelaah' => 'Analis Pemanfaatan IPTEK',
            // NULL, "lektor", atau nilai lain yang tak dikenal -> fallback paling umum.
            default => 'Peneliti',
        };
    }

    private function mapTingkatToLevel(?string $tingkat): string
    {
        $tingkat = $tingkat === null ? '' : strtolower(trim(rtrim($tingkat, ') ')));

        return match ($tingkat) {
            'pertama' => 'Ahli Pertama',
            'muda' => 'Ahli Muda',
            'madya' => 'Ahli Madya',
            'utama' => 'Ahli Utama',
            // "kebijakan" (menyertai penelaah), data kosong, atau data kotor -> level terendah.
            default => 'Ahli Pertama',
        };
    }

    private function buildPosisi(?string $jenis, ?string $tingkat): string
    {
        $posisi = trim($this->mapJenisToCategory($jenis).' '.$this->mapTingkatToLevel($tingkat));

        // Jaring pengaman terakhir: pastikan hasil akhir selalu salah satu opsi resmi,
        // supaya konsisten dengan dropdown/filter yang membaca config('options.posisi').
        $valid = config('options.posisi', []);
        if (! empty($valid) && ! in_array($posisi, $valid, true)) {
            return $valid[0];
        }

        return $posisi;
    }

    private function cleanNull(?string $value): ?string
    {
        if ($value === null) {
            return null;
        }
        $value = trim($value);

        return ($value === '' || strtoupper($value) === 'NULL') ? null : $value;
    }

    /**
     * Kunci normalisasi untuk deduplikasi & pencocokan nama (bukan untuk
     * ditampilkan). Apostrof HARUS dibuang (bukan cuma titik/koma) — data
     * sumber punya ejaan tidak konsisten untuk nama yang sama, mis.
     * "Iftitahu Ni'mah" vs "Iftitahu Ni''mah", yang tanpa ini akan lolos
     * sebagai 2 orang berbeda alih-alih dianggap duplikat.
     */
    private function normalizeName(string $name): string
    {
        $name = strtolower(str_replace(['.', ',', "'", "\u{2019}"], ' ', trim($name)));

        return trim(preg_replace('/\s+/', ' ', $name));
    }

    private function titleCase(string $name): string
    {
        $name = trim(preg_replace('/\s+/', ' ', $name));

        return mb_convert_case($name, MB_CASE_TITLE, 'UTF-8');
    }

    /**
     * Format diminta eksplisit: spasi (termasuk titik yang sudah ada) -> titik,
     * lalu @brin.go.id. Tanda baca selain spasi/titik (apostrof, dsb — mis.
     * "Ni'mah", "Nu'man", "'Allam") dibuang, bukan ikut ke local-part email.
     */
    private function generateEmail(string $rawName): string
    {
        $slug = strtolower(trim($rawName));
        $slug = str_replace(["'", "\u{2019}"], '', $slug);
        $slug = str_replace(' ', '.', $slug);
        $slug = preg_replace('/\.+/', '.', $slug);
        $slug = trim($slug, '.');

        return $slug.'@brin.go.id';
    }

    /** Format diminta eksplisit: 5 huruf awal nama (tanpa spasi/tanda baca) + "123". */
    private function generatePassword(string $rawName): string
    {
        $base = strtolower(preg_replace('/[^a-zA-Z0-9]/', '', $rawName));

        return substr($base, 0, 5).'123';
    }

    private function uniqueEmail(string $email): string
    {
        if (! isset($this->usedEmails[$email])) {
            $this->usedEmails[$email] = true;

            return $email;
        }

        [$local, $domain] = explode('@', $email, 2);
        $i = 2;
        do {
            $candidate = "{$local}{$i}@{$domain}";
            $i++;
        } while (isset($this->usedEmails[$candidate]));

        $this->usedEmails[$candidate] = true;

        return $candidate;
    }
}