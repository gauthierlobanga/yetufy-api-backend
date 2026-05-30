<?php

namespace App\Filament\Resources\CommentLikes\Tables;

use Filament\Actions\Action;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Forms\Components\DatePicker;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class CommentLikesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('comment.id')
                    ->label('ID Commentaire')
                    ->searchable()
                    ->sortable()
                    ->copyable()
                    ->icon('heroicon-o-chat-bubble-left'),

                TextColumn::make('comment.contenu') // Adaptez 'contenu' selon votre colonne
                    ->label('Contenu du commentaire')
                    ->limit(50)
                    ->searchable()
                    ->toggleable()
                    ->tooltip(fn ($record) => $record->comment?->contenu ?? $record->comment?->content ?? ''),

                TextColumn::make('user.name') // Vérifiez si c'est 'name' ou 'nom'
                    ->label('Utilisateur')
                    ->searchable()
                    ->sortable()
                    ->icon('heroicon-o-user')
                    ->description(fn ($record) => $record->user?->email),

                TextColumn::make('type')
                    ->label('Type de réaction')
                    ->searchable()
                    ->badge()
                    ->formatStateUsing(fn ($state) => match ($state) {
                        'like' => '👍 Like',
                        'love' => '❤️ Love',
                        'laugh' => '😄 Haha',
                        'wow' => '😮 Wow',
                        'sad' => '😢 Triste',
                        'angry' => '😠 En colère',
                        default => $state,
                    })
                    ->color(fn ($state) => match ($state) {
                        'like' => 'success',
                        'love' => 'danger',
                        'laugh' => 'warning',
                        'wow' => 'warning',
                        'sad' => 'info',
                        'angry' => 'danger',
                        default => 'gray',
                    })
                    ->icon(fn ($state) => match ($state) {
                        'like' => 'heroicon-o-hand-thumb-up',
                        'love' => 'heroicon-o-heart',
                        'laugh' => 'heroicon-o-face-smile',
                        'wow' => 'heroicon-o-face-smile',
                        'sad' => 'heroicon-o-face-frown',
                        'angry' => 'heroicon-o-face-frown',
                        default => null,
                    }),

                TextColumn::make('created_at')
                    ->label('Créé le')
                    ->dateTime('d/m/Y H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true)
                    ->icon('heroicon-o-calendar'),

                TextColumn::make('updated_at')
                    ->label('Mis à jour le')
                    ->dateTime('d/m/Y H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true)
                    ->icon('heroicon-o-clock'),
            ])
            ->filters([
                SelectFilter::make('type')
                    ->label('Type de réaction')
                    ->options([
                        'like' => '👍 Like',
                        'love' => '❤️ Love',
                        'laugh' => '😄 Haha',
                        'wow' => '😮 Wow',
                        'sad' => '😢 Triste',
                        'angry' => '😠 En colère',
                    ])
                    ->multiple(),

                SelectFilter::make('user_id')
                    ->label('Utilisateur')
                    ->relationship('user', 'name') // Adaptez 'name' selon votre colonne
                    ->searchable()
                    ->preload(),

                Filter::make('created_at')
                    ->form([
                        DatePicker::make('created_from')
                            ->label('Créé depuis')
                            ->placeholder('Date de début'),
                        DatePicker::make('created_until')
                            ->label('Créé jusqu\'au')
                            ->placeholder('Date de fin'),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query
                            ->when(
                                $data['created_from'],
                                fn (Builder $query, $date): Builder => $query->whereDate('created_at', '>=', $date),
                            )
                            ->when(
                                $data['created_until'],
                                fn (Builder $query, $date): Builder => $query->whereDate('created_at', '<=', $date),
                            );
                    }),
            ])
            ->recordActions([ // Changé de recordActions() à actions()
                EditAction::make()
                    ->icon('heroicon-o-pencil')
                    ->label('Modifier'),

                ViewAction::make()
                    ->icon('heroicon-o-eye')
                    ->label('Voir'),

                DeleteAction::make()
                    ->icon('heroicon-o-trash')
                    ->label('Supprimer')
                    ->requiresConfirmation(),
            ])
            ->toolbarActions([ // Changé de toolbarActions() à bulkActions()
                BulkActionGroup::make([
                    DeleteBulkAction::make()
                        ->label('Supprimer la sélection')
                        ->icon('heroicon-o-trash')
                        ->requiresConfirmation(),

                    Action::make('export_selected')
                        ->label('Exporter la sélection')
                        ->icon('heroicon-o-arrow-down-tray')
                        ->color('gray')
                        ->action(function ($records) {
                            // Logique d'export ici
                        }),
                ]),
            ])
            ->defaultSort('created_at', 'desc')
            ->striped()
            ->poll('60s') // Actualisation automatique toutes les 60 secondes
            ->paginated([10, 25, 50, 100])
            ->defaultPaginationPageOption(25);
    }
}
