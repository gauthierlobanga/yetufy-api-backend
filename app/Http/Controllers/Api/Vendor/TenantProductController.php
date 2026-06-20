<?php

namespace App\Http\Controllers\Api\Vendor;

use App\Http\Controllers\Controller;
use App\Models\ProductCategory;
use App\Models\Produit;
use App\Services\TenantPropsService;

class TenantProductController extends Controller
{
    public function index(TenantPropsService $tenantProps)
    {
        $tenant = tenant();

        $recentProducts = Produit::with('media')
            ->latest()
            ->take(18)
            ->get()
            ->map(function ($product) use ($tenant) {
                return [
                    'id' => $product->id,
                    'nom' => $product->nom,
                    'slug' => $product->slug,
                    'prix' => $product->prix_actuel,
                    'stock' => $product->quantite_stock,
                    'statut' => $product->statut,
                    'image' => $product->getFirstMediaUrl('image_principale')
                               ?: $product->getFirstMediaUrl('images')
                               ?: '/storage/images/placeholder-product.jpg',
                    'edit_url' => $tenant->admin_url.'/products/produits/'.$product->id.'/edit',
                ];
            });

        // Récupérer les 24 catégories les plus récentes ou toutes (actives)
        $categories = ProductCategory::active() // scopeActive défini dans votre modèle
            ->orderBy('order')
            ->take(18)
            ->get()
            ->map(function ($cat) use ($tenant) {
                return [
                    'id' => $cat->id,
                    'nom' => $cat->nom,
                    'slug' => $cat->slug,
                    'description' => $cat->description,
                    'color' => $cat->color ?? '#059669',
                    'image' => $cat->getFirstMediaUrl('icon')
                               ?: $cat->getFirstMediaUrl('image')
                               ?: '/storage/images/Vue-Storefront.png',
                    'products_count' => $cat->getProductsCountAttribute(), // accesseur existant
                    'url' => $tenant->admin_url.'/products/product-categories/'.$cat->id.'/edit',
                ];
            });

        return response()->json([
            'tenant' => $tenantProps->getTenantProps($tenant),
            'recentProducts' => $recentProducts,
            'categories' => $categories,
        ]);
    }
}
