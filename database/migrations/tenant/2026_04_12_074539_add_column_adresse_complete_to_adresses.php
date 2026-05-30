<?php

// database/migrations/xxxx_xx_xx_xxxxxx_add_adresse_complete_virtual_column.php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (DB::connection()->getDriverName() === 'pgsql') {
            DB::statement('
                ALTER TABLE adresses
                ADD COLUMN adresse_complete TEXT GENERATED ALWAYS AS (
                    rue ||
                    COALESCE(\', \' || complement, \'\') ||
                    \', \' || code_postal || \' \' || ville ||
                    \', \' || pays
                ) STORED
            ');
        } else {
            // Pour MySQL
            DB::statement('
                ALTER TABLE adresses
                ADD COLUMN adresse_complete TEXT GENERATED ALWAYS AS (
                    CONCAT_WS(\', \',
                        rue,
                        complement,
                        CONCAT(code_postal, \' \', ville),
                        pays
                    )
                ) STORED
            ');
        }
    }

    public function down(): void
    {
        Schema::table('adresses', function (Blueprint $table) {
            $table->dropColumn('adresse_complete');
        });
    }
};
