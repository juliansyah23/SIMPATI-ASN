<?php

/**
 * Daftar opsi resmi yang dipakai di banyak tempat (dropdown register, filter
 * halaman Data, opsi demografi kuisioner, whitelist validasi).
 *
 * INI SATU-SATUNYA SUMBER (single source of truth) untuk list "Posisi" &
 * "Pusat Riset". Kalau mau ubah salah satu list, ubah di sini SAJA — semua
 * tempat lain (AuthController, DataController, KuisionerController,
 * register.blade.php, QuestionnaireSeeder) otomatis ikut berubah karena
 * mereka membaca dari config('options.posisi') / config('options.pusat_riset').
 *
 * JANGAN hardcode ulang list ini di file lain.
 */

return [

    'posisi' => [
       "Peneliti Ahli Pertama",
        "Peneliti Ahli Muda",
        "Peneliti Ahli Madya",
        "Peneliti Ahli Utama",

        "Perekayasa Ahli Pertama",
        "Perekayasa Ahli Muda",
        "Perekayasa Ahli Madya",
        "Perekayasa Ahli Utama",

        "Analis Data Ilmiah Ahli Pertama",
        "Analis Data Ilmiah Ahli Muda",
        "Analis Data Ilmiah Ahli Madya",
        "Analis Data Ilmiah Ahli Utama",

        "Analis Pemanfaatan IPTEK Ahli Pertama",
        "Analis Pemanfaatan IPTEK Ahli Muda",
        "Analis Pemanfaatan IPTEK Ahli Madya",
        "Analis Pemanfaatan IPTEK Ahli Utama",

        "Teknisi Litkayasa Terampil",
        "Teknisi Litkayasa Mahir",
        "Teknisi Litkayasa Penyelia",
        "Teknisi Litkayasa Ahli Pertama",
        "Teknisi Litkayasa Ahli Muda",
        "Teknisi Litkayasa Ahli Madya"
    ],

    'pusat_riset' => [
        'Pusat Riset Elektronika',
        'Pusat Riset Geoinformatika',
        'Pusat Riset Kecerdasan Artifisial dan Keamanan Siber',
        'Pusat Riset Komputasi',
        'Pusat Riset Mekatronika Cerdas',
        'Pusat Riset Sains Data dan Informasi',
        'Pusat Riset Telekomunikasi',
    ],

];
