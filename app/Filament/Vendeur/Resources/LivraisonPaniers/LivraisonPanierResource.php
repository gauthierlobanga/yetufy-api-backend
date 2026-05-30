<?php

namespace App\Filament\Vendeur\Resources\LivraisonPaniers;

use App\Filament\Vendeur\Clusters\Commandes\CommandesCluster;
use App\Filament\Vendeur\Resources\LivraisonPaniers\Pages\CreateLivraisonPanier;
use App\Filament\Vendeur\Resources\LivraisonPaniers\Pages\EditLivraisonPanier;
use App\Filament\Vendeur\Resources\LivraisonPaniers\Pages\ListLivraisonPaniers;
use App\Filament\Vendeur\Resources\LivraisonPaniers\Schemas\LivraisonPanierForm;
use App\Filament\Vendeur\Resources\LivraisonPaniers\Tables\LivraisonPaniersTable;
use App\Models\LivraisonPanier;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class LivraisonPanierResource extends Resource
{
    protected static ?string $model = LivraisonPanier::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedTruck;

    protected static ?string $recordTitleAttribute = 'panier_id';

    protected static bool $isScopedToTenant = false;

    protected static ?string $cluster = CommandesCluster::class;

    public static function form(Schema $schema): Schema
    {
        return LivraisonPanierForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return LivraisonPaniersTable::configure($table);
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
            'index' => ListLivraisonPaniers::route('/'),
            'create' => CreateLivraisonPanier::route('/create'),
            'edit' => EditLivraisonPanier::route('/{record}/edit'),
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
