<?php

namespace App\Filament\Resources\Plans\Schemas;

use Filament\Forms\Components\KeyValue;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

// vainqueur bofodia (vainqueur-12)
class PlanForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->columns(3)
            ->components([
                // ==========================================
                // COLONNE PRINCIPALE (2/3) – Informations essentielles
                // ==========================================
                Section::make('Détails du plan')
                    ->description('Nom, prix, devise et période de facturation')
                    ->columnSpan(2)
                    ->icon('heroicon-o-identification')
                    ->columns(2)
                    ->schema([
                        TextInput::make('name')
                            ->label('Nom du plan')
                            ->required()
                            ->maxLength(255)
                            ->placeholder('Ex: Starter, Business, Premium')
                            ->hintIcon('heroicon-o-question-mark-circle', 'Visible sur la page d\'inscription'),

                        TextInput::make('slug')
                            ->label('Slug')
                            ->required()
                            ->unique(ignoreRecord: true)
                            ->alphaDash()
                            ->placeholder('starter')
                            ->helperText('Identifiant unique (URL, API, Stripe)'),

                        Textarea::make('description')
                            ->label('Description')
                            ->columnSpanFull()
                            ->rows(3)
                            ->maxLength(500)
                            ->placeholder('Décrivez ce que ce plan offre à vos clients...'),

                        TextInput::make('highlight')
                            ->label('Texte d\'accroche')
                            ->columnSpanFull()
                            ->placeholder('"Le choix parfait pour les petites boutiques"')
                            ->maxLength(255)
                            ->helperText('Une courte phrase mise en avant sur la carte du plan'),

                        // ---- Bloc Prix / Devise / Intervalle / Essai ----
                        Grid::make(4)
                            ->columnSpanFull()
                            ->schema([
                                TextInput::make('price')
                                    ->label('Prix')
                                    ->numeric()
                                    ->required()
                                    ->default(0)
                                    ->prefix(fn ($get) => $get('currency') === 'CDF' ? 'FC' : '$')
                                    ->minValue(0)
                                    ->step(0.01),

                                Select::make('currency')
                                    ->label('Devise')
                                    ->options([
                                        'CDF' => 'Franc congolais (CDF)',
                                        'USD' => 'Dollar US (USD)',
                                        'EUR' => 'Euro (EUR)',
                                    ])
                                    ->default('CDF')
                                    ->required()
                                    ->live(), // met à jour le préfixe du prix

                                Select::make('interval')
                                    ->label('Période')
                                    ->options([
                                        'day' => 'Jour',
                                        'week' => 'Semaine',
                                        'month' => 'Mois',
                                        'quarter' => 'Trimestre',
                                        'year' => 'Année',
                                        'lifetime' => 'À vie',
                                    ])
                                    ->default('month')
                                    ->required(),

                                TextInput::make('trial_days')
                                    ->label('Jours d\'essai')
                                    ->numeric()
                                    ->default(0)
                                    ->minValue(0)
                                    ->suffix('jours')
                                    ->helperText('0 = pas d\'essai'),
                            ]),
                    ]),

                // ==========================================
                // COLONNE LATÉRALE (1/3) – Statut et affichage
                // ==========================================
                Section::make('Statut & visibilité')
                    ->description('Activez et mettez en avant ce plan')
                    ->columnSpan(1)
                    ->icon('heroicon-o-eye')
                    ->schema([
                        Toggle::make('is_active')
                            ->label('Plan actif')
                            ->default(true)
                            ->onColor('success')
                            ->offColor('danger')
                            ->helperText('Désactivez pour masquer ce plan sans le supprimer'),

                        Toggle::make('is_featured')
                            ->label('Plan vedette')
                            ->helperText('Mis en avant sur la page d\'inscription'),

                        Toggle::make('is_recommended')
                            ->label('Plan recommandé')
                            ->helperText('Un badge "Populaire" sera affiché'),

                        TextInput::make('badge')
                            ->label('Texte du badge')
                            ->placeholder('Populaire')
                            ->maxLength(30)
                            ->helperText('Laissez vide pour ne pas afficher de badge'),

                        Select::make('badge_color')
                            ->label('Couleur du badge')
                            ->options([
                                'gray' => 'Gris',
                                'amber' => 'Ambre',
                                'green' => 'Vert',
                                'blue' => 'Bleu',
                                'red' => 'Rouge',
                                'purple' => 'Violet',
                            ])
                            ->default('amber'),

                        TextInput::make('button_text')
                            ->label('Texte du bouton')
                            ->placeholder('Choisir ce plan')
                            ->default('S\'abonner')
                            ->helperText('Le texte du bouton d\'action'),

                        TextInput::make('sort_order')
                            ->label('Ordre d\'affichage')
                            ->numeric()
                            ->default(0)
                            ->minValue(0)
                            ->helperText('Les petits chiffres apparaissent en premier'),
                    ]),

                // ==========================================
                // SECTION PLEINE LARGEUR – Fonctionnalités & limites
                // ==========================================
                Section::make('Fonctionnalités & limites')
                    ->description('Définissez ce que ce plan inclut et ses restrictions')
                    ->columnSpanFull()
                    ->icon('heroicon-o-list-bullet')
                    ->collapsed()
                    ->columns(2)
                    ->schema([
                        KeyValue::make('features')
                            ->label('Fonctionnalités incluses')
                            ->addActionLabel('Ajouter une fonctionnalité')
                            ->keyLabel('Icône (emoji)')
                            ->valueLabel('Description')
                            ->keyPlaceholder('🚀')
                            ->valuePlaceholder('Produits illimités')
                            ->default([])
                            ->helperText('Utilisez des emojis comme icônes pour un rendu visuel attractif'),

                        KeyValue::make('limits')
                            ->label('Limites')
                            ->addActionLabel('Ajouter une limite')
                            ->keyLabel('Nom (ex: nb_produits)')
                            ->valueLabel('Valeur')
                            ->keyPlaceholder('nb_produits')
                            ->valuePlaceholder('100')
                            ->default([])
                            ->helperText('Ces limites seront appliquées automatiquement'),
                    ]),

                // ==========================================
                // SECTION PLEINE LARGEUR – Stripe (collapsée)
                // ==========================================
                Section::make('Intégration Stripe')
                    ->description('Configuration du paiement via Stripe')
                    ->columnSpanFull()
                    ->icon('heroicon-o-credit-card')
                    ->collapsed()
                    ->columns(2)
                    ->schema([
                        TextInput::make('stripe_price_id')
                            ->label('ID du prix Stripe')
                            ->placeholder('price_xxxxxxxxxxxxx')
                            ->disabled(fn () => app()->environment('production'))
                            ->helperText('Laissez vide si vous gérez les prix via le Dashboard Stripe'),

                        TextInput::make('stripe_product_id')
                            ->label('ID du produit Stripe')
                            ->placeholder('prod_xxxxxxxxxxxxx')
                            ->helperText('Un seul produit Stripe suffit généralement pour tous vos plans'),
                    ]),

                // ==========================================
                // SECTION PLEINE LARGEUR – Métadonnées (collapsée)
                // ==========================================
                Section::make('Métadonnées')
                    ->description('Informations supplémentaires (interne)')
                    ->columnSpanFull()
                    ->icon('heroicon-o-cog-6-tooth')
                    ->collapsed()
                    ->schema([
                        KeyValue::make('metadata')
                            ->label('Métadonnées additionnelles')
                            ->addActionLabel('Ajouter une méta')
                            ->keyLabel('Clé')
                            ->valueLabel('Valeur')
                            ->keyPlaceholder('couleur_theme')
                            ->valuePlaceholder('#FF5733'),
                    ]),
            ]);
    }
}
