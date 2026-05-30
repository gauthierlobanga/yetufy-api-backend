<?php

namespace App\Filament\Vendeur\Resources\RelancePaniers\Schemas;

use App\Models\AbandonPanier;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\KeyValue;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\ToggleButtons;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class RelancePanierForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->columns(2)
            ->components([
                Section::make('Configuration de la relance')
                    ->icon('heroicon-o-chat-bubble-left-right')
                    ->columnSpanFull()
                    ->schema([
                        Grid::make(2)
                            ->schema([

                                Select::make('abandon_panier_id')
                                    ->label('Panier abandonné')
                                    ->relationship('abandonPanier', 'id', fn ($query) => $query->with('panier'))
                                    ->searchable()
                                    ->preload()
                                    ->required()
                                    ->live()
                                    ->afterStateUpdated(function ($state, callable $set) {
                                        if ($state) {
                                            $abandon = AbandonPanier::find($state);
                                            if ($abandon && $abandon->panier) {
                                                $set('client_email', $abandon->panier?->client?->email);
                                                $set('valeur_panier', $abandon->panier->total_general);
                                            }
                                        }
                                    }),

                                Select::make('canal')
                                    ->label('Canal')
                                    ->options([
                                        'email' => 'Email',
                                        'sms' => 'SMS',
                                        'push' => 'Notification push',
                                        'notification' => 'Notification',
                                    ])
                                    ->required()
                                    ->default('email'),

                                Select::make('statut')
                                    ->label('Statut')
                                    ->options([
                                        'envoye' => 'Envoyé',
                                        'ouvert' => 'Ouvert',
                                        'clique' => 'Cliqué',
                                        'converti' => 'Converti',
                                        'echec' => 'Échec',
                                    ])
                                    ->required()
                                    ->default('envoye'),

                                TextInput::make('taux_conversion')
                                    ->label('Taux de conversion (%)')
                                    ->numeric()
                                    ->step(0.01)
                                    ->minValue(0)
                                    ->maxValue(100),
                            ]),
                    ]),

                Section::make('Contenu')
                    ->icon('heroicon-o-document-text')
                    ->columnSpan(1)
                    ->schema([
                        Textarea::make('contenu.sujet')
                            ->label('Sujet')
                            ->rows(2)
                            ->placeholder('Objet du message'),

                        Textarea::make('contenu.message')
                            ->label('Message')
                            ->rows(5)
                            ->placeholder('Contenu du message de relance'),
                    ]),

                Section::make('Suivi des interactions')
                    ->icon('heroicon-o-chart-bar')
                    ->columnSpan(1)
                    ->schema([
                        DateTimePicker::make('envoye_at')
                            ->label("Date d'envoi")
                            ->native(false)
                            ->displayFormat('d/m/Y H:i'),

                        DateTimePicker::make('ouvert_at')
                            ->label("Date d'ouverture")
                            ->native(false)
                            ->displayFormat('d/m/Y H:i'),

                        DateTimePicker::make('clique_at')
                            ->label('Date de clic')
                            ->native(false)
                            ->displayFormat('d/m/Y H:i'),

                        ToggleButtons::make('a_conduit_achat')
                            ->label('A conduit à un achat')
                            ->options([
                                true => 'Oui',
                                false => 'Non',
                            ])
                            ->colors([
                                true => 'success',
                                false => 'gray',
                            ])
                            ->inline()
                            ->default(false),
                    ]),

                Section::make('Personnalisation')
                    ->icon('heroicon-o-cog-6-tooth')
                    ->collapsible()
                    ->collapsed()
                    ->columnSpanFull()
                    ->schema([
                        KeyValue::make('contenu.personnalisation')
                            ->label('Variables de personnalisation')
                            ->keyLabel('Variable')
                            ->valueLabel('Valeur')
                            ->addActionLabel('Ajouter une variable')
                            ->reorderable()
                            ->helperText('Ex: {nom_client}, {valeur_panier}, {code_promo}'),
                    ]),
            ]);
    }
}
