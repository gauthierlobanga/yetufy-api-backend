<?php

namespace App\Http\Controllers\Api\Shop;

use App\Http\Controllers\Controller;
use App\Models\AvisClient;
use App\Models\Produit;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ReviewController extends Controller
{
    public function productsReviewsIndex(Produit $produit)
    {
        $reviews = $produit->approvedAvis()->with('client')->latest()->paginate(10);

        return response()->json([
            'product' => app(ProductController::class)->formatProduct($produit),
            'reviews' => $reviews,
        ]);
    }

    public function productsReviewsStore(Request $request, Produit $produit)
    {
        $validated = $request->validate([
            'note' => 'required|integer|min:1|max:5',
            'commentaire' => 'required|string|min:10',
        ]);

        $client = Auth::user()->client;
        $avis = $client->avis()->create([
            'produit_id' => $produit->id,
            'note' => $validated['note'],
            'commentaire' => $validated['commentaire'],
            'approuve' => false, // Modération
        ]);

        return response()->json(['message' => 'Votre avis a été soumis et sera publié après modération']);
    }

    public function productsReviewsUpdate(Request $request, AvisClient $avis)
    {
        /** @var AuthorizesRequests $this */
        $this->authorize('update', $avis);
        $validated = $request->validate([
            'note' => 'required|integer|min:1|max:5',
            'commentaire' => 'required|string|min:10',
        ]);

        $avis->update($validated);
        $avis->approuve = false;
        $avis->save();

        return response()->json(['message' => 'Avis mis à jour']);
    }

    public function productsReviewsDestroy(AvisClient $avis)
    {
        /** @var AuthorizesRequests $this */
        $this->authorize('delete', $avis);
        $avis->delete($avis);

        return response()->json(['message' => 'Avis supprimé']);
    }
}
