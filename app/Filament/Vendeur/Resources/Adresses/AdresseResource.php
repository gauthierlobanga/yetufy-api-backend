<?php

namespace App\Filament\Vendeur\Resources\Adresses;

use App\Enums\NavigationGroup;
use App\Filament\Vendeur\Resources\Adresses\Pages\CreateAdresse;
use App\Filament\Vendeur\Resources\Adresses\Pages\EditAdresse;
use App\Filament\Vendeur\Resources\Adresses\Pages\ListAdresses;
use App\Filament\Vendeur\Resources\Adresses\Schemas\AdresseForm;
use App\Filament\Vendeur\Resources\Adresses\Tables\AdressesTable;
use App\Models\Adresse;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use UnitEnum;

class AdresseResource extends Resource
{
    protected static ?string $model = Adresse::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedRectangleStack;

    protected static string|UnitEnum|null $navigationGroup = NavigationGroup::Profile;

    protected static ?string $recordTitleAttribute = 'code_postal';

    protected static bool $isScopedToTenant = false;

    public static function form(Schema $schema): Schema
    {
        return AdresseForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return AdressesTable::configure($table);
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
            'index' => ListAdresses::route('/'),
            'create' => CreateAdresse::route('/create'),
            'edit' => EditAdresse::route('/{record}/edit'),
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
