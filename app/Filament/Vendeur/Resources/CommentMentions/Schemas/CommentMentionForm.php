<?php

namespace App\Filament\Vendeur\Resources\CommentMentions\Schemas;

use Filament\Forms\Components\Select;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Illuminate\Support\Str;

class CommentMentionForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Informations de la mention')
                    ->icon('heroicon-o-at-symbol')
                    ->schema([
                        Select::make('comment_id')
                            ->relationship(
                                name: 'comment',
                                titleAttribute: 'contenu' // Adaptez selon votre colonne
                            )
                            ->searchable()
                            ->preload()
                            ->required()
                            ->label('Commentaire')
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
                            ->label('Utilisateur mentionné')
                            ->selectablePlaceholder(false)
                            ->getOptionLabelFromRecordUsing(fn ($record) => "{$record->name} ({$record->email})"
                            ),
                    ])->columns(2),
            ]);
    }
}
