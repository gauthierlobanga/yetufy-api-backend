<?php

namespace App\Http\Controllers\Api\Shop;

use App\Http\Controllers\Controller;
use App\Models\Client;
use App\Models\Coupon;
use App\Models\ItemPanier;
use App\Models\Panier;
use App\Models\Produit;
use App\Models\VarianteProduit;
use App\Models\VisitorEvent;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Auth as FacadesAuth;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Storage;


class CartController extends Controller
{
    public function cartIndex(Request $request)
    {
        $cart = $this->getOrCreateCart($request);

        return response()->json([
            'cart' => $this->formatCart($cart),
        ]);
    }

    public function cartAdd(Request $request, Produit $produit)
    {
        $validated = $request->validate([
            'quantite' => 'integer|min:1',
            'variante_id' => 'nullable|exists:variante_produits,id',
        ]);

        $cart = $this->getOrCreateCart($request);
        $variante = isset($validated['variante_id']) ? VarianteProduit::find($validated['variante_id']) : null;

        $cart->ajouterItem($produit, $validated['quantite'] ?? 1, $variante);

        VisitorEvent::create([
            'session_id' => Session::getId(),
            'visitor_id' => $request->cookie('y_visitor'),
            'event_type' => 'add_to_cart',
            'product_id' => $produit->id,
            'occurred_at' => now(),
        ]);

        if ($request->wantsJson()) {
            return response()->json(['success' => true, 'cart' => $this->formatCart($cart)]);
        }

        return redirect()->route('tenant.cart.index')->with('success', 'Produit ajouté au panier');
    }

    public function cartUpdate(Request $request, ItemPanier $item)
    {
        $request->validate(['quantite' => 'required|integer|min:1']);

        $item->quantite = $request->quantite;
        $item->prix_total = $item->prix_unitaire * $item->quantite;
        $item->save();

        $item->panier->recalculerTotaux();

        if ($request->wantsJson()) {
            return response()->json([
                'cart' => $this->formatCart($item->panier),
            ]);
        }

        return back()->with('success', 'Panier mis à jour');
    }

    public function cartRemove(ItemPanier $item)
    {
        $item->delete();
        $item->panier->recalculerTotaux();

        return back()->with('success', 'Article retiré du panier');
    }

    public function cartClear(Request $request)
    {
        $cart = $this->getOrCreateCart($request);
        $cart->vider();

        return back()->with('success', 'Panier vidé');
    }

    public function cartApplyCoupon(Request $request)
    {
        $request->validate(['code' => 'required|string']);
        $cart = $this->getOrCreateCart($request);
        $coupon = Coupon::where('code', $request->code)->actif()->first();

        if (! $coupon || ! $coupon->est_valide) {
            return back()->withErrors(['code' => 'Code promo invalide ou expiré']);
        }

        $reduction = $coupon->calculerReduction($cart->sous_total);
        if ($reduction <= 0) {
            return back()->withErrors(['code' => 'Le montant minimum du panier n\'est pas atteint']);
        }

        $cart->promotions()->attach($coupon, [
            'montant_applique' => $reduction,
            'applied_at' => now(),
            'code_saisi' => $request->code,
        ]);

        $cart->total_remises += $reduction;
        $cart->recalculerTotaux();
        $coupon->incrementUtilisation();

        return back()->with('success', 'Code promo appliqué');
    }

    public function cartRemoveCoupon(Request $request)
    {
        $cart = $this->getOrCreateCart($request);
        $cart->promotions()->detach();
        $cart->total_remises = 0;
        $cart->recalculerTotaux();

        return back()->with('success', 'Code promo retiré');
    }

