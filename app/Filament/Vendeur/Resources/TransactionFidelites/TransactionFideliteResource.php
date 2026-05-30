<?php

namespace App\Filament\Vendeur\Resources\TransactionFidelites;

use App\Enums\NavigationGroup;
use App\Filament\Vendeur\Resources\TransactionFidelites\Pages\CreateTransactionFidelite;
use App\Filament\Vendeur\Resources\TransactionFidelites\Pages\EditTransactionFidelite;
use App\Filament\Vendeur\Resources\TransactionFidelites\Pages\ListTransactionFidelites;
use App\Filament\Vendeur\Resources\TransactionFidelites\Schemas\TransactionFideliteForm;
use App\Filament\Vendeur\Resources\TransactionFidelites\Tables\TransactionFidelitesTable;
use App\Models\TransactionFidelite;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use UnitEnum;

class TransactionFideliteResource extends Resource
{
    protected static ?string $model = TransactionFidelite::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedRectangleStack;

    protected static ?string $recordTitleAttribute = 'compte_fidelite_id';

    protected static string|UnitEnum|null $navigationGroup = NavigationGroup::Shop;

    protected static ?int $navigationSort = 10;

    protected static bool $isScopedToTenant = false;

    public static function form(Schema $schema): Schema
    {
        return TransactionFideliteForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return TransactionFidelitesTable::configure($table);
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
            'index' => ListTransactionFidelites::route('/'),
            'create' => CreateTransactionFidelite::route('/create'),
            'edit' => EditTransactionFidelite::route('/{record}/edit'),
        ];
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
