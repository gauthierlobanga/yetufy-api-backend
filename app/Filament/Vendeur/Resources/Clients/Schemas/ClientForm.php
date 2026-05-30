<?php

namespace App\Filament\Vendeur\Resources\Clients\Schemas;

use Filament\Forms\Components\KeyValue;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Components\ToggleButtons;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Group;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Illuminate\Support\Number;

class ClientForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->columns(3)
            ->components([
                Group::make()
                    ->columnSpanFull()
                    ->schema([
                        Section::make('Informations personnelles')
                            ->icon('heroicon-o-user')
                            ->schema([
                                Grid::make(3)
                                    ->schema([
                                        Select::make('user_id')
                                            ->label('Compte utilisateur')
                                            ->relationship('user', 'name', fn ($query) => $query->select('id', 'name', 'email'))
                                            ->searchable(['name', 'email'])
                                            ->preload()
                                            ->helperText('Lier à un compte utilisateur existant'),
                                        Select::make('civilite')
                                            ->label('Civilité')
                                            ->options([
                                                'M.' => 'Monsieur',
                                                'Mme' => 'Madame',
                                                'Mlle' => 'Mademoiselle',
                                            ])
                                            ->preload()
                                            ->searchable()
                                            ->placeholder('Sélectionner une civilité'),
                                        ToggleButtons::make('type')
                                            ->label('Type de client')
                                            ->options([
                                                'particulier' => 'Particulier',
                                                'professionnel' => 'Prof',
                                                'entreprise' => 'Entreprise',
                                            ])
                                            ->colors([
                                                'particulier' => 'primary',
                                                'professionnel' => 'warning',
                                                'entreprise' => 'success',
                                            ])
                                            ->icons([
                                                'particulier' => 'heroicon-o-user',
                                                'professionnel' => 'heroicon-o-briefcase',
                                                'entreprise' => 'heroicon-o-building-office',
                                            ])
                                            ->inline()
                                            ->default('particulier')
                                            ->required()
                                            ->live()
                                            ->afterStateUpdated(function ($state, callable $set) {
                                                if ($state !== 'entreprise') {
                                                    $set('societe', null);
                                                    $set('siret', null);
                                                }
                                            }),

                                    ]),
                                Grid::make(2)
                                    ->schema([
                                        TextInput::make('nom')
                                            ->label('Nom')
                                            ->required()
                                            ->maxLength(255)
                                            ->placeholder('Dupont'),

                                        TextInput::make('prenom')
                                            ->label('Prénom')
                                            ->maxLength(255)
                                            ->placeholder('Jean')
                                            ->visible(fn ($get) => $get('type') !== 'entreprise'),

                                        TextInput::make('societe')
                                            ->label('Société')
                                            ->maxLength(255)
                                            ->placeholder('Dupont SAS')
                                            ->visible(fn ($get) => in_array($get('type'), ['professionnel', 'entreprise']))
                                            ->required(fn ($get) => $get('type') === 'entreprise'),

                                        TextInput::make('siret')
                                            ->label('Numéro SIRET')
                                            ->maxLength(14)
                                            ->placeholder('12345678901234')
                                            ->helperText('Format à 14 chiffres')
                                            ->visible(fn ($get) => in_array($get('type'), ['professionnel', 'entreprise'])),

                                        TextInput::make('email')
                                            ->label('Email')
                                            ->email()
                                            ->maxLength(255)
                                            ->placeholder('client@exemple.com')
                                            ->prefixIcon('heroicon-m-envelope'),

                                        TextInput::make('telephone')
                                            ->label('Téléphone')
                                            ->tel()
                                            ->maxLength(20)
                                            ->placeholder('+33 1 23 45 67 89')
                                            ->prefixIcon('heroicon-m-phone'),
                                    ]),
                            ]),
                    ]),

                Group::make()
                    ->columnSpan(1)
                    ->schema([
                        Section::make('Statistiques')
                            ->icon('heroicon-o-chart-bar')
                            ->visible(fn ($record) => $record !== null)
                            ->schema([
                                TextInput::make('date_premier_achat')
                                    ->label('Premier achat')
                                    ->disabled()
                                    ->dehydrated(false)
                                    ->formatStateUsing(fn ($record) => $record?->date_premier_achat?->format('d/m/Y') ?? 'Jamais'),

                                TextInput::make('date_dernier_achat')
                                    ->label('Dernier achat')
                                    ->disabled()
                                    ->dehydrated(false)
                                    ->formatStateUsing(fn ($record) => $record?->date_dernier_achat?->format('d/m/Y') ?? 'Jamais'),

                                TextInput::make('total_achats')
                                    ->label('Total des achats')
                                    ->disabled()
                                    ->dehydrated(false)
                                    ->formatStateUsing(fn ($record) => Number::currency($record?->total_achats ?? 0, 'EUR')),

                                TextInput::make('nombre_commandes')
                                    ->label('Nombre de commandes')
                                    ->disabled()
                                    ->dehydrated(false)
                                    ->formatStateUsing(fn ($record) => $record?->nombre_commandes ?? 0),

                                TextInput::make('panier_moyen')
                                    ->label('Panier moyen')
                                    ->disabled()
                                    ->dehydrated(false)
                                    ->formatStateUsing(fn ($record) => Number::currency(
                                        $record?->nombre_commandes > 0
                                            ? ($record->total_achats / $record->nombre_commandes)
                                            : 0,
                                        'EUR'
                                    )),
                            ]),
                    ]),

                Group::make()
                    ->columnSpanFull()
                    ->schema([
                        Section::make('Adresses')
                            ->icon('heroicon-o-map-pin')
                            ->collapsible()
                            ->schema([
                                Repeater::make('adresses')
                                    ->label('Adresses')
                                    ->relationship('adresses')
                                    ->schema([
                                        Grid::make(2)
                                            ->schema([
                                                Select::make('type')
                                                    ->label('Type d\'adresse')
                                                    ->options([
                                                        'facturation' => 'Facturation',
                                                        'livraison' => 'Livraison',
                                                    ])
                                                    ->required(),

                                                Toggle::make('est_defaut')
                                                    ->label('Adresse par défaut'),

                                                TextInput::make('rue')
                                                    ->label('Rue')
                                                    ->required()
                                                    ->maxLength(255),

                                                TextInput::make('complement')
                                                    ->label('Complément')
                                                    ->maxLength(255),

                                                TextInput::make('code_postal')
                                                    ->label('Code postal')
                                                    ->required()
                                                    ->maxLength(20),

                                                TextInput::make('ville')
                                                    ->label('Ville')
                                                    ->required()
                                                    ->maxLength(255),

                                                TextInput::make('pays')
                                                    ->label('Pays')
                                                    ->required()
                                                    ->maxLength(255)
                                                    ->default('France'),

                                                TextInput::make('telephone')
                                                    ->label('Téléphone')
                                                    ->tel()
                                                    ->maxLength(20),

                                                Textarea::make('instructions')
                                                    ->label('Instructions')
                                                    ->rows(2)
                                                    ->maxLength(500),
                                            ]),
                                    ])
                                    ->columns(1)
                                    ->addActionLabel('Ajouter une adresse')
                                    ->defaultItems(0)
                                    ->collapsible(),
                            ]),

                        Section::make('Préférences')
                            ->icon('heroicon-o-cog-6-tooth')
                            ->collapsible()
                            ->collapsed()
                            ->schema([
                                KeyValue::make('preferences')
                                    ->label('Préférences personnalisées')
                                    ->keyLabel('Clé')
                                    ->valueLabel('Valeur')
                                    ->addActionLabel('Ajouter une préférence')
                                    ->reorderable()
                                    ->helperText('Ex: newsletter, marketing, notifications'),

                                KeyValue::make('metadata')
                                    ->label('Métadonnées')
                                    ->keyLabel('Clé')
                                    ->valueLabel('Valeur')
                                    ->addActionLabel('Ajouter une métadonnée')
                                    ->reorderable(),
                            ]),
                    ]),
            ]);
    }
}
