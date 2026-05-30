<?php

namespace App\Filament\Vendeur\Resources\ProductCategories\Pages;

use App\Filament\Vendeur\Resources\ProductCategories\ProductCategoryResource;
use Filament\Resources\Pages\CreateRecord;

class CreateProductCategory extends CreateRecord
{
    protected static string $resource = ProductCategoryResource::class;
}
