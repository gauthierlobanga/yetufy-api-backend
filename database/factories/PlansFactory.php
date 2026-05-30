<?php

// database/factories/PlanFactory.php

namespace Database\Factories;

use App\Models\Plan;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<Plan>
 */
class PlansFactory extends Factory
{
    protected $model = Plan::class;

    public function definition(): array
    {
        $name = fake()->unique()->randomElement([
            'Gratuit',
            'Starter',
            'Pro',
            'Business',
            'Enterprise',
        ]);

        $prices = [
            'Gratuit' => 0,
            'Starter' => 29,
            'Pro' => 79,
            'Business' => 199,
            'Enterprise' => 499,
        ];

        return [
            'name' => $name,
            'slug' => Str::slug($name),
            'description' => fake()->sentence(),
            'highlight' => null,
            'price' => $prices[$name] ?? 0,
            'currency' => 'CDF',
            'interval' => 'month',
            'trial_days' => $name === 'Gratuit' ? 0 : 14,
            'features' => $this->getFeaturesForPlan($name),
            'limits' => $this->getLimitsForPlan($name),
            'sort_order' => array_search($name, ['Gratuit', 'Starter', 'Pro', 'Business', 'Enterprise']),
            'is_active' => true,
            'is_featured' => $name === 'Pro',
            'is_recommended' => $name === 'Pro',
            'badge' => $name === 'Pro' ? 'Populaire' : null,
            'badge_color' => 'amber',
        ];
    }

    private function getFeaturesForPlan(string $name): array
    {
        $features = [
            'Gratuit' => [
                'Jusqu\'à 10 produits',
                '1 compte personnel',
                'Support par email',
                'Sous-domaine gratuit',
                'Thèmes de base',
                'Paiement à la livraison',
                'Statistiques de base',
            ],
            'Starter' => [
                'Jusqu\'à 100 produits',
                '3 comptes personnel',
                'Support prioritaire',
                'Sous-domaine gratuit',
                'Thèmes premium',
                'Paiement en ligne (Stripe)',
                'Statistiques avancées',
                'Export des données',
            ],
            'Pro' => [
                'Produits illimités',
                '10 comptes personnel',
                'Support prioritaire 24/7',
                'Nom de domaine personnalisé',
                'Tous les thèmes',
                'Paiement en ligne (Stripe, PayPal)',
                'Statistiques avancées',
                'Export des données',
                'API REST',
                'Marketplace multi-vendeurs',
                'Programme de fidélité',
            ],
            'Business' => [
                'Produits illimités',
                '25 comptes personnel',
                'Support dédié',
                'Nom de domaine personnalisé',
                'Tous les thèmes',
                'Tous les modes de paiement',
                'Statistiques avancées',
                'Export des données',
                'API REST + Webhooks',
                'Marketplace multi-vendeurs',
                'Programme de fidélité',
                'Rapports personnalisés',
                'Gestion des stocks avancée',
            ],
            'Enterprise' => [
                'Produits illimités',
                'Comptes personnel illimités',
                'Support dédié 24/7',
                'Nom de domaine personnalisé',
                'Tous les thèmes + thèmes sur mesure',
                'Tous les modes de paiement',
                'Statistiques avancées + IA',
                'Export des données',
                'API REST + Webhooks',
                'Marketplace multi-vendeurs',
                'Programme de fidélité',
                'Rapports personnalisés',
                'Gestion des stocks avancée',
                'SLA garanti',
                'Formation dédiée',
            ],
        ];

        return $features[$name] ?? $features['Gratuit'];
    }

    private function getLimitsForPlan(string $name): array
    {
        $limits = [
            'Gratuit' => [
                'products' => '10',
                'storage' => '500 Mo',
                'bandwidth' => '5 Go',
                'staff_accounts' => '1',
            ],
            'Starter' => [
                'products' => '100',
                'storage' => '5 Go',
                'bandwidth' => '50 Go',
                'staff_accounts' => '3',
            ],
            'Pro' => [
                'products' => 'Illimité',
                'storage' => '50 Go',
                'bandwidth' => '500 Go',
                'staff_accounts' => '10',
            ],
            'Business' => [
                'products' => 'Illimité',
                'storage' => '200 Go',
                'bandwidth' => '2 To',
                'staff_accounts' => '25',
            ],
            'Enterprise' => [
                'products' => 'Illimité',
                'storage' => 'Illimité',
                'bandwidth' => 'Illimité',
                'staff_accounts' => 'Illimité',
            ],
        ];

        return $limits[$name] ?? $limits['Gratuit'];
    }

    public function free(): static
    {
        return $this->state(fn (array $attributes) => [
            'price' => 0,
            'trial_days' => 0,
        ]);
    }

    public function featured(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_featured' => true,
        ]);
    }

    public function annual(): static
    {
        return $this->state(fn (array $attributes) => [
            'interval' => 'year',
            'price' => $attributes['price'] * 10, // Moins cher qu'en mensuel
        ]);
    }
}
