<?php

// database/migrations/2026_05_22_000001_create_visits_table.php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('visits', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('visitor_id')->index();
            $table->string('session_id')->nullable()->index();
            $table->nullableUuidMorphs('visitable'); // polymorphique
            $table->string('url')->nullable();
            $table->string('path')->nullable();
            $table->string('method')->default('GET');
            $table->string('referrer')->nullable();
            $table->string('ip')->nullable();
            $table->string('device')->nullable();
            $table->string('platform')->nullable();
            $table->string('browser')->nullable();
            $table->string('language')->nullable();
            $table->json('utm_params')->nullable();
            $table->integer('duration')->default(0);   // temps passé (secondes)
            $table->timestamp('visited_at')->useCurrent();
            $table->timestamps();

            // $table->index(['visitable_type', 'visitable_id']);
            $table->index('visited_at');
        });
    }

    public function down()
    {
        Schema::dropIfExists('visits');
    }
};
