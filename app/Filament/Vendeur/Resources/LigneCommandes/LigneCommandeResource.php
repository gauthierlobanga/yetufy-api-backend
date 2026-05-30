<?php

namespace App\Filament\Vendeur\Resources\LigneCommandes;

use App\Filament\Vendeur\Clusters\Commandes\CommandesCluster;
use App\Filament\Vendeur\Resources\LigneCommandes\Pages\CreateLigneCommande;
use App\Filament\Vendeur\Resources\LigneCommandes\Pages\EditLigneCommande;
use App\Filament\Vendeur\Resources\LigneCommandes\Pages\ListLigneCommandes;
use App\Filament\Vendeur\Resources\LigneCommandes\Schemas\LigneCommandeForm;
use App\Filament\Vendeur\Resources\LigneCommandes\Tables\LigneCommandesTable;
use App\Models\LigneCommande;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class LigneCommandeResource extends Resource
{
    protected static ?string $model = LigneCommande::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedDocumentText;

    protected static ?string $recordTitleAttribute = 'commande_id';

    protected static ?string $cluster = CommandesCluster::class;

    protected static bool $isScopedToTenant = false;

    public static function form(Schema $schema): Schema
    {
        return LigneCommandeForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return LigneCommandesTable::configure($table);
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
            'index' => ListLigneCommandes::route('/'),
            'create' => CreateLigneCommande::route('/create'),
            'edit' => EditLigneCommande::route('/{record}/edit'),
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
