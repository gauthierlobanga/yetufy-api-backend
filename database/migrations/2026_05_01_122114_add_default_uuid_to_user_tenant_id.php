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
        // Ajout de la valeur par défaut pour les nouvelles lignes
        DB::statement('ALTER TABLE user_tenant ALTER COLUMN id SET DEFAULT gen_random_uuid()');
    }

    public function down(): void
    {
        DB::statement('ALTER TABLE user_tenant ALTER COLUMN id DROP DEFAULT');
    }
};
