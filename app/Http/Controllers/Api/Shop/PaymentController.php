<?php

namespace App\Http\Controllers\Api\Shop;

use App\Http\Controllers\Controller;
use App\Models\Commande;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\Request;


class PaymentController extends Controller
{
    public function paymentPay(Commande $commande)
    {
        /** @var AuthorizesRequests $this */
        $this->authorize('pay', $commande);

        // Simuler une intégration Stripe
        return response()->json([
            'commande' => $commande,
            'clientSecret' => 'pi_dummy_secret',
        ]);
    }

    public function PaymentCallback(Request $request)
    {
        /** @var Commande $commande */
        $commande = Commande::find($request->input('commande_id'));
        if ($commande) {
            $commande->marquerPayee();

            return response()->json(['message' => 'Paiement réussi']);
        }

        return response()->json(['error' => 'Paiement échoué'], 400);
    }
}
