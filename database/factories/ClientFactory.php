<?php

namespace Database\Factories;

use App\Models\Client;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Client>
 */
class ClientFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'type' => fake()->randomElement([Client::TYPE_PARTICULIER, Client::TYPE_PROFESSIONNEL, Client::TYPE_ENTREPRISE]),
            'civilite' => fake()->randomElement([Client::CIVILITE_M, Client::CIVILITE_MME, Client::CIVILITE_MLLE]),
            'nom' => fake()->lastName(),
            'prenom' => fake()->firstName(),
            'societe' => fake()->company(),
            'siret' => fake()->numerify('#############'),
            'code_tva' => fake()->countryCode().fake()->numerify('#########'),
            'email' => fake()->unique()->safeEmail(),
            'telephone' => fake()->phoneNumber(),
            'portable' => fake()->phoneNumber(),
            'fax' => fake()->phoneNumber(),
            'site_web' => fake()->url(),
            'notes' => fake()->text(),
            'preferences' => [
                'language' => fake()->randomElement(['fr', 'en', 'es']),
                'currency' => fake()->randomElement(['EUR', 'USD', 'GBP']),
            ],
            'date_premier_achat' => fake()->dateTimeBetween('-2 years', '-1 year'),
            'date_dernier_achat' => fake()->dateTimeBetween('-1 year', 'now'),
            'date_derniere_connexion' => fake()->dateTimeBetween('-6 months', 'now'),
            'total_achats' => fake()->randomFloat(2, 0, 10000),
            'nombre_commandes' => fake()->numberBetween(0, 100),
            'total_remises' => fake()->randomFloat(2, 0, 1000),
            'chiffre_affaire' => fake()->randomFloat(2, 0, 50000),
            'points_fidelite' => fake()->numberBetween(0, 5000),
            'niveau_fidelite' => fake()->randomElement([Client::NIVEAU_BRONZE, Client::NIVEAU_ARGENT, Client::NIVEAU_OR, Client::NIVEAU_PLATINE, Client::NIVEAU_DIAMANT]),
            'statut' => fake()->randomElement([Client::STATUT_ACTIF, Client::STATUT_INACTIF, Client::STATUT_SUSPENDU, Client::STATUT_FIDELISE, Client::STATUT_VIP]),
            'source' => fake()->randomElement([Client::SOURCE_DIRECT, Client::SOURCE_GOOGLE, Client::SOURCE_FACEBOOK, Client::SOURCE_INSTAGRAM, Client::SOURCE_REFERAL, Client::SOURCE_NEWSLETTER]),
            'metadata' => [
                'created_from_ip' => fake()->ipv4(),
                'referral_code' => fake()->word(),
            ],
        ];
    }

    /**
     * Indicate that the client is a particulier.
     */
    public function particulier(): static
    {
        return $this->state(fn (array $attributes) => [
            'type' => Client::TYPE_PARTICULIER,
            'societe' => null,
            'siret' => null,
            'code_tva' => null,
        ]);
    }

    /**
     * Indicate that the client is a professionnel.
     */
    public function professionnel(): static
    {
        return $this->state(fn (array $attributes) => [
            'type' => Client::TYPE_PROFESSIONNEL,
            'societe' => fake()->company(),
        ]);
    }

    /**
     * Indicate that the client is an entreprise.
     */
    public function entreprise(): static
    {
        return $this->state(fn (array $attributes) => [
            'type' => Client::TYPE_ENTREPRISE,
            'societe' => fake()->company(),
            'siret' => fake()->numerify('#############'),
            'code_tva' => fake()->countryCode().fake()->numerify('#########'),
        ]);
    }

    /**
     * Indicate that the client is active.
     */
    public function actif(): static
    {
        return $this->state(fn (array $attributes) => [
            'statut' => Client::STATUT_ACTIF,
        ]);
    }

    /**
     * Indicate that the client is VIP.
     */
    public function vip(): static
    {
        return $this->state(fn (array $attributes) => [
            'statut' => Client::STATUT_VIP,
            'niveau_fidelite' => Client::NIVEAU_DIAMANT,
            'total_achats' => fake()->randomFloat(2, 10000, 50000),
        ]);
    }

    /**
     * Indicate that the client has no orders.
     */
    public function sansCommande(): static
    {
        return $this->state(fn (array $attributes) => [
            'nombre_commandes' => 0,
            'total_achats' => 0,
            'date_premier_achat' => null,
            'date_dernier_achat' => null,
        ]);
    }
}
