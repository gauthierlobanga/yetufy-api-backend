<?php

namespace App\Filament\Vendeur\Resources\Fournisseurs;

use App\Enums\NavigationGroup;
use App\Filament\Vendeur\Resources\Fournisseurs\Pages\CreateFournisseur;
use App\Filament\Vendeur\Resources\Fournisseurs\Pages\EditFournisseur;
use App\Filament\Vendeur\Resources\Fournisseurs\Pages\ListFournisseurs;
use App\Filament\Vendeur\Resources\Fournisseurs\Schemas\FournisseurForm;
use App\Filament\Vendeur\Resources\Fournisseurs\Tables\FournisseursTable;
use App\Models\Fournisseur;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use UnitEnum;

class FournisseurResource extends Resource
{
    protected static ?string $model = Fournisseur::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedRectangleStack;

    protected static ?string $recordTitleAttribute = 'nom';

    protected static string|UnitEnum|null $navigationGroup = NavigationGroup::Profile;

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
        return FournisseurForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return FournisseursTable::configure($table);
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
            'index' => ListFournisseurs::route('/'),
            'create' => CreateFournisseur::route('/create'),
            'edit' => EditFournisseur::route('/{record}/edit'),
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
