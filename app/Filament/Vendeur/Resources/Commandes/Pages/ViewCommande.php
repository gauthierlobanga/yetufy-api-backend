<?php

namespace App\Filament\Vendeur\Resources\Commandes\Pages;

use App\Filament\Vendeur\Resources\Commandes\CommandeResource;
use Filament\Actions;
use Filament\Resources\Pages\ViewRecord;

class ViewCommande extends ViewRecord
{
    protected static string $resource = CommandeResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\EditAction::make(),
            Actions\Action::make('invoice')
                ->label('Facture')
                ->icon('heroicon-o-document')
                ->url(fn ($record) => route('tenant.orders.invoice', $record))
                ->openUrlInNewTab(),
            Actions\Action::make('markAsPaid')
                ->label('Marquer payée')
                ->icon('heroicon-o-check-circle')
                ->color('success')
                ->action(fn ($record) => $record->marquerPayee())
                ->visible(fn ($record) => $record->statut === 'en_attente')
                ->requiresConfirmation(),
        ];
    }
}
