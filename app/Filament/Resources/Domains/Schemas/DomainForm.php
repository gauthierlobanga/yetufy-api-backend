<?php

namespace App\Filament\Resources\Domains\Schemas;

use App\Models\Tenant;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Illuminate\Support\Str;

class DomainForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->columns(3)
            ->components([
                Section::make('Informations du domaine')
                    ->description('Configuration du nom de domaine ou sous‑domaine')
                    ->columnSpan(2)
                    ->icon('heroicon-o-globe-alt')
                    ->columns(2)
                    ->schema([
                        TextInput::make('domain')
                            ->label('Domaine')
                            ->required()
                            ->maxLength(255)
                            ->unique(ignoreRecord: true, modifyRuleUsing: fn ($rule) => $rule->whereNull('deleted_at'))
                            ->placeholder('ma-boutique ou mon-domaine.cd')
                            ->hintIcon('heroicon-o-question-mark-circle', 'Sous‑domaine (ex: boutique) ou domaine complet (ex: maboutique.cd)')
                            ->live(onBlur: true)
                            ->afterStateUpdated(function ($state, callable $set) {
                                $set('type', Str::contains($state, '.') ? 'custom' : 'subdomain');
                            }),

                        Select::make('tenant_id')
                            ->label('Boutique associée')
                            ->relationship('tenant', 'raison_sociale')
                            ->preload()
                            ->searchable()
                            ->required()
                            ->getOptionLabelFromRecordUsing(fn ($record) => $record->raison_sociale ?? $record->slug ?? "Boutique #{$record->id}")
                            ->createOptionForm([
                                TextInput::make('raison_sociale')
                                    ->label('Nom de la boutique')
                                    ->required()
                                    ->maxLength(255)
                                    ->live(onBlur: true)
                                    ->afterStateUpdated(fn ($state, callable $set) => $set('slug', Str::slug($state))),
                                TextInput::make('slug')
                                    ->label('Slug')
                                    ->required()
                                    ->unique('tenants', 'slug'),
                                TextInput::make('email')
                                    ->label('Email de contact')
                                    ->email(),
                            ])
                            ->createOptionUsing(fn ($data) => Tenant::create($data)->id),
                        Select::make('type')
                            ->label('Type de domaine')
                            ->options([
                                'subdomain' => 'Sous‑domaine (ex: boutique.plateforme.cd)',
                                'custom' => 'Domaine personnalisé (ex: maboutique.cd)',
                                'redirect' => 'Redirection',
                            ])
                            ->default('subdomain')
                            ->required(),

                        Toggle::make('is_primary')
                            ->label('Domaine principal')
                            ->helperText('Un seul domaine principal par boutique')
                            ->default(true),

                        Toggle::make('is_active')
                            ->label('Domaine actif')
                            ->default(true)
                            ->onColor('success')
                            ->offColor('danger'),
                    ]),

                Section::make('Vérification & statut')
                    ->columnSpan(1)
                    ->icon('heroicon-o-shield-check')
                    ->schema([
                        // ✅ Remplacement de Placeholder par TextInput en lecture seule
                        TextInput::make('status_display')
                            ->label('État du domaine')
                            ->default(fn ($record) => $record?->is_active ? '✅ Domaine actif' : '⚠️ Domaine inactif')
                            ->disabled()
                            ->dehydrated(false)
                            ->hidden(fn ($record) => ! $record),

                        DateTimePicker::make('verified_at')
                            ->label('Date de vérification')
                            ->native(false)
                            ->displayFormat('d/m/Y H:i')
                            ->seconds(false)
                            ->helperText('Laissez vide si le domaine n\'est pas encore vérifié')
                            ->placeholder('Non vérifié'),
                    ]),

                Section::make('Métadonnées')
                    ->columnSpanFull()
                    ->icon('heroicon-o-cog-6-tooth')
                    ->collapsed()
                    ->schema([
                        Grid::make(2)
                            ->schema([
                                TextInput::make('dns_provider')
                                    ->label('Fournisseur DNS')
                                    ->placeholder('Cloudflare, OVH, etc.')
                                    ->maxLength(255),

                                TextInput::make('ssl_status')
                                    ->label('Statut SSL')
                                    ->placeholder('active, pending, none')
                                    ->maxLength(50),

                                TextInput::make('notes')
                                    ->label('Notes internes')
                                    ->columnSpanFull()
                                    ->maxLength(500),
                            ]),
                    ]),
            ]);
    }
}
