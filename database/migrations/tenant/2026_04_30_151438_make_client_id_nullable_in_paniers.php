<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('paniers', function (Blueprint $table) {
            $table->uuid('user_id')->nullable()->change();
            $table->uuid('client_id')->nullable()->change();
        });
    }

    public function down(): void
    {
        Schema::table('paniers', function (Blueprint $table) {
            $table->uuid('user_id')->nullable(false)->change();
            $table->uuid('client_id')->nullable(false)->change();
        });
    }
};
