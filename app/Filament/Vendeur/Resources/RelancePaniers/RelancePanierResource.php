<?php

namespace App\Filament\Vendeur\Resources\RelancePaniers;

use App\Filament\Vendeur\Clusters\Paniers\PaniersCluster;
use App\Filament\Vendeur\Resources\RelancePaniers\Pages\CreateRelancePanier;
use App\Filament\Vendeur\Resources\RelancePaniers\Pages\EditRelancePanier;
use App\Filament\Vendeur\Resources\RelancePaniers\Pages\ListRelancePaniers;
use App\Filament\Vendeur\Resources\RelancePaniers\Schemas\RelancePanierForm;
use App\Filament\Vendeur\Resources\RelancePaniers\Tables\RelancePaniersTable;
use App\Models\RelancePanier;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class RelancePanierResource extends Resource
{
    protected static ?string $model = RelancePanier::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedChatBubbleLeft;

    protected static ?string $recordTitleAttribute = 'canal';

    protected static ?string $cluster = PaniersCluster::class;

    protected static ?int $navigationSort = 4;

    protected static bool $isScopedToTenant = false;

    public static function form(Schema $schema): Schema
    {
        return RelancePanierForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return RelancePaniersTable::configure($table);
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
            'index' => ListRelancePaniers::route('/'),
            'create' => CreateRelancePanier::route('/create'),
            'edit' => EditRelancePanier::route('/{record}/edit'),
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
