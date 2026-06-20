<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasColumn('user_tenant', 'id')) {
            Schema::table('user_tenant', function (Blueprint $table) {
                // Ajoute d'abord la colonne sans contrainte NOT NULL
                $table->uuid('id')->nullable();
            });

            // Remplir la colonne id avec des UUID pour les enregistrements existants
            DB::table('user_tenant')->orderBy('tenant_id')->each(function ($row) {
                DB::table('user_tenant')->where('tenant_id', $row->tenant_id)->where('user_id', $row->user_id)
                    ->update(['id' => (string) Str::orderedUuid()]);
            });

            Schema::table('user_tenant', function (Blueprint $table) {
                // Rendre la colonne NOT NULL et PRIMARY KEY
                $table->uuid('id')->primary()->change();
            });
        }
    }

    public function down(): void
    {
        Schema::table('user_tenant', function (Blueprint $table) {
            // $table->dropColumn('id');
        });
    }
};
