<?php

namespace App\Filament\Vendeur\Resources\Wishlists\Schemas;

use App\Models\Client;
use App\Models\Produit;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\ToggleButtons;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class WishlistForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->columns(2)
            ->components([
                Section::make('Informations de la liste')
                    ->icon('heroicon-o-heart')
                    ->columnSpanFull()
                    ->schema([
                        Grid::make(3)
                            ->schema([

                                Select::make('client_id')
                                    ->label('Client')
                                    ->relationship('client', 'nom', fn ($query) => $query->select('id', 'nom', 'prenom', 'email'))
                                    ->searchable(['nom', 'prenom', 'email'])
                                    ->preload()
                                    ->required()
                                    ->live()
                                    ->afterStateUpdated(function ($state, callable $set) {
                                        if ($state) {
                                            $client = Client::find($state);
                                            if ($client) {
                                                $set('client_email', $client->email);
                                                $set('client_phone', $client->telephone);
                                            }
                                        }
                                    }),

                                TextInput::make('nom')
                                    ->label('Nom de la liste')
                                    ->maxLength(255)
                                    ->placeholder('Ma liste de souhaits')
                                    ->helperText('Laissez vide pour utiliser le nom par défaut'),

                                ToggleButtons::make('est_publique')
                                    ->label('Visibilité')
                                    ->options([
                                        true => 'Publique',
                                        false => 'Privée',
                                    ])
                                    ->colors([
                                        true => 'success',
                                        false => 'gray',
                                    ])
                                    ->icons([
                                        true => 'heroicon-o-globe-alt',
                                        false => 'heroicon-o-lock-closed',
                                    ])
                                    ->inline()
                                    ->default(false)
                                    ->helperText('Les listes publiques peuvent être partagées par lien'),

                                // ✅ Remplacer Placeholder par TextInput disabled
                                TextInput::make('share_url')
                                    ->label('Lien de partage')
                                    ->disabled()
                                    ->dehydrated(false)
                                    ->formatStateUsing(fn ($record) => $record && $record->est_publique
                                        ? route('tenant.wishlist.toggle', $record->id)
                                        : 'Rendez la liste publique pour obtenir un lien')
                                    ->visible(fn ($record) => $record !== null),
                            ]),
                    ]),

                Section::make('Statistiques')
                    ->icon('heroicon-o-chart-bar')
                    ->columnSpan(1)
                    ->visible(fn ($record) => $record !== null)
                    ->schema([
                        // ✅ Remplacer Placeholder par TextInput disabled
                        TextInput::make('items_count')
                            ->label('Nombre d\'articles')
                            ->disabled()
                            ->dehydrated(false)
                            ->formatStateUsing(fn ($record) => $record?->items_count ?? 0),

                        TextInput::make('created_at_display')
                            ->label('Créée le')
                            ->disabled()
                            ->dehydrated(false)
                            ->formatStateUsing(fn ($record) => $record?->created_at?->format('d/m/Y H:i')),

                        TextInput::make('updated_at_display')
                            ->label('Dernière modification')
                            ->disabled()
                            ->dehydrated(false)
                            ->formatStateUsing(fn ($record) => $record?->updated_at?->diffForHumans()),
                    ]),

                Section::make('Articles de la liste')
                    ->icon('heroicon-o-shopping-bag')
                    ->collapsible()
                    ->columnSpanFull()
                    ->schema([
                        Repeater::make('items')
                            ->label('Produits')
                            ->relationship('items')
                            ->schema([
                                Grid::make(4)
                                    ->schema([
                                        Select::make('produit_id')
                                            ->label('Produit')
                                            ->relationship('produit', 'nom')
                                            ->searchable()
                                            ->preload()
                                            ->required()
                                            ->live()
                                            ->afterStateUpdated(function ($state, callable $set, callable $get) {
                                                if ($state) {
                                                    $produit = Produit::find($state);
                                                    if ($produit) {
                                                        $set('produit_prix', $produit->prix_ttc);
                                                        $set('produit_image', $produit->image_principale_thumb);
                                                    }
                                                }
                                            }),

                                        TextInput::make('quantite')
                                            ->label('Quantité')
                                            ->numeric()
                                            ->default(1)
                                            ->minValue(1)
                                            ->step(1),

                                        // ✅ Remplacer Placeholder par TextInput disabled
                                        TextInput::make('produit_prix')
                                            ->label('Prix unitaire')
                                            ->disabled()
                                            ->dehydrated(false)
                                            ->formatStateUsing(fn ($get) => number_format($get('produit_prix') ?? 0, 2).' €'),

                                        Textarea::make('note')
                                            ->label('Note personnelle')
                                            ->maxLength(500)
                                            ->rows(2)
                                            ->placeholder('Pourquoi ce produit ?'),
                                    ]),
                            ])
                            ->columns(1)
                            ->addActionLabel('Ajouter un produit')
                            ->defaultItems(0)
                            ->collapsible(),
                    ]),
            ]);
    }
}
