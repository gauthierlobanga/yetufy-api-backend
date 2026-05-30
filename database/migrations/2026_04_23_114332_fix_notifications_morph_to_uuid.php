<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Supprimer l'ancien index
        Schema::table('notifications', function (Blueprint $table) {
            $table->dropMorphs('notifiable');
        });

        // Recréer avec uuidMorphs
        Schema::table('notifications', function (Blueprint $table) {
            $table->uuidMorphs('notifiable');
        });
    }

    public function down(): void
    {
        Schema::table('notifications', function (Blueprint $table) {
            $table->dropMorphs('notifiable');
        });

        Schema::table('notifications', function (Blueprint $table) {
            $table->morphs('notifiable');
        });
    }
};
