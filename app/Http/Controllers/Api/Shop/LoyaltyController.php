<?php

namespace App\Http\Controllers\Api\Shop;

use App\Http\Controllers\Controller;
use App\Models\ProgrammeFidelite;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;


class LoyaltyController extends Controller
{
    public function loyaltyIndex()
    {
        $client = Auth::user()->client;
        abort_unless($client, 404);

        // Récupère le premier programme de fidélité (sans filtre sur est_actif)
        $programmeDefaut = ProgrammeFidelite::first();

        // Si aucun programme n'existe, on en crée un par défaut
        if (! $programmeDefaut) {
            $programmeDefaut = ProgrammeFidelite::create([
                'nom' => 'Programme standard',
                'type' => ProgrammeFidelite::TYPE_POINTS,
                'regles' => [
                    'seuils' => [
                        'bronze' => 0,
                        'argent' => 500,
                        'or' => 2000,
                        'platine' => 5000,
                        'diamant' => 10000,
                    ],
                    'gain' => [
                        'type' => 'montant',
                        'valeur' => 1,    // 1 € = 1 point
                        'points' => 1,
                    ],
                    'taux_conversion' => 100, // 100 points = 1 €
                ],
                'recompenses' => [],
                'date_debut' => null,
                'date_fin' => null,
            ]);
        }

        // Créer le compte fidélité du client s'il n'existe pas déjà
        $compte = $client->compteFidelite ?? $client->compteFidelite()->create([
            'programme_fidelite_id' => $programmeDefaut->id,
            'points' => 0,
            'points_cumules' => 0,
            'niveau' => 'bronze',
        ]);

        return response()->json(['compte' => $compte->load('transactions')]);
    }

    public function loyaltyRedeem(Request $request)
    {
        $client = Auth::user()->client;
        $compte = $client->compteFidelite;

        if (! $compte) {
            return response()->json(['error' => 'Aucun compte fidélité'], 404);
        }

        $points = (int) $request->input('points', 0);

        if ($points <= 0) {
            return response()->json(['error' => 'Montant de points invalide'], 400);
        }

        if ($compte->utiliserPoints($points, 'Échange de points')) {
            return back()->with('success', "$points points échangés.");
        }

        return back()->with('error', 'Points insuffisants.');
    }
}
