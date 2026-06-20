<?php

namespace App\Filament\Vendeur\Resources\AvisClients;

use App\Enums\NavigationGroup;
use App\Filament\Vendeur\Resources\AvisClients\Pages\CreateAvisClient;
use App\Filament\Vendeur\Resources\AvisClients\Pages\EditAvisClient;
use App\Filament\Vendeur\Resources\AvisClients\Pages\ListAvisClients;
use App\Filament\Vendeur\Resources\AvisClients\Schemas\AvisClientForm;
use App\Filament\Vendeur\Resources\AvisClients\Tables\AvisClientsTable;
use App\Models\AvisClient;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use UnitEnum;

class AvisClientResource extends Resource
{
    protected static ?string $model = AvisClient::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedRectangleStack;

    protected static string|UnitEnum|null $navigationGroup = NavigationGroup::Shop;

    protected static bool $isScopedToTenant = false;

    protected static ?int $navigationSort = 9;

    protected static ?string $recordTitleAttribute = 'note';

    public static function form(Schema $schema): Schema
    {
        return AvisClientForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return AvisClientsTable::configure($table);
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
            'index' => ListAvisClients::route('/'),
            'create' => CreateAvisClient::route('/create'),
            'edit' => EditAvisClient::route('/{record}/edit'),
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
