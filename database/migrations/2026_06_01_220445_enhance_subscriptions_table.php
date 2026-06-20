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
        Schema::table('subscriptions', function (Blueprint $table) {
            // Add new columns if they don't exist
            if (! Schema::hasColumn('subscriptions', 'tenant_id')) {
                $table->string('tenant_id')
                    ->nullable()
                    ->after('user_id')
                    ->constrained()
                    ->cascadeOnDelete();
            }

            if (! Schema::hasColumn('subscriptions', 'plan_id')) {
                $table->foreignUuid('plan_id')
                    ->nullable()
                    ->after('tenant_id')
                    ->constrained()
                    ->cascadeOnDelete();
            }

            if (! Schema::hasColumn('subscriptions', 'stripe_customer_id')) {
                $table->string('stripe_customer_id')
                    ->nullable()
                    ->after('stripe_id');
            }

            if (! Schema::hasColumn('subscriptions', 'stripe_subscription_id')) {
                $table->string('stripe_subscription_id')
                    ->nullable()
                    ->unique()
                    ->after('stripe_customer_id');
            }

            if (! Schema::hasColumn('subscriptions', 'current_period_start')) {
                $table->timestamp('current_period_start')
                    ->nullable()
                    ->after('stripe_price');
            }

            if (! Schema::hasColumn('subscriptions', 'current_period_end')) {
                $table->timestamp('current_period_end')
                    ->nullable()
                    ->after('current_period_start');
            }

            if (! Schema::hasColumn('subscriptions', 'canceled_at')) {
                $table->timestamp('canceled_at')
                    ->nullable()
                    ->after('ends_at');
            }

            if (! Schema::hasColumn('subscriptions', 'cancellation_reason')) {
                $table->string('cancellation_reason')
                    ->nullable()
                    ->after('canceled_at');
            }

            if (! Schema::hasColumn('subscriptions', 'auto_renewal')) {
                $table->boolean('auto_renewal')
                    ->default(true)
                    ->after('cancellation_reason');
            }

            if (! Schema::hasColumn('subscriptions', 'payment_history')) {
                $table->json('payment_history')
                    ->nullable()
                    ->after('auto_renewal');
            }

            if (! Schema::hasColumn('subscriptions', 'trial_started_at')) {
                $table->timestamp('trial_started_at')
                    ->nullable()
                    ->after('quantity');
            }

            if (! Schema::hasColumn('subscriptions', 'grace_period_ends_at')) {
                $table->timestamp('grace_period_ends_at')
                    ->nullable()
                    ->after('payment_history');
            }

            if (! Schema::hasColumn('subscriptions', 'is_blocked')) {
                $table->boolean('is_blocked')
                    ->default(false)
                    ->after('grace_period_ends_at');
            }

            if (! Schema::hasColumn('subscriptions', 'deleted_at')) {
                $table->softDeletes()
                    ->after('updated_at');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('subscriptions', function (Blueprint $table) {
            $table->dropForeignKeyIfExists(['tenant_id']);
            $table->dropForeignKeyIfExists(['plan_id']);
            $table->dropColumnIfExists([
                'tenant_id',
                'plan_id',
                'stripe_customer_id',
                'stripe_subscription_id',
                'current_period_start',
                'current_period_end',
                'canceled_at',
                'cancellation_reason',
                'auto_renewal',
                'payment_history',
                'trial_started_at',
                'grace_period_ends_at',
                'is_blocked',
                'deleted_at',
            ]);
        });
    }
};
