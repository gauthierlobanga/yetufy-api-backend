<?php

namespace App\Filament\Vendeur\Resources\VarianteProduits;

use App\Filament\Vendeur\Clusters\Products\ProductsCluster;
use App\Filament\Vendeur\Resources\VarianteProduits\Pages\CreateVarianteProduit;
use App\Filament\Vendeur\Resources\VarianteProduits\Pages\EditVarianteProduit;
use App\Filament\Vendeur\Resources\VarianteProduits\Pages\ListVarianteProduits;
use App\Filament\Vendeur\Resources\VarianteProduits\Schemas\VarianteProduitForm;
use App\Filament\Vendeur\Resources\VarianteProduits\Tables\VarianteProduitsTable;
use App\Models\VarianteProduit;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class VarianteProduitResource extends Resource
{
    protected static ?string $model = VarianteProduit::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedSquare3Stack3d;

    protected static ?string $cluster = ProductsCluster::class;

    protected static ?string $recordTitleAttribute = 'nom';

    protected static bool $isScopedToTenant = false;

    protected static ?int $navigationSort = 3;

    public static function form(Schema $schema): Schema
    {
        return VarianteProduitForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return VarianteProduitsTable::configure($table);
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
            'index' => ListVarianteProduits::route('/'),
            'create' => CreateVarianteProduit::route('/create'),
            'edit' => EditVarianteProduit::route('/{record}/edit'),
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
