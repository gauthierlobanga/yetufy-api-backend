<?php

namespace App\Filament\Resources\CategoriePostPivots\Schemas;

use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;

class CategoriePostPivotForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Select::make('post_id')
                    ->relationship('post', 'title')
                    ->preload()
                    ->searchable()
                    ->required(),
                Toggle::make('est_principale')
                    ->required(),
                TextInput::make('order')
                    ->required()
                    ->numeric()
                    ->default(0),
                Select::make('category_id')
                    ->relationship('categorie', 'nom')
                    ->preload()
                    ->searchable()
                    ->required(),
                Toggle::make('is_primary')

                    ->required(),
            ]);
    }
}
