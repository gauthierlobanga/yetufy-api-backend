<?php

namespace App\Http\Controllers\Api\Shop;

use App\Http\Controllers\Controller;
use App\Models\Commande;
use App\Models\VisitorEvent;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Session;

class CheckoutController extends Controller
{
    protected CartController $cartController;

    public function __construct(CartController $cartController)
    {
        $this->cartController = $cartController;
    }

    /**
     * Page de checkout enrichie avec les options de livraison et de paiement.
     */
    public function checkoutIndex(Request $request)
    {
        $cart = $this->cartController->getCart($request);

        if ($cart->est_vide) {
            return redirect()->route('tenant.cart.index')
                ->with('error', 'Votre panier est vide.');
        }

        $addresses = Auth::user()?->adresses()->get() ?? collect();

        // Méthodes de livraison (peuvent être dynamiques depuis la base / config)
        $shippingMethods = [
            [
                'id' => 'standard',
                'name' => 'Standard',
                'description' => 'Livraison à domicile sous 5-7 jours ouvrés',
                'price' => 0,
                'estimatedDays' => '5-7 jours',
            ],
            [
                'id' => 'express',
                'name' => 'Express',
                'description' => 'Livraison prioritaire sous 24-48h',
                'price' => 15000,
                'estimatedDays' => '1-2 jours',
            ],
        ];

        // Méthodes de paiement
        $paymentMethods = [
            [
                'id' => 'mobile_money',
                'name' => 'Mobile Money',
                'description' => 'M-Pesa, Airtel Money, Orange Money',
            ],
            [
                'id' => 'card',
                'name' => 'Carte bancaire',
                'description' => 'Visa, Mastercard',
            ],
            [
                'id' => 'cash',
                'name' => 'Paiement à la livraison',
            ],
        ];

        // Enregistrement de l'événement début du checkout
        VisitorEvent::create([
            'session_id' => Session::getId(),
            'visitor_id' => request()->cookie('y_visitor'),
            'event_type' => 'begin_checkout',
            'occurred_at' => now(),
        ]);

        return response()->json([
            'cart' => $this->cartController->formatCart($cart),
            'addresses' => $addresses,
            'shippingMethods' => $shippingMethods,
            'paymentMethods' => $paymentMethods,
        ]);
    }

    /**
     * Traitement final de la commande.
     */
    public function checkoutProcess(Request $request)
    {
        $validated = $request->validate([
            'adresse_facturation_id' => 'required|exists:adresses,id',
            'adresse_livraison_id' => 'required|exists:adresses,id',
            'payment_method_id' => 'required|string',
            'shipping_method_id' => 'sometimes|string',
            'notes' => 'nullable|string|max:1000',
        ]);

        $cart = $this->cartController->getCart($request);

        // Vérification des stocks
        foreach ($cart->items as $item) {
            if (! $item->produit->hasSufficientStock($item->quantite)) {
                return back()->withErrors([
                    'stock' => "Stock insuffisant pour {$item->produit->nom}",
                ]);
            }
            if ($item->variante && ! $item->variante->hasSufficientStock($item->quantite)) {
                return back()->withErrors([
                    'stock' => "Stock insuffisant pour la variante {$item->variante->valeur} de {$item->produit->nom}",
                ]);
            }
        }

        try {
            $commande = $cart->convertirEnCommande();
            $commande->update([
                'adresse_facturation_id' => $validated['adresse_facturation_id'],
                'adresse_livraison_id' => $validated['adresse_livraison_id'],
                'mode_paiement' => $validated['payment_method_id'],
                'shipping_method_id' => $validated['shipping_method_id'] ?? null,
                'notes' => $validated['notes'] ?? null,
            ]);

            // Décrémenter les stocks
            foreach ($commande->lignes as $ligne) {
                $ligne->produit->decrementerStock($ligne->quantite);
                if ($ligne->variante) {
                    $ligne->variante->decrementerStock($ligne->quantite);
                }
            }

            return redirect()->route('tenant.payment.pay', $commande);
        } catch (\Exception $e) {
            Log::error('Checkout error', [
                'message' => $e->getMessage(),
                'user' => Auth::id(),
                'cart' => $cart->id,
            ]);

            return back()->withErrors([
                'checkout' => 'Une erreur inattendue est survenue. Veuillez réessayer.',
            ]);
        }
    }

    /**
     * Page de succès après paiement.
     */
    public function checkoutSuccess(Commande $commande)
    {
        if ($commande->client_id !== optional(Auth::user()->client)->id) {
            abort(403);
        }

        // Enregistrement de l'événement d'achat réussi (purchase)
        VisitorEvent::create([
            'session_id' => Session::getId(),
            'visitor_id' => request()->cookie('y_visitor'),
            'event_type' => 'purchase',
            'order_id' => $commande->id,
            'value' => $commande->total,
            'occurred_at' => now(),
        ]);

        return response()->json(['commande' => $commande->load('lignes.produit')]);
    }

    /**
     * Page d'annulation du paiement.
     */
    public function checkoutCancel()
    {
        return response()->json(['message' => 'Le paiement a été annulé. Vous pouvez réessayer quand vous le souhaitez.']);
    }
}
