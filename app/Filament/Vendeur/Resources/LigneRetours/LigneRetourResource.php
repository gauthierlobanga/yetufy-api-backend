<?php

namespace App\Filament\Vendeur\Resources\LigneRetours;

use App\Filament\Vendeur\Clusters\Commandes\CommandesCluster;
use App\Filament\Vendeur\Resources\LigneRetours\Pages\CreateLigneRetour;
use App\Filament\Vendeur\Resources\LigneRetours\Pages\EditLigneRetour;
use App\Filament\Vendeur\Resources\LigneRetours\Pages\ListLigneRetours;
use App\Filament\Vendeur\Resources\LigneRetours\Schemas\LigneRetourForm;
use App\Filament\Vendeur\Resources\LigneRetours\Tables\LigneRetoursTable;
use App\Models\LigneRetour;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class LigneRetourResource extends Resource
{
    protected static ?string $model = LigneRetour::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedDocumentArrowUp;

    protected static ?string $recordTitleAttribute = 'retour_id';

    protected static bool $isScopedToTenant = false;

    protected static ?string $cluster = CommandesCluster::class;

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
        return LigneRetourForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return LigneRetoursTable::configure($table);
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
            'index' => ListLigneRetours::route('/'),
            'create' => CreateLigneRetour::route('/create'),
            'edit' => EditLigneRetour::route('/{record}/edit'),
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
