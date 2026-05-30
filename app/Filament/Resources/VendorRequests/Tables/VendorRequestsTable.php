<?php

// app/Filament/Resources/VendorRequests/Tables/VendorRequestsTable.php

namespace App\Filament\Resources\VendorRequests\Tables;

use App\Models\VendorRequest;
use App\Services\VendorRegistrationService;
use Filament\Actions\Action;
use Filament\Actions\ActionGroup;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ForceDeleteBulkAction;
use Filament\Actions\RestoreBulkAction;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Textarea;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Str;

class VendorRequestsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('tenant.raison_sociale')
                    ->label('Organisation')
                    ->searchable()
                    ->sortable()
                    ->icon('heroicon-o-user')
                    ->iconColor('gray')
                    // ->description(fn ($record) => $record->user?->email)
                    // ->tooltip(fn ($record) => 'ID utilisateur : '.$record->user_id)
                    ->weight('medium'),

                TextColumn::make('user.name')
                    ->label('Demandeur')
                    ->searchable()
                    ->sortable()
                    ->icon('heroicon-o-user')
                    ->iconColor('gray')
                    ->description(fn ($record) => $record->user?->email)
                    ->tooltip(fn ($record) => 'ID utilisateur : '.$record->user_id)
                    ->weight('medium'),

                TextColumn::make('plan.name')
                    ->label('Plan choisi')
                    ->searchable()
                    ->sortable()
                    ->icon('heroicon-o-banknotes')
                    ->iconColor('amber')
                    ->badge()
                    ->color(fn ($record) => $record->plan?->isFree() ? 'gray' : 'amber')
                    ->formatStateUsing(fn ($state, $record) => $state.($record->plan?->isFree() ? ' (gratuit)' : '')),

                TextColumn::make('shop_name')
                    ->label('Boutique')
                    ->searchable()
                    ->sortable()
                    ->weight('bold')
                    ->icon('heroicon-o-building-storefront')
                    ->iconColor('primary')
                    ->description(fn ($record) => $record->shop_slug.'.'.config('app.domain')),

                TextColumn::make('status')
                    ->label('Statut')
                    ->searchable()
                    ->sortable()
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        VendorRequest::STATUS_PENDING => 'warning',
                        VendorRequest::STATUS_PAYMENT_PENDING => 'gray',
                        VendorRequest::STATUS_APPROVED => 'success',
                        VendorRequest::STATUS_REJECTED => 'danger',
                        default => 'gray',
                    })
                    ->icon(fn (string $state): string => match ($state) {
                        VendorRequest::STATUS_PENDING => 'heroicon-o-clock',
                        VendorRequest::STATUS_PAYMENT_PENDING => 'heroicon-o-credit-card',
                        VendorRequest::STATUS_APPROVED => 'heroicon-o-check-circle',
                        VendorRequest::STATUS_REJECTED => 'heroicon-o-x-circle',
                        default => 'heroicon-o-question-mark-circle',
                    })
                    ->formatStateUsing(fn ($state) => match ($state) {
                        VendorRequest::STATUS_PENDING => 'En attente de validation',
                        VendorRequest::STATUS_PAYMENT_PENDING => 'Paiement en attente',
                        VendorRequest::STATUS_APPROVED => 'Approuvée',
                        VendorRequest::STATUS_REJECTED => 'Rejetée',
                        default => $state,
                    }),

                TextColumn::make('contact_email')
                    ->label('Contact')
                    ->searchable()
                    ->icon('heroicon-o-envelope')
                    ->toggleable()
                    ->description(fn ($record) => $record->contact_phone ?? '')
                    ->copyable(),

                TextColumn::make('payment_status')
                    ->label('Paiement')
                    ->badge()
                    ->color(fn ($state) => match ($state) {
                        'paid' => 'success',
                        'pending' => 'warning',
                        'failed' => 'danger',
                        default => 'gray',
                    })
                    ->placeholder('Non concerné')
                    ->toggleable(isToggledHiddenByDefault: false),

                TextColumn::make('created_at')
                    ->label('Demandé le')
                    ->dateTime('d/m/Y H:i')
                    ->sortable()
                    ->icon('heroicon-o-calendar')
                    ->toggleable(),

                TextColumn::make('approved_at')
                    ->label('Approuvé le')
                    ->dateTime('d/m/Y H:i')
                    ->sortable()
                    ->icon('heroicon-o-check-badge')
                    ->placeholder('—')
                    ->toggleable(),
            ])
            ->filters([
                SelectFilter::make('status')
                    ->label('Statut')
                    ->options(VendorRequest::getStatuses()),

                SelectFilter::make('plan_id')
                    ->label('Plan')
                    ->relationship('plan', 'name'),

                TernaryFilter::make('has_payment')
                    ->label('Avec paiement')
                    ->queries(
                        true: fn (Builder $query) => $query->where('status', VendorRequest::STATUS_PAYMENT_PENDING),
                        false: fn (Builder $query) => $query->where('status', '!=', VendorRequest::STATUS_PAYMENT_PENDING),
                    ),

                Filter::make('created_at')
                    ->form([
                        DatePicker::make('created_from'),
                        DatePicker::make('created_until'),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query
                            ->when($data['created_from'], fn ($q, $date) => $q->whereDate('created_at', '>=', $date))
                            ->when($data['created_until'], fn ($q, $date) => $q->whereDate('created_at', '<=', $date));
                    }),
            ])
            ->recordActions([
                ActionGroup::make([
                    Action::make('approve')
                        ->label('Approuver')
                        ->icon('heroicon-o-check-circle')
                        ->color('success')
                        ->visible(fn ($record) => $record->status === VendorRequest::STATUS_PENDING)
                        ->action(function (VendorRequest $record) {
                            $service = app(VendorRegistrationService::class);
                            $tenant = $service->approve($record);
                            // Redirection ou notification
                        })
                        ->requiresConfirmation()
                        ->modalHeading('Approuver cette demande ?')
                        ->modalDescription(fn ($record) => "La boutique « {$record->shop_name} » sera créée et l'utilisateur deviendra propriétaire.")
                        ->modalSubmitActionLabel('Oui, approuver'),

                    Action::make('reject')
                        ->label('Rejeter')
                        ->icon('heroicon-o-x-circle')
                        ->color('danger')
                        ->visible(fn ($record) => $record->status === VendorRequest::STATUS_PENDING)
                        ->schema([
                            Textarea::make('reason')
                                ->label('Motif du rejet')
                                ->required()
                                ->maxLength(500),
                        ])
                        ->action(function (VendorRequest $record, array $data) {
                            $service = app(VendorRegistrationService::class);
                            $service->reject($record, $data['reason']);
                        })
                        ->requiresConfirmation()
                        ->modalHeading('Rejeter cette demande ?')
                        ->modalSubmitActionLabel('Oui, rejeter'),

                    Action::make('markPaymentReceived')
                        ->label('Paiement reçu')
                        ->icon('heroicon-o-credit-card')
                        ->color('success')
                        ->visible(fn ($record) => $record->status === VendorRequest::STATUS_PAYMENT_PENDING)
                        ->action(function (VendorRequest $record) {
                            $service = app(VendorRegistrationService::class);
                            $service->markPaymentReceived($record, 'manual_'.Str::random(10));
                        }),

                    EditAction::make(),
                    DeleteAction::make(),
                ])
                    ->tooltip('Actions')
                    ->icon('heroicon-o-ellipsis-horizontal'),
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
