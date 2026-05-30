<?php

namespace App\Filament\Vendeur\Resources\CommentReports\Schemas;

use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Illuminate\Support\Str;

class CommentReportForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Informations du signalement')
                    ->icon('heroicon-o-flag')
                    ->schema([
                        Select::make('comment_id')
                            ->relationship(
                                name: 'comment',
                                titleAttribute: 'contenu' // Adaptez selon votre colonne
                            )
                            ->searchable()
                            ->preload()
                            ->required()
                            ->label('Commentaire signalé')
                            ->selectablePlaceholder(false)
                            ->getOptionLabelFromRecordUsing(fn ($record) => Str::limit($record->contenu ?? $record->content ?? '', 50)
                            ),

                        Select::make('user_id')
                            ->relationship(
                                name: 'user',
                                titleAttribute: 'name' // Vérifiez si c'est 'name' ou 'nom'
                            )
                            ->searchable()
                            ->preload()
                            ->required()
                            ->label('Signalé par')
                            ->selectablePlaceholder(false)
                            ->getOptionLabelFromRecordUsing(fn ($record) => "{$record->name} ({$record->email})"
                            ),

                        Select::make('reason')
                            ->options([
                                'spam' => 'Spam',
                                'harassment' => 'Harcèlement',
                                'inappropriate' => 'Contenu inapproprié',
                                'violence' => 'Violence',
                                'hate_speech' => 'Discours haineux',
                                'other' => 'Autre',
                            ])
                            ->required()
                            ->label('Raison du signalement')
                            ->native(false)
                            ->selectablePlaceholder(false),

                        Textarea::make('details')
                            ->label('Détails supplémentaires')
                            ->rows(3)
                            ->columnSpanFull()
                            ->maxLength(500)
                            ->hint(fn ($state) => strlen($state ?? '').'/500 caractères'),

                        Select::make('status')
                            ->options([
                                'pending' => 'En attente',
                                'resolved' => 'Résolu',
                                'rejected' => 'Rejeté',
                            ])
                            ->required()
                            ->label('Statut')
                            ->default('pending')
                            ->native(false),
                    ])->columns(2),
            ]);
    }
}
