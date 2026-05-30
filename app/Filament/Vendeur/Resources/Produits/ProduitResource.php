<?php

namespace App\Filament\Vendeur\Resources\Produits;

use App\Filament\Vendeur\Clusters\Products\ProductsCluster;
use App\Filament\Vendeur\Resources\Produits\Pages\CreateProduit;
use App\Filament\Vendeur\Resources\Produits\Pages\EditProduit;
use App\Filament\Vendeur\Resources\Produits\Pages\ListProduits;
use App\Filament\Vendeur\Resources\Produits\Schemas\ProduitForm;
use App\Filament\Vendeur\Resources\Produits\Tables\ProduitsTable;
use App\Models\Produit;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class ProduitResource extends Resource
{
    protected static ?string $model = Produit::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedShoppingBag;

    protected static ?string $cluster = ProductsCluster::class;

    protected static ?int $navigationSort = 1;

    protected static ?string $recordTitleAttribute = 'nom';

    protected static bool $isScopedToTenant = false;

    public static function form(Schema $schema): Schema
    {
        return ProduitForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return ProduitsTable::configure($table);
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
            'index' => ListProduits::route('/'),
            'create' => CreateProduit::route('/create'),
            'edit' => EditProduit::route('/{record}/edit'),
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
