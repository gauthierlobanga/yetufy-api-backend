<?php

namespace Database\Factories;

use App\Models\Plan;
use App\Models\Tenant;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

class TenantFactory extends Factory
{
    protected $model = Tenant::class;

    public function definition()
    {
        return [
            'id' => (string) Str::orderedUuid(),
            'raison_sociale' => $this->faker->company,
            'slug' => $this->faker->unique()->slug,
            'email' => $this->faker->unique()->companyEmail,
            'password' => bcrypt('password'),
            'telephone' => $this->faker->phoneNumber,
            'is_active' => true,
            'statut' => Tenant::STATUT_ACTIF,
            'plan_id' => Plan::factory(),
            'date_activation' => now(),
        ];
    }
}
