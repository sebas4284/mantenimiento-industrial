<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        DB::table('assets')->where('status', 'fuera_servicio')->update(['status' => 'inactivo']);
        DB::table('assets')->where('status', 'mantenimiento')->update(['status' => 'operativo']);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::table('assets')->where('status', 'inactivo')->update(['status' => 'fuera_servicio']);
    }
};
