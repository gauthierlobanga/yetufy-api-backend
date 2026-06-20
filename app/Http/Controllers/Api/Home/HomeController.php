<?php

namespace App\Http\Controllers\Api\Home;

use App\Http\Controllers\Controller;
use App\Models\Brand;
use App\Models\ProductCategory;
use App\Models\Produit;
use App\Models\Promotion;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;

class HomeController extends Controller
{
    public function homeIndex(Request $request)
    {
        $productsCount = Produit::published()->count();
        $recentProducts = Produit::published()->latest()->take(4)->get();

        $hasProductCategories = Schema::hasTable('produit_categories');
        $canLoadProductCategories = $hasProductCategories && Schema::hasTable('produit_categorie_pivot');
        $hasProducts = Schema::hasTable('produits');
        $hasBrands = Schema::hasTable('brands');
        $hasPromotions = Schema::hasTable('promotions');
        $productRelations = ['media', 'brand'];

        if ($canLoadProductCategories) {
            $productRelations[] = 'categories';
        }

        $baseProductQuery = $hasProducts
            ? Produit::published()->inStock()->with($productRelations)
            : null;

        $categoriesModels = $hasProductCategories
            ? ProductCategory::active()
                ->inMenu()
                ->parents()
                ->ordered()
                ->with(['media', 'children'])
                ->get()
            : collect();

        // Formatage pour le frontend
        $categories = $categoriesModels->map(fn ($category) => $this->formatCategory($category));

        // Produits mis en avant
        $featuredPage = $request->input('page', 1);
        $featuredProducts = $hasProducts
            ? Produit::published()
                ->inStock()
                ->featured()
                ->with($productRelations)
                ->paginate(12, ['*'], 'featuredPage', $featuredPage)
                ->through(fn ($product) => $this->formatProduct($product))
            : new LengthAwarePaginator([], 0, 12, $featuredPage);

        // Produits tendance
        $trendingProducts = $hasProducts
            ? Produit::published()
                ->inStock()
                ->bestseller()
                ->with($productRelations)
                ->take(4)
                ->get()
                ->map(fn ($product) => $this->formatProduct($product))
            : collect();

        // Produits par catégorie pour les onglets
        $productsByCategory = [];
        foreach ($canLoadProductCategories ? $categoriesModels : collect() as $category) {
            $categoryIds = $category->getAllChildrenIds();
            $products = Produit::published()
                ->inStock()
                ->whereHas('categories', fn ($q) => $q->whereIn('produit_categories.id', $categoryIds))
                ->with(['media', 'brand'])
                ->take(6)
                ->get()
                ->map(fn ($product) => $this->formatProduct($product));

            if ($products->isNotEmpty()) {
                $productsByCategory[$category->slug] = [
                    'category' => $this->formatCategory($category),
                    'products' => $products,
                ];
            }
        }

        // Promotion active dynamique
        $promo = null;
        $activePromotion = $hasPromotions ? Promotion::activePromotion() : null;

        if ($activePromotion && $hasProducts) {
            // Récupère 3 produits best‑sellers pour la section "Les meilleures ventes"
            $promoProducts = Produit::published()
                ->inStock()
                ->bestseller()
                ->take(10)
                ->get()
                ->map(fn ($product) => $this->formatProduct($product));

            $promo = [
                'title' => $activePromotion->nom,
                'description' => $activePromotion->description,
                'end_date' => optional($activePromotion->date_fin)->toIso8601String(),
                'image' => $activePromotion->image_url,
                'discount_percentage' => $activePromotion->type === Promotion::TYPE_POURCENTAGE
                    ? (int) $activePromotion->valeur
                    : null,
                'coupons' => $activePromotion->coupons,
                'featuredProducts' => $promoProducts,
                'is_active' => $activePromotion->is_currently_active,
            ];
        }

        // Meilleures ventes
        $bestSellers = $baseProductQuery
            ? (clone $baseProductQuery)
                ->bestseller()
                ->take(10)
                ->get()
                ->map(fn ($product) => $this->formatProduct($product))
            : collect();

        // Deal du jour
        // $dealOfTheDay = $hasProducts && $hasPromotions
        //     ? Produit::published()
        //         ->inStock()
        //         ->onSale()
        //         ->whereHas('promotions', function ($q) {
        //             $q->where('type', 'pourcentage')
        //                 ->where('est_active', true)
        //                 ->where('valeur', '>=', 30)
        //                 ->where('date_debut', '<=', now())
        //                 ->where(function ($q2) {
        //                     $q2->whereNull('date_fin')->orWhere('date_fin', '>=', now());
        //                 });
        //         })
        //         ->with(['media', 'brand', 'promotions' => function ($q) {
        //             $q->where('est_active', true)->where('type', 'pourcentage');
        //         }])
        //         ->take(10)
        //         ->get()
        //         ->map(function ($product) {
        //             $data = $this->formatProduct($product);
        //             $maxDiscount = $product->promotions->max('valeur');
        //             $data['discount_label'] = $maxDiscount ? "-{$maxDiscount}%" : null;

        //             return $data;
        //         })
        //     : collect();
        $dealOfTheDay = $hasProducts
            ? Produit::dealOfTheDay()
                ->latest('expires_at')
                ->take(10)
                ->get()
                ->map(function ($product) {
                    $data = $this->formatProduct($product);
                    // Le `discount_label` peut être calculé à partir de la réduction
                    $data['discount_label'] = $product->reduction_pourcentage
                        ? "-{$product->reduction_pourcentage}%"
                        : null;
                    $data['is_deal_of_the_day'] = true; // optionnel, pour un badge éventuel

                    return $data;
                })
            : collect();

        $brands = $hasBrands
            ? Brand::where('is_active', true)
                ->with('media')
                ->take(12)
                ->get()
                ->map(fn ($brand) => $this->formatBrand($brand))
            : collect();

        return response()->json([
            'featuredProducts' => $featuredProducts,
            'trendingProducts' => $trendingProducts,
            'categories' => $categories,
            'productsByCategory' => $productsByCategory,
            'promo' => $promo,
            'bestSellers' => $bestSellers,
            'dealOfTheDay' => $dealOfTheDay,
            'brands' => $brands,
            'productsCount' => $productsCount,
            'recentProducts' => $recentProducts,
        ]);
    }

