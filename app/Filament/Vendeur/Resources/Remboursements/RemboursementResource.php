<?php

namespace App\Filament\Vendeur\Resources\Remboursements;

use App\Filament\Vendeur\Clusters\Commandes\CommandesCluster;
use App\Filament\Vendeur\Resources\Remboursements\Pages\CreateRemboursement;
use App\Filament\Vendeur\Resources\Remboursements\Pages\EditRemboursement;
use App\Filament\Vendeur\Resources\Remboursements\Pages\ListRemboursements;
use App\Filament\Vendeur\Resources\Remboursements\Schemas\RemboursementForm;
use App\Filament\Vendeur\Resources\Remboursements\Tables\RemboursementsTable;
use App\Models\Remboursement;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class RemboursementResource extends Resource
{
    protected static ?string $model = Remboursement::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedCurrencyEuro;

    protected static ?string $recordTitleAttribute = 'reference';

    protected static ?string $cluster = CommandesCluster::class;

    protected static bool $isScopedToTenant = false;

    public static function form(Schema $schema): Schema
    {
        return RemboursementForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return RemboursementsTable::configure($table);
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
            'index' => ListRemboursements::route('/'),
            'create' => CreateRemboursement::route('/create'),
            'edit' => EditRemboursement::route('/{record}/edit'),
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
