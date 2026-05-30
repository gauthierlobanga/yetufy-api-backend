<?php

namespace Database\Factories;

use App\Models\AvisClient;
use App\Models\Client;
use App\Models\Produit;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

class AvisClientFactory extends Factory
{
    protected $model = AvisClient::class;

    public function definition()
    {
        return [
            'id' => (string) Str::orderedUuid(),
            'client_id' => Client::factory(),
            'produit_id' => Produit::factory(),
            'note' => $this->faker->numberBetween(1, 5),
            'commentaire' => $this->faker->paragraph,
            'approuve' => true,
            'date_avis' => now(),
        ];
    }
}
