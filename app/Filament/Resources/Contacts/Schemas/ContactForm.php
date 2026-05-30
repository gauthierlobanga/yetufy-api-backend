<?php

namespace App\Filament\Resources\Contacts\Schemas;

use App\Models\Contact;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\KeyValue;
use Filament\Forms\Components\MarkdownEditor;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Group;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;

class ContactForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->columns(3)
            ->components([
                Group::make()
                    ->columnSpan(2)
                    ->schema([
                        Section::make('Message')
                            ->icon('heroicon-o-chat-bubble-left-right')
                            ->schema([
                                Grid::make(2)
                                    ->schema([
                                        TextInput::make('nom')
                                            ->label('Nom')
                                            ->required()
                                            ->maxLength(255)
                                            ->placeholder('Dupont')
                                            ->live(),

                                        TextInput::make('prenom')
                                            ->label('Prénom')
                                            ->maxLength(255)
                                            ->placeholder('Jean'),

                                        TextInput::make('email')
                                            ->label('Email')
                                            ->email()
                                            ->required()
                                            ->maxLength(255)
                                            ->placeholder('jean.dupont@exemple.com')
                                            ->prefixIcon('heroicon-m-envelope'),

                                        TextInput::make('telephone')
                                            ->label('Téléphone')
                                            ->tel()
                                            ->maxLength(20)
                                            ->placeholder('+33 1 23 45 67 89')
                                            ->prefixIcon('heroicon-m-phone'),
                                    ]),

                                TextInput::make('sujet')
                                    ->label('Sujet')
                                    ->required()
                                    ->maxLength(255)
                                    ->placeholder('Question sur un produit...'),

                                Textarea::make('message')
                                    ->label('Message')
                                    ->required()
                                    ->rows(6)
                                    ->placeholder('Détaillez votre demande...'),
                            ]),
                    ]),

                Group::make()
                    ->columnSpan(1)
                    ->schema([
                        Section::make('Classification')
                            ->icon('heroicon-o-tag')
                            ->schema([
                                Select::make('categorie')
                                    ->label('Catégorie')
                                    ->options(Contact::getCategories())
                                    ->required()
                                    ->default('general')
                                    ->live(),

                                Select::make('priorite')
                                    ->label('Priorité')
                                    ->options(Contact::getPriorites())
                                    ->required()
                                    ->default('moyenne')
                                    ->live(),

                                Select::make('status')
                                    ->label('Statut')
                                    ->options(Contact::getStatuses())
                                    ->required()
                                    ->searchable()
                                    ->preload()
                                    ->default('en_attente')
                                    ->live()
                                    ->afterStateUpdated(function ($state, callable $set, callable $get) {
                                        if ($state === 'lu' && ! $get('lu_at')) {
                                            $set('lu_at', now());
                                        } elseif ($state === 'repondu' && ! $get('repondu_at')) {
                                            $set('repondu_at', now());
                                        }
                                    }),
                            ]),
                    ]),

                Group::make()
                    ->columnSpanFull()
                    ->schema([
                        Section::make('Réponse')
                            ->icon(Heroicon::OutlinedEnvelopeOpen)
                            ->collapsible()
                            ->schema([
                                MarkdownEditor::make('reponse')
                                    ->label('Réponse')
                                    ->toolbarButtons([
                                        'bold',
                                        'bulletList',
                                        'heading',
                                        'italic',
                                        'link',
                                        'orderedList',
                                        'redo',
                                        'undo',
                                    ])
                                    ->columnSpanFull()
                                    ->helperText('Rédigez votre réponse ici. Elle sera envoyée par email au client.'),

                                Grid::make(2)
                                    ->schema([
                                        DateTimePicker::make('lu_at')
                                            ->label('Lu le')
                                            ->native(false)
                                            ->displayFormat('d/m/Y H:i')
                                            ->disabled()
                                            ->dehydrated(false),

                                        DateTimePicker::make('repondu_at')
                                            ->label('Répondu le')
                                            ->native(false)
                                            ->displayFormat('d/m/Y H:i')
                                            ->disabled()
                                            ->dehydrated(false),
                                    ]),
                            ]),

                        Section::make('Informations techniques')
                            ->icon('heroicon-o-code-bracket')
                            ->collapsible()
                            ->collapsed()
                            ->schema([
                                TextInput::make('ip_address')
                                    ->label('Adresse IP')
                                    ->disabled()
                                    ->dehydrated(false),

                                TextInput::make('user_agent')
                                    ->label('User Agent')
                                    ->disabled()
                                    ->dehydrated(false)
                                    ->columnSpanFull(),

                                KeyValue::make('metadata')
                                    ->label('Métadonnées')
                                    ->keyLabel('Clé')
                                    ->valueLabel('Valeur')
                                    ->addActionLabel('Ajouter')
                                    ->reorderable(),
                            ]),
                    ]),
            ]);
    }
}
