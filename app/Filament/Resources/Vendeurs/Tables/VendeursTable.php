<?php

namespace App\Filament\Resources\Vendeurs\Tables;

use App\Models\Tenant;
use App\Models\User;
use Filament\Actions\Action;
use Filament\Actions\BulkAction;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ForceDeleteBulkAction;
use Filament\Actions\RestoreBulkAction;
use Filament\Forms\Components\Select;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\SpatieMediaLibraryImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Filters\TrashedFilter;
use Filament\Tables\Table;
use Illuminate\Support\Facades\Auth;

class VendeursTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                SpatieMediaLibraryImageColumn::make('tenant_avatar')
                    ->label('Avatar')
                    ->circular()
                    ->collection('tenant_avatar')
                    ->defaultImageUrl(fn ($record) => $record->avatar_url),

                TextColumn::make('raison_sociale')
                    ->searchable()
                    ->sortable()
                    ->description(fn (Tenant $record) => $record->email),

                TextColumn::make('utilisateurs')
                    ->searchable()
                    ->sortable()
                    ->counts('users')
                    ->description(fn (Tenant $record) => $record->email),

                TextColumn::make('type_entite')
                    ->label('Type')
                    ->badge()
                    ->formatStateUsing(fn ($state) => Tenant::getTypesEntite()[$state] ?? $state),

                TextColumn::make('statut')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'actif' => 'success',
                        'inactif' => 'gray',
                        'en_attente' => 'warning',
                        'suspendu' => 'danger',
                        default => 'gray',
                    })
                    ->toggleable(isToggledHiddenByDefault: true)
                    ->formatStateUsing(fn ($state) => Tenant::getStatuts()[$state] ?? $state),

                IconColumn::make('is_active')
                    ->boolean()
                    ->label('Actif'),

                TextColumn::make('documents_legaux_count')
                    ->label('Docs')
                    ->counts('documentsLegaux')
                    ->alignCenter(),

                TextColumn::make('pourcentage_verification')
                    ->label('Vérification')
                    ->state(fn (Tenant $record) => $record->pourcentage_verification.'%')
                    ->sortable(),

                TextColumn::make('created_at')
                    ->dateTime('d/m/Y')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                SelectFilter::make('statut')
                    ->options(Tenant::getStatuts())
                    ->preload()
                    ->searchable(),
                SelectFilter::make('type_entite')
                    ->options(Tenant::getTypesEntite())
                    ->preload()
                    ->searchable(),
                TernaryFilter::make('is_active')
                    ->preload()
                    ->searchable(),
                TrashedFilter::make()
                    ->placeholder('Enregistrer supprimés')
                    ->searchable()
                    ->preload(),
            ])
            ->recordActions([
                Action::make('members')
                    ->visible(fn (): bool => Auth::user()->hasRole('super_admin'))
                    ->icon(Heroicon::OutlinedPlusCircle)
                    ->schema(function () {
                        return [
                            Select::make('selectedUsers')
                                ->options(User::pluck('name', 'id')->toArray())
                                ->multiple()
                                ->preload()
                                ->searchable(),
                        ];
                    })->action(function (Tenant $record, array $data) {
                        $selectedUsers = $data['selectedUsers'];
                        $record->users()->syncWithoutDetaching($selectedUsers);
                    }),

                Action::make('activate')
                    ->label('Activer')
                    ->icon('heroicon-o-check-circle')
                    ->color('success')
                    ->action(fn (Tenant $record) => $record->update(['statut' => 'actif', 'is_active' => true]))
                    ->visible(fn (Tenant $record) => $record->statut !== 'actif')
                    ->requiresConfirmation(),
                Action::make('suspend')
                    ->label('Suspendre')
                    ->icon('heroicon-o-x-circle')
                    ->color('danger')
                    ->action(fn (Tenant $record) => $record->update(['statut' => 'suspendu', 'is_active' => false]))
                    ->visible(fn (Tenant $record) => $record->statut !== 'suspendu')
                    ->requiresConfirmation(),
                EditAction::make()
                    ->label('')
                    ->icon(Heroicon::OutlinedPencilSquare),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    BulkAction::make('activate')                  // ← BulkAction au lieu de Action
                        ->label('Activer la sélection')
                        ->icon('heroicon-o-check-circle')
                        ->color('success')
                        ->action(fn ($records) => $records->each->update(['statut' => 'actif', 'is_active' => true]))
                        ->deselectRecordsAfterCompletion()
                        ->requiresConfirmation(),
                    DeleteBulkAction::make(),
                    ForceDeleteBulkAction::make(),
                    RestoreBulkAction::make(),
                ]),
            ])->emptyStateHeading('Aucune boutique')
            ->emptyStateDescription('Commencez par créer un une boutique en cliquant sur le bouton ci-dessous.')
            ->emptyStateIcon('heroicon-o-users')
            ->defaultSort('created_at', 'desc')
            ->striped()
            ->paginated([10, 25, 50, 100]);
    }
}
