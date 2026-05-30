<?php

namespace App\Filament\Vendeur\Resources\CommentLikes\Schemas;

use Filament\Forms\Components\Select;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Illuminate\Support\Str;

class CommentLikeForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Informations du like')
                    ->icon('heroicon-o-heart')
                    ->schema([
                        Select::make('comment_id')
                            ->relationship(
                                name: 'comment',
                                titleAttribute: 'contenu' // Adaptez selon votre colonne (content/contenu/texte)
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
                            ->label('Utilisateur')
                            ->selectablePlaceholder(false)
                            ->getOptionLabelFromRecordUsing(fn ($record) => "{$record->name} ({$record->email})"
                            ),

                        Select::make('type')
                            ->options([
                                'like' => '👍 Like',
                                'love' => '❤️ Love',
                                'laugh' => '😄 Haha',
                                'wow' => '😮 Wow',
                                'sad' => '😢 Triste',
                                'angry' => '😠 En colère',
                            ])
                            ->required()
                            ->label('Type de réaction')
                            ->default('like')
                            ->native(false),
                    ])->columns(3),
            ]);
    }
}
