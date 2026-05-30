<?php

namespace App\Filament\Resources\CommentReports;

use App\Enums\NavigationGroup;
use App\Filament\Resources\CommentReports\Pages\CreateCommentReport;
use App\Filament\Resources\CommentReports\Pages\EditCommentReport;
use App\Filament\Resources\CommentReports\Pages\ListCommentReports;
use App\Filament\Resources\CommentReports\Schemas\CommentReportForm;
use App\Filament\Resources\CommentReports\Tables\CommentReportsTable;
use App\Models\CommentReport;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use UnitEnum;

class CommentReportResource extends Resource
{
    protected static ?string $model = CommentReport::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedRectangleStack;

    protected static ?string $recordTitleAttribute = 'status';

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
        return CommentReportForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return CommentReportsTable::configure($table);
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
            'index' => ListCommentReports::route('/'),
            'create' => CreateCommentReport::route('/create'),
            'edit' => EditCommentReport::route('/{record}/edit'),
        ];
    }
}
