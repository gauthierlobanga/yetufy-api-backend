<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('personal_access_tokens')) {
            return;
        }

        DB::table('personal_access_tokens')->delete();

        $driver = DB::getDriverName();

        if ($driver === 'pgsql') {
            DB::statement('DROP INDEX IF EXISTS personal_access_tokens_tokenable_type_tokenable_id_index');
            DB::statement('ALTER TABLE personal_access_tokens ALTER COLUMN tokenable_id TYPE uuid USING tokenable_id::text::uuid');
            DB::statement('CREATE INDEX personal_access_tokens_tokenable_type_tokenable_id_index ON personal_access_tokens (tokenable_type, tokenable_id)');

            return;
        }

        if ($driver === 'mysql') {
            DB::statement('ALTER TABLE personal_access_tokens MODIFY tokenable_id CHAR(36) NOT NULL');
        }
    }

    public function down(): void
    {
        if (! Schema::hasTable('personal_access_tokens')) {
            return;
        }

        DB::table('personal_access_tokens')->delete();

        $driver = DB::getDriverName();

        if ($driver === 'pgsql') {
            DB::statement('DROP INDEX IF EXISTS personal_access_tokens_tokenable_type_tokenable_id_index');
            DB::statement('ALTER TABLE personal_access_tokens ALTER COLUMN tokenable_id TYPE bigint USING NULL');
            DB::statement('CREATE INDEX personal_access_tokens_tokenable_type_tokenable_id_index ON personal_access_tokens (tokenable_type, tokenable_id)');

            return;
        }

        if ($driver === 'mysql') {
            DB::statement('ALTER TABLE personal_access_tokens MODIFY tokenable_id BIGINT UNSIGNED NOT NULL');
        }
    }
};
