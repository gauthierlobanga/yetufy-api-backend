<?php

namespace App\Filament\Vendeur\Resources\MouvementStocks;

use App\Filament\Vendeur\Clusters\Inventor\InventorCluster;
use App\Filament\Vendeur\Resources\MouvementStocks\Pages\CreateMouvementStock;
use App\Filament\Vendeur\Resources\MouvementStocks\Pages\EditMouvementStock;
use App\Filament\Vendeur\Resources\MouvementStocks\Pages\ListMouvementStocks;
use App\Filament\Vendeur\Resources\MouvementStocks\Schemas\MouvementStockForm;
use App\Filament\Vendeur\Resources\MouvementStocks\Tables\MouvementStocksTable;
use App\Models\MouvementStock;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class MouvementStockResource extends Resource
{
    protected static ?string $model = MouvementStock::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedArrowsRightLeft;

    protected static ?string $cluster = InventorCluster::class;

    protected static ?string $navigationLabel = 'Stocks';

    protected static ?string $recordTitleAttribute = 'type';

    protected static bool $isScopedToTenant = false;

    public static function form(Schema $schema): Schema
    {
        return MouvementStockForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return MouvementStocksTable::configure($table);
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
            'index' => ListMouvementStocks::route('/'),
            'create' => CreateMouvementStock::route('/create'),
            'edit' => EditMouvementStock::route('/{record}/edit'),
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
