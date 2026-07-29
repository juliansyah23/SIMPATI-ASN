<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Data pegawai hasil impor SK Kelompok Riset OREI tidak menyertakan NIP,
     * sehingga kolom ini perlu boleh kosong (index unique tetap aman karena
     * MySQL memperbolehkan banyak NULL pada unique index).
     */
    public function up(): void
    {
        DB::statement('ALTER TABLE users MODIFY nip VARCHAR(18) NULL');
    }

    public function down(): void
    {
        DB::statement('ALTER TABLE users MODIFY nip VARCHAR(18) NOT NULL');
    }
};
