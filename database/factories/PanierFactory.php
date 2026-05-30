<?php

namespace Database\Factories;

use App\Models\Client;
use App\Models\Panier;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Panier>
 */
class PanierFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $sousTotal = fake()->randomFloat(2, 0, 500);
        $totalTaxes = $sousTotal * 0.2;
        $totalLivraison = fake()->randomFloat(2, 0, 20);
        $totalRemises = fake()->randomFloat(2, 0, 50);
        $totalGeneral = $sousTotal + $totalTaxes + $totalLivraison - $totalRemises;

        return [
            'client_id' => Client::factory(),
            'user_id' => User::factory(),
            'session_id' => fake()->uuid(),
            'statut' => fake()->randomElement([
                Panier::STATUT_ACTIF,
                Panier::STATUT_ABANDONNE,
                Panier::STATUT_CONVERTI,
                Panier::STATUT_EXPIRE,
            ]),
            'sous_total' => $sousTotal,
            'total_taxes' => $totalTaxes,
            'total_livraison' => $totalLivraison,
            'total_remises' => $totalRemises,
            'total_general' => $totalGeneral,
            'metadata' => [
                'ip_address' => fake()->ipv4(),
                'user_agent' => fake()->userAgent(),
            ],
            'date_creation' => fake()->dateTimeBetween('-1 month', 'now'),
            'date_modification' => fake()->dateTimeBetween('-1 month', 'now'),
            'date_abandon' => fake()->optional()->dateTimeBetween('-1 month', 'now'),
            'date_conversion' => fake()->optional()->dateTimeBetween('-1 month', 'now'),
            'expires_at' => fake()->optional()->dateTimeBetween('now', '+30 days'),
        ];
    }

    /**
     * Indicate that the cart is active.
     */
    public function actif(): static
    {
        return $this->state(fn (array $attributes) => [
            'statut' => Panier::STATUT_ACTIF,
            'date_abandon' => null,
            'date_conversion' => null,
            'expires_at' => now()->addDays(30),
        ]);
    }

    /**
     * Indicate that the cart is abandoned.
     */
    public function abandonne(): static
    {
        return $this->state(fn (array $attributes) => [
            'statut' => Panier::STATUT_ABANDONNE,
            'date_abandon' => fake()->dateTimeBetween('-1 month', 'now'),
            'date_conversion' => null,
        ]);
    }

    /**
     * Indicate that the cart is converted.
     */
    public function converti(): static
    {
        return $this->state(fn (array $attributes) => [
            'statut' => Panier::STATUT_CONVERTI,
            'date_conversion' => fake()->dateTimeBetween('-1 month', 'now'),
            'date_abandon' => null,
        ]);
    }

    /**
     * Indicate that the cart is expired.
     */
    public function expire(): static
    {
        return $this->state(fn (array $attributes) => [
            'statut' => Panier::STATUT_EXPIRE,
            'expires_at' => fake()->dateTimeBetween('-30 days', 'now'),
        ]);
    }

    /**
     * Indicate that the cart is empty.
     */
    public function vide(): static
    {
        return $this->state(fn (array $attributes) => [
            'sous_total' => 0,
            'total_taxes' => 0,
            'total_livraison' => 0,
            'total_remises' => 0,
            'total_general' => 0,
        ]);
    }

    /**
     * Indicate that the cart has items.
     */
    public function avecItems(): static
    {
        return $this->state(fn (array $attributes) => [
            'sous_total' => fake()->randomFloat(2, 10, 500),
            'total_taxes' => fake()->randomFloat(2, 2, 100),
            'total_livraison' => fake()->randomFloat(2, 0, 20),
            'total_remises' => fake()->randomFloat(2, 0, 50),
            'total_general' => fake()->randomFloat(2, 10, 500),
        ]);
    }
}
