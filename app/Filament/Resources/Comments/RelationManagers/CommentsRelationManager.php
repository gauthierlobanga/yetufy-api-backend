<?php

namespace App\Filament\Resources\Comments\RelationManagers;

use Filament\Actions\Action;
use Filament\Actions\AssociateAction;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\CreateAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\DissociateAction;
use Filament\Actions\DissociateBulkAction;
use Filament\Actions\EditAction;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class CommentsRelationManager extends RelationManager
{
    protected static string $relationship = 'comments';

    protected static bool $isLazy = false;

    protected static ?string $title = 'Commentaires';

    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([

            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('content')
            ->columns([
                TextColumn::make('user.name')
                    ->label('Auteur')
                    ->searchable()
                    ->sortable()
                    ->weight('medium'),

                TextColumn::make('excerpt')
                    ->label('Commentaire')
                    ->limit(80)
                    ->searchable(query: function ($query, $search) {
                        return $query->where('content', 'like', "%{$search}%");
                    }),

                TextColumn::make('likes_count')
                    ->label('👍')
                    ->numeric()
                    ->sortable()
                    ->alignCenter(),

                TextColumn::make('replies_count')
                    ->label('Réponses')
                    ->numeric()
                    ->sortable()
                    ->alignCenter()
                    ->badge()
                    ->color('info'),

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
                    }),

                TextColumn::make('created_at')
                    ->label('Date')
                    ->dateTime('d/m/Y H:i')
                    ->since(),
            ])
            ->filters([
                //
            ])
            ->defaultSort('created_at', 'desc')
            ->headerActions([
                Action::make('view')
                    ->label('Voir')
                    ->icon('heroicon-m-eye')
                    ->url(fn ($record) => route('filament.admin.resources.comments.edit', $record))
                    ->openUrlInNewTab(),

                Action::make('approve')
                    ->label('Approuver')
                    ->icon('heroicon-m-check-circle')
                    ->color('success')
                    ->visible(fn ($record) => $record && $record->status === 'pending') // ✅ Vérification du null
                    ->action(function ($record) {
                        if ($record) {
                            $record->approve();
                            $this->notify('success', 'Commentaire approuvé');
                        }
                    }),

                // ✅ Action "Spam"
                Action::make('mark_as_spam')
                    ->label('Spam')
                    ->icon('heroicon-m-x-circle')
                    ->color('danger')
                    ->visible(fn ($record) => $record && ! in_array($record->status, ['spam', 'trashed']))
                    ->action(function ($record) {
                        if ($record) {
                            $record->markAsSpam();
                            $this->notify('success', 'Commentaire marqué comme spam');
                        }
                    }),
                CreateAction::make(),
                AssociateAction::make(),
            ])
            ->recordActions([
                EditAction::make(),
                DissociateAction::make(),
                DeleteAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DissociateBulkAction::make(),
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