    private function formatProduct(Produit $product, bool $withDetails = false): array
    {
        $primaryImage = $product->getPrimaryImage();

        $data = [
            'id' => $product->id,
            'nom' => $product->nom,
            'slug' => $product->slug,
            'prix_ttc' => (float) $product->prix_ttc,
            'prix_promotion' => $product->prix_promotion ? (float) $product->prix_promotion : null,
            'prix_actuel' => (float) $product->prix_actuel,
            'est_en_promotion' => (bool) $product->est_en_promotion,
            'reduction_pourcentage' => $product->reduction_pourcentage,
            'image_principale' => $primaryImage['medium'] ?? null,
            'image_thumb' => $primaryImage['thumb'] ?? null,
            'note_moyenne' => (float) $product->note_moyenne,
            'nombre_avis' => (int) $product->nombre_avis,
            'badge' => $product->is_new ? 'Nouveauté' : ($product->is_bestseller ? 'Best Seller' : null),
            'brand' => $product->brand ? ['nom' => $product->brand->nom, 'slug' => $product->brand->slug] : null,
            'url' => route('tenant.product.show', $product->slug),
            'sold_count' => (int) $product->sold_count,
        ];

        if ($withDetails) {
            // Description
            $data['description'] = $product->description_longue;
            $data['short_description'] = $product->short_description;

            // Images (galerie complète)
            $data['images'] = $product->images;

            // Catégories
            $data['categories'] = $product->categories->map(fn ($c) => [
                'nom' => $c->nom,
                'slug' => $c->slug,
            ])->values()->toArray();

            // Variantes
            $data['variantes'] = $product->variantes->map(fn ($v) => [
                'id' => $v->id,
                'nom' => $v->nom,
                'valeur' => $v->valeur,
                'supplement_prix' => (float) $v->supplement_prix,
                'stock' => (int) $v->stock,
                'prix_actuel' => (float) $v->prix_actuel,
            ])->values()->toArray();

            // Stock disponible total
            $data['stock_disponible'] = $product->stock_disponible;

            // Avis approuvés
            $avis = $product->approvedAvis()->with('client')->latest()->get();
            $data['avis'] = $avis->map(fn ($a) => [
                'id' => $a->id,
                'note' => (int) $a->note,
                'commentaire' => $a->commentaire,
                'client' => $a->client->full_name ?? 'Client',
                'date' => $a->created_at->diffForHumans(),
                'utile' => $a->votes_utiles ?? 0, // si vous avez ce champ
            ])->values()->toArray();

            // Statistiques des avis (distribution)
            $distribution = [5 => 0, 4 => 0, 3 => 0, 2 => 0, 1 => 0];
            foreach ($avis as $a) {
                $note = (int) $a->note;
                if (isset($distribution[$note])) {
                    $distribution[$note]++;
                }
            }

            $data['rating_stats'] = [
                'average' => $product->note_moyenne,
                'total' => $avis->count(),
                'distribution' => $distribution,
            ];

            // Offres groupées (exemple simple basé sur le prix actuel)
            $prixBase = $product->prix_actuel;
            $data['bulk_discounts'] = [
                ['quantity' => 1, 'discount_percentage' => 0,  'price' => $prixBase],
                ['quantity' => 2, 'discount_percentage' => 10, 'price' => round($prixBase * 2 * 0.9, 2)],
                ['quantity' => 3, 'discount_percentage' => 20, 'price' => round($prixBase * 3 * 0.8, 2)],
            ];
        }

        return $data;
    }

    private function formatCategory(ProductCategory $category): array
    {
        return [
            'id' => $category->id,
            'nom' => $category->nom,
            'slug' => $category->slug,
            'description' => $category->short_description,
            'image' => $category->image_url,
            'icon' => $category->icon_url,
            'url' => route('tenant.product.category.show', $category->slug),
            'children' => $category->children->map(fn ($child) => $this->formatCategory($child)),
        ];
    }

    private function formatBrand(Brand $brand): array
    {
        $logo = $brand->getFirstMediaUrl('logo') ?: Storage::url('images/');

        return [
            'id' => $brand->id,
            'nom' => $brand->nom,
            'slug' => $brand->slug,
            'logo' => $logo,
            'url' => route('tenant.brands.show', $brand->slug),
        ];
    }
}
