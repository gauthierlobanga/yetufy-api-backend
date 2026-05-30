<?php

namespace App\Filament\Vendeur\Resources\Contacts\Tables;

use App\Models\Contact;
use Filament\Actions\Action;
use Filament\Actions\ActionGroup;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ForceDeleteBulkAction;
use Filament\Actions\RestoreBulkAction;
use Filament\Forms\Components\DatePicker;
use Filament\Notifications\Notification;
use Filament\Support\Enums\Size;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Filters\TrashedFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class ContactsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([

                TextColumn::make('full_name')
                    ->label('Expéditeur')
                    ->searchable(['prenom', 'nom', 'email'])
                    ->sortable()
                    ->weight('medium')
                    ->description(fn ($record) => $record->email)
                    ->copyable()
                    ->toggleable(),

                TextColumn::make('sujet')
                    ->label('Sujet')
                    ->searchable()
                    ->sortable()
                    ->limit(40)
                    ->tooltip(fn ($record) => $record->sujet)
                    ->toggleable(),

                TextColumn::make('categorie')
                    ->label('Catégorie')
                    ->badge()
                    ->color(fn ($record) => $record->categorie_color)
                    ->formatStateUsing(fn ($state) => Contact::getCategories()[$state] ?? $state)
                    ->sortable()
                    ->toggleable(),

                TextColumn::make('priorite')
                    ->label('Priorité')
                    ->badge()
                    ->color(fn ($record) => $record->priorite_color)
                    ->formatStateUsing(fn ($state) => Contact::getPriorites()[$state] ?? $state)
                    ->sortable()
                    ->toggleable(),

                TextColumn::make('status')
                    ->label('Statut')
                    ->badge()
                    ->color(fn ($record) => $record->status_color)
                    ->formatStateUsing(fn ($state) => Contact::getStatuses()[$state] ?? $state)
                    ->sortable()
                    ->toggleable(),

                TextColumn::make('temps_reponse')
                    ->label('Délai réponse')
                    ->getStateUsing(fn ($record) => $record->repondu_at ? $record->repondu_at->diffForHumans($record->created_at, true) : '-')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),

                IconColumn::make('est_lu')
                    ->label('Lu')
                    ->boolean()
                    ->trueIcon('heroicon-o-check-circle')
                    ->falseIcon('heroicon-o-clock')
                    ->trueColor('success')
                    ->falseColor('warning')
                    ->getStateUsing(fn ($record) => ! is_null($record->lu_at))
                    ->toggleable(),

                TextColumn::make('created_at')
                    ->label('Reçu le')
                    ->dateTime('d/m/Y H:i')
                    ->sortable()
                    ->since()
                    ->toggleable(),

                TextColumn::make('updated_at')
                    ->label('Modifié le')
                    ->dateTime('d/m/Y H:i')
                    ->sortable()
                    ->since()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->defaultSort('created_at', 'desc')
            ->filters([
                SelectFilter::make('status')
                    ->label('Statut')
                    ->options(Contact::getStatuses())
                    ->multiple(),

                SelectFilter::make('priorite')
                    ->label('Priorité')
                    ->options(Contact::getPriorites())
                    ->multiple(),

                SelectFilter::make('categorie')
                    ->label('Catégorie')
                    ->options(Contact::getCategories())
                    ->multiple(),

                TernaryFilter::make('traite')
                    ->label('Traitement')
                    ->placeholder('Tous')
                    ->trueLabel('Traité')
                    ->falseLabel('Non traité')
                    ->queries(
                        true: fn (Builder $query) => $query->whereIn('status', ['repondu', 'archive']),
                        false: fn (Builder $query) => $query->whereIn('status', ['en_attente', 'lu']),
                    ),

                TernaryFilter::make('urgent')
                    ->label('Urgent')
                    ->placeholder('Tous')
                    ->trueLabel('Urgents')
                    ->falseLabel('Non urgents')
                    ->queries(
                        true: fn (Builder $query) => $query->where('priorite', 'urgente'),
                        false: fn (Builder $query) => $query->where('priorite', '!=', 'urgente'),
                    ),

                Filter::make('date_range')
                    ->label('Période')
                    ->schema([
                        DatePicker::make('date_from')
                            ->label('Du'),
                        DatePicker::make('date_to')
                            ->label('Au'),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query
                            ->when($data['date_from'], fn ($q, $date) => $q->whereDate('created_at', '>=', $date))
                            ->when($data['date_to'], fn ($q, $date) => $q->whereDate('created_at', '<=', $date));
                    }),

                TrashedFilter::make(),
            ])
            ->recordActions([
                ActionGroup::make([
                    EditAction::make()
                        ->icon(Heroicon::OutlinedPencilSquare),
                    Action::make('view')
                        ->label('Voir')
                        ->icon(Heroicon::OutlinedEye)
                        ->url(fn ($record) => route('filament.vendeur.resources.contacts.edit', $record))
                        ->color('gray'),

                    Action::make('mark_as_read')
                        ->label('Marquer lu')
                        ->icon(Heroicon::OutlinedEye)
                        ->color('info')
                        ->visible(fn ($record) => $record->status === 'en_attente')
                        ->action(function ($record) {
                            $record->marquerLu();
                            Notification::make()
                                ->success()
                                ->title('Message marqué comme lu')
                                ->send();
                        }),

                    Action::make('reply')
                        ->label('Répondre')
                        ->icon(Heroicon::OutlinedEnvelope)
                        ->color('success')
                        ->url(fn ($record) => route('filament.vendeur.resources.contacts.edit', $record).'#reponse')
                        ->visible(fn ($record) => ! in_array($record->status, ['repondu', 'archive'])),

                    Action::make('mark_as_spam')
                        ->label('Spam')
                        ->icon(Heroicon::OutlinedNoSymbol)
                        ->color('danger')
                        ->requiresConfirmation()
                        ->visible(fn ($record) => ! in_array($record->status, ['spam', 'archive']))
                        ->action(function ($record) {
                            $record->marquerSpam();
                            Notification::make()
                                ->success()
                                ->title('Message marqué comme spam')
                                ->send();
                        }),

                    Action::make('archive')
                        ->label('Archiver')
                        ->icon('heroicon-m-archive-box')
                        ->color('gray')
                        ->visible(fn ($record) => $record->status !== 'archive')
                        ->action(function ($record) {
                            $record->archiver();
                            Notification::make()
                                ->success()
                                ->title('Message archivé')
                                ->send();
                        }),
                ])
                    ->badge()
                    ->size(Size::Medium)
                    ->label(''),

            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make()
                        ->requiresConfirmation()
                        ->modalHeading('Supprimer les messages sélectionnés')
                        ->modalSubmitActionLabel('Oui, supprimer'),
                    ForceDeleteBulkAction::make(),
                    RestoreBulkAction::make(),
                    Action::make('bulk_mark_read')
                        ->label('Marquer lus')
                        ->icon('heroicon-m-eye')
                        ->color('info')
                        ->action(fn ($records) => $records->each->marquerLu()),
                    Action::make('bulk_archive')
                        ->label('Archiver')
                        ->icon('heroicon-m-archive-box')
                        ->color('gray')
                        ->action(fn ($records) => $records->each->archiver()),
                    Action::make('bulk_mark_spam')
                        ->label('Marquer spams')
                        ->icon('heroicon-m-no-symbol')
                        ->color('danger')
                        ->action(fn ($records) => $records->each->marquerSpam()),
                ]),
            ])
            ->emptyStateHeading('Aucun message')
            ->emptyStateDescription('Les messages de contact apparaîtront ici.')
            ->emptyStateIcon('heroicon-o-chat-bubble-left-right')
            ->poll('30s')
            ->striped()
            ->paginated([10, 25, 50, 100, 250])
            ->persistFiltersInSession()
            ->persistSearchInSession()
            ->defaultSort('created_at', 'desc');
    }
}
