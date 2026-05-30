<?php

namespace App\Filament\Resources\TenantDocumentLegals;

use App\Enums\NavigationGroup;
use App\Filament\Resources\TenantDocumentLegals\Pages\CreateTenantDocumentLegal;
use App\Filament\Resources\TenantDocumentLegals\Pages\EditTenantDocumentLegal;
use App\Filament\Resources\TenantDocumentLegals\Pages\ListTenantDocumentLegals;
use App\Filament\Resources\TenantDocumentLegals\Schemas\TenantDocumentLegalForm;
use App\Filament\Resources\TenantDocumentLegals\Tables\TenantDocumentLegalsTable;
use App\Models\TenantDocumentLegal;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use UnitEnum;

class TenantDocumentLegalResource extends Resource
{
    protected static ?string $model = TenantDocumentLegal::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedRectangleStack;

    protected static ?string $recordTitleAttribute = 'numero_document';

    protected static ?string $navigationLabel = 'Tenant Document';

    protected static string|UnitEnum|null $navigationGroup = NavigationGroup::Organisation;

    protected static ?int $navigationSort = 3;

    public static function form(Schema $schema): Schema
    {
        return TenantDocumentLegalForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return TenantDocumentLegalsTable::configure($table);
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
            'index' => ListTenantDocumentLegals::route('/'),
            'create' => CreateTenantDocumentLegal::route('/create'),
            'edit' => EditTenantDocumentLegal::route('/{record}/edit'),
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
