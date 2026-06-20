<?php

namespace App\Filament\Resources\Vendeurs\Schemas;

use App\Models\Tenant;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\KeyValue;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\SpatieMediaLibraryFileUpload;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Illuminate\Support\Str;

class VendeurForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                SpatieMediaLibraryFileUpload::make('tenant_avatar')
                    ->label('Logo de la boutique')
                    ->image()
                    ->collection('tenant_avatar')
                    ->disk('public')
                    ->visibility('public')
                    ->directory('tenants/avatars')
                    ->acceptedFileTypes(['image/jpeg', 'image/png', 'image/webp', 'image/svg+xml'])
                    ->columnSpanFull(),

                Section::make('Informations générales')
                    ->schema([
                        TextInput::make('raison_sociale')
                            ->required()
                            ->maxLength(255)
                            ->live(onBlur: true)
                            ->afterStateUpdated(fn ($state, callable $set) => $set('slug', Str::slug($state))),

                        TextInput::make('slug')
                            ->required()
                            ->maxLength(255)
                            ->unique(ignoreRecord: true),

                        Select::make('type_entite')
                            ->label('Type d\'entité')
                            ->options(Tenant::getTypesEntite())
                            ->preload()
                            ->searchable()
                            ->required(),

                        TextInput::make('email')
                            ->email()
                            ->maxLength(255)
                            ->unique(ignoreRecord: true),

                        TextInput::make('password')
                            ->password()
                            ->revealable()
                            ->minLength(8)
                            ->dehydrated(fn ($state) => filled($state)) // ne pas écraser avec une chaîne vide
                            ->required(fn (string $operation): bool => $operation === 'create'),

                        Select::make('statut')
                            ->options(Tenant::getStatuts())
                            ->preload()
                            ->searchable()
                            ->default('en_attente')
                            ->required()
                            ->live()
                            ->afterStateUpdated(function ($state, callable $set, callable $get) {
                                // Synchroniser l'état actif/inactif avec le statut
                                if ($state === 'actif') {
                                    $set('is_active', true);
                                    // Définir automatiquement la date d'activation si elle n'existe pas encore
                                    if (! $get('date_activation')) {
                                        $set('date_activation', now()->format('Y-m-d H:i:s'));
                                    }
                                } elseif (in_array($state, ['inactif', 'suspendu'])) {
                                    $set('is_active', false);
                                } else {
                                    // en_attente : laisser l'utilisateur choisir
                                }
                            })
                            ->helperText('Définit le statut opérationnel de la boutique.'),

                    ])
                    ->columns(2),
                Section::make('Informations générales')
                    ->schema([

                        TextInput::make('telephone')
                            ->tel()
                            ->maxLength(255),

                        TextInput::make('domain')
                            // ->url()
                            ->label('Domain')
                            ->prefix('http://')
                            ->prefixIcon(Heroicon::OutlinedGlobeAlt)
                            ->prefixIconColor('success')
                            ->suffix('.com')
                            ->maxLength(255)
                            ->helperText('Ex: maboutique.cd (laissez vide pour utiliser le sous-domaine automatique)'),
                    ])
                    ->columns(1),

                Section::make('Statut & Activation')
                    ->icon('heroicon-o-cog-8-tooth')
                    ->description('Gérez le cycle de vie du compte vendeur.')
                    ->schema([
                        DateTimePicker::make('date_activation')
                            ->label('Date d’activation')
                            ->native(false)
                            ->displayFormat('d/m/Y H:i')
                            ->visible(fn (callable $get) => $get('statut') === 'actif')
                            ->helperText('Date à laquelle la boutique a été activée (remplie automatiquement si laissée vide).'),

                        DateTimePicker::make('date_expiration')
                            ->label('Date d’expiration')
                            ->native(false)
                            ->displayFormat('d/m/Y H:i')
                            ->afterOrEqual('date_activation')
                            ->visible(fn (callable $get) => in_array($get('statut'), ['actif', 'suspendu']))
                            ->helperText('Optionnel. La boutique sera automatiquement suspendue après cette date.'),

                        Toggle::make('is_active')
                            ->label('Compte actif')
                            ->default(false)
                            ->live()
                            ->onIcon('heroicon-o-check-circle')
                            ->offIcon('heroicon-o-x-circle')
                            ->onColor('success')
                            ->offColor('danger')
                            ->helperText('Active ou désactive immédiatement l’accès à la boutique.'),

                    ]),

                Section::make('Configuration avancée')
                    ->schema([
                        KeyValue::make('configuration')
                            ->label('Paramètres personnalisés')
                            ->keyLabel('Clé')
                            ->valueLabel('Valeur')
                            ->addActionLabel('Ajouter un paramètre'),
                    ]),
            ]);
    }
}
