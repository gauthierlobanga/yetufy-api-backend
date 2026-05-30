<?php

namespace App\Services;

use App\Events\TenantDatabaseNotificationsSent;
use App\Models\Client;
use App\Models\Commande;
use App\Models\ItemPanier;
use App\Models\MouvementStock;
use App\Models\Paiement;
use App\Models\Panier;
use App\Models\Produit;
use App\Models\Promotion;
use App\Models\Retour;
use App\Models\User;
use App\Notifications\TenantDashboardActivityNotification;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Route;
use Throwable;

class TenantRealtimeActivityService
{
    /**
     * @param  array<string, mixed>  $changes
     */
    public function notify(Model $model, string $action, array $changes = []): void
    {
        if (! $this->canNotify($model, $action)) {
            return;
        }

        $tenant = function_exists('tenant') ? tenant() : null;

        if (! $tenant || ! isset($tenant->id)) {
            return;
        }

        $payload = $this->buildPayload(
            model: $model,
            action: $action,
            changes: $changes,
            tenantId: (string) $tenant->id,
        );

        if ($payload === null) {
            return;
        }

        try {
            $recipients = $this->resolveRecipients();

            if ($recipients->isEmpty()) {
                return;
            }

            Notification::send($recipients, new TenantDashboardActivityNotification($payload));

            $recipients->each(
                fn (User $user) => TenantDatabaseNotificationsSent::dispatch(
                    userId: (string) $user->getKey(),
                    tenantId: (string) $tenant->id,
                ),
            );
        } catch (Throwable $exception) {
            Log::warning('Tenant realtime notification failed.', [
                'model' => $model::class,
                'model_id' => (string) $model->getKey(),
                'action' => $action,
                'tenant_id' => $tenant->id,
                'exception' => $exception->getMessage(),
            ]);
            // Ne jamais bloquer un flux métier à cause de la notification temps réel.
        }
    }

    protected function canNotify(Model $model, string $action): bool
    {
        if (app()->runningUnitTests()) {
            return false;
        }

        if (! function_exists('tenancy') || ! tenancy()->initialized) {
            return false;
        }

        if ($model instanceof Panier && $action === 'created') {
            return false;
        }

        if ($model instanceof MouvementStock && $action !== 'created') {
            return false;
        }

        return true;
    }

    protected function resolveRecipients(): Collection
    {
        return User::query()
            ->where('is_active', true)
            ->get();
    }

    /**
     * @param  array<string, mixed>  $changes
     * @return array<string, mixed>|null
     */
    protected function buildPayload(Model $model, string $action, array $changes, string $tenantId): ?array
    {
        $basePayload = [
            'tenant_id' => $tenantId,
            'entity_id' => (string) $model->getKey(),
            'entity_type' => class_basename($model),
            'action' => $action,
            'changes' => array_values(array_keys($changes)),
            'occurred_at' => now()->toIso8601String(),
            'url' => $this->resolveUrl($model),
            'actor' => $this->resolveActor(),
            'type' => 'system',
            'title' => 'Activité détectée',
            'message' => 'Une nouvelle activité a été enregistrée.',
        ];

        return match (true) {
            $model instanceof Commande => [
                ...$basePayload,
                ...$this->buildCommandePayload($model, $action, $changes),
                'type' => 'order',
            ],
            $model instanceof Paiement => [
                ...$basePayload,
                ...$this->buildPaiementPayload($model, $action, $changes),
                'type' => 'payment',
            ],
            $model instanceof Produit => [
                ...$basePayload,
                ...$this->buildProduitPayload($model, $action, $changes),
            ],
            $model instanceof Promotion => [
                ...$basePayload,
                ...$this->buildPromotionPayload($model, $action),
            ],
            $model instanceof Client => [
                ...$basePayload,
                ...$this->buildClientPayload($model, $action),
            ],
            $model instanceof Panier => [
                ...$basePayload,
                ...$this->buildPanierPayload($model, $action, $changes),
            ],
            $model instanceof ItemPanier => [
                ...$basePayload,
                ...$this->buildItemPanierPayload($model, $action, $changes),
                'type' => 'cart',
            ],
            $model instanceof Retour => [
                ...$basePayload,
                ...$this->buildRetourPayload($model, $action, $changes),
            ],
            $model instanceof MouvementStock => [
                ...$basePayload,
                ...$this->buildMouvementStockPayload($model),
            ],
            default => null,
        };
    }

    /**
     * @param  array<string, mixed>  $changes
     * @return array<string, string>
     */
    protected function buildCommandePayload(Commande $commande, string $action, array $changes): array
    {
        $reference = $commande->numero_commande ?: '#'.$commande->id;

        if ($action === 'created') {
            return [
                'title' => 'Nouvelle commande',
                'message' => "La commande {$reference} a été enregistrée ({$this->formatAmount($commande->total)}).",
            ];
        }

        if ($action === 'deleted') {
            return [
                'title' => 'Commande supprimée',
                'message' => "La commande {$reference} a été supprimée.",
            ];
        }

        if (array_key_exists('statut', $changes)) {
            return [
                'title' => 'Statut de commande mis à jour',
                'message' => "La commande {$reference} est maintenant « {$commande->statut} ».",
            ];
        }

        return [
            'title' => 'Commande mise à jour',
            'message' => "La commande {$reference} a été modifiée.",
        ];
    }

