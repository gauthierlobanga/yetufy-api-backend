<?php

namespace App\Filament\Vendeur\Resources\Coupons;

use App\Filament\Vendeur\Clusters\Inventor\InventorCluster;
use App\Filament\Vendeur\Resources\Coupons\Pages\CreateCoupon;
use App\Filament\Vendeur\Resources\Coupons\Pages\EditCoupon;
use App\Filament\Vendeur\Resources\Coupons\Pages\ListCoupons;
use App\Filament\Vendeur\Resources\Coupons\Schemas\CouponForm;
use App\Filament\Vendeur\Resources\Coupons\Tables\CouponsTable;
use App\Models\Coupon;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class CouponResource extends Resource
{
    protected static ?string $model = Coupon::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedTicket;

    protected static ?string $cluster = InventorCluster::class;

    protected static ?string $recordTitleAttribute = 'nom';

    protected static bool $isScopedToTenant = false;

    public static function getNavigationBadge(): ?string
    {
        return static::getModel()::count();
    }

    public static function getNavigationBadgeColor(): string|array|null
    {
        return static::getModel()::count() > 10 ? 'success' : 'warning';
    }

    public static function form(Schema $schema): Schema
    {
        return CouponForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return CouponsTable::configure($table);
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
            'index' => ListCoupons::route('/'),
            'create' => CreateCoupon::route('/create'),
            'edit' => EditCoupon::route('/{record}/edit'),
        ];
    }

    public static function getRecordRouteBindingEloquentQuery(): Builder
    {
        return parent::getRecordRouteBindingEloquentQuery()
            ->withoutGlobalScopes([
                SoftDeletingScope::class,
            ]);
    }
}
