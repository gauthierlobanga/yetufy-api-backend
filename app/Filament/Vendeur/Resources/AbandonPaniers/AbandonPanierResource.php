<?php

namespace App\Filament\Vendeur\Resources\AbandonPaniers;

use App\Filament\Vendeur\Clusters\Paniers\PaniersCluster;
use App\Filament\Vendeur\Resources\AbandonPaniers\Pages\CreateAbandonPanier;
use App\Filament\Vendeur\Resources\AbandonPaniers\Pages\EditAbandonPanier;
use App\Filament\Vendeur\Resources\AbandonPaniers\Pages\ListAbandonPaniers;
use App\Filament\Vendeur\Resources\AbandonPaniers\Schemas\AbandonPanierForm;
use App\Filament\Vendeur\Resources\AbandonPaniers\Tables\AbandonPaniersTable;
use App\Models\AbandonPanier;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class AbandonPanierResource extends Resource
{
    protected static ?string $model = AbandonPanier::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedXCircle;

    protected static ?string $recordTitleAttribute = 'raison';

    protected static bool $isScopedToTenant = false;

    protected static ?string $cluster = PaniersCluster::class;

    protected static ?int $navigationSort = 5;

    public static function form(Schema $schema): Schema
    {
        return AbandonPanierForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return AbandonPaniersTable::configure($table);
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
            'index' => ListAbandonPaniers::route('/'),
            'create' => CreateAbandonPanier::route('/create'),
            'edit' => EditAbandonPanier::route('/{record}/edit'),
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
