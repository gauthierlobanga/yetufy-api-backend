<?php

namespace App\Filament\Vendeur\Resources\ReglePaniers;

use App\Filament\Vendeur\Clusters\Paniers\PaniersCluster;
use App\Filament\Vendeur\Resources\ReglePaniers\Pages\CreateReglePanier;
use App\Filament\Vendeur\Resources\ReglePaniers\Pages\EditReglePanier;
use App\Filament\Vendeur\Resources\ReglePaniers\Pages\ListReglePaniers;
use App\Filament\Vendeur\Resources\ReglePaniers\Schemas\ReglePanierForm;
use App\Filament\Vendeur\Resources\ReglePaniers\Tables\ReglePaniersTable;
use App\Models\ReglePanier;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class ReglePanierResource extends Resource
{
    protected static ?string $model = ReglePanier::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedCog6Tooth;

    protected static ?string $recordTitleAttribute = 'type';

    protected static ?string $cluster = PaniersCluster::class;

    protected static ?int $navigationSort = 3;

    protected static bool $isScopedToTenant = false;

    public static function form(Schema $schema): Schema
    {
        return ReglePanierForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return ReglePaniersTable::configure($table);
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
            'index' => ListReglePaniers::route('/'),
            'create' => CreateReglePanier::route('/create'),
            'edit' => EditReglePanier::route('/{record}/edit'),
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
