<?php

namespace App\Filament\Vendeur\Resources\Comments\Schemas;

use App\Models\Post;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\ToggleButtons;
use Filament\Schemas\Components\Fieldset;
use Filament\Schemas\Components\Group;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class CommentForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->columns(3)
            ->components([

                // ✅ Champs cachés pour la relation polymorphique
                Hidden::make('commentable_type')
                    ->default(function ($record) {
                        if ($record) {
                            return $record->commentable_type;
                        }

                        // Valeur par défaut si vous créez un commentaire via la resource
                        return request()->input('commentable_type', Post::class);
                    }),

                Hidden::make('commentable_id')
                    ->default(function ($record) {
                        if ($record) {
                            return $record->commentable_id;
                        }

                        return request()->input('commentable_id');
                    }),

                Group::make()
                    ->columnSpan(2)
                    ->schema([
                        Section::make('Contenu du commentaire')
                            ->icon('heroicon-o-chat-bubble-left-right')
                            ->schema([
                                Textarea::make('content')
                                    ->label('Commentaire')
                                    ->required()
                                    ->rows(8)
                                    ->helperText('Le contenu du commentaire')
                                    ->columnSpanFull(),

                                Fieldset::make('Aperçu')
                                    ->label('Aperçu du contenu')
                                    ->schema([
                                        TextInput::make('content_preview')
                                            ->label('')
                                            ->disabled()
                                            ->dehydrated(false)
                                            ->formatStateUsing(fn ($record) => $record?->excerpt ?? '-')
                                            ->columnSpanFull(),
                                    ])
                                    ->visible(fn ($record) => $record !== null),
                            ]),
                    ]),

                Group::make()
                    ->columnSpan(1)
                    ->schema([
                        Section::make('Statut')
                            ->icon('heroicon-o-shield-check')
                            ->schema([
                                ToggleButtons::make('status')
                                    ->label('Statut')
                                    ->options([
                                        'pending' => 'En attente',
                                        'approved' => 'Approuvé',
                                        'spam' => 'Spam',
                                        'trashed' => 'Corbeille',
                                    ])
                                    ->colors([
                                        'pending' => 'warning',
                                        'approved' => 'success',
                                        'spam' => 'danger',
                                        'trashed' => 'gray',
                                    ])
                                    ->icons([
                                        'pending' => 'heroicon-o-clock',
                                        'approved' => 'heroicon-o-check-circle',
                                        'spam' => 'heroicon-o-x-circle',
                                        'trashed' => 'heroicon-o-trash',
                                    ])
                                    ->inline()
                                    ->default('pending')
                                    ->required(),

                                TextInput::make('approved_at_display')
                                    ->label("Date d'approbation")
                                    ->disabled()
                                    ->dehydrated(false)
                                    ->formatStateUsing(fn ($record) => $record?->approved_at?->format('d/m/Y H:i') ?? 'Non approuvé')
                                    ->visible(fn ($record) => $record !== null),
                            ]),

                        Section::make('Auteur')
                            ->icon('heroicon-o-user')
                            ->schema([
                                Select::make('user_id')
                                    ->label('Utilisateur')
                                    ->relationship('user', 'name')
                                    ->searchable(['name', 'email'])
                                    ->preload()
                                    ->required(),

                                TextInput::make('user_email_display')
                                    ->label('Email')
                                    ->disabled()
                                    ->dehydrated(false)
                                    ->formatStateUsing(fn ($record) => $record?->user?->email ?? '-'),

                                TextInput::make('ip_address_display')
                                    ->label('Adresse IP')
                                    ->disabled()
                                    ->dehydrated(false)
                                    ->formatStateUsing(fn ($record) => $record?->ip_address ?? '-'),
                            ]),

                        Section::make('Informations techniques')
                            ->icon('heroicon-o-code-bracket')
                            ->collapsible()
                            ->collapsed()
                            ->schema([
                                TextInput::make('user_agent_display')
                                    ->label('User Agent')
                                    ->disabled()
                                    ->dehydrated(false)
                                    ->formatStateUsing(fn ($record) => $record?->user_agent ?? '-')
                                    ->columnSpanFull(),

                                TextInput::make('metadata_display')
                                    ->label('Métadonnées')
                                    ->disabled()
                                    ->dehydrated(false)
                                    ->formatStateUsing(fn ($record) => $record?->metadata ? json_encode($record->metadata, JSON_PRETTY_PRINT) : '-')
                                    ->columnSpanFull(),

                                TextInput::make('created_at_display')
                                    ->label('Créé le')
                                    ->disabled()
                                    ->dehydrated(false)
                                    ->formatStateUsing(fn ($record) => $record?->created_at?->format('d/m/Y H:i') ?? '-'),

                                TextInput::make('updated_at_display')
                                    ->label('Modifié le')
                                    ->disabled()
                                    ->dehydrated(false)
                                    ->formatStateUsing(fn ($record) => $record?->updated_at?->format('d/m/Y H:i') ?? '-'),
                            ]),
                    ]),
            ]);
    }
}
