<?php

namespace App\Filament\Vendeur\Resources\TransactionFidelites\Schemas;

use App\Models\CompteFidelite;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\KeyValue;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\ToggleButtons;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Group;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class TransactionFideliteForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->columns(3)
            ->components([
                Group::make()
                    ->columnSpan(2)
                    ->schema([
                        Section::make('Informations de la transaction')
                            ->icon('heroicon-o-currency-euro')
                            ->schema([
                                Grid::make(2)
                                    ->schema([
                                        Select::make('compte_fidelite_id')
                                            ->label('Compte fidélité')
                                            ->relationship('compteFidelite', 'id', fn ($query) => $query->with('client'))
                                            ->searchable()
                                            ->preload()
                                            ->required()
                                            ->live()
                                            ->afterStateUpdated(function ($state, callable $set) {
                                                if ($state) {
                                                    $compte = CompteFidelite::find($state);
                                                    if ($compte && $compte->client) {
                                                        $set('client_nom', $compte->client->full_name);
                                                        $set('client_email', $compte->client->email);
                                                        $set('points_actuels', $compte->points);
                                                    }
                                                }
                                            }),

                                        ToggleButtons::make('type')
                                            ->label('Type de transaction')
                                            ->options([
                                                'gain' => 'Gain',
                                                'utilisation' => 'Utilisation',
                                                'expiration' => 'Expiration',
                                                'ajustement' => 'Ajustement',
                                            ])
                                            ->colors([
                                                'gain' => 'success',
                                                'utilisation' => 'warning',
                                                'expiration' => 'danger',
                                                'ajustement' => 'info',
                                            ])
                                            ->icons([
                                                'gain' => 'heroicon-o-plus-circle',
                                                'utilisation' => 'heroicon-o-minus-circle',
                                                'expiration' => 'heroicon-o-clock',
                                                'ajustement' => 'heroicon-o-cog-6-tooth',
                                            ])
                                            ->inline()
                                            ->required()
                                            ->default('gain')
                                            ->live()
                                            ->afterStateUpdated(function ($state, callable $set) {
                                                if ($state === 'gain') {
                                                    $set('points', abs(intval($set('points') ?? 0)));
                                                } elseif (in_array($state, ['utilisation', 'expiration'])) {
                                                    $set('points', -abs(intval($set('points') ?? 0)));
                                                }
                                            }),

                                        TextInput::make('points')
                                            ->label('Points')
                                            ->numeric()
                                            ->required()
                                            ->live()
                                            ->afterStateUpdated(function ($state, callable $set, callable $get) {
                                                $type = $get('type');
                                                if ($type === 'gain') {
                                                    $set('points', abs($state));
                                                } elseif (in_array($type, ['utilisation', 'expiration'])) {
                                                    $set('points', -abs($state));
                                                }
                                            }),

                                        // ✅ Remplacer Placeholder par TextInput disabled
                                        TextInput::make('valeur_euro')
                                            ->label('Valeur en euros')
                                            ->disabled()
                                            ->dehydrated(false)
                                            ->formatStateUsing(function ($get) {
                                                $points = abs(intval($get('points') ?? 0));
                                                $taux = 100; // À récupérer du programme
                                                $valeur = $points / $taux;

                                                return number_format($valeur, 2).' €';
                                            }),
                                    ]),
                            ]),
                    ]),

                Group::make()
                    ->columnSpan(1)
                    ->schema([
                        Section::make('Client')
                            ->icon('heroicon-o-user')
                            ->schema([
                                // ✅ Remplacer Placeholder par TextInput disabled
                                TextInput::make('client_nom')
                                    ->label('Nom')
                                    ->disabled()
                                    ->dehydrated(false)
                                    ->formatStateUsing(fn ($get) => $get('client_nom') ?? '-'),

                                // ✅ Remplacer Placeholder par TextInput disabled
                                TextInput::make('client_email')
                                    ->label('Email')
                                    ->disabled()
                                    ->dehydrated(false)
                                    ->formatStateUsing(fn ($get) => $get('client_email') ?? '-'),

                                // ✅ Remplacer Placeholder par TextInput disabled
                                TextInput::make('points_actuels')
                                    ->label('Points actuels')
                                    ->disabled()
                                    ->dehydrated(false)
                                    ->formatStateUsing(fn ($get) => number_format($get('points_actuels') ?? 0, 0, ',', ' ').' pts'),
                            ]),
                    ]),

                Group::make()
                    ->columnSpanFull()
                    ->schema([
                        Section::make('Détails')
                            ->icon('heroicon-o-document-text')
                            ->schema([
                                Textarea::make('raison')
                                    ->label('Raison')
                                    ->rows(2)
                                    ->placeholder('Ex: Achat de produit, Bonus fidélité, Anniversaire...')
                                    ->required(),

                                DateTimePicker::make('date_transaction')
                                    ->label('Date de la transaction')
                                    ->required()
                                    ->default(now())
                                    ->native(false)
                                    ->displayFormat('d/m/Y H:i')
                                    ->seconds(false),

                                KeyValue::make('metadata')
                                    ->label('Métadonnées')
                                    ->keyLabel('Clé')
                                    ->valueLabel('Valeur')
                                    ->addActionLabel('Ajouter')
                                    ->reorderable()
                                    ->helperText('Informations supplémentaires (commande_id, produit_id, etc.)'),
                            ]),
                    ]),
            ]);
    }
}
