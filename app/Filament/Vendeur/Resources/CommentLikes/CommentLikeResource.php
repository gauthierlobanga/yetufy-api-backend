<?php

namespace App\Filament\Vendeur\Resources\CommentLikes;

use App\Enums\NavigationGroup;
use App\Filament\Vendeur\Resources\CommentLikes\Pages\CreateCommentLike;
use App\Filament\Vendeur\Resources\CommentLikes\Pages\EditCommentLike;
use App\Filament\Vendeur\Resources\CommentLikes\Pages\ListCommentLikes;
use App\Filament\Vendeur\Resources\CommentLikes\Schemas\CommentLikeForm;
use App\Filament\Vendeur\Resources\CommentLikes\Tables\CommentLikesTable;
use App\Models\CommentLike;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use UnitEnum;

class CommentLikeResource extends Resource
{
    protected static ?string $model = CommentLike::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedRectangleStack;

    protected static ?string $recordTitleAttribute = 'type';

    protected static string|UnitEnum|null $navigationGroup = NavigationGroup::Share;

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
        return CommentLikeForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return CommentLikesTable::configure($table);
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
            'index' => ListCommentLikes::route('/'),
            'create' => CreateCommentLike::route('/create'),
            'edit' => EditCommentLike::route('/{record}/edit'),
        ];
    }
}