    /**
     * @param  array<string, mixed>  $changes
     * @return array<string, string>
     */
    protected function buildPaiementPayload(Paiement $paiement, string $action, array $changes): array
    {
        $reference = $paiement->reference ?: ($paiement->transaction_id ?: '#'.$paiement->id);

        if ($action === 'created') {
            return [
                'title' => 'Nouveau paiement',
                'message' => "Paiement {$reference} enregistré ({$this->formatAmount($paiement->montant)}).",
            ];
        }

        if ($action === 'deleted') {
            return [
                'title' => 'Paiement supprimé',
                'message' => "Le paiement {$reference} a été supprimé.",
            ];
        }

        if (array_key_exists('statut', $changes)) {
            return [
                'title' => 'Statut de paiement mis à jour',
                'message' => "Le paiement {$reference} est maintenant « {$paiement->statut} ».",
            ];
        }

        return [
            'title' => 'Paiement mis à jour',
            'message' => "Le paiement {$reference} a été modifié.",
        ];
    }

    /**
     * @param  array<string, mixed>  $changes
     * @return array<string, string>
     */
    protected function buildProduitPayload(Produit $produit, string $action, array $changes): array
    {
        $nom = $produit->nom ?: 'Produit sans nom';

        if ($action === 'created') {
            return [
                'title' => 'Nouveau produit',
                'message' => "Le produit « {$nom} » a été ajouté.",
            ];
        }

        if ($action === 'deleted') {
            return [
                'title' => 'Produit supprimé',
                'message' => "Le produit « {$nom} » a été supprimé.",
            ];
        }

        if (array_key_exists('quantite_stock', $changes) && $this->isLowStock($produit)) {
            return [
                'title' => 'Alerte stock faible',
                'message' => "Le produit « {$nom} » est en stock faible ({$produit->quantite_stock} restants).",
            ];
        }

        return [
            'title' => 'Produit mis à jour',
            'message' => "Le produit « {$nom} » a été modifié.",
        ];
    }

    /**
     * @return array<string, string>
     */
    protected function buildPromotionPayload(Promotion $promotion, string $action): array
    {
        $label = $promotion->nom ?: ($promotion->code ?: 'Promotion');

        if ($action === 'created') {
            return [
                'title' => 'Nouvelle promotion',
                'message' => "La promotion « {$label} » a été créée.",
            ];
        }

        if ($action === 'deleted') {
            return [
                'title' => 'Promotion supprimée',
                'message' => "La promotion « {$label} » a été supprimée.",
            ];
        }

        return [
            'title' => 'Promotion mise à jour',
            'message' => "La promotion « {$label} » a été modifiée.",
        ];
    }

    /**
     * @return array<string, string>
     */
    protected function buildClientPayload(Client $client, string $action): array
    {
        $label = trim(($client->prenom ? "{$client->prenom} " : '').($client->nom ?: $client->email ?: 'Client'));

        if ($action === 'created') {
            return [
                'title' => 'Nouveau client',
                'message' => "Le client « {$label} » a été ajouté.",
            ];
        }

        if ($action === 'deleted') {
            return [
                'title' => 'Client supprimé',
                'message' => "Le client « {$label} » a été supprimé.",
            ];
        }

        return [
            'title' => 'Client mis à jour',
            'message' => "Le client « {$label} » a été modifié.",
        ];
    }

    /**
     * @param  array<string, mixed>  $changes
     * @return array<string, string>
     */
    protected function buildPanierPayload(Panier $panier, string $action, array $changes): array
    {
        $reference = '#'.$panier->id;

        if ($action === 'deleted') {
            return [
                'title' => 'Panier supprimé',
                'message' => "Le panier {$reference} a été supprimé.",
            ];
        }

        if (array_key_exists('statut', $changes)) {
            return match ($panier->statut) {
                Panier::STATUT_ABANDONNE => [
                    'title' => 'Panier abandonné',
                    'message' => "Le panier {$reference} a été marqué comme abandonné.",
                ],
                Panier::STATUT_CONVERTI => [
                    'title' => 'Panier converti',
                    'message' => "Le panier {$reference} a été converti en commande.",
                ],
                default => [
                    'title' => 'Statut du panier mis à jour',
                    'message' => "Le panier {$reference} est maintenant « {$panier->statut} ».",
                ],
            };
        }

        return [
            'title' => 'Panier mis à jour',
            'message' => "Le panier {$reference} a été modifié.",
        ];
    }

