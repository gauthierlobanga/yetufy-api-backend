<?php

namespace App\Http\Controllers\Api\Shop;

use App\Http\Controllers\Controller;
use App\Models\Brand;
use App\Models\ProductCategory;
use App\Models\ProductView;
use App\Models\Produit;
use App\Support\Search\ProductIntelligentSearch;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Storage;


class ProductController extends Controller
{
    public function __construct(
        protected ProductIntelligentSearch $intelligentSearch,
    ) {}

    public function searchByImage(Request $request)
    {
        $request->validate([
            'image' => 'required|image|max:5120',
        ]);

        $image = $request->file('image');
        $path = $image->store('temp/search', 'public');
        $fullPath = Storage::disk('public')->path($path);

        try {
            $result = $this->intelligentSearch->searchByImage(
                $this->buildProductQuery(),
                $fullPath,
                48,
            );

            Storage::disk('public')->delete($path);

            if (blank($result['query'])) {
                $message = 'Aucun produit exploitable n’a pu être détecté dans cette image.';

                return $request->expectsJson()
                    ? response()->json(['error' => $message], 422)
                    : back()->with('error', $message);
            }

            $redirectUrl = route('tenant.product.index', array_filter([
                'search' => $result['query'],
                'image_search' => 1,
            ]));

            if ($request->expectsJson() || $request->ajax()) {
                return response()->json([
                    'redirect_url' => $redirectUrl,
                    'query' => $result['query'],
                    'analysis' => $result['analysis'],
                ]);
            }

            return redirect()->to($redirectUrl)->with('image_search_analysis', $result['analysis']);
        } catch (\Throwable $e) {
            Storage::disk('public')->delete($path);

            return back()->with('error', 'Impossible de traiter l\'image. Veuillez réessayer.');
        }
    }

    public function productsIndex(Request $request)
    {
        $query = $this->buildProductQuery();

        if ($request->filled('category')) {
            $query->byCategorySlug($request->category);
        }
        if ($request->filled('brand')) {
            $query->byBrand($request->brand);
        }
        if ($request->filled('min_price') && $request->filled('max_price')) {
            $query->priceRange($request->min_price, $request->max_price);
        }

        $semanticSearchApplied = false;

        if ($request->filled('search')) {
            $searchResults = $this->intelligentSearch->search($query, (string) $request->search);

            if (! empty($searchResults['ids'])) {
                $semanticSearchApplied = true;
                $orderedIds = $searchResults['ids'];
                $caseSql = collect($orderedIds)
                    ->map(fn ($id, $index) => "WHEN '{$id}' THEN {$index}")
                    ->implode(' ');

                $query->whereIn('produits.id', $orderedIds)
                    ->orderByRaw("CASE produits.id {$caseSql} ELSE ".count($orderedIds).' END');
            } else {
                $query->search((string) $request->search);
            }
        }

        $sort = $request->input('sort', 'newest');

        if (! $semanticSearchApplied || $sort !== 'newest') {
            match ($sort) {
                'price_asc' => $query->orderBy('prix_ttc', 'asc'),
                'price_desc' => $query->orderBy('prix_ttc', 'desc'),
                'popular' => $query->orderBy('sold_count', 'desc'),
                default => ! $semanticSearchApplied ? $query->latest() : null,
            };
        }

        $products = $query->paginate(48)->through(fn ($p) => $this->formatProduct($p));

        $categories = ProductCategory::active()->parents()->ordered()->get();

        $brands = Brand::has('products')->get(['id', 'name']);

        // ✅ Calcul dynamique du prix actuel (promotion ou TTC) directement en SQL
        $priceStats = Produit::published()->inStock()
            ->selectRaw('
            MIN(
                CASE
                    WHEN prix_promotion IS NOT NULL AND prix_promotion < prix_ttc THEN prix_promotion
                    ELSE prix_ttc
                END
            ) as min_price,
            MAX(
                CASE
                    WHEN prix_promotion IS NOT NULL AND prix_promotion < prix_ttc THEN prix_promotion
                    ELSE prix_ttc
                END
            ) as max_price
        ')
            ->first();

        return response()->json([
            'products' => $products,
            'categories' => $categories,
            'brands' => $brands,
            'filters' => $request->only(['category', 'brand', 'min_price', 'max_price', 'search', 'sort']),
            'priceRange' => [
                'min' => (int) ($priceStats->min_price ?? 0),
                'max' => (int) ($priceStats->max_price ?? 500),
            ],
            'searchContext' => [
                'query' => (string) $request->input('search', ''),
                'mode' => $request->boolean('image_search') ? 'image' : 'text',
                'semantic' => $semanticSearchApplied,
            ],
        ]);
    }

    public function productsShow(Produit $produit)
    {
        $produit->load(['media', 'brand', 'categories', 'variantes', 'approvedAvis.client']);
        $produit->incrementerVues();

        $related = $produit->getRelatedProducts(24)->map(fn ($p) => $this->formatProduct($p));

        ProductView::create([
            'product_id' => $produit->id,
            'session_id' => Session::getId(),
            'visitor_id' => request()->cookie('y_visitor'),
            'url' => request()->fullUrl(),
            'viewed_at' => now(),
        ]);

        return response()->json([
            'product' => $this->formatProduct($produit, true),
            'relatedProducts' => $related,
        ]);
    }

    public function productsQuickView(Produit $produit)
    {
        return response()->json($this->formatProduct($produit, true));
    }

    public function formatProduct(Produit $product, $withDetails = false): array
    {
        $primary = $product->getPrimaryImage();
        $data = [
            'id' => $product->id,
            'nom' => $product->nom,
            'slug' => $product->slug,
            'prix_ttc' => (float) $product->prix_ttc,
            'prix_actuel' => (float) $product->prix_actuel,
            'est_en_promotion' => $product->est_en_promotion,
            'reduction_pourcentage' => $product->reduction_pourcentage,
            'image_principale' => $primary['medium'] ?? null,
            'is_deal_of_the_day' => (bool) $product->is_deal_of_the_day,
            'image_thumb' => $primary['thumb'] ?? null,
            'note_moyenne' => (float) $product->note_moyenne,
            'nombre_avis' => $product->nombre_avis,
            'badge' => $product->is_new ? 'Nouveauté' : ($product->is_bestseller ? 'Best ' : null),
            'url' => route('tenant.product.show', $product->slug),
        ];

        if ($withDetails) {
            $data['description'] = $product->description_longue;
            $data['short_description'] = $product->short_description;
            $data['updated_at'] = $product->updated_at->toIso8601String();
            $data['images'] = $product->images;
            $data['brand'] = $product->brand ? ['nom' => $product->brand->nom, 'slug' => $product->brand->slug] : null;
            $data['categories'] = $product->categories->map(fn ($c) => ['nom' => $c->nom, 'slug' => $c->slug]);
            $data['variantes'] = $product->variantes->map(fn ($v) => [
                'id' => $v->id,
                'nom' => $v->nom,
                'valeur' => $v->valeur,
                'supplement_prix' => $v->supplement_prix,
                'stock' => $v->stock,
                'prix_actuel' => $v->prix_actuel,
            ]);
            $data['avis'] = $product->approvedAvis()->with('client')->latest()->take(5)->get()->map(fn ($a) => [
                'note' => $a->note,
                'commentaire' => $a->commentaire,
                'client' => $a->client->full_name,
                'date' => $a->created_at->diffForHumans(),
            ]);
            $data['stock_disponible'] = $product->stock_disponible;
        }

        return $data;
    }

    protected function buildProductQuery()
    {
        return Produit::published()->inStock()->with(['media', 'brand', 'categories']);
    }
}
