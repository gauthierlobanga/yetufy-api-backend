<?php

namespace App\Filament\Vendeur\Resources\Paiements;

use App\Filament\Vendeur\Clusters\Inventor\InventorCluster;
use App\Filament\Vendeur\Resources\Paiements\Pages\CreatePaiement;
use App\Filament\Vendeur\Resources\Paiements\Pages\EditPaiement;
use App\Filament\Vendeur\Resources\Paiements\Pages\ListPaiements;
use App\Filament\Vendeur\Resources\Paiements\Schemas\PaiementForm;
use App\Filament\Vendeur\Resources\Paiements\Tables\PaiementsTable;
use App\Models\Paiement;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class PaiementResource extends Resource
{
    protected static ?string $model = Paiement::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedCreditCard;

    protected static ?string $cluster = InventorCluster::class;

    protected static ?string $recordTitleAttribute = 'reference';

    protected static bool $isScopedToTenant = false;

    public static function form(Schema $schema): Schema
    {
        return PaiementForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return PaiementsTable::configure($table);
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
            'index' => ListPaiements::route('/'),
            'create' => CreatePaiement::route('/create'),
            'edit' => EditPaiement::route('/{record}/edit'),
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