    /**
     * @param  array<string, mixed>  $changes
     * @return array<string, string>
     */
    protected function buildItemPanierPayload(ItemPanier $item, string $action, array $changes): array
    {
        $item->loadMissing(['panier.client', 'produit']);

        $produit = $item->produit?->nom ?? 'Produit';
        $quantite = (int) ($item->quantite ?? 0);
        $client = $this->formatCartClient($item->panier);

        if ($action === 'created') {
            return [
                'title' => 'Article ajouté au panier',
                'message' => "{$client} a ajouté {$quantite} x « {$produit} » au panier.",
            ];
        }

        if ($action === 'deleted') {
            return [
                'title' => 'Article retiré du panier',
                'message' => "{$client} a retiré « {$produit} » du panier.",
            ];
        }

        if (array_key_exists('quantite', $changes)) {
            return [
                'title' => 'Quantité du panier mise à jour',
                'message' => "{$client} a maintenant {$quantite} x « {$produit} » dans le panier.",
            ];
        }

        return [
            'title' => 'Panier mis à jour',
            'message' => "{$client} a modifié « {$produit} » dans le panier.",
        ];
    }

    /**
     * @param  array<string, mixed>  $changes
     * @return array<string, string>
     */
    protected function buildRetourPayload(Retour $retour, string $action, array $changes): array
    {
        $reference = $retour->reference ?: '#'.$retour->id;

        if ($action === 'created') {
            return [
                'title' => 'Nouveau retour client',
                'message' => "Le retour {$reference} a été créé.",
            ];
        }

        if ($action === 'deleted') {
            return [
                'title' => 'Retour supprimé',
                'message' => "Le retour {$reference} a été supprimé.",
            ];
        }

        if (array_key_exists('statut', $changes)) {
            return [
                'title' => 'Statut de retour mis à jour',
                'message' => "Le retour {$reference} est maintenant « {$retour->statut} ».",
            ];
        }

        return [
            'title' => 'Retour mis à jour',
            'message' => "Le retour {$reference} a été modifié.",
        ];
    }

    /**
     * @return array<string, string>
     */
    protected function buildMouvementStockPayload(MouvementStock $mouvementStock): array
    {
        $quantite = (int) $mouvementStock->quantite;
        $prefix = $quantite > 0 ? '+' : '';
        $produit = $mouvementStock->produit?->nom ?? 'Produit';

        return [
            'title' => 'Mouvement de stock',
            'message' => "Mouvement {$mouvementStock->type}: {$prefix}{$quantite} unité(s) sur « {$produit} ».",
        ];
    }

    protected function isLowStock(Produit $produit): bool
    {
        if ($produit->quantite_stock === null) {
            return false;
        }

        if (! isset($produit->seuil_alerte) || $produit->seuil_alerte === null) {
            return $produit->quantite_stock <= 0;
        }

        return $produit->quantite_stock <= $produit->seuil_alerte;
    }

    /**
     * @return array<string, string>|null
     */
    protected function resolveActor(): ?array
    {
        $actor = Auth::user();

        if (! $actor) {
            return null;
        }

        return [
            'id' => (string) $actor->id,
            'name' => (string) $actor->name,
        ];
    }

    protected function resolveUrl(Model $model): ?string
    {
        return match (true) {
            $model instanceof Commande,
            $model instanceof Retour => $this->firstAvailableRoute([
                'tenant.vendor.orders.index',
                'vendor.orders.index',
                'vendor.dashboard',
            ]),
            $model instanceof Paiement => $this->firstAvailableRoute([
                'tenant.vendor.payments.index',
                'vendor.payments.index',
                'vendor.dashboard',
            ]),
            $model instanceof Produit,
            $model instanceof Promotion,
            $model instanceof MouvementStock => $this->firstAvailableRoute([
                'dashboard.products.index',
                'vendor.dashboard',
            ]),
            $model instanceof ItemPanier,
            $model instanceof Panier => $this->firstAvailableRoute([
                'filament.vendeur.paniers.resources.paniers.index',
                'vendor.dashboard',
            ]),
            default => $this->firstAvailableRoute(['vendor.dashboard']),
        };
    }

    protected function formatCartClient(?Panier $panier): string
    {
        if (! $panier) {
            return 'Un visiteur';
        }

        $client = $panier->client;

        if (! $client) {
            return 'Un visiteur';
        }

        $name = trim(($client->prenom ? "{$client->prenom} " : '').($client->nom ?: ''));

        return $name !== '' ? "Le client {$name}" : 'Un client';
    }

    /**
     * @param  array<int, string>  $routeNames
     */
    protected function firstAvailableRoute(array $routeNames): ?string
    {
        foreach ($routeNames as $routeName) {
            if (Route::has($routeName)) {
                return route($routeName);
            }
        }

        return null;
    }

    protected function formatAmount(float|int|string|null $amount): string
    {
        return number_format((float) ($amount ?? 0), 2, ',', ' ');
    }
}
