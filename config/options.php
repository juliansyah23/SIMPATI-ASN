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
        'Peneliti',
        'Perekayasa',
        'Analis Data Ilmiah',
        'Analis Pemanfaatan IPTEK',
        'Teknisi Litkayasa',
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
