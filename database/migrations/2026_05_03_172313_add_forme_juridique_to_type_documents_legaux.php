<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('type_documents_legaux', function (Blueprint $table) {
            $table->string('forme_juridique')->nullable()->after('est_obligatoire');
        });

        // Mettre à jour les enregistrements existants
        DB::table('type_documents_legaux')
            ->whereIn('code', ['RCCM', 'STATUTS', 'CARTE_ARTISAN', 'TPE'])
            ->update(['forme_juridique' => 'societe_commerciale']);

        DB::table('type_documents_legaux')
            ->where('code', 'PATENTE')
            ->update(['forme_juridique' => 'petit_commercant']);

        DB::table('type_documents_legaux')
            ->where('code', 'PERSONNALITE_JURIDIQUE')
            ->update(['forme_juridique' => 'organisation_sans_but_lucratif']);

        // Documents communs à toutes les formes
        DB::table('type_documents_legaux')
            ->whereIn('code', ['ID_NAT', 'IFU'])
            ->update(['forme_juridique' => 'toutes']);
    }

    public function down(): void
    {
        Schema::table('type_documents_legaux', function (Blueprint $table) {
            $table->dropColumn('forme_juridique');
        });
    }
};
