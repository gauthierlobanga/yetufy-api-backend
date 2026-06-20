<?php

namespace App\Filament\Vendeur\Resources\CampagneMarketings\Schemas;

use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\KeyValue;
use Filament\Forms\Components\MarkdownEditor;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TagsInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Group;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class CampagneMarketingForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->columns(3)
            ->components([
                Group::make()
                    ->columnSpan(2)
                    ->schema([
                        Section::make('Informations générales')
                            ->icon('heroicon-o-megaphone')
                            ->schema([
                                Grid::make(2)
                                    ->schema([

                                        TextInput::make('nom')
                                            ->label('Nom de la campagne')
                                            ->required()
                                            ->maxLength(255)
                                            ->placeholder('Soldes d\'été 2024')
                                            ->live(onBlur: true),

                                        Select::make('type')
                                            ->label('Type de campagne')
                                            ->options([
                                                'newsletter' => 'Newsletter',
                                                'promotion' => 'Promotion',
                                                'saisonniere' => 'Saisonnière',
                                                'relance' => 'Relance',
                                                'fidelisation' => 'Fidélisation',
                                            ])
                                            ->required()
                                            ->default('promotion')
                                            ->searchable(),

                                        Select::make('canal')
                                            ->label('Canal de diffusion')
                                            ->options([
                                                'email' => 'Email',
                                                'sms' => 'SMS',
                                                'reseaux' => 'Réseaux sociaux',
                                                'push' => 'Notification push',
                                                'affiliation' => 'Affiliation',
                                            ])
                                            ->required()
                                            ->default('email')
                                            ->searchable(),

                                        Select::make('statut')
                                            ->label('Statut')
                                            ->options([
                                                'planifiee' => 'Planifiée',
                                                'active' => 'Active',
                                                'terminee' => 'Terminée',
                                                'annulee' => 'Annulée',
                                                'en_pause' => 'En pause',
                                            ])
                                            ->required()
                                            ->default('planifiee')
                                            ->live()
                                            ->afterStateUpdated(function ($state, callable $set) {
                                                if ($state === 'active') {
                                                    $set('date_debut', now());
                                                } elseif ($state === 'terminee') {
                                                    $set('date_fin', now());
                                                }
                                            }),
                                    ]),
                            ]),
                    ]),

                Group::make()
                    ->columnSpan(1)
                    ->schema([
                        Section::make('Planning')
                            ->icon('heroicon-o-calendar')
                            ->schema([
                                DateTimePicker::make('date_debut')
                                    ->label('Date de début')
                                    ->native(false)
                                    ->displayFormat('d/m/Y H:i')
                                    ->seconds(false)
                                    ->live(),

                                DateTimePicker::make('date_fin')
                                    ->label('Date de fin')
                                    ->native(false)
                                    ->displayFormat('d/m/Y H:i')
                                    ->seconds(false)
                                    ->live()
                                    ->afterStateUpdated(function ($state, callable $set, callable $get) {
                                        $debut = $get('date_debut');
                                        if ($state && $debut && $state < $debut) {
                                            $set('date_fin', null);
                                        }
                                    }),

                                TextInput::make('budget')
                                    ->label('Budget (€)')
                                    ->numeric()
                                    ->step(0.01)
                                    ->minValue(0)
                                    ->prefix('€')
                                    ->placeholder('0.00'),
                            ]),
                    ]),

                Group::make()
                    ->columnSpanFull()
                    ->schema([
                        Section::make('Ciblage')
                            ->icon('heroicon-o-user-group')
                            ->collapsible()
                            ->schema([
                                Grid::make(2)
                                    ->schema([
                                        TagsInput::make('cible.segments')
                                            ->label('Segments clients')
                                            ->placeholder('Ajouter un segment')
                                            ->suggestions([
                                                'nouveaux_clients',
                                                'clients_actifs',
                                                'clients_inactifs',
                                                'vip',
                                                'panier_abandonne',
                                                'premier_achat',
                                                'anniversaire',
                                            ])
                                            ->helperText('Segments de clients ciblés par la campagne'),

                                        TagsInput::make('cible.exclusions')
                                            ->label('Exclusions')
                                            ->placeholder('Ajouter une exclusion')
                                            ->suggestions([
                                                'clients_sans_email',
                                                'clients_desabonnes',
                                                'test',
                                            ]),

                                        KeyValue::make('cible.filtres')
                                            ->label('Filtres personnalisés')
                                            ->keyLabel('Critère')
                                            ->valueLabel('Valeur')
                                            ->columnSpanFull()
                                            ->addActionLabel('Ajouter un filtre')
                                            ->helperText('Ex: total_achats > 1000, date_dernier_achat < 30'),
                                    ]),
                            ]),

                        Section::make('Contenu')
                            ->icon('heroicon-o-document-text')
                            ->collapsible()
                            ->schema([
                                MarkdownEditor::make('statistiques.contenu_email')
                                    ->label('Contenu de l\'email')
                                    ->toolbarButtons([
                                        'bold',
                                        'bulletList',
                                        'heading',
                                        'italic',
                                        'link',
                                        'orderedList',
                                        'redo',
                                        'undo',
                                    ])
                                    ->columnSpanFull(),

                                Textarea::make('statistiques.contenu_sms')
                                    ->label('Contenu SMS')
                                    ->rows(3)
                                    ->maxLength(160)
                                    ->helperText('160 caractères maximum'),

                                KeyValue::make('statistiques.variables')
                                    ->label('Variables personnalisées')
                                    ->keyLabel('Variable')
                                    ->valueLabel('Valeur')
                                    ->addActionLabel('Ajouter une variable')
                                    ->helperText('Variables pour personnaliser les messages (ex: {nom_client}, {code_promo})'),
                            ]),

                        Section::make('Statistiques et suivi')
                            ->icon('heroicon-o-chart-bar')
                            ->collapsible()
                            ->collapsed()
                            ->schema([
                                Grid::make(3)
                                    ->schema([
                                        TextInput::make('statistiques.envois')
                                            ->label('Nombre d\'envois')
                                            ->numeric()
                                            ->default(0)
                                            ->disabled()
                                            ->dehydrated(false),

                                        TextInput::make('statistiques.ouvertures')
                                            ->label('Ouvertures')
                                            ->numeric()
                                            ->default(0)
                                            ->disabled()
                                            ->dehydrated(false),

                                        TextInput::make('statistiques.clics')
                                            ->label('Clics')
                                            ->numeric()
                                            ->default(0)
                                            ->disabled()
                                            ->dehydrated(false),

                                        TextInput::make('statistiques.conversions')
                                            ->label('Conversions')
                                            ->numeric()
                                            ->default(0)
                                            ->disabled()
                                            ->dehydrated(false),

                                        TextInput::make('statistiques.revenus')
                                            ->label('Revenus générés')
                                            ->numeric()
                                            ->step(0.01)
                                            ->prefix('€')
                                            ->default(0)
                                            ->disabled()
                                            ->dehydrated(false),

                                        TextInput::make('statistiques.taux_ouverture')
                                            ->label('Taux d\'ouverture (%)')
                                            ->numeric()
                                            ->step(0.01)
                                            ->default(0)
                                            ->disabled()
                                            ->dehydrated(false),

                                        TextInput::make('statistiques.taux_clic')
                                            ->label('Taux de clic (%)')
                                            ->numeric()
                                            ->step(0.01)
                                            ->default(0)
                                            ->disabled()
                                            ->dehydrated(false),

                                        TextInput::make('statistiques.taux_conversion')
                                            ->label('Taux de conversion (%)')
                                            ->numeric()
                                            ->step(0.01)
                                            ->default(0)
                                            ->disabled()
                                            ->dehydrated(false),

                                        TextInput::make('statistiques.retour_investissement')
                                            ->label('ROI (%)')
                                            ->numeric()
                                            ->step(0.01)
                                            ->default(0)
                                            ->disabled()
                                            ->dehydrated(false),
                                    ]),
                            ]),

                        Section::make('Métadonnées')
                            ->icon('heroicon-o-code-bracket')
                            ->collapsible()
                            ->collapsed()
                            ->schema([
                                KeyValue::make('metadata')
                                    ->label('Métadonnées')
                                    ->keyLabel('Clé')
                                    ->valueLabel('Valeur')
                                    ->addActionLabel('Ajouter')
                                    ->reorderable(),
                            ]),
                    ]),
            ]);
    }
}
