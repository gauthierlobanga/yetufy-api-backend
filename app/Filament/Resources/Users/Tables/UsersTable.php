<?php

namespace App\Filament\Resources\Users\Tables;

use App\Models\User;
use Filament\Actions\Action;
use Filament\Actions\ActionGroup;
use Filament\Actions\BulkAction;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ForceDeleteBulkAction;
use Filament\Actions\RestoreBulkAction;
use Filament\Actions\ViewAction;
use Filament\Forms\Components\DatePicker;
// use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\SpatieMediaLibraryImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Filters\TrashedFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

// use STS\FilamentImpersonate\Actions\Impersonate;

class UsersTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                SpatieMediaLibraryImageColumn::make('avatar_url')
                    ->label('Avatar')
                    ->collection('avatar')
                    ->conversion('thumbnail')
                    ->visibility('public')
                    ->circular()
                    ->imageSize(50)
                    ->defaultImageUrl(fn ($record) => 'https://ui-avatars.com/api/?name='.urlencode($record->name).'&color=7F9CF5&background=EBF4FF&size=128&bold=true'),

                TextColumn::make('name')
                    ->label('Nom')
                    ->searchable(['name', 'email'])
                    ->sortable()
                    ->description(fn ($record) => $record->email)
                    ->weight('medium'),

                TextColumn::make('roles.name')
                    ->label('Rôles')
                    ->badge()
                    ->separator(',')
                    ->color(fn (string $state): string => match ($state) {
                        'super_admin' => 'danger',
                        'admin' => 'warning',
                        'editor' => 'info',
                        default => 'gray',
                    })
                    ->formatStateUsing(fn ($state) => ucfirst($state))
                    ->searchable(),

                IconColumn::make('is_active')
                    ->label('Actif')
                    ->boolean()
                    ->trueIcon('heroicon-o-check-circle')
                    ->falseIcon('heroicon-o-x-circle')
                    ->trueColor('success')
                    ->falseColor('danger')
                    ->sortable(),

                IconColumn::make('email_verified_at')
                    ->label('Vérifié')
                    ->boolean()
                    ->trueIcon('heroicon-o-check-circle')
                    ->falseIcon('heroicon-o-x-circle')
                    ->trueColor('success')
                    ->falseColor('warning'),

                TextColumn::make('created_at')
                    ->label('Créé le')
                    ->dateTime('d/m/Y H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),

                TextColumn::make('updated_at')
                    ->label('Modifié le')
                    ->dateTime('d/m/Y H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),

                TextColumn::make('two_factor_confirmed_at')
                    ->label('2FA confirmé')
                    ->dateTime('d/m/Y H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([

                TrashedFilter::make()
                    ->searchable()
                    ->preload(),

                TernaryFilter::make('is_active')
                    ->label('Statut du compte')
                    ->placeholder('Tous les utilisateurs')
                    ->trueLabel('Utilisateurs actifs')
                    ->searchable()
                    ->falseLabel('Utilisateurs inactifs'),

                TernaryFilter::make('email_verified_at')
                    ->label('Vérification email')
                    ->placeholder('Tous')
                    ->searchable()
                    ->trueLabel('Emails vérifiés')
                    ->falseLabel('Emails non vérifiés')
                    ->queries(
                        true: fn (Builder $query) => $query->whereNotNull('email_verified_at'),
                        false: fn (Builder $query) => $query->whereNull('email_verified_at'),
                        blank: fn (Builder $query) => $query,
                    ),

                SelectFilter::make('roles')
                    ->relationship('roles', 'name')
                    ->multiple()
                    ->preload()
                    ->searchable()
                    ->label('Rôles'),

                Filter::make('created_at')
                    ->schema([
                        DatePicker::make('created_from')
                            ->label('Créé depuis')
                            ->native(false),
                        DatePicker::make('created_until')
                            ->label('Créé jusqu\'à')
                            ->native(false),
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
            ->recordActions([
                // Impersonate::make()
                //     ->label('Se connecter en tant que')
                //     ->icon(Heroicon::Swatch)
                //     ->color('warning')
                //     ->requiresConfirmation()
                //     ->modalHeading('Connexion impersonnelle')
                //     ->modalDescription('Vous allez vous connecter en tant que cet utilisateur. Vous pourrez revenir à votre compte en cliquant sur "Arrêter l\'impersonation".')
                //     ->guard('web')
                //     ->redirectTo('/dashboard'), // Garde par défaut, à adapter si besoin
                // ->redirectTo(redirect(uri('/dashboard'))), // Où rediriger après impersonation

                ActionGroup::make([
                    ViewAction::make()
                        ->label('Voir')
                        ->icon('heroicon-m-eye'),

                    EditAction::make()
                        ->label('Modifier')
                        ->icon('heroicon-m-pencil-square'),

                    Action::make('activate')
                        ->label('Activer')
                        ->icon('heroicon-m-check-circle')
                        ->color('success')
                        ->requiresConfirmation()
                        ->modalHeading('Activer l\'utilisateur')
                        ->modalDescription('Êtes-vous sûr de vouloir activer cet utilisateur ?')
                        ->visible(fn (User $record): bool => ! $record->is_active)
                        ->action(fn (User $record) => $record->update(['is_active' => true])),

                    Action::make('deactivate')
                        ->label('Désactiver')
                        ->icon('heroicon-m-x-circle')
                        ->color('danger')
                        ->requiresConfirmation()
                        ->modalHeading('Désactiver l\'utilisateur')
                        ->modalDescription('Êtes-vous sûr de vouloir désactiver cet utilisateur ?')
                        ->visible(fn (User $record): bool => $record->is_active)
                        ->action(fn (User $record) => $record->update(['is_active' => false])),

                ])
                    ->label('')
                    ->icon('heroicon-m-ellipsis-vertical')
                    ->size('sm')
                    ->button()
                    ->color('gray'),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                    ForceDeleteBulkAction::make(),
                    RestoreBulkAction::make(),
                    DeleteBulkAction::make()
                        ->requiresConfirmation()
                        ->modalHeading('Supprimer les utilisateurs sélectionnés')
                        ->modalDescription('Cette action est irréversible. Les utilisateurs sélectionnés seront définitivement supprimés.')
                        ->modalSubmitActionLabel('Oui, supprimer'),

                    BulkAction::make('activate')
                        ->label('Activer les sélectionnés')
                        ->icon('heroicon-m-check-circle')
                        ->color('success')
                        ->requiresConfirmation()
                        ->modalHeading('Activer les utilisateurs sélectionnés')
                        ->modalDescription('Êtes-vous sûr de vouloir activer tous les utilisateurs sélectionnés ?')
                        ->action(fn ($records) => $records->each->update(['is_active' => true])),

                    BulkAction::make('deactivate')
                        ->label('Désactiver les sélectionnés')
                        ->icon('heroicon-m-x-circle')
                        ->color('danger')
                        ->requiresConfirmation()
                        ->modalHeading('Désactiver les utilisateurs sélectionnés')
                        ->modalDescription('Êtes-vous sûr de vouloir désactiver tous les utilisateurs sélectionnés ?')
                        ->action(fn ($records) => $records->each->update(['is_active' => false])),
                ]),
            ])
            ->emptyStateHeading('Aucun utilisateur')
            ->emptyStateDescription('Commencez par créer un utilisateur en cliquant sur le bouton ci-dessous.')
            ->emptyStateIcon('heroicon-o-users')
            ->defaultSort('created_at', 'desc')
            ->striped()
            ->paginated([10, 25, 50, 100]);
    }
}
