<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\DemografiField;
use App\Models\Question;
use App\Models\Questionnaire;
use App\Models\User;
use Illuminate\Database\Seeder;

class QuestionnaireSeeder extends Seeder
{
    /**
     * Memindahkan struktur kuisioner & 8 kategori yang sebelumnya hardcode di
     * KuisionerController (questionnaires(), categories(), demografiFields())
     * ke tabel sungguhan, supaya controller bisa di-refactor untuk query DB
     * tanpa mengubah tampilan/struktur yang sudah ada.
     */
    public function run(): void
    {
        $admin = User::where('role', 'admin')->first();

        $q1 = Questionnaire::firstOrCreate(
            ['judul' => 'Pengaruh WFO/WFA terhadap Kinerja Pegawai BRIN'],
            [
                'tahun'      => '2024',
                'deskripsi'  => 'Kuisioner penelitian untuk mengukur pengaruh kebijakan WFO/WFA terhadap kinerja, motivasi, dan kesejahteraan pegawai BRIN',
                'status'     => 'aktif',
                'created_by' => $admin?->id,
            ]
        );

        Questionnaire::firstOrCreate(
            ['judul' => 'Kuisioner Evaluasi Kinerja 2023'],
            [
                'tahun'      => '2023',
                'deskripsi'  => 'Kuisioner evaluasi kinerja pegawai tahun 2023.',
                'status'     => 'ditutup',
                'created_by' => $admin?->id,
            ]
        );

        // Struktur 8 kategori hanya dibuat untuk kuisioner aktif (q1) — sesuai data asli.
        $this->buildCategories($q1);
    }

