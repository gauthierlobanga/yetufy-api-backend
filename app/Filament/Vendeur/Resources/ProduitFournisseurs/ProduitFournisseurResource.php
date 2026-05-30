<?php

namespace App\Filament\Vendeur\Resources\ProduitFournisseurs;

use App\Filament\Vendeur\Clusters\Products\ProductsCluster;
use App\Filament\Vendeur\Resources\ProduitFournisseurs\Pages\CreateProduitFournisseur;
use App\Filament\Vendeur\Resources\ProduitFournisseurs\Pages\EditProduitFournisseur;
use App\Filament\Vendeur\Resources\ProduitFournisseurs\Pages\ListProduitFournisseurs;
use App\Filament\Vendeur\Resources\ProduitFournisseurs\Schemas\ProduitFournisseurForm;
use App\Filament\Vendeur\Resources\ProduitFournisseurs\Tables\ProduitFournisseursTable;
use App\Models\ProduitFournisseur;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class ProduitFournisseurResource extends Resource
{
    protected static ?string $model = ProduitFournisseur::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedTruck;

    protected static ?string $cluster = ProductsCluster::class;

    protected static ?string $recordTitleAttribute = 'reference_fournisseur';

    protected static ?int $navigationSort = 5;

    protected static bool $isScopedToTenant = false;

    public static function form(Schema $schema): Schema
    {
        return ProduitFournisseurForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return ProduitFournisseursTable::configure($table);
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
            'index' => ListProduitFournisseurs::route('/'),
            'create' => CreateProduitFournisseur::route('/create'),
            'edit' => EditProduitFournisseur::route('/{record}/edit'),
        ];
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
