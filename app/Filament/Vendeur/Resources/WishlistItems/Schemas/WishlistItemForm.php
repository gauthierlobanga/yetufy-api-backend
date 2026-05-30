<?php

namespace App\Filament\Vendeur\Resources\WishlistItems\Schemas;

use App\Models\Produit;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Illuminate\Support\Number;

class WishlistItemForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->columns(2)
            ->components([
                Section::make('Informations du produit')
                    ->icon('heroicon-o-shopping-bag')
                    ->columnSpanFull()
                    ->schema([
                        Grid::make(2)
                            ->schema([

                                Select::make('wishlist_id')
                                    ->label('Liste de souhaits')
                                    ->relationship('wishlist', 'nom', fn ($query) => $query->select('id', 'nom'))
                                    ->searchable()
                                    ->preload()
                                    ->required(),

                                Select::make('produit_id')
                                    ->label('Produit')
                                    ->relationship('produit', 'nom')
                                    ->searchable()
                                    ->preload()
                                    ->required()
                                    ->live()
                                    ->afterStateUpdated(function ($state, callable $set) {
                                        if ($state) {
                                            $produit = Produit::find($state);
                                            if ($produit) {
                                                $set('produit_prix', $produit->prix_ttc);
                                                $set('produit_reference', $produit->reference);
                                                $set('produit_stock', $produit->stock_disponible);
                                            }
                                        }
                                    }),

                                TextInput::make('quantite')
                                    ->label('Quantité souhaitée')
                                    ->numeric()
                                    ->required()
                                    ->default(1)
                                    ->minValue(1)
                                    ->step(1),

                                DateTimePicker::make('added_at')
                                    ->label('Ajouté le')
                                    ->required()
                                    ->default(now())
                                    ->native(false)
                                    ->displayFormat('d/m/Y H:i'),

                                Textarea::make('note')
                                    ->label('Note personnelle')
                                    ->rows(3)
                                    ->maxLength(500)
                                    ->columnSpanFull()
                                    ->placeholder('Pourquoi ce produit vous intéresse ?')
                                    ->helperText('Note privée visible uniquement par vous'),
                            ]),
                    ]),

                Section::make('Aperçu produit')
                    ->icon('heroicon-o-eye')
                    ->columnSpanFull()
                    ->visible(fn ($get) => $get('produit_id'))
                    ->schema([
                        Grid::make(3)
                            ->schema([
                                // ✅ Remplacer Placeholder par TextInput disabled (pour l'image, on utilise formatStateWithHtml)
                                TextInput::make('produit_image')
                                    ->label('Image')
                                    ->disabled()
                                    ->dehydrated(false)
                                    ->formatStateUsing(function ($get) {
                                        $produit = Produit::find($get('produit_id'));
                                        if ($produit && $produit->image_principale_thumb) {
                                            return '<img src="'.$produit->image_principale_thumb.'" class="h-20 w-20 object-cover rounded">';
                                        }

                                        return 'Aucune image';
                                    })
                                    ->extraAttributes(['class' => 'prose']),

                                // ✅ Remplacer Placeholder par TextInput disabled
                                TextInput::make('produit_prix')
                                    ->label('Prix actuel')
                                    ->disabled()
                                    ->dehydrated(false)
                                    ->formatStateUsing(fn ($get) => Number::currency($get('produit_prix') ?? 0, 'EUR')),

                                // ✅ Remplacer Placeholder par TextInput disabled
                                TextInput::make('produit_stock')
                                    ->label('Disponibilité')
                                    ->disabled()
                                    ->dehydrated(false)
                                    ->formatStateUsing(fn ($get) => ($get('produit_stock') ?? 0) > 0 ? 'En stock' : 'Rupture de stock'),
                                // ->color(fn ($get) => ($get('produit_stock') ?? 0) > 0 ? 'success' : 'danger'),
                            ]),
                    ]),
            ]);
    }
}
