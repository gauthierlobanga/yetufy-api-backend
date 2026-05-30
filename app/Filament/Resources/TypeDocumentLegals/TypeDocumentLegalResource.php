<?php

namespace App\Filament\Resources\TypeDocumentLegals;

use App\Enums\NavigationGroup;
use App\Filament\Resources\TypeDocumentLegals\Pages\CreateTypeDocumentLegal;
use App\Filament\Resources\TypeDocumentLegals\Pages\EditTypeDocumentLegal;
use App\Filament\Resources\TypeDocumentLegals\Pages\ListTypeDocumentLegals;
use App\Filament\Resources\TypeDocumentLegals\Schemas\TypeDocumentLegalForm;
use App\Filament\Resources\TypeDocumentLegals\Tables\TypeDocumentLegalsTable;
use App\Models\TypeDocumentLegal;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use UnitEnum;

class TypeDocumentLegalResource extends Resource
{
    protected static ?string $model = TypeDocumentLegal::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedRectangleStack;

    protected static ?string $recordTitleAttribute = 'code';

    protected static ?string $navigationLabel = 'Type Document';

    protected static string|UnitEnum|null $navigationGroup = NavigationGroup::Organisation;

    protected static ?int $navigationSort = 2;

    public static function form(Schema $schema): Schema
    {
        return TypeDocumentLegalForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return TypeDocumentLegalsTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [

        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListTypeDocumentLegals::route('/'),
            'create' => CreateTypeDocumentLegal::route('/create'),
            'edit' => EditTypeDocumentLegal::route('/{record}/edit'),
        ];
    }

    public static function getNavigationBadge(): ?string
    {
        return static::getModel()::count();
    }

    public static function getNavigationBadgeColor(): ?string
    {
        return static::getModel()::count() > 10 ? 'success' : 'danger';
    }
}
