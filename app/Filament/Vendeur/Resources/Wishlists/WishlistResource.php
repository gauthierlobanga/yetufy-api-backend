<?php

namespace App\Filament\Vendeur\Resources\Wishlists;

use App\Filament\Vendeur\Clusters\Wishlists\WishlistsCluster;
use App\Filament\Vendeur\Resources\Wishlists\Pages\CreateWishlist;
use App\Filament\Vendeur\Resources\Wishlists\Pages\EditWishlist;
use App\Filament\Vendeur\Resources\Wishlists\Pages\ListWishlists;
use App\Filament\Vendeur\Resources\Wishlists\Schemas\WishlistForm;
use App\Filament\Vendeur\Resources\Wishlists\Tables\WishlistsTable;
use App\Models\Wishlist;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class WishlistResource extends Resource
{
    protected static ?string $model = Wishlist::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedBookmark;

    protected static ?string $recordTitleAttribute = 'nom';

    protected static ?int $navigationSort = 1;

    protected static ?string $cluster = WishlistsCluster::class;

    protected static bool $isScopedToTenant = false;

    public static function form(Schema $schema): Schema
    {
        return WishlistForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return WishlistsTable::configure($table);
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
            'index' => ListWishlists::route('/'),
            'create' => CreateWishlist::route('/create'),
            'edit' => EditWishlist::route('/{record}/edit'),
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
