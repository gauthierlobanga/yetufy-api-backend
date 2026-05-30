<?php

namespace Database\Factories;

use App\Models\Plan;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

class PlanFactory extends Factory
{
    protected $model = Plan::class;

    public function definition()
    {
        return [
            'id' => (string) Str::orderedUuid(),
            'name' => $this->faker->word,
            'slug' => $this->faker->slug,
            'price' => $this->faker->randomFloat(2, 0, 100),
            'currency' => 'CDF',
            'interval' => Plan::INTERVAL_MONTH,
            'trial_days' => 14,
            'features' => ['feature1', 'feature2'],
            'limits' => ['products' => 100, 'storage' => '5GB'],
            'is_active' => true,
            'is_featured' => false,
            'is_recommended' => false,
        ];
    }
}
