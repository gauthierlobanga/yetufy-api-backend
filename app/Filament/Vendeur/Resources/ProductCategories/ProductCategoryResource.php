<?php

namespace App\Filament\Vendeur\Resources\ProductCategories;

use App\Filament\Vendeur\Clusters\Products\ProductsCluster;
use App\Filament\Vendeur\Resources\ProductCategories\Pages\CreateProductCategory;
use App\Filament\Vendeur\Resources\ProductCategories\Pages\EditProductCategory;
use App\Filament\Vendeur\Resources\ProductCategories\Pages\ListProductCategories;
use App\Filament\Vendeur\Resources\ProductCategories\Schemas\ProductCategoryForm;
use App\Filament\Vendeur\Resources\ProductCategories\Tables\ProductCategoriesTable;
use App\Models\ProductCategory;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class ProductCategoryResource extends Resource
{
    protected static ?string $model = ProductCategory::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedTag;

    protected static ?string $cluster = ProductsCluster::class;

    protected static ?string $navigationLabel = 'Catégories';

    protected static ?string $recordTitleAttribute = 'nom';

    protected static ?int $navigationSort = 2;

    protected static bool $isScopedToTenant = false;

    public static function form(Schema $schema): Schema
    {
        return ProductCategoryForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return ProductCategoriesTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListProductCategories::route('/'),
            'create' => CreateProductCategory::route('/create'),
            'edit' => EditProductCategory::route('/{record}/edit'),
        ];
    }

    public static function getRecordRouteBindingEloquentQuery(): Builder
    {
        return parent::getRecordRouteBindingEloquentQuery()
            ->withoutGlobalScopes([
                SoftDeletingScope::class,
            ]);
    }

    public static function getNavigationBadge(): ?string
    {
        return static::getModel()::count();
    }

    public static function getNavigationBadgeColor(): string|array|null
    {
        return static::getModel()::count() > 10 ? 'success' : 'warning';
    }
}
