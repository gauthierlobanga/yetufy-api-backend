<?php

namespace App\Filament\Vendeur\Resources\Devises;

use App\Enums\NavigationGroup;
use App\Filament\Vendeur\Resources\Devises\Pages\CreateDevise;
use App\Filament\Vendeur\Resources\Devises\Pages\EditDevise;
use App\Filament\Vendeur\Resources\Devises\Pages\ListDevises;
use App\Filament\Vendeur\Resources\Devises\Schemas\DeviseForm;
use App\Filament\Vendeur\Resources\Devises\Tables\DevisesTable;
use App\Models\Devise;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use UnitEnum;

class DeviseResource extends Resource
{
    protected static ?string $model = Devise::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedRectangleStack;

    protected static string|UnitEnum|null $navigationGroup = NavigationGroup::Settings;

    protected static ?string $recordTitleAttribute = 'symbole';

    protected static bool $isScopedToTenant = false;

    public static function form(Schema $schema): Schema
    {
        return DeviseForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return DevisesTable::configure($table);
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
            'index' => ListDevises::route('/'),
            'create' => CreateDevise::route('/create'),
            'edit' => EditDevise::route('/{record}/edit'),
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
