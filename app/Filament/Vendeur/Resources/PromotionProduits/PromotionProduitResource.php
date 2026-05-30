<?php

namespace App\Filament\Vendeur\Resources\PromotionProduits;

use App\Filament\Vendeur\Clusters\Promotions\PromotionsCluster;
use App\Filament\Vendeur\Resources\PromotionProduits\Pages\CreatePromotionProduit;
use App\Filament\Vendeur\Resources\PromotionProduits\Pages\EditPromotionProduit;
use App\Filament\Vendeur\Resources\PromotionProduits\Pages\ListPromotionProduits;
use App\Filament\Vendeur\Resources\PromotionProduits\Schemas\PromotionProduitForm;
use App\Filament\Vendeur\Resources\PromotionProduits\Tables\PromotionProduitsTable;
use App\Models\PromotionProduit;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class PromotionProduitResource extends Resource
{
    protected static ?string $model = PromotionProduit::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedCube;

    protected static ?string $recordTitleAttribute = 'promotion_id';

    protected static ?string $cluster = PromotionsCluster::class;

    protected static ?int $navigationSort = 2;

    protected static bool $isScopedToTenant = false;

    public static function form(Schema $schema): Schema
    {
        return PromotionProduitForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return PromotionProduitsTable::configure($table);
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
            'index' => ListPromotionProduits::route('/'),
            'create' => CreatePromotionProduit::route('/create'),
            'edit' => EditPromotionProduit::route('/{record}/edit'),
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

    public static function getTableQuery(): Builder
    {
        $query = parent::getTableQuery();

        return $query->reorder()->orderBy('created_at', 'desc');
    }

    protected static function getTableRecordIdUsing(): ?\Closure
    {
        return fn ($record) => $record->promotion_id.'-'.$record->produit_id;
    }
}
