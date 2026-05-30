<?php

namespace App\Filament\Vendeur\Resources\Taxes;

use App\Enums\NavigationGroup;
use App\Filament\Vendeur\Resources\Taxes\Pages\CreateTaxe;
use App\Filament\Vendeur\Resources\Taxes\Pages\EditTaxe;
use App\Filament\Vendeur\Resources\Taxes\Pages\ListTaxes;
use App\Filament\Vendeur\Resources\Taxes\Schemas\TaxeForm;
use App\Filament\Vendeur\Resources\Taxes\Tables\TaxesTable;
use App\Models\Taxe;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use UnitEnum;

class TaxeResource extends Resource
{
    protected static ?string $model = Taxe::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedRectangleStack;

    protected static string|UnitEnum|null $navigationGroup = NavigationGroup::Settings;

    protected static ?string $recordTitleAttribute = 'taux';

    protected static bool $isScopedToTenant = false;

    public static function form(Schema $schema): Schema
    {
        return TaxeForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return TaxesTable::configure($table);
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
            'index' => ListTaxes::route('/'),
            'create' => CreateTaxe::route('/create'),
            'edit' => EditTaxe::route('/{record}/edit'),
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