    private function buildCategories(Questionnaire $questionnaire): void
    {
        // ── Kategori 1: Data Demografi Responden ───────────────────────────
        $demografi = Category::firstOrCreate(
            ['questionnaire_id' => $questionnaire->id, 'kode' => 'demografi'],
            [
                'title'    => 'A. Data Demografi Responden',
                'subtitle' => 'Informasi dasar tentang responden',
                'type'     => 'demografi',
                'urutan'   => 1,
            ]
        );

        $fields = [
            ['field_key' => 'jenis_kelamin', 'label' => 'Jenis Kelamin', 'options' => ['Laki-laki', 'Perempuan']],
            ['field_key' => 'usia', 'label' => 'Usia', 'options' => ['<30 tahun', '30–39 tahun', '40–49 tahun', '≥50 tahun']],
            ['field_key' => 'kategori_jabatan', 'label' => 'Kategori Jabatan', 'options' => config('options.posisi')],
            ['field_key' => 'lama_bekerja', 'label' => 'Lama Bekerja', 'options' => ['<5 tahun', '5–10 tahun', '11–20 tahun', '>20 tahun']],
            ['field_key' => 'pola_kehadiran', 'label' => 'Pola Kehadiran', 'options' => ['WFA penuh', 'WFO 2x/minggu', 'WFO 3x/minggu', 'WFO 5x/minggu']],
        ];

        foreach ($fields as $i => $f) {
            DemografiField::firstOrCreate(
                ['category_id' => $demografi->id, 'field_key' => $f['field_key']],
                [
                    'label'   => $f['label'],
                    'type'    => 'choice',
                    'options' => $f['options'],
                    'urutan'  => $i + 1,
                ]
            );
        }

        // ── Kategori 2-8: dimensi psikososial (Likert + esai) ───────────────
        $dimensi = [
            [
                'kode' => 'persepsi_kebijakan',
                'title' => 'I. Persepsi terhadap Kebijakan WFO/WFA (Job Resources: Clarity & Flexibility)',
                'subtitle' => 'Persepsi pegawai terhadap kebijakan dan fleksibilitas kerja',
                'likert' => [
                    'Aturan WFO/WFA mudah dipahami dan konsisten.',
                    'Kebijakan WFO/WFA berlaku adil bagi semua pegawai.',
                    'Kebijakan WFO/WFA mendukung aktivitas penelitian/pekerjaan saya.',
                    'Kebijakan WFO/WFA berpengaruh positif terhadap produktivitas saya.',
                ],
                'esai' => [
                    'Apa tantangan terbesar yang Anda hadapi dalam memahami atau mengimplementasikan aturan WFO/WFA?',
                    'Jika Anda bisa mengubah satu hal dari kebijakan WFO/WFA BRIN, apa itu dan mengapa?',
                ],
            ],
            [
                'kode' => 'motivasi_kerja',
                'title' => 'II. Motivasi Kerja',
                'subtitle' => 'Tingkat motivasi dan semangat kerja pegawai',
                'likert' => [
                    'Saya merasa bersemangat dalam menjalankan tugas sehari-hari.',
                    'Saya memiliki dorongan untuk menyelesaikan pekerjaan dengan hasil terbaik.',
                    'Saya termotivasi untuk mengembangkan kompetensi diri di tempat kerja.',
                    'Mode kerja saat ini meningkatkan motivasi kerja saya.',
                ],
                'esai' => [
                    'Apa faktor yang paling memengaruhi motivasi kerja Anda dalam mode kerja saat ini?',
                    'Dukungan apa yang Anda perlukan agar motivasi kerja Anda lebih baik?',
                ],
            ],
            [
                'kode' => 'kepuasan_kerja',
                'title' => 'III. Kepuasan Kerja',
                'subtitle' => 'Tingkat kepuasan pegawai terhadap pekerjaan dan lingkungan kerja',
                'likert' => [
                    'Saya merasa puas dengan pekerjaan yang saya jalani saat ini.',
                    'Saya merasa puas dengan hubungan kerja bersama rekan dan atasan.',
                    'Saya merasa puas dengan fasilitas dan dukungan kerja yang diberikan institusi.',
                    'Secara keseluruhan, saya puas dengan mode kerja (WFO/WFA) yang saya jalani.',
                ],
                'esai' => [
                    'Apa hal yang membuat Anda paling puas dalam pekerjaan saat ini?',
                    'Apa hal yang masih membuat Anda kurang puas dan perlu diperbaiki?',
                ],
            ],
            [
                'kode' => 'engagement_pegawai',
                'title' => 'IV. Engagement Pegawai',
                'subtitle' => 'Tingkat keterlibatan dan keterikatan pegawai terhadap organisasi',
                'likert' => [
                    'Saya merasa terlibat secara penuh dalam pekerjaan saya.',
                    'Saya bangga menjadi bagian dari institusi ini.',
                    'Saya bersedia memberikan usaha lebih demi keberhasilan tim/institusi.',
                    'Mode kerja saat ini membuat saya tetap terhubung dengan tim dan institusi.',
                ],
                'esai' => [
                    'Apa yang membuat Anda merasa terhubung (engaged) dengan tim atau institusi?',
                    'Apa yang dapat meningkatkan rasa keterlibatan Anda terhadap institusi?',
                ],
            ],
            [
                'kode' => 'stres_kerja',
                'title' => 'V. Stres Kerja',
                'subtitle' => 'Tingkat tekanan dan stres yang dirasakan dalam bekerja',
                'likert' => [
                    'Saya sering merasa tertekan dengan beban kerja saat ini.',
                    'Saya kesulitan memisahkan waktu kerja dan waktu pribadi.',
                    'Saya merasa cemas atau lelah secara emosional akibat pekerjaan.',
                    'Mode kerja saat ini meningkatkan tingkat stres saya.',
                ],
                'esai' => [
                    'Apa sumber stres kerja terbesar yang Anda rasakan dalam mode kerja saat ini?',
                    'Bagaimana cara Anda mengelola stres kerja selama ini?',
                ],
            ],
            [
                'kode' => 'dukungan_organisasi',
                'title' => 'VI. Persepsi Dukungan Organisasi',
                'subtitle' => 'Persepsi pegawai terhadap dukungan yang diberikan organisasi',
                'likert' => [
                    'Organisasi memperhatikan kesejahteraan pegawai dengan baik.',
                    'Atasan memberikan dukungan yang memadai dalam pekerjaan saya.',
                    'Organisasi menyediakan sarana dan prasarana yang mendukung pekerjaan saya.',
                    'Saya merasa didengar ketika menyampaikan masukan atau keluhan kepada organisasi.',
                ],
                'esai' => [
                    'Bentuk dukungan organisasi apa yang paling Anda rasakan manfaatnya?',
                    'Dukungan apa yang menurut Anda masih perlu ditingkatkan oleh organisasi?',
                ],
            ],
            [
                'kode' => 'wlb',
                'title' => 'VII. Work–Life Balance',
                'subtitle' => 'Keseimbangan antara kehidupan kerja dan kehidupan pribadi',
                'likert' => [
                    'Saya mampu menjaga keseimbangan antara pekerjaan dan kehidupan pribadi.',
                    'Mode kerja saat ini memberikan saya fleksibilitas yang cukup.',
                    'Saya memiliki cukup waktu untuk keluarga dan kegiatan pribadi di luar pekerjaan.',
                    'Pekerjaan tidak mengganggu waktu istirahat dan kesehatan saya secara berlebihan.',
                ],
                'esai' => [
                    'Bagaimana mode kerja saat ini memengaruhi keseimbangan hidup dan kerja Anda?',
                    'Apa yang dapat membantu Anda mencapai work-life balance yang lebih baik?',
                ],
            ],
        ];

        foreach ($dimensi as $i => $d) {
            $category = Category::firstOrCreate(
                ['questionnaire_id' => $questionnaire->id, 'kode' => $d['kode']],
                [
                    'title'    => $d['title'],
                    'subtitle' => $d['subtitle'],
                    'type'     => 'likert',
                    'urutan'   => $i + 2, // demografi sudah urutan 1
                ]
            );

            foreach ($d['likert'] as $j => $pertanyaan) {
                Question::firstOrCreate(
                    ['category_id' => $category->id, 'type' => 'likert', 'pertanyaan' => $pertanyaan],
                    ['urutan' => $j + 1]
                );
            }

            foreach ($d['esai'] as $j => $pertanyaan) {
                Question::firstOrCreate(
                    ['category_id' => $category->id, 'type' => 'esai', 'pertanyaan' => $pertanyaan],
                    ['urutan' => $j + 1]
                );
            }
        }
    }
}
