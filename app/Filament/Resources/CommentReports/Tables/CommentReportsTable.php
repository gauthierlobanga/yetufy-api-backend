<?php

namespace App\Filament\Resources\CommentReports\Tables;

use Filament\Actions\Action;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\BadgeColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class CommentReportsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('comment.content')
                    ->label('Commentaire signalé')
                    ->limit(50)
                    ->searchable()
                    ->tooltip(fn ($record) => $record->comment?->content),

                TextColumn::make('user.name')
                    ->label('Signalé par')
                    ->searchable()
                    ->sortable()
                    ->icon('heroicon-o-flag'),

                TextColumn::make('reason')
                    ->label('Raison')
                    ->searchable()
                    ->badge()
                    ->color(fn ($state) => match ($state) {
                        'spam' => 'warning',
                        'harassment' => 'danger',
                        'inappropriate' => 'danger',
                        'violence' => 'danger',
                        default => 'gray',
                    }),

                TextColumn::make('details')
                    ->label('Détails')
                    ->limit(30)
                    ->toggleable()
                    ->tooltip(fn ($record) => $record->details),

                BadgeColumn::make('status')
                    ->label('Statut')
                    ->colors([
                        'warning' => 'pending',
                        'success' => 'resolved',
                        'danger' => 'rejected',
                    ])
                    ->icons([
                        'heroicon-o-clock' => 'pending',
                        'heroicon-o-check-circle' => 'resolved',
                        'heroicon-o-x-circle' => 'rejected',
                    ])
                    ->formatStateUsing(fn ($state) => ucfirst($state)),

                TextColumn::make('created_at')
                    ->label('Signalé le')
                    ->dateTime('d/m/Y H:i')
                    ->sortable()
                    ->toggleable(),
            ])
            ->filters([
                SelectFilter::make('status')
                    ->label('Statut')
                    ->options([
                        'pending' => 'En attente',
                        'resolved' => 'Résolu',
                        'rejected' => 'Rejeté',
                    ]),

                SelectFilter::make('reason')
                    ->label('Raison')
                    ->options([
                        'spam' => 'Spam',
                        'harassment' => 'Harcèlement',
                        'inappropriate' => 'Contenu inapproprié',
                        'violence' => 'Violence',
                        'other' => 'Autre',
                    ]),
            ])
            ->recordActions([
                EditAction::make()
                    ->icon('heroicon-o-pencil'),

                Action::make('resolve')
                    ->label('Marquer comme résolu')
                    ->icon('heroicon-o-check-circle')
                    ->color('success')
                    ->action(fn ($record) => $record->update(['status' => 'resolved']))
                    ->visible(fn ($record) => $record->status === 'pending')
                    ->requiresConfirmation(),

                Action::make('reject')
                    ->label('Rejeter le signalement')
                    ->icon('heroicon-o-x-circle')
                    ->color('danger')
                    ->action(fn ($record) => $record->update(['status' => 'rejected']))
                    ->visible(fn ($record) => $record->status === 'pending')
                    ->requiresConfirmation(),

                Action::make('delete_comment')
                    ->label('Supprimer le commentaire')
                    ->icon('heroicon-o-trash')
                    ->color('danger')
                    ->action(function ($record) {
                        $record->comment?->delete();
                        $record->update(['status' => 'resolved']);
                    })
                    ->requiresConfirmation()
                    ->modalHeading('Supprimer le commentaire')
                    ->modalDescription('Êtes-vous sûr de vouloir supprimer ce commentaire ? Cette action est irréversible.')
                    ->modalSubmitActionLabel('Oui, supprimer'),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ])
            ->defaultSort('created_at', 'desc');
    }
}
