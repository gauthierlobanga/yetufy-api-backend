<?php

namespace App\Filament\Resources\Domains\Tables;

use Filament\Actions\Action;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ForceDeleteBulkAction;
use Filament\Actions\RestoreBulkAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TrashedFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;

class DomainsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('domain')
                    ->label('Domaine')
                    ->searchable()
                    ->sortable()
                    ->copyable()
                    ->copyMessage('Domaine copié !')
                    ->copyMessageDuration(1500)
                    ->description(fn ($record) => $record->is_primary ? 'Domaine principal' : 'Domaine secondaire')
                    ->tooltip(fn ($record) => 'Cliquez pour copier le domaine')
                    ->icon('heroicon-o-globe-alt')
                    ->iconColor('primary')
                    ->weight('bold'),

                TextColumn::make('tenant.raison_sociale')
                    ->label('Boutique')
                    ->searchable()
                    ->sortable()
                    ->url(fn ($record) => $record->tenant
                        ? route('filament.admin.resources.vendeurs.edit', $record->tenant)
                        : null)
                    ->icon('heroicon-o-building-storefront')
                    ->iconColor('amber'),

                TextColumn::make('tenant.slug')
                    ->label('Slug')
                    ->searchable()
                    ->visible(fn () => Auth::user()?->hasRole('super_admin'))
                    ->toggleable(),

                IconColumn::make('is_primary')
                    ->label('Principal')
                    ->boolean()
                    ->trueIcon('heroicon-o-star')
                    ->falseIcon('heroicon-o-star')
                    ->trueColor('amber')
                    ->falseColor('gray')
                    ->sortable(),

                IconColumn::make('is_active')
                    ->label('Actif')
                    ->boolean()
                    ->trueIcon('heroicon-o-check-circle')
                    ->falseIcon('heroicon-o-x-circle')
                    ->trueColor('success')
                    ->falseColor('danger')
                    ->sortable(),

                TextColumn::make('type')
                    ->label('Type')
                    ->badge()
                    ->color(fn ($state) => match ($state) {
                        'subdomain' => 'gray',
                        'custom' => 'success',
                        'redirect' => 'info',
                        default => 'gray',
                    })
                    ->formatStateUsing(fn ($state) => match ($state) {
                        'subdomain' => 'Sous‑domaine',
                        'custom' => 'Personnalisé',
                        'redirect' => 'Redirection',
                        default => $state,
                    })
                    ->toggleable(isToggledHiddenByDefault: false),

                TextColumn::make('verified_at')
                    ->label('Vérifié le')
                    ->dateTime('d/m/Y H:i')
                    ->sortable()
                    ->placeholder('Non vérifié')
                    ->toggleable(isToggledHiddenByDefault: true),

                TextColumn::make('created_at')
                    ->label('Ajouté le')
                    ->dateTime('d/m/Y H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),

                TextColumn::make('updated_at')
                    ->label('Modifié le')
                    ->dateTime('d/m/Y H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                SelectFilter::make('tenant_id')
                    ->label('Boutique')
                    ->relationship('tenant', 'raison_sociale')
                    ->searchable()
                    ->preload(),

                SelectFilter::make('type')
                    ->label('Type')
                    ->options([
                        'subdomain' => 'Sous‑domaine',
                        'custom' => 'Personnalisé',
                        'redirect' => 'Redirection',
                    ]),

                SelectFilter::make('is_primary')
                    ->label('Statut')
                    ->options([
                        '1' => 'Principal',
                        '0' => 'Secondaire',
                    ]),

                Filter::make('verified')
                    ->label('Vérifié')
                    ->query(fn (Builder $query) => $query->whereNotNull('verified_at')),

                Filter::make('unverified')
                    ->label('Non vérifié')
                    ->query(fn (Builder $query) => $query->whereNull('verified_at')),
            ])
            ->recordActions([
                EditAction::make()
                    ->iconButton()
                    ->tooltip('Modifier'),

                Action::make('verify')
                    ->label('Vérifier')
                    ->icon('heroicon-o-shield-check')
                    ->color('success')
                    ->visible(fn ($record) => ! $record->verified_at)
                    ->action(fn ($record) => $record->update(['verified_at' => now()]))
                    ->requiresConfirmation()
                    ->tooltip('Marquer comme vérifié'),

                Action::make('visit')
                    ->label('Visiter')
                    ->icon('heroicon-o-arrow-top-right-on-square')
                    ->url(fn ($record) => 'http://'.$record->domain, shouldOpenInNewTab: true)
                    ->visible(fn ($record) => $record->is_active),

                DeleteAction::make()
                    ->iconButton()
                    ->tooltip('Supprimer')
                    ->requiresConfirmation()
                    ->before(function ($action, $record) {
                        if (! $record->tenant) {
                            $action->halt();
                        }
                    }),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                    ForceDeleteBulkAction::make(),
                    RestoreBulkAction::make(),
                ]),
            ])
            ->defaultSort('created_at', 'desc');
    }
}
