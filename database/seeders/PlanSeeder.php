<?php

namespace Database\Seeders;

use App\Models\Plan;
use Illuminate\Database\Seeder;

class PlanSeeder extends Seeder
{
    public function run(): void
    {
        $plans = [
            [
                'name' => 'Gratuit',
                'slug' => 'gratuit',
                'description' => 'Idéal pour démarrer votre boutique en ligne.',
                'highlight' => null,
                'price' => 0,
                'currency' => 'CDF',
                'interval' => 'month',
                'trial_days' => 0,
                'features' => $this->getFeaturesFor('Gratuit'),
                'limits' => $this->getLimitsFor('Gratuit'),
                'sort_order' => 0,
                'is_active' => true,
                'is_featured' => false,
                'is_recommended' => false,
                'badge' => null,
                'badge_color' => null,
            ],
            [
                'name' => 'Starter',
                'slug' => 'starter',
                'description' => 'Pour les petites boutiques qui veulent plus de fonctionnalités.',
                'highlight' => null,
                'price' => 29,
                'currency' => 'CDF',
                'interval' => 'month',
                'trial_days' => 14,
                'features' => $this->getFeaturesFor('Starter'),
                'limits' => $this->getLimitsFor('Starter'),
                'sort_order' => 1,
                'is_active' => true,
                'is_featured' => false,
                'is_recommended' => false,
                'badge' => null,
                'badge_color' => null,
            ],
            [
                'name' => 'Pro',
                'slug' => 'pro',
                'description' => 'Le meilleur rapport qualité‑prix pour les boutiques professionnelles.',
                'highlight' => 'Le plus populaire',
                'price' => 79,
                'currency' => 'CDF',
                'interval' => 'month',
                'trial_days' => 14,
                'features' => $this->getFeaturesFor('Pro'),
                'limits' => $this->getLimitsFor('Pro'),
                'sort_order' => 2,
                'is_active' => true,
                'is_featured' => true,
                'is_recommended' => true,
                'badge' => 'Populaire',
                'badge_color' => 'amber',
            ],
            [
                'name' => 'Business',
                'slug' => 'business',
                'description' => 'Pour les entreprises qui veulent aller plus loin.',
                'highlight' => null,
                'price' => 199,
                'currency' => 'CDF',
                'interval' => 'month',
                'trial_days' => 14,
                'features' => $this->getFeaturesFor('Business'),
                'limits' => $this->getLimitsFor('Business'),
                'sort_order' => 3,
                'is_active' => true,
                'is_featured' => false,
                'is_recommended' => false,
                'badge' => null,
                'badge_color' => null,
            ],
            [
                'name' => 'Enterprise',
                'slug' => 'enterprise',
                'description' => 'Solution sur mesure pour les grandes structures.',
                'highlight' => null,
                'price' => 499,
                'currency' => 'CDF',
                'interval' => 'month',
                'trial_days' => 14,
                'features' => $this->getFeaturesFor('Enterprise'),
                'limits' => $this->getLimitsFor('Enterprise'),
                'sort_order' => 4,
                'is_active' => true,
                'is_featured' => false,
                'is_recommended' => false,
                'badge' => 'Sur mesure',
                'badge_color' => 'blue',
            ],
        ];

        foreach ($plans as $plan) {
            Plan::create($plan);
        }
    }

    private function getFeaturesFor(string $name): array
    {
        // Les mêmes listes que votre factory
        return match ($name) {
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
        };
    }

    private function getLimitsFor(string $name): array
    {
        return match ($name) {
            'Gratuit' => ['products' => '10', 'storage' => '500 Mo', 'bandwidth' => '5 Go', 'staff_accounts' => '1'],
            'Starter' => ['products' => '100', 'storage' => '5 Go', 'bandwidth' => '50 Go', 'staff_accounts' => '3'],
            'Pro' => ['products' => 'Illimité', 'storage' => '50 Go', 'bandwidth' => '500 Go', 'staff_accounts' => '10'],
            'Business' => ['products' => 'Illimité', 'storage' => '200 Go', 'bandwidth' => '2 To', 'staff_accounts' => '25'],
            'Enterprise' => ['products' => 'Illimité', 'storage' => 'Illimité', 'bandwidth' => 'Illimité', 'staff_accounts' => 'Illimité'],
        };
    }
}
