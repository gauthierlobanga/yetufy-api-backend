<?php

namespace App\Filament\Vendeur\Resources\Commandes;

use App\Filament\Vendeur\Clusters\Commandes\CommandesCluster;
use App\Filament\Vendeur\Resources\Commandes\Pages\CreateCommande;
use App\Filament\Vendeur\Resources\Commandes\Pages\EditCommande;
use App\Filament\Vendeur\Resources\Commandes\Pages\ListCommandes;
use App\Filament\Vendeur\Resources\Commandes\Pages\ViewCommande;
use App\Filament\Vendeur\Resources\Commandes\Schemas\CommandeForm;
use App\Filament\Vendeur\Resources\Commandes\Tables\CommandesTable;
use App\Models\Commande;
use BackedEnum;
use Filament\Infolists\Components\RepeatableEntry;
use Filament\Infolists\Components\TextEntry;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Fieldset;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class CommandeResource extends Resource
{
    protected static ?string $model = Commande::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedShoppingCart;

    protected static ?string $recordTitleAttribute = 'numero_commande';

    protected static ?string $cluster = CommandesCluster::class;

    protected static bool $isScopedToTenant = false;

    public static function form(Schema $schema): Schema
    {
        return CommandeForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return CommandesTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListCommandes::route('/'),
            'create' => CreateCommande::route('/create'),
            'view' => ViewCommande::route('/{record}'),
            'edit' => EditCommande::route('/{record}/edit'),
        ];
    }

    public static function getRecordRouteBindingEloquentQuery(): Builder
    {
        return parent::getRecordRouteBindingEloquentQuery()
            ->withoutGlobalScopes([
                SoftDeletingScope::class,
            ]);
    }

    public static function getNavigationBadge(): ?string
    {
        return static::getModel()::count();
    }

    public static function getNavigationBadgeColor(): string|array|null
    {
        return static::getModel()::count() > 10 ? 'success' : 'warning';
    }

    public static function infolist(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Informations générales')
                    ->icon('heroicon-o-shopping-bag')
                    ->columns(3)
                    ->schema([
                        TextEntry::make('numero_commande')
                            ->label('N° commande')
                            ->weight('bold')
                            ->copyable(),
                        TextEntry::make('statut')
                            ->label('Statut')
                            ->badge()
                            ->color(fn (string $state): string => match ($state) {
                                'en_attente' => 'warning',
                                'en_cours' => 'primary',
                                'termine' => 'success',
                                'annule' => 'danger',
                                default => 'gray',
                            }),
                        TextEntry::make('date_commande')
                            ->label('Date commande')
                            ->dateTime('d/m/Y H:i'),
                        TextEntry::make('mode_paiement')
                            ->label('Mode de paiement'),
                        TextEntry::make('date_paiement')
                            ->label('Payée le')
                            ->dateTime('d/m/Y H:i')
                            ->placeholder('Non payée'),
                        TextEntry::make('notes')
                            ->label('Notes')
                            ->columnSpanFull()
                            ->placeholder('Aucune note'),
                    ]),

                Section::make('Client')
                    ->icon('heroicon-o-user')
                    ->columns(3)
                    ->schema([
                        TextEntry::make('client.full_name')
                            ->label('Nom complet'),
                        TextEntry::make('client.email')
                            ->label('Email')
                            ->copyable(),
                        TextEntry::make('client.telephone')
                            ->label('Téléphone'),
                    ]),

                Section::make('Adresses')
                    ->icon('heroicon-o-map-pin')
                    ->columns(2)
                    ->schema([
                        Fieldset::make('Facturation')
                            ->schema([
                                TextEntry::make('adresseFacturation.rue'),
                                TextEntry::make('adresseFacturation.code_postal'),
                                TextEntry::make('adresseFacturation.ville'),
                                TextEntry::make('adresseFacturation.pays'),
                            ]),
                        Fieldset::make('Livraison')
                            ->schema([
                                TextEntry::make('adresseLivraison.rue'),
                                TextEntry::make('adresseLivraison.code_postal'),
                                TextEntry::make('adresseLivraison.ville'),
                                TextEntry::make('adresseLivraison.pays'),
                            ]),
                    ]),

                Section::make('Lignes de commande')
                    ->icon('heroicon-o-list-bullet')
                    ->schema([
                        RepeatableEntry::make('lignes')
                            ->schema([
                                Grid::make(6)
                                    ->schema([
                                        TextEntry::make('produit.nom')
                                            ->label('Produit')
                                            ->columnSpan(2),
                                        TextEntry::make('quantite')
                                            ->label('Qté')
                                            ->alignCenter(),
                                        TextEntry::make('prix_unitaire')
                                            ->label('Prix unit.')
                                            ->money('EUR')
                                            ->alignEnd(),
                                        TextEntry::make('taxe')
                                            ->label('Taxe')
                                            ->money('EUR')
                                            ->alignEnd(),
                                        TextEntry::make('prix_total')
                                            ->label('Total')
                                            ->money('EUR')
                                            ->weight('bold')
                                            ->alignEnd(),
                                    ]),
                            ]),
                    ]),

                Section::make('Total')
                    ->icon('heroicon-o-banknotes')
                    ->schema([
                        TextEntry::make('sous_total')
                            ->label('Sous-total')
                            ->money('EUR')
                            ->inlineLabel()
                            ->alignEnd(),
                        TextEntry::make('taxe')
                            ->label('Taxes')
                            ->money('EUR')
                            ->inlineLabel()
                            ->alignEnd(),
                        TextEntry::make('frais_livraison')
                            ->label('Frais de livraison')
                            ->money('EUR')
                            ->inlineLabel()
                            ->alignEnd(),
                        TextEntry::make('total')
                            ->label('Total TTC')
                            ->money('EUR')
                            ->weight('bold')
                            ->size('large')
                            ->inlineLabel()
                            ->alignEnd(),
                    ]),
            ]);
    }
}
