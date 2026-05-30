<?php

namespace App\Filament\Vendeur\Resources\WishlistItems;

use App\Filament\Vendeur\Clusters\Wishlists\WishlistsCluster;
use App\Filament\Vendeur\Resources\WishlistItems\Pages\CreateWishlistItem;
use App\Filament\Vendeur\Resources\WishlistItems\Pages\EditWishlistItem;
use App\Filament\Vendeur\Resources\WishlistItems\Pages\ListWishlistItems;
use App\Filament\Vendeur\Resources\WishlistItems\Schemas\WishlistItemForm;
use App\Filament\Vendeur\Resources\WishlistItems\Tables\WishlistItemsTable;
use App\Models\WishlistItem;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class WishlistItemResource extends Resource
{
    protected static ?string $model = WishlistItem::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedSquaresPlus;

    protected static ?string $recordTitleAttribute = 'wishlist_id';

    protected static ?int $navigationSort = 2;

    protected static ?string $cluster = WishlistsCluster::class;

    protected static bool $isScopedToTenant = false;

    public static function form(Schema $schema): Schema
    {
        return WishlistItemForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return WishlistItemsTable::configure($table);
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
            'index' => ListWishlistItems::route('/'),
            'create' => CreateWishlistItem::route('/create'),
            'edit' => EditWishlistItem::route('/{record}/edit'),
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
