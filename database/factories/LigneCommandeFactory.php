<?php

namespace Database\Factories;

use App\Models\Commande;
use App\Models\LigneCommande;
use App\Models\Produit;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

class LigneCommandeFactory extends Factory
{
    protected $model = LigneCommande::class;

    public function definition()
    {
        return [
            'id' => (string) Str::orderedUuid(),
            'commande_id' => Commande::factory(),
            'produit_id' => Produit::factory(),
            'quantite' => $this->faker->numberBetween(1, 5),
            'prix_unitaire' => $this->faker->randomFloat(2, 10, 100),
            'prix_total' => 0,
        ];
    }
}
