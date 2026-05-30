<?php

namespace App\Filament\Resources\Vendeurs;

use App\Enums\NavigationGroup;
use App\Filament\Concerns\ExclureFromResources;
use App\Filament\Resources\Vendeurs\Pages\CreateVendeur;
use App\Filament\Resources\Vendeurs\Pages\EditVendeur;
use App\Filament\Resources\Vendeurs\Pages\ListVendeurs;
use App\Filament\Resources\Vendeurs\Schemas\VendeurForm;
use App\Filament\Resources\Vendeurs\Tables\VendeursTable;
use App\Models\Tenant;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use UnitEnum;

class VendeurResource extends Resource
{
    use ExclureFromResources;

    protected static ?string $model = Tenant::class;

    protected static string|UnitEnum|null $navigationGroup = NavigationGroup::Organisation;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedRectangleStack;

    protected static ?string $recordTitleAttribute = 'name';

    protected static ?int $navigationSort = 1;

    public static function form(Schema $schema): Schema
    {
        return VendeurForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return VendeursTable::configure($table);
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
            'index' => ListVendeurs::route('/'),
            'create' => CreateVendeur::route('/create'),
            'edit' => EditVendeur::route('/{record}/edit'),
        ];
    }

    public static function getNavigationBadge(): ?string
    {
        return static::getModel()::count();
    }

    public static function getNavigationBadgeColor(): ?string
    {
        return static::getModel()::count() > 10 ? 'success' : 'danger';
    }

    public static function getRecordRouteBindingEloquentQuery(): Builder
    {
        return parent::getRecordRouteBindingEloquentQuery()
            ->withoutGlobalScopes([
                SoftDeletingScope::class,
            ]);
    }
}
