<?php

namespace App\Filament\Vendeur\Resources\Users\Schemas;

use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\SpatieMediaLibraryFileUpload;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Group;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Illuminate\Support\Facades\Hash;

class UserForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->columns(3)
            ->components([
                Group::make()
                    ->columnSpan(2)
                    ->components([
                        Section::make('Informations personnelles')
                            ->icon('heroicon-o-user')
                            ->components([
                                Grid::make(2)
                                    ->components([
                                        TextInput::make('name')
                                            ->label('Nom complet')
                                            ->required()
                                            ->maxLength(255)
                                            ->placeholder('Gauthier Lobanga')
                                            ->autofocus(),

                                        TextInput::make('email')
                                            ->label('Adresse email')
                                            ->email()
                                            ->required()
                                            ->maxLength(255)
                                            ->unique(ignoreRecord: true)
                                            ->placeholder('gauthier@exemple.com')
                                            ->suffixIcon('heroicon-m-envelope'),
                                    ]),

                                Grid::make(2)
                                    ->components([

                                        TextInput::make('first_name')
                                            ->label('Nom')
                                            ->required()
                                            ->maxLength(255)
                                            ->placeholder('Lobanga')
                                            ->autofocus(),

                                        TextInput::make('last_name')
                                            ->label('Prénom')
                                            ->maxLength(100)
                                            ->placeholder('Gauthier'),

                                    ]),
                            ]),

                        Section::make('Avatar')
                            ->icon('heroicon-o-photo')
                            ->components([
                                SpatieMediaLibraryFileUpload::make('avatar')
                                    ->label('Photo de profil')
                                    ->collection('avatar')
                                    ->avatar()
                                    ->imageEditor()
                                    ->circleCropper()
                                    ->disk('public')
                                    ->maxSize(2048)
                                    ->acceptedFileTypes(['image/jpeg', 'image/png', 'image/webp'])
                                    ->columnSpanFull(),
                            ]),
                    ]),

                Group::make()
                    ->columnSpan(1)
                    ->components([
                        Section::make('Authentification')
                            ->icon('heroicon-o-lock-closed')
                            ->components([
                                // Dans la section Authentification
                                TextInput::make('password')
                                    ->label('Mot de passe')
                                    ->password()
                                    ->revealable()
                                    ->minLength(8)
                                    ->same('passwordConfirmation')
                                    ->dehydrated(fn ($state) => filled($state))
                                    ->dehydrateStateUsing(fn ($state) => Hash::make($state))
                                    ->required(fn (string $operation): bool => $operation === 'create')
                                    ->placeholder('••••••••')
                                    ->helperText(fn (string $operation) => $operation === 'edit' ? 'Laissez vide pour conserver le mot de passe actuel.' : null)
                                    ->live(onBlur: true),

                                TextInput::make('passwordConfirmation')
                                    ->label('Confirmer le mot de passe')
                                    ->password()
                                    ->revealable()
                                    ->dehydrated(false)
                                    ->required(fn (string $operation): bool => $operation === 'create')
                                    ->visible(fn (callable $get) => filled($get('password'))),
                            ]),

                        Section::make('Statut du compte')
                            ->icon('heroicon-o-shield-check')
                            ->components([
                                Toggle::make('is_active')
                                    ->label('Compte activé')
                                    ->default(true)
                                    ->helperText('Désactiver pour bloquer l\'accès à l\'utilisateur')
                                    ->onColor('success')
                                    ->offColor('danger'),

                                DateTimePicker::make('email_verified_at')
                                    ->label('Email vérifié le')
                                    ->native(false)
                                    ->displayFormat('d/m/Y H:i')
                                    ->placeholder('Non vérifié'),
                            ]),

                        Section::make('Rôles')
                            ->icon('heroicon-o-shield-exclamation')
                            ->components([
                                Select::make('roles')
                                    ->relationship('roles', 'name')
                                    ->multiple()
                                    ->preload()
                                    ->searchable()
                                    ->native(false)
                                    ->placeholder('Sélectionner des rôles')
                                    ->helperText('Attribuer des rôles à l\'utilisateur'),
                            ]),
                    ]),

                Textarea::make('two_factor_secret')
                    ->columnSpanFull()
                    ->hidden(),

                Textarea::make('two_factor_recovery_codes')
                    ->columnSpanFull()
                    ->hidden(),

                DateTimePicker::make('two_factor_confirmed_at')
                    ->hidden(),
            ]);
    }
}
