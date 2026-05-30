<?php

namespace App\Http\Controllers\Api\Shop;

use App\Events\WishlistActivity;
use App\Http\Controllers\Controller;
use App\Models\Client;
use App\Models\Produit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;


class WishlistController extends Controller
{
    public function wishlistIndex()
    {
        $client = Auth::user()->client;
        $wishlist = $client->wishlists()->firstOrCreate(['nom' => 'Ma liste'], ['est_publique' => false]);
        $items = $wishlist->items()->with('produit')->get();

        return response()->json([
            'wishlist' => $wishlist,
            'items' => $items->map(fn ($i) => [
                'id' => $i->id,
                'produit' => app(ProductController::class)->formatProduct($i->produit),
                'quantite' => $i->quantite,
                'note' => $i->note,
            ]),
        ]);
    }

    public function wishlistAdd()
    {
        $client = Auth::user()->client;
        $wishlist = $client->wishlists()->firstOrCreate(['nom' => 'Ma liste'], ['est_publique' => false]);
        $items = $wishlist->items()->with('produit')->get();

        return response()->json([
            'wishlist' => $wishlist,
            'items' => $items->map(fn ($i) => [
                'id' => $i->id,
                'produit' => app(ProductController::class)->formatProduct($i->produit),
                'quantite' => $i->quantite,
                'note' => $i->note,
            ]),
        ]);
    }

    // public function wishlistToggle(Request $request, Produit $produit)
    // {
    //     $client = Auth::user()->client;
    //     if (! $client) {
    //         $client = Auth::user()->client()->create([
    //             'nom' => Auth::user()->name ?? 'Client',
    //             'prenom' => '',
    //             'email' => Auth::user()->email,
    //             'type' => Client::TYPE_PARTICULIER,
    //             'statut' => Client::STATUT_ACTIF,
    //         ]);
    //     }

    //     $wishlist = $client->wishlists()->firstOrCreate(['nom' => 'Ma liste']);

    //     if ($wishlist->items()->where('produit_id', $produit->id)->exists()) {
    //         $wishlist->removeProduct($produit);
    //         $message = 'Produit retiré de la wishlist';
    //         $type = 'wishlist_remove';
    //     } else {
    //         $wishlist->addProduct($produit);
    //         $message = 'Produit ajouté à la wishlist';
    //         $type = 'wishlist_add';
    //     }

    //     // Notification au tenant en temps réel
    //     $tenant = tenant(); // fonction stancl/tenancy
    //     if ($tenant) {
    //         event(new WishlistActivity(
    //             $tenant->id,
    //             'Activité wishlist',
    //             "Un client a {$message} : {$produit->nom}",
    //             $type
    //         ));
    //     }

    //     return response()->json(['success' => true, 'message' => $message]);
    // }

    public function wishlistToggle(Request $request, Produit $produit)
    {
        $client = Auth::user()->client;
        if (! $client) {
            $client = Auth::user()->client()->create([
                'nom' => Auth::user()->name ?? 'Client',
                'prenom' => '',
                'email' => Auth::user()->email,
                'type' => Client::TYPE_PARTICULIER,
                'statut' => Client::STATUT_ACTIF,
            ]);
        }

        $wishlist = $client->wishlists()->firstOrCreate(['nom' => 'Ma liste']);

        if ($wishlist->items()->where('produit_id', $produit->id)->exists()) {
            $wishlist->removeProduct($produit);
            $message = 'Produit retiré de la wishlist';
            $type = 'wishlist_remove';
        } else {
            $wishlist->addProduct($produit);
            $message = 'Produit ajouté à la wishlist';
            $type = 'wishlist_add';
        }

        // Notification au tenant en temps réel
        $tenant = tenant();
        if ($tenant) {
            event(new WishlistActivity(
                $tenant->id,
                'Activité wishlist',
                "Un client a {$message} : {$produit->nom}",
                $type
            ));
        }

        return response()->json(['success' => true, 'message' => $message]);
    }

    public function wishlistRemove(Produit $produit)
    {
        $client = Auth::user()->client;
        $wishlist = $client->wishlists()->first();
        if ($wishlist) {
            $wishlist->removeProduct($produit);
        }

        return response()->json(['message' => 'Produit retiré']);
    }
}