    public function getOrCreateCart(Request $request): Panier
    {
        if (Auth::check()) {
            $user = Auth::user();
            $client = $user->client;
            if (! $client) {
                $client = $user->client()->create([
                    'nom' => $user->name ?? 'Client',
                    'prenom' => '',
                    'email' => $user->email,
                    'type' => Client::TYPE_PARTICULIER,
                    'statut' => Client::STATUT_ACTIF,
                ]);
            }
            $cart = Panier::firstOrCreate(
                ['client_id' => $client->id, 'statut' => Panier::STATUT_ACTIF],
                ['date_creation' => now(), 'expires_at' => now()->addDays(7)]
            );
        } else {
            $sessionId = $request->session()->getId();
            $cart = Panier::firstOrCreate(
                ['session_id' => $sessionId, 'statut' => Panier::STATUT_ACTIF],
                ['date_creation' => now(), 'expires_at' => now()->addDays(7)]
            );
        }

        return $cart;
    }

    public function formatCart(Panier $cart): array
    {
        $cart->load(['items', 'promotions']);

        return [
            'id' => $cart->id,
            'nb_articles' => $cart->nb_articles,
            'sous_total' => $cart->sous_total,
            'total_taxes' => $cart->total_taxes,
            'total_livraison' => $cart->total_livraison,
            'total_remises' => $cart->total_remises,
            'total_general' => $cart->total_general,
            'items' => $cart->items->map(fn ($item) => [
                'id' => $item->id,
                'produit' => [
                    'id' => $item->produit->id,
                    'nom' => $item->nom_produit,
                    'slug' => $item->produit->slug,
                    'image' => $item->produit->getImageUrl('small')
                        ?: Storage::url('images/Vue-Storefront.png'),
                ],
                'quantite' => (int) $item->quantite,
                'prix_unitaire' => (float) $item->prix_unitaire,
                'prix_total' => (float) $item->prix_total,
            ])->values(),
            'promotions' => $cart->promotions->map(fn ($p) => [
                'code' => $p->code,
                'montant' => (float) $p->pivot->montant_applique,
            ])->values(),
        ];
    }

    public function getCart(Request $request): Panier
    {
        if (FacadesAuth::check()) {
            $client = FacadesAuth::user()->client;
            if (! $client) {
                $client = FacadesAuth::user()->client()->create();
            }
            $cart = Panier::firstOrCreate(
                ['client_id' => $client->id, 'statut' => Panier::STATUT_ACTIF],
                ['date_creation' => now(), 'expires_at' => now()->addDays(7)]
            );
        } else {
            $sessionId = $request->session()->getId();
            $cart = Panier::firstOrCreate(
                ['session_id' => $sessionId, 'statut' => Panier::STATUT_ACTIF],
                ['date_creation' => now(), 'expires_at' => now()->addDays(7)]
            );
        }

        return $cart;
    }

    public function cartCalculate(Request $request)
    {
        $request->validate([
            'item_ids' => 'sometimes|array',
            'item_ids.*' => 'string',
        ]);

        $cart = $this->getOrCreateCart($request);
        $selectedIds = $request->input('item_ids', []);

        $validIds = $cart->items()
            ->whereIn('id', $selectedIds)
            ->pluck('id')
            ->toArray();

        if (empty($validIds)) {
            return response()->json([
                'calculatedTotals' => [
                    'sous_total' => 0,
                    'total_taxes' => 0,
                    'total_livraison' => 0,
                    'total_remises' => 0,
                    'total_general' => 0,
                    'selected_count' => 0,
                ],
            ]);
        }

        $selectedItems = $cart->items()->whereIn('id', $validIds)->get();

        $sousTotal = $selectedItems->sum('prix_total');
        $totalTaxes = $selectedItems->sum(fn ($item) => ($item->taxe_unitaire ?? 0) * $item->quantite);

        return response()->json([
            'calculatedTotals' => [
                'sous_total' => round($sousTotal, 2),
                'total_taxes' => round($totalTaxes, 2),
                'total_livraison' => (float) $cart->total_livraison,
                'total_remises' => (float) $cart->total_remises,
                'total_general' => round($sousTotal + $totalTaxes + $cart->total_livraison - $cart->total_remises, 2),
                'selected_count' => $selectedItems->sum('quantite'),
            ],
        ]);
    }
}
