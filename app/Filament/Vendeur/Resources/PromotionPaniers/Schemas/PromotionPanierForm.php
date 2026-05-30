<?php

namespace App\Filament\Vendeur\Resources\PromotionPaniers\Schemas;

use App\Models\Panier;
use App\Models\Promotion;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\KeyValue;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\ToggleButtons;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class PromotionPanierForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->columns(2)
            ->components([
                Section::make('Informations')
                    ->icon('heroicon-o-shopping-cart')
                    ->columnSpanFull()
                    ->schema([
                        Grid::make(2)
                            ->schema([

                                Select::make('promotion_id')
                                    ->label('Promotion')
                                    ->relationship('promotion', 'code')
                                    ->searchable()
                                    ->preload()
                                    ->required()
                                    ->live()
                                    ->afterStateUpdated(function ($state, callable $set) {
                                        if ($state) {
                                            $promotion = Promotion::find($state);
                                            if ($promotion) {
                                                $set('promotion_type', $promotion->type);
                                                $set('promotion_valeur', $promotion->valeur);
                                            }
                                        }
                                    }),

                                Select::make('panier_id')
                                    ->label('Panier')
                                    ->relationship('panier', 'id')
                                    ->searchable()
                                    ->preload()
                                    ->required()
                                    ->live()
                                    ->afterStateUpdated(function ($state, callable $set) {
                                        if ($state) {
                                            $panier = Panier::find($state);
                                            if ($panier) {
                                                $set('panier_total', $panier->total_general);
                                                $set('client_nom', $panier->client?->full_name);
                                            }
                                        }
                                    }),

                                TextInput::make('montant_applique')
                                    ->label('Montant appliqué')
                                    ->numeric()
                                    ->required()
                                    ->step(0.01)
                                    ->prefix('€')
                                    ->live()
                                    ->afterStateUpdated(function ($state, callable $set, callable $get) {
                                        $panierTotal = $get('panier_total') ?? 0;
                                        if ($state > $panierTotal) {
                                            $set('montant_applique', $panierTotal);
                                        }
                                    }),

                                TextInput::make('code_saisi')
                                    ->label('Code saisi')
                                    ->maxLength(255)
                                    ->placeholder('Code promo saisi par l\'utilisateur')
                                    ->helperText('Code que l\'utilisateur a entré'),

                                ToggleButtons::make('est_manuelle')
                                    ->label('Application')
                                    ->options([
                                        true => 'Manuelle',
                                        false => 'Automatique',
                                    ])
                                    ->colors([
                                        true => 'warning',
                                        false => 'success',
                                    ])
                                    ->icons([
                                        true => 'heroicon-o-user',
                                        false => 'heroicon-o-cog-6-tooth',
                                    ])
                                    ->inline()
                                    ->default(false),

                                DateTimePicker::make('applied_at')
                                    ->label("Date d'application")
                                    ->required()
                                    ->default(now())
                                    ->native(false)
                                    ->displayFormat('d/m/Y H:i'),

                                KeyValue::make('details')
                                    ->label('Détails')
                                    ->keyLabel('Information')
                                    ->valueLabel('Valeur')
                                    ->addActionLabel('Ajouter un détail')
                                    ->reorderable()
                                    ->helperText('Informations supplémentaires sur l\'application'),
                            ]),
                    ]),
            ]);
    }
}
