<?php

namespace Database\Factories;

use App\Models\Promotion;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

class PromotionFactory extends Factory
{
    protected $model = Promotion::class;

    public function definition()
    {
        return [
            'id' => (string) Str::orderedUuid(),
            'nom' => $this->faker->word,
            'type' => $this->faker->randomElement([Promotion::TYPE_POURCENTAGE, Promotion::TYPE_MONTANT_FIXE]),
            'valeur' => $this->faker->randomFloat(2, 5, 50),
            'est_active' => true,
            'date_debut' => now()->subDays(1),
            'date_fin' => now()->addDays(30),
        ];
    }
}
