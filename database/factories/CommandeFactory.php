<?php

namespace Database\Factories;

use App\Models\Client;
use App\Models\Commande;
use App\Models\Panier;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Commande>
 */
class CommandeFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $sousTotal = fake()->randomFloat(2, 10, 500);
        $taxe = $sousTotal * 0.2;
        $fraisLivraison = fake()->randomFloat(2, 0, 20);
        $total = $sousTotal + $taxe + $fraisLivraison;

        return [
            'client_id' => Client::factory(),
            'panier_id' => Panier::factory(),
            'adresse_facturation_id' => null,
            'adresse_livraison_id' => null,
            'numero_commande' => 'CMD-'.fake()->unique()->numerify('########'),
            'statut' => fake()->randomElement([
                Commande::STATUT_EN_ATTENTE,
                Commande::STATUT_EN_COURS,
                Commande::STATUT_TERMINE,
                Commande::STATUT_ANNULE,
                Commande::STATUT_REJETE,
            ]),
            'sous_total' => $sousTotal,
            'taxe' => $taxe,
            'frais_livraison' => $fraisLivraison,
            'total' => $total,
            'mode_paiement' => fake()->randomElement(['card', 'paypal', 'bank_transfer', 'cash_on_delivery']),
            'notes' => fake()->optional()->text(),
            'date_commande' => fake()->dateTimeBetween('-2 years', 'now'),
            'date_paiement' => fake()->optional()->dateTimeBetween('-2 years', 'now'),
            'date_expedition' => fake()->optional()->dateTimeBetween('-2 years', 'now'),
            'date_livraison' => fake()->optional()->dateTimeBetween('-2 years', 'now'),
            'metadata' => [
                'ip_address' => fake()->ipv4(),
                'user_agent' => fake()->userAgent(),
            ],
        ];
    }

    /**
     * Indicate that the order is pending.
     */
    public function enAttente(): static
    {
        return $this->state(fn (array $attributes) => [
            'statut' => Commande::STATUT_EN_ATTENTE,
            'date_paiement' => null,
            'date_expedition' => null,
            'date_livraison' => null,
        ]);
    }

    /**
     * Indicate that the order is in progress.
     */
    public function enCours(): static
    {
        return $this->state(fn (array $attributes) => [
            'statut' => Commande::STATUT_EN_COURS,
            'date_paiement' => fake()->dateTimeBetween('-1 month', 'now'),
            'date_expedition' => fake()->dateTimeBetween('-1 month', 'now'),
        ]);
    }

    /**
     * Indicate that the order is completed.
     */
    public function terminee(): static
    {
        return $this->state(fn (array $attributes) => [
            'statut' => Commande::STATUT_TERMINE,
            'date_paiement' => fake()->dateTimeBetween('-2 months', '-1 month'),
            'date_expedition' => fake()->dateTimeBetween('-2 months', '-1 month'),
            'date_livraison' => fake()->dateTimeBetween('-2 months', '-1 month'),
        ]);
    }

    /**
     * Indicate that the order is cancelled.
     */
    public function annulee(): static
    {
        return $this->state(fn (array $attributes) => [
            'statut' => Commande::STATUT_ANNULE,
        ]);
    }

    /**
     * Indicate that the order is rejected.
     */
    public function rejetee(): static
    {
        return $this->state(fn (array $attributes) => [
            'statut' => Commande::STATUT_REJETE,
        ]);
    }

    /**
     * Indicate that the order is paid.
     */
    public function payee(): static
    {
        return $this->state(fn (array $attributes) => [
            'statut' => Commande::STATUT_EN_COURS,
            'date_paiement' => fake()->dateTimeBetween('-1 month', 'now'),
        ]);
    }

    /**
     * Indicate that the order is delivered.
     */
    public function livree(): static
    {
        return $this->state(fn (array $attributes) => [
            'statut' => Commande::STATUT_TERMINE,
            'date_paiement' => fake()->dateTimeBetween('-2 months', '-1 month'),
            'date_expedition' => fake()->dateTimeBetween('-2 months', '-1 month'),
            'date_livraison' => fake()->dateTimeBetween('-2 months', '-1 month'),
        ]);
    }
}
