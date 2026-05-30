<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        // Table visitor_events (événements génériques)
        Schema::create('visitor_events', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('session_id')->index();
            $table->string('visitor_id')->index();
            $table->string('event_type'); // add_to_cart, begin_checkout, purchase
            $table->string('url')->nullable();
            $table->uuid('product_id')->nullable();
            $table->uuid('order_id')->nullable();
            $table->decimal('value', 10, 2)->nullable();
            $table->jsonb('metadata')->nullable();
            $table->timestamp('occurred_at')->useCurrent();
            $table->timestamps();

            $table->index(['event_type', 'occurred_at']);
        });

        // Table product_views (vues produits)
        Schema::create('product_views', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('product_id');
            $table->string('session_id')->nullable();
            $table->string('visitor_id')->nullable();
            $table->string('url')->nullable();
            $table->timestamp('viewed_at')->useCurrent();
            $table->timestamps();

            $table->index('product_id');
            $table->index('viewed_at');
        });

        // Table conversion_events (funnel détaillé)
        Schema::create('conversion_events', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('session_id')->index();
            $table->string('visitor_id')->index();
            $table->string('step'); // product_view, cart_add, checkout_start, purchase
            $table->boolean('completed')->default(false);
            $table->decimal('value', 10, 2)->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->timestamps();

            $table->index(['step', 'completed_at']);
        });

        // Table analytics_snapshots (agrégations quotidiennes)
        Schema::create('analytics_snapshots', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->date('date');
            $table->jsonb('metrics'); // total_visits, revenue, conversion_rate, etc.
            $table->timestamps();

            $table->unique('date');
        });
    }

    public function down()
    {
        Schema::dropIfExists('visitor_events');
        Schema::dropIfExists('product_views');
        Schema::dropIfExists('conversion_events');
        Schema::dropIfExists('analytics_snapshots');
    }
};
