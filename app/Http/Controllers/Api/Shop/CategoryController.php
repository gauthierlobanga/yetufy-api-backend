<?php

namespace App\Http\Controllers\Api\Shop;

use App\Http\Controllers\Controller;
use App\Models\ProductCategory;

class CategoryController extends Controller
{
    public function categoriesIndex()
    {
        $categories = ProductCategory::active()->ordered()->with('media')->get()
            ->map(fn ($c) => $this->formatCategory($c));

        return response()->json(['categories' => $categories]);
    }

    public function categoriesShow(ProductCategory $category)
    {
        $category->load('media');
        $products = $category->products()->published()->inStock()->paginate(24)
            ->through(fn ($p) => app(ProductController::class)->formatProduct($p));

        $subcategories = $category->children()->active()->ordered()->get()->map(fn ($c) => $this->formatCategory($c));
        $breadcrumb = $category->getBreadcrumb();

        return response()->json([
            'category' => $this->formatCategory($category),
            'products' => $products,
            'subcategories' => $subcategories,
            'breadcrumb' => $breadcrumb,
        ]);
    }

    private function formatCategory(ProductCategory $category): array
    {
        return [
            'id' => $category->id,
            'nom' => $category->nom,
            'slug' => $category->slug,
            'description' => $category->short_description,
            'image' => $category->image,
            'icon' => $category->icon,
            'banner' => $category->banner,
            'image_thumb' => $category->image_thumb,
            'url' => route('tenant.product.category.show', $category->slug),
            'products_count' => $category->products_count,
        ];
    }
}
