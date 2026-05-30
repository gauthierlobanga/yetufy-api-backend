<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Post;
use App\Models\PostCategory;
use App\Models\ProductCategory;
use App\Models\Produit;
use App\Models\User;
use Illuminate\Http\Request;

class SearchController extends Controller
{
    /**
     * Page de résultats de recherche (Inertia)
     */
    public function shopSearch(Request $request)
    {
        $query = $request->input('q', '');
        $limit = $request->input('limit', 12);

        $results = $this->performSearch($query, $limit);

        return response()->json([
            'results' => $results,
            'query' => $query,
        ]);
    }

    /**
     * API pour la recherche instantanée (JSON)
     */
    public function shopApi(Request $request)
    {
        $query = $request->input('q', '');
        $limit = $request->input('limit', 5); // faible pour les suggestions

        $results = $this->performSearch($query, $limit);

        return response()->json(['results' => $results]);
    }

    /**
     * Logique de recherche commune (pour la page et l'API)
     */
    private function performSearch(string $query, int $limit): array
    {
        $all = [];

        if (strlen($query) >= 2) {
            // 1. Produits
            $produits = Produit::where('statut', Produit::STATUS_PUBLISHED)
                ->where(function ($q) use ($query) {
                    $q->where('nom', 'like', "%{$query}%")
                        ->orWhere('description_longue', 'like', "%{$query}%")
                        ->orWhere('short_description', 'like', "%{$query}%");
                })
                ->limit($limit)
                ->get()
                ->map(fn ($p) => [
                    'id' => $p->id,
                    'title' => $p->nom,
                    'slug' => $p->slug,
                    'image' => $p->getImageUrl('thumb') ?? '/storage/images/Vue-Storefront.png',
                    'url' => route('tenant.product.show', $p->slug),
                    'price' => number_format($p->prix_actuel, 2, ',', ' ').' €',
                    'type' => 'product',
                ]);

            // 2. Articles
            $posts = Post::with('user', 'categories', 'media')
                ->where('status', 'published')
                ->where(function ($q) use ($query) {
                    $q->where('title', 'LIKE', "%{$query}%")
                        ->orWhere('content', 'LIKE', "%{$query}%")
                        ->orWhere('excerpt', 'LIKE', "%{$query}%");
                })
                ->limit($limit)
                ->get()
                ->map(fn ($post) => [
                    'id' => $post->id,
                    'title' => $post->title,
                    'slug' => $post->slug,
                    'image' => $post->featured_image_thumb,
                    'url' => route('tenant.blog.show', $post->slug),
                    'description' => $post->excerpt,
                    'type' => 'post',
                ]);

            // 3. Catégories de produits (pour le shop)
            $productCategories = ProductCategory::where('est_active', true)
                ->where('nom', 'LIKE', "%{$query}%")
                ->limit($limit)
                ->get()
                ->map(fn ($cat) => [
                    'id' => $cat->id,
                    'title' => $cat->nom,
                    'slug' => $cat->slug,
                    'url' => route('tenant.product.category.show', $cat->slug),
                    'image' => $cat->getFirstMediaUrl('icon'),
                    'type' => 'product_category',
                ]);

            // 4. Catégories de blog
            $blogCategories = PostCategory::where('est_active', true)
                ->where('nom', 'LIKE', "%{$query}%")
                ->limit(2)
                ->get()
                ->map(fn ($cat) => [
                    'id' => $cat->id,
                    'title' => $cat->nom,
                    'slug' => $cat->slug,
                    'url' => route('tenant.blog.category', $cat->slug),
                    'type' => 'blog_category',
                ]);

            // 5. Utilisateurs (facultatif)
            $users = User::where('name', 'LIKE', "%{$query}%")
                ->limit(2)
                ->get()
                ->map(fn ($user) => [
                    'id' => $user->id,
                    'title' => $user->name,
                    'image' => $user->avatar_url,
                    'url' => route('profile.show', $user->id) ?? '#',
                    'type' => 'user',
                ]);

            // Fusion et tri par pertinence (ordre souhaité)
            $all = collect()
                ->merge($produits)
                ->merge($productCategories)
                ->merge($posts)
                ->merge($blogCategories)
                ->merge($users)
                ->take($limit)
                ->toArray();
        }

        return $all;
    }
}
