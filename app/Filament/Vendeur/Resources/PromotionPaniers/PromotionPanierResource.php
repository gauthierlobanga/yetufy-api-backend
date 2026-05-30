<?php

namespace App\Filament\Vendeur\Resources\PromotionPaniers;

use App\Filament\Vendeur\Clusters\Promotions\PromotionsCluster;
use App\Filament\Vendeur\Resources\PromotionPaniers\Pages\CreatePromotionPanier;
use App\Filament\Vendeur\Resources\PromotionPaniers\Pages\EditPromotionPanier;
use App\Filament\Vendeur\Resources\PromotionPaniers\Pages\ListPromotionPaniers;
use App\Filament\Vendeur\Resources\PromotionPaniers\Schemas\PromotionPanierForm;
use App\Filament\Vendeur\Resources\PromotionPaniers\Tables\PromotionPaniersTable;
use App\Models\PromotionPanier;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class PromotionPanierResource extends Resource
{
    protected static ?string $model = PromotionPanier::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedShoppingCart;

    protected static ?string $recordTitleAttribute = 'promotion_id';

    protected static ?string $cluster = PromotionsCluster::class;

    protected static ?int $navigationSort = 3;

    protected static bool $isScopedToTenant = false;

    public static function form(Schema $schema): Schema
    {
        return PromotionPanierForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return PromotionPaniersTable::configure($table);
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
            'index' => ListPromotionPaniers::route('/'),
            'create' => CreatePromotionPanier::route('/create'),
            'edit' => EditPromotionPanier::route('/{record}/edit'),
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
