<?php

namespace App\Filament\Vendeur\Resources\ProgrammeFidelites;

use App\Filament\Vendeur\Clusters\Promotions\PromotionsCluster;
use App\Filament\Vendeur\Resources\ProgrammeFidelites\Pages\CreateProgrammeFidelite;
use App\Filament\Vendeur\Resources\ProgrammeFidelites\Pages\EditProgrammeFidelite;
use App\Filament\Vendeur\Resources\ProgrammeFidelites\Pages\ListProgrammeFidelites;
use App\Filament\Vendeur\Resources\ProgrammeFidelites\Schemas\ProgrammeFideliteForm;
use App\Filament\Vendeur\Resources\ProgrammeFidelites\Tables\ProgrammeFidelitesTable;
use App\Models\ProgrammeFidelite;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class ProgrammeFideliteResource extends Resource
{
    protected static ?string $model = ProgrammeFidelite::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedStar;

    protected static ?string $recordTitleAttribute = 'nom';

    protected static ?string $navigationLabel = 'Fidelité';

    protected static ?string $cluster = PromotionsCluster::class;

    protected static ?int $navigationSort = 5;

    protected static bool $isScopedToTenant = false;

    public static function form(Schema $schema): Schema
    {
        return ProgrammeFideliteForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return ProgrammeFidelitesTable::configure($table);
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
            'index' => ListProgrammeFidelites::route('/'),
            'create' => CreateProgrammeFidelite::route('/create'),
            'edit' => EditProgrammeFidelite::route('/{record}/edit'),
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
