<?php

namespace App\Filament\Resources\TenantDocumentLegals\Schemas;

use App\Models\TypeDocumentLegal;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\KeyValue;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Group;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Schemas\Schema;
use Illuminate\Support\Facades\Auth;

class TenantDocumentLegalForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Informations sur le document légal')
                    ->schema([
                        // Colonne principale (2/3)
                        Group::make()
                            ->columnSpan(['default' => 2, 'lg' => 2])
                            ->schema([
                                Section::make('Identification du document')
                                    ->icon('heroicon-o-identification')
                                    ->columns(2)
                                    ->schema([
                                        Select::make('tenant_id')
                                            ->label('Boutique (Tenant)')
                                            ->relationship('tenant', 'raison_sociale')
                                            ->preload()
                                            ->searchable()
                                            ->native(false)
                                            ->required()
                                            ->columnSpanFull(),

                                        Select::make('vendor_request_id')
                                            ->label('Demande d’inscription associée')
                                            ->relationship('vendorRequest', 'shop_name')
                                            ->disabled()
                                            ->dehydrated()
                                            ->visible(fn ($record) => $record && $record->vendor_request_id)
                                            ->columnSpanFull(),

                                        Select::make('type_document_id')
                                            ->label('Type de document')
                                            ->relationship('typeDocument', 'nom')
                                            ->preload()
                                            ->searchable()
                                            ->native(false)
                                            ->required()
                                            ->live()
                                            ->afterStateUpdated(function (Set $set, $state) {
                                                if (! $state) {
                                                    $set('forme_juridique', null);

                                                    return;
                                                }
                                                $type = TypeDocumentLegal::find($state);
                                                $set('forme_juridique', $type ? $type->forme_juridique_label : null);
                                            }),

                                        TextInput::make('forme_juridique')
                                            ->label('Forme juridique concernée')
                                            ->disabled()
                                            ->dehydrated(false)
                                            ->visible(fn (Get $get) => filled($get('type_document_id'))),

                                        TextInput::make('numero_document')
                                            ->label('Numéro de document')
                                            ->prefixIcon('heroicon-o-hashtag')
                                            ->maxLength(100)
                                            ->columnSpanFull(),
                                    ]),

                                Section::make('Délivrance & validité')
                                    ->icon('heroicon-o-calendar-days')
                                    ->columns(2)
                                    ->schema([
                                        DatePicker::make('date_delivrance')
                                            ->label('Date de délivrance')
                                            ->native(false)
                                            ->prefixIcon('heroicon-m-calendar'),

                                        DatePicker::make('date_expiration')
                                            ->label('Date d’expiration')
                                            ->native(false)
                                            ->prefixIcon('heroicon-m-calendar-days')
                                            ->after('date_delivrance'),

                                        TextInput::make('lieu_delivrance')
                                            ->label('Lieu de délivrance')
                                            ->prefixIcon('heroicon-o-map-pin')
                                            ->maxLength(255),

                                        TextInput::make('autorite_delivrance')
                                            ->label('Autorité émettrice')
                                            ->prefixIcon('heroicon-o-building-library')
                                            ->maxLength(255),
                                    ]),
                            ]),

                        // Colonne latérale (1/3)
                        Group::make()
                            ->columnSpan(['default' => 3, 'lg' => 3])
                            ->schema([
                                Section::make('Vérification')
                                    ->icon('heroicon-o-shield-check')
                                    ->schema([
                                        Toggle::make('est_verifie')
                                            ->label('Document vérifié')
                                            ->onColor('success')
                                            ->offColor('gray')
                                            ->live()
                                            ->afterStateUpdated(function (Set $set, $state) {
                                                if ($state) {
                                                    $set('verifie_le', now());
                                                    $set('verifie_par', Auth::id());
                                                } else {
                                                    $set('verifie_le', null);
                                                    $set('verifie_par', null);
                                                }
                                            }),

                                        DateTimePicker::make('verifie_le')
                                            ->label('Vérifié le')
                                            ->native(false)
                                            ->disabled()
                                            ->dehydrated()
                                            ->visible(fn (Get $get) => $get('est_verifie')),

                                        Select::make('verifie_par')
                                            ->label('Vérifié par')
                                            ->relationship('verifiePar', 'name')
                                            ->native(false)
                                            ->disabled()
                                            ->dehydrated()
                                            ->visible(fn (Get $get) => $get('est_verifie')),
                                    ]),

                                Section::make('Métadonnées')
                                    ->icon('heroicon-o-cpu-chip')
                                    ->collapsed()
                                    ->visible(fn ($record) => $record !== null)
                                    ->schema([
                                        KeyValue::make('metadata')
                                            ->label('Données supplémentaires')
                                            ->keyLabel('Clé')
                                            ->valueLabel('Valeur')
                                            ->disabled()
                                            ->dehydrated(false)
                                            ->columnSpanFull(),
                                    ]),
                            ]),
                    ])->columnSpanFull(),
            ]);
    }
}
