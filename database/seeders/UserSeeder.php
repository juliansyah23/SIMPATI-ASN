<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    /**
     * Memindahkan akun demo yang sebelumnya hardcode di AuthController::loginSubmit()
     * ke tabel users sungguhan.
     */
    public function run(): void
    {
        User::firstOrCreate(
            ['email' => 'admin@brin.go.id'],
            [
                'nip'         => '198001012010011001',
                'name'        => 'Admin SIMPATI ASN',
                'institusi'   => 'BRIN',
                'pusat_riset' => 'Pusat Riset Komputasi',
                'posisi'      => 'Analis Data Ilmiah',
                'role'        => 'admin',
                'password'    => Hash::make('admin123'),
            ]
        );

        User::firstOrCreate(
            ['email' => 'employee@brin.go.id'],
            [
                'nip'         => '199002022015012002',
                'name'        => 'Pegawai Contoh',
                'institusi'   => 'BRIN',
                'pusat_riset' => 'Pusat Riset Telekomunikasi',
                'posisi'      => 'Peneliti',
                'role'        => 'pegawai',
                'password'    => Hash::make('employee123'),
            ]
        );
    }
}
