<?php

namespace App\Filament\Vendeur\Resources\Comments;

use App\Enums\NavigationGroup;
use App\Filament\Vendeur\Resources\Comments\Pages\CreateComment;
use App\Filament\Vendeur\Resources\Comments\Pages\EditComment;
use App\Filament\Vendeur\Resources\Comments\Pages\ListComments;
use App\Filament\Vendeur\Resources\Comments\Schemas\CommentForm;
use App\Filament\Vendeur\Resources\Comments\Tables\CommentsTable;
use App\Models\Comment;
use App\Models\Post;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use UnitEnum;

class CommentResource extends Resource
{
    protected static ?string $model = Comment::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedRectangleStack;

    protected static ?string $recordTitleAttribute = 'user_id';

    protected static string|UnitEnum|null $navigationGroup = NavigationGroup::Share;

    protected static bool $isScopedToTenant = false;

    public static function getNavigationBadge(): ?string
    {
        return static::getModel()::where('status', 'pending')
            ->where('commentable_type', Post::class)
            ->count();
    }

    public static function getNavigationBadgeColor(): string|array|null
    {
        return static::getModel()::count() > 10 ? 'danger' : 'warning';
    }

    public static function form(Schema $schema): Schema
    {
        return CommentForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return CommentsTable::configure($table);
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
            'index' => ListComments::route('/'),
            'create' => CreateComment::route('/create'),
            'edit' => EditComment::route('/{record}/edit'),
        ];
    }

    public static function getRecordRouteBindingEloquentQuery(): Builder
    {
        return parent::getRecordRouteBindingEloquentQuery()
            ->withoutGlobalScopes([
                SoftDeletingScope::class,
            ]);
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->where('commentable_type', Post::class)
            ->with(['user', 'commentable']);
    }
}
