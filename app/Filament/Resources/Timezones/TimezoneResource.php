<?php

namespace App\Filament\Resources\Timezones;

use App\Enums\NavigationGroup;
use App\Filament\Resources\Timezones\Pages\CreateTimezone;
use App\Filament\Resources\Timezones\Pages\EditTimezone;
use App\Filament\Resources\Timezones\Pages\ListTimezones;
use App\Filament\Resources\Timezones\Schemas\TimezoneForm;
use App\Filament\Resources\Timezones\Tables\TimezonesTable;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Nnjeim\World\Models\Timezone;
use UnitEnum;

class TimezoneResource extends Resource
{
    protected static ?string $model = Timezone::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedRectangleStack;

    protected static string|UnitEnum|null $navigationGroup = NavigationGroup::World;

    protected static ?string $recordTitleAttribute = 'name';

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
        return TimezoneForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return TimezonesTable::configure($table);
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
            'index' => ListTimezones::route('/'),
            'create' => CreateTimezone::route('/create'),
            'edit' => EditTimezone::route('/{record}/edit'),
        ];
    }
}
