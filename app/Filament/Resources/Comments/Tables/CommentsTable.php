<?php

namespace App\Filament\Resources\Comments\Tables;

use App\Models\Comment;
use Filament\Actions\Action;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Notifications\Notification;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;

class CommentsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                // Auteur
                TextColumn::make('user.name')
                    ->label('Auteur')
                    ->searchable()
                    ->sortable()
                    ->weight('medium')
                    ->description(fn ($record) => $record->user?->email)
                    ->toggleable(),

                // Contenu (extrait)
                TextColumn::make('excerpt')
                    ->label('Commentaire')
                    ->searchable(query: function ($query, $search) {
                        return $query->where('content', 'like', "%{$search}%");
                    })
                    ->limit(80)
                    ->tooltip(fn ($record) => strip_tags($record->content))
                    ->toggleable(),

                // Type d'entité commentée
                TextColumn::make('commentable_type')
                    ->label('Type')
                    ->badge()
                    ->color('gray')
                    ->formatStateUsing(fn ($state) => class_basename($state))
                    ->toggleable(),

                // ID de l'entité
                TextColumn::make('commentable_id')
                    ->label('ID')
                    ->numeric()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),

                // Réponses
                TextColumn::make('replies_count')
                    ->label('Réponses')
                    ->numeric()
                    ->sortable()
                    ->alignCenter()
                    ->badge()
                    ->color('info')
                    ->toggleable(),

                // Likes
                TextColumn::make('likes_count')
                    ->label('👍')
                    ->numeric()
                    ->sortable()
                    ->alignCenter()
                    ->toggleable(),

                // Dislikes
                TextColumn::make('dislikes_count')
                    ->label('👎')
                    ->numeric()
                    ->sortable()
                    ->alignCenter()
                    ->toggleable(isToggledHiddenByDefault: true),

                // Signalements
                TextColumn::make('reports_count')
                    ->label('🚩')
                    ->numeric()
                    ->sortable()
                    ->alignCenter()
                    ->badge()
                    ->color('danger')
                    ->toggleable(),

                // Ajoutez cette colonne
                // TextColumn::make('comments_count')
                //     ->label('💬')
                //     ->counts('comments')
                //     ->sortable()
                //     ->alignCenter()
                //     ->badge()
                //     ->color('info')
                //     ->toggleable(),

                // Statut
                TextColumn::make('status')
                    ->label('Statut')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'approved' => 'success',
                        'pending' => 'warning',
                        'spam' => 'danger',
                        'trashed' => 'gray',
                        default => 'gray',
                    })
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'approved' => 'Approuvé',
                        'pending' => 'En attente',
                        'spam' => 'Spam',
                        'trashed' => 'Corbeille',
                        default => $state,
                    })
                    ->sortable()
                    ->toggleable(),

                // Dates
                TextColumn::make('created_at')
                    ->label('Créé le')
                    ->dateTime('d/m/Y H:i')
                    ->sortable()
                    ->since()
                    ->toggleable(),

                TextColumn::make('approved_at')
                    ->label('Approuvé le')
                    ->dateTime('d/m/Y H:i')
                    ->sortable()
                    ->since()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->defaultSort('created_at', 'desc')
            ->filters([
                SelectFilter::make('status')
                    ->label('Statut')
                    ->options(Comment::getStatuses())
                    ->multiple(),

                SelectFilter::make('commentable_type')
                    ->label('Type d\'entité')
                    ->options(function () {
                        return Comment::distinct()
                            ->pluck('commentable_type', 'commentable_type')
                            ->map(fn ($type) => class_basename($type))
                            ->toArray();
                    }),

                TernaryFilter::make('has_replies')
                    ->label('Avec réponses')
                    ->placeholder('Tous')
                    ->trueLabel('Avec réponses')
                    ->falseLabel('Sans réponses')
                    ->queries(
                        true: fn ($query) => $query->where('replies_count', '>', 0),
                        false: fn ($query) => $query->where('replies_count', 0),
                    ),

                TernaryFilter::make('has_reports')
                    ->label('Signalés')
                    ->placeholder('Tous')
                    ->trueLabel('Signalés')
                    ->falseLabel('Non signalés')
                    ->queries(
                        true: fn ($query) => $query->where('reports_count', '>', 0),
                        false: fn ($query) => $query->where('reports_count', 0),
                    ),
            ])
            ->recordActions([
                Action::make('approve')
                    ->label('Approuver')
                    ->icon('heroicon-m-check-circle')
                    ->color('success')
                    ->visible(fn ($record) => $record->status === 'pending')
                    ->action(function ($record) {
                        $record->approve();
                        Notification::make()
                            ->success()
                            ->title('Commentaire approuvé')
                            ->send();
                    }),

                Action::make('mark_as_spam')
                    ->label('Spam')
                    ->icon('heroicon-m-x-circle')
                    ->color('danger')
                    ->visible(fn ($record) => ! in_array($record->status, ['spam', 'trashed']))
                    ->action(function ($record) {
                        $record->markAsSpam();
                        Notification::make()
                            ->success()
                            ->title('Commentaire marqué comme spam')
                            ->send();
                    }),

                Action::make('trash')
                    ->label('Corbeille')
                    ->icon('heroicon-m-trash')
                    ->color('gray')
                    ->visible(fn ($record) => $record->status !== 'trashed')
                    ->action(function ($record) {
                        $record->trash();
                        Notification::make()
                            ->success()
                            ->title('Commentaire déplacé vers la corbeille')
                            ->send();
                    }),

                EditAction::make()
                    ->icon('heroicon-m-pencil-square'),

                Action::make('view_on_site')
                    ->label('Voir sur le site')
                    ->icon('heroicon-m-arrow-top-right-on-square')
                    ->color('gray')
                    ->url(fn ($record) => $record->commentable?->url.'#comment-'.$record->id)
                    ->openUrlInNewTab()
                    ->visible(fn ($record) => $record->commentable && method_exists($record->commentable, 'url')),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make()
                        ->requiresConfirmation()
                        ->modalHeading('Supprimer les commentaires sélectionnés')
                        ->modalSubmitActionLabel('Oui, supprimer'),

                    Action::make('bulk_approve')
                        ->label('Approuver')
                        ->icon('heroicon-m-check-circle')
                        ->color('success')
                        ->action(fn ($records) => $records->each->approve()),

                    Action::make('bulk_mark_as_spam')
                        ->label('Marquer comme spam')
                        ->icon('heroicon-m-x-circle')
                        ->color('danger')
                        ->action(fn ($records) => $records->each->markAsSpam()),

                    Action::make('bulk_trash')
                        ->label('Déplacer vers la corbeille')
                        ->icon('heroicon-m-trash')
                        ->color('gray')
                        ->action(fn ($records) => $records->each->trash()),
                ]),
            ])
            ->emptyStateHeading('Aucun commentaire')
            ->emptyStateDescription('Les commentaires apparaîtront ici une fois que les utilisateurs auront commenté.')
            ->emptyStateIcon('heroicon-o-chat-bubble-left-right')
            ->poll('60s')
            ->striped()
            ->paginated([10, 25, 50, 100])
            ->persistFiltersInSession()
            ->persistSearchInSession()
            ->defaultSort('created_at', 'desc');
    }
}
