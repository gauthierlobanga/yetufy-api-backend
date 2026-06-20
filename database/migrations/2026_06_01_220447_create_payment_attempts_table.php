<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('payment_attempts', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('subscription_id')->constrained()->cascadeOnDelete();
            $table->string('stripe_charge_id')->unique();
            $table->string('status'); // succeeded|failed|disputed
            $table->decimal('amount', 12, 2);
            $table->string('currency', 3)->default('CDF');
            $table->string('reason_code')->nullable();
            $table->text('failure_message')->nullable();
            $table->timestamp('attempted_at');
            $table->integer('retry_count')->default(0);
            $table->timestamps();

            $table->index(['subscription_id', 'status']);
            $table->index(['stripe_charge_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('payment_attempts');
    }
};
