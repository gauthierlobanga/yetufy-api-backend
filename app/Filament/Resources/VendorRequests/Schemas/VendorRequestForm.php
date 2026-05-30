<?php

namespace App\Filament\Resources\VendorRequests\Schemas;

use App\Models\VendorRequest;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class VendorRequestForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->columns(3)
            ->components([
                Section::make('Demandeur & Plan')
                    ->columnSpan(2)
                    ->icon('heroicon-o-user-group')
                    ->columns(2)
                    ->schema([
                        Select::make('user_id')
                            ->label('Demandeur')
                            ->relationship('user', 'name')
                            ->searchable()
                            ->preload()
                            ->required()
                            ->createOptionForm([
                                TextInput::make('name')->required()->label('Nom'),
                                TextInput::make('email')->required()->email()->unique('users'),
                                TextInput::make('password')->required()->password()->minLength(8),
                            ])
                            ->native(false),

                        Select::make('tenant_id')
                            ->label('Organisation')
                            ->relationship('tenant', 'raison_sociale')
                            ->searchable()
                            ->preload()
                            ->required()
                            ->native(false)
                            ->helperText('L\'organisation'),

                        Select::make('plan_id')
                            ->label('Plan')
                            ->relationship('plan', 'name')
                            ->searchable()
                            ->preload()
                            ->required()
                            ->native(false)
                            ->helperText('Le plan auquel le vendeur a souscrit'),
                    ]),

                Section::make('Détails de la boutique')
                    ->columnSpanFull()
                    ->icon('heroicon-o-building-storefront')
                    ->columns(2)
                    ->schema([
                        TextInput::make('shop_name')
                            ->label('Nom de la boutique')
                            ->required()
                            ->maxLength(255)
                            ->columnSpan(1),

                        TextInput::make('shop_slug')
                            ->label('Slug / Sous‑domaine')
                            ->required()
                            ->maxLength(63)
                            ->hint(fn ($record) => $record ? $record->domain : '')
                            ->helperText('Identifiant unique dans l\'URL (lettres minuscules, chiffres, tirets)')
                            ->columnSpan(1),

                        Textarea::make('shop_description')
                            ->label('Description')
                            ->columnSpanFull()
                            ->rows(3)
                            ->maxLength(500),

                        TextInput::make('contact_email')
                            ->label('Email de contact')
                            ->email()
                            ->required()
                            ->columnSpan(1),

                        TextInput::make('contact_phone')
                            ->label('Téléphone')
                            ->tel()
                            ->placeholder('+243 XXX XXX XXX')
                            ->columnSpan(1),
                    ]),

                Section::make('Statut & traitement')
                    ->columnSpan(1)
                    ->icon('heroicon-o-check-circle')
                    ->schema([
                        Select::make('status')
                            ->label('Statut')
                            ->options(VendorRequest::getStatuses())
                            ->required()
                            ->default(VendorRequest::STATUS_PENDING)
                            ->native(false)
                            ->live()
                            ->afterStateUpdated(function ($state, callable $set) {
                                if ($state === VendorRequest::STATUS_APPROVED) {
                                    $set('approved_at', now());
                                }
                                if ($state === VendorRequest::STATUS_REJECTED) {
                                    $set('rejected_at', now());
                                }
                            }),

                        Textarea::make('rejection_reason')
                            ->label('Motif du rejet')
                            ->visible(fn ($get) => $get('status') === VendorRequest::STATUS_REJECTED)
                            ->maxLength(500)
                            ->rows(3),

                        DateTimePicker::make('approved_at')
                            ->label('Date d\'approbation')
                            ->native(false)
                            ->displayFormat('d/m/Y H:i')
                            ->seconds(false)
                            ->visible(fn ($get) => $get('status') === VendorRequest::STATUS_APPROVED),

                        DateTimePicker::make('rejected_at')
                            ->label('Date de rejet')
                            ->native(false)
                            ->displayFormat('d/m/Y H:i')
                            ->seconds(false)
                            ->visible(fn ($get) => $get('status') === VendorRequest::STATUS_REJECTED),
                    ]),

                Section::make('Informations de paiement')
                    ->columnSpanFull()
                    ->icon('heroicon-o-credit-card')
                    ->collapsed()
                    ->schema([
                        TextInput::make('payment_session_id')
                            ->label('ID de session de paiement')
                            ->placeholder('Session Stripe ou autre')
                            ->maxLength(255),

                        Select::make('payment_status')
                            ->label('État du paiement')
                            ->options([
                                'pending' => 'En attente',
                                'paid' => 'Payé',
                                'failed' => 'Échoué',
                            ])
                            ->default('pending')
                            ->native(false),
                    ]),
            ]);
    }
}
