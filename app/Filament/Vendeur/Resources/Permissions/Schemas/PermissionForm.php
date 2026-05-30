<?php

namespace App\Filament\Vendeur\Resources\Permissions\Schemas;

use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class PermissionForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->columns(2)
            ->components([
                Section::make('Informations de la permission')
                    ->icon('heroicon-o-shield-check')
                    ->columnSpanFull()
                    ->schema([
                        Grid::make(2)
                            ->schema([
                                TextInput::make('name')
                                    ->label('Nom de la permission')
                                    ->required()
                                    ->maxLength(255)
                                    ->placeholder('Ex: view_users, create_posts, delete_products')
                                    ->helperText('Format recommandé: action_ressource (ex: view_users)')
                                    ->live(onBlur: true)
                                    ->afterStateUpdated(function ($state, callable $set) {
                                        // Normaliser le nom
                                        $normalized = strtolower(trim($state));
                                        $normalized = preg_replace('/[^a-z0-9_]/', '_', $normalized);
                                        $set('name', $normalized);
                                    }),

                                Select::make('guard_name')
                                    ->label('Guard')
                                    ->options([
                                        'web' => 'Web',
                                        'api' => 'API',
                                        'sanctum' => 'Sanctum',
                                    ])
                                    ->default('web')
                                    ->required()
                                    ->helperText('Guard d\'authentification associé'),
                            ]),
                    ]),
            ]);
    }
}
