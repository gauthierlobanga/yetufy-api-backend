<?php

namespace App\Observers;

use App\Models\Client;
use App\Models\Commande;
use App\Models\ItemPanier;
use App\Models\Paiement;
use App\Models\Panier;
use App\Models\Produit;
use App\Models\Promotion;
use App\Models\Retour;
use App\Services\TenantRealtimeActivityService;
use Illuminate\Database\Eloquent\Model;

class TenantRealtimeActivityObserver
{
    /**
     * @var array<class-string<Model>, array<int, string>>
     */
    private const WATCHED_UPDATE_FIELDS = [
        Commande::class => ['statut', 'total', 'date_paiement', 'date_expedition', 'date_livraison'],
        Paiement::class => ['statut', 'montant', 'mode', 'transaction_id'],
        Produit::class => ['nom', 'statut', 'quantite_stock', 'prix_ttc', 'prix_promotion', 'is_featured'],
        Promotion::class => ['nom', 'code', 'est_active', 'valeur', 'date_debut', 'date_fin'],
        Client::class => ['nom', 'prenom', 'email', 'telephone', 'statut', 'points_fidelite'],
        Panier::class => ['statut'],
        ItemPanier::class => ['quantite', 'prix_total'],
        Retour::class => ['statut', 'action', 'date_traitement'],
    ];

    public function __construct(
        private readonly TenantRealtimeActivityService $activityService
    ) {}

    public function created(Model $model): void
    {
        $this->activityService->notify($model, 'created');
    }

    public function updated(Model $model): void
    {
        $changes = $this->extractRelevantChanges($model);

        if ($changes === []) {
            return;
        }

        $this->activityService->notify($model, 'updated', $changes);
    }

    public function deleted(Model $model): void
    {
        $this->activityService->notify($model, 'deleted');
    }

    /**
     * @return array<string, mixed>
     */
    protected function extractRelevantChanges(Model $model): array
    {
        $changes = $model->getChanges();

        unset($changes['updated_at']);

        if ($changes === []) {
            return [];
        }

        $watchedFields = self::WATCHED_UPDATE_FIELDS[$model::class] ?? [];

        if ($watchedFields === []) {
            return $changes;
        }

        return array_intersect_key($changes, array_flip($watchedFields));
    }
}
