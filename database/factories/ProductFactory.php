<?php

namespace Database\Factories;

use App\Models\Produit;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

class ProductFactory extends Factory
{
    protected $model = Produit::class;

    public function definition()
    {
        return [
            'id' => (string) Str::orderedUuid(),
            'nom' => $this->faker->word,
            'slug' => $this->faker->unique()->slug,
            'reference' => $this->faker->unique()->ean13,
            'prix_ht' => $this->faker->randomFloat(2, 10, 500),
            'prix_ttc' => $this->faker->randomFloat(2, 12, 600),
            'quantite_stock' => $this->faker->numberBetween(0, 100),
            'sku' => $this->faker->unique()->ean8,
            'statut' => Produit::STATUS_PUBLISHED,
            'is_featured' => false,
            'is_new' => false,
            'is_bestseller' => false,
            'views_count' => 0,
            'sold_count' => 0,
            'average_rating' => 0,
            'reviews_count' => 0,
            'created_at' => now(),
            'updated_at' => now(),
        ];
    }
}
