<?php

namespace App\Filament\Vendeur\Resources\Paniers;

use App\Filament\Vendeur\Clusters\Paniers\PaniersCluster;
use App\Filament\Vendeur\Resources\Paniers\Pages\CreatePanier;
use App\Filament\Vendeur\Resources\Paniers\Pages\EditPanier;
use App\Filament\Vendeur\Resources\Paniers\Pages\ListPaniers;
use App\Filament\Vendeur\Resources\Paniers\Schemas\PanierForm;
use App\Filament\Vendeur\Resources\Paniers\Tables\PaniersTable;
use App\Models\Panier;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class PanierResource extends Resource
{
    protected static ?string $model = Panier::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedShoppingCart;

    protected static ?string $cluster = PaniersCluster::class;

    protected static ?string $recordTitleAttribute = 'user_id';

    protected static ?int $navigationSort = 1;

    protected static bool $isScopedToTenant = false;

    public static function form(Schema $schema): Schema
    {
        return PanierForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return PaniersTable::configure($table);
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
            'index' => ListPaniers::route('/'),
            'create' => CreatePanier::route('/create'),
            'edit' => EditPanier::route('/{record}/edit'),
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
