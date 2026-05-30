<?php

namespace Database\Factories;

use App\Models\Brand;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

class BrandFactory extends Factory
{
    protected $model = Brand::class;

    public function definition()
    {
        return [
            'id' => (string) Str::orderedUuid(),
            'nom' => $this->faker->company,
            'slug' => $this->faker->unique()->slug,
        ];
    }
}
