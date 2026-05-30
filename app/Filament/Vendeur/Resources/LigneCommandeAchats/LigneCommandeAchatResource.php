<?php

namespace App\Filament\Vendeur\Resources\LigneCommandeAchats;

use App\Filament\Vendeur\Clusters\Achats\AchatsCluster;
use App\Filament\Vendeur\Resources\LigneCommandeAchats\Pages\CreateLigneCommandeAchat;
use App\Filament\Vendeur\Resources\LigneCommandeAchats\Pages\EditLigneCommandeAchat;
use App\Filament\Vendeur\Resources\LigneCommandeAchats\Pages\ListLigneCommandeAchats;
use App\Filament\Vendeur\Resources\LigneCommandeAchats\Schemas\LigneCommandeAchatForm;
use App\Filament\Vendeur\Resources\LigneCommandeAchats\Tables\LigneCommandeAchatsTable;
use App\Models\LigneCommandeAchat;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class LigneCommandeAchatResource extends Resource
{
    protected static ?string $model = LigneCommandeAchat::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedDocumentText;

    protected static ?string $cluster = AchatsCluster::class;

    protected static ?string $recordTitleAttribute = 'commande_achat';

    protected static ?string $navigationLabel = 'Ligne Achats';

    protected static bool $isScopedToTenant = false;

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
        return LigneCommandeAchatForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return LigneCommandeAchatsTable::configure($table);
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
            'index' => ListLigneCommandeAchats::route('/'),
            'create' => CreateLigneCommandeAchat::route('/create'),
            'edit' => EditLigneCommandeAchat::route('/{record}/edit'),
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
