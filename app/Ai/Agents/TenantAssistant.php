<?php

namespace App\Ai\Agents;

use App\Ai\Tools\CreateDiscount;
use App\Ai\Tools\GetAbandonedCarts;
use App\Ai\Tools\GetCart;
use App\Ai\Tools\GetCustomerStats;
use App\Ai\Tools\GetLowStockProducts;
use App\Ai\Tools\GetOrders;
use App\Ai\Tools\GetRevenueStats;
use App\Ai\Tools\GetStats;
use App\Ai\Tools\GetTopProducts;
use App\Ai\Tools\SearchProducts;
use App\Models\Tenant;
// use Laravel\Ai\Attributes\Model;
use Laravel\Ai\Attributes\Provider;
use Laravel\Ai\Concerns\RemembersConversations;
use Laravel\Ai\Contracts\Agent;
use Laravel\Ai\Contracts\Conversational;
use Laravel\Ai\Contracts\HasTools;
use Laravel\Ai\Enums\Lab;
use Laravel\Ai\Promptable;

#[Provider(Lab::Gemini)]
// #[Provider(Lab::DeepSeek)]
// #[Model('deepseek-chat')]
// #[Provider(Lab::OpenAI)]
// #[Model('gpt-4o-mini')]
class TenantAssistant implements Agent, Conversational, HasTools
{
    use Promptable, RemembersConversations;

    public function __construct(protected Tenant $tenant) {}

    public function instructions(): string
    {
        $context = [
            'boutique' => $this->tenant->raison_sociale,
            'slug' => $this->tenant->slug,
            'plan' => $this->tenant->plan?->name ?? 'gratuit',
        ];

        return <<<PROMPT
            Tu es l'assistant e‑commerce de la boutique « {$context['boutique']} ».
            Voici le plan actif : {$context['plan']}.
            Tu disposes de plusieurs outils pour répondre aux questions du propriétaire :

            - `GetStats` : statistiques générales (produits, commandes, paniers, etc.)
            - `GetTopProducts` : produits les plus vendus
            - `SearchProducts` : rechercher des produits par mot-clé
            - `CreateDiscount` : créer une promotion (demander le type, le montant, le code, les dates)


            Sois proactif : propose des actions concrètes (lancer une promotion, améliorer les fiches produits, etc.) basées sur les données que tu obtiens.
            Parle uniquement en français et reste professionnel.
            PROMPT;
    }

    public function tools(): iterable
    {
        return [
            new GetStats,
            new GetCart,
            new GetOrders,
            new GetTopProducts,
            new SearchProducts,
            new CreateDiscount,
            new GetLowStockProducts,
            new GetRevenueStats,
            new GetAbandonedCarts,
            new GetCustomerStats,
        ];
    }
}
