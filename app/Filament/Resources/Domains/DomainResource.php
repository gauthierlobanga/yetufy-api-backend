<?php

namespace App\Filament\Resources\Domains;

use App\Enums\NavigationGroup;
use App\Filament\Resources\Domains\Pages\CreateDomain;
use App\Filament\Resources\Domains\Pages\EditDomain;
use App\Filament\Resources\Domains\Pages\ListDomains;
use App\Filament\Resources\Domains\Schemas\DomainForm;
use App\Filament\Resources\Domains\Tables\DomainsTable;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Illuminate\Support\Facades\Auth;
use Stancl\Tenancy\Database\Models\Domain;
use UnitEnum;

class DomainResource extends Resource
{
    protected static ?string $model = Domain::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedGlobeAlt;

    protected static string|UnitEnum|null $navigationGroup = NavigationGroup::Organisation;

    protected static ?string $recordTitleAttribute = 'domain';

    public static function form(Schema $schema): Schema
    {
        return DomainForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return DomainsTable::configure($table);
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
            'index' => ListDomains::route('/'),
            'create' => CreateDomain::route('/create'),
            'edit' => EditDomain::route('/{record}/edit'),
        ];
    }

    public static function getNavigationBadge(): ?string
    {
        if (Auth::user()->hasRole('super_admin')) {
            return static::getModel()::count();
        }

        return null;
    }

    public static function getNavigationBadgeColor(): string|array|null
    {
        if (Auth::user()->hasRole('super_admin')) {
            return static::getModel()::count() > 10 ? 'success' : 'warning';
        }

        return null;
    }

    public static function getRecordRouteBindingEloquentQuery(): Builder
    {
        return parent::getRecordRouteBindingEloquentQuery()
            ->withoutGlobalScopes([
                SoftDeletingScope::class,
            ]);
    }
}
