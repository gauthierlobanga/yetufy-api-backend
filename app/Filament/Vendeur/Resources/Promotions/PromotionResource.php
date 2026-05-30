<?php

namespace App\Filament\Vendeur\Resources\Promotions;

use App\Filament\Vendeur\Clusters\Promotions\PromotionsCluster;
use App\Filament\Vendeur\Resources\Promotions\Pages\CreatePromotion;
use App\Filament\Vendeur\Resources\Promotions\Pages\EditPromotion;
use App\Filament\Vendeur\Resources\Promotions\Pages\ListPromotions;
use App\Filament\Vendeur\Resources\Promotions\Schemas\PromotionForm;
use App\Filament\Vendeur\Resources\Promotions\Tables\PromotionsTable;
use App\Models\Promotion;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class PromotionResource extends Resource
{
    protected static ?string $model = Promotion::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedTicket;

    protected static ?string $recordTitleAttribute = 'code';

    protected static ?string $cluster = PromotionsCluster::class;

    protected static ?int $navigationSort = 1;

    protected static bool $isScopedToTenant = false;

    public static function form(Schema $schema): Schema
    {
        return PromotionForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return PromotionsTable::configure($table);
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
            'index' => ListPromotions::route('/'),
            'create' => CreatePromotion::route('/create'),
            'edit' => EditPromotion::route('/{record}/edit'),
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
