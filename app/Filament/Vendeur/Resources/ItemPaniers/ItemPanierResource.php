<?php

namespace App\Filament\Vendeur\Resources\ItemPaniers;

use App\Filament\Vendeur\Clusters\Paniers\PaniersCluster;
use App\Filament\Vendeur\Resources\ItemPaniers\Pages\CreateItemPanier;
use App\Filament\Vendeur\Resources\ItemPaniers\Pages\EditItemPanier;
use App\Filament\Vendeur\Resources\ItemPaniers\Pages\ListItemPaniers;
use App\Filament\Vendeur\Resources\ItemPaniers\Schemas\ItemPanierForm;
use App\Filament\Vendeur\Resources\ItemPaniers\Tables\ItemPaniersTable;
use App\Models\ItemPanier;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class ItemPanierResource extends Resource
{
    protected static ?string $model = ItemPanier::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedSquaresPlus;

    protected static ?string $cluster = PaniersCluster::class;

    protected static ?string $recordTitleAttribute = 'panier_id';

    protected static bool $isScopedToTenant = false;

    protected static ?int $navigationSort = 2;

    public static function getNavigationBadge(): ?string
    {
        return static::getModel()::count();
    }

    public static function getNavigationBadgeColor(): string|array|null
    {
        return static::getModel()::count() > 10 ? 'success' : 'warning';
    }

    public static function form(Schema $schema): Schema
    {
        return ItemPanierForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return ItemPaniersTable::configure($table);
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
            'index' => ListItemPaniers::route('/'),
            'create' => CreateItemPanier::route('/create'),
            'edit' => EditItemPanier::route('/{record}/edit'),
        ];
    }

    public static function getRecordRouteBindingEloquentQuery(): Builder
    {
        return parent::getRecordRouteBindingEloquentQuery()
            ->withoutGlobalScopes([
                SoftDeletingScope::class,
            ]);
    }
}
