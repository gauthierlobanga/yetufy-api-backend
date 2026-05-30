<?php

namespace App\Filament\Resources\CommentMentions;

use App\Enums\NavigationGroup;
use App\Filament\Resources\CommentMentions\Pages\CreateCommentMention;
use App\Filament\Resources\CommentMentions\Pages\EditCommentMention;
use App\Filament\Resources\CommentMentions\Pages\ListCommentMentions;
use App\Filament\Resources\CommentMentions\Schemas\CommentMentionForm;
use App\Filament\Resources\CommentMentions\Tables\CommentMentionsTable;
use App\Models\CommentMention;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use UnitEnum;

class CommentMentionResource extends Resource
{
    protected static ?string $model = CommentMention::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedRectangleStack;

    protected static ?string $recordTitleAttribute = 'comment_id';

    protected static ?string $navigationLabel = 'Mentions';

    protected static string|UnitEnum|null $navigationGroup = NavigationGroup::Share;

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
        return CommentMentionForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return CommentMentionsTable::configure($table);
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
            'index' => ListCommentMentions::route('/'),
            'create' => CreateCommentMention::route('/create'),
            'edit' => EditCommentMention::route('/{record}/edit'),
        ];
    }
}
