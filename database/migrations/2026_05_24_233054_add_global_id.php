<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    // Exemple de migration
    public function up(): void
    {
        // DB::statement('UPDATE users SET global_id = id WHERE global_id IS NULL');
        // Schema::table('users', function (Blueprint $table) {
        //     $table->uuid('global_id')->nullable(false)->change();
        // });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            //
        });
    }
};
