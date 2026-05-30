<?php

namespace App\Filament\Resources\VendorRequests\Pages;

use App\Filament\Resources\VendorRequests\VendorRequestResource;
use App\Models\VendorRequest;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;
use Filament\Schemas\Components\Tabs\Tab;

class ListVendorRequests extends ListRecords
{
    protected static string $resource = VendorRequestResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }

    public function getTabs(): array
    {
        return [
            'all' => Tab::make('Toutes')
                ->icon('heroicon-o-inbox-stack')
                ->badge(VendorRequest::count()),

            'pending' => Tab::make('En attente')
                ->icon('heroicon-o-clock')
                ->badge(VendorRequest::where('status', 'pending')->count())
                ->badgeColor('warning')
                ->modifyQueryUsing(fn ($query) => $query->where('status', 'pending')),

            'payment_pending' => Tab::make('Paiement en attente')
                ->icon('heroicon-o-credit-card')
                ->badge(VendorRequest::where('status', 'payment_pending')->count())
                ->badgeColor('amber')
                ->modifyQueryUsing(fn ($query) => $query->where('status', 'payment_pending')),

            'approved' => Tab::make('Approuvées')
                ->icon('heroicon-o-check-badge')
                ->badge(VendorRequest::where('status', 'approved')->count())
                ->badgeColor('success')
                ->modifyQueryUsing(fn ($query) => $query->where('status', 'approved')),

            'rejected' => Tab::make('Rejetées')
                ->icon('heroicon-o-x-circle')
                ->badge(VendorRequest::where('status', 'rejected')->count())
                ->badgeColor('danger')
                ->modifyQueryUsing(fn ($query) => $query->where('status', 'rejected')),

            'today' => Tab::make("Aujourd'hui")
                ->icon('heroicon-o-calendar-days')
                ->badge(VendorRequest::whereDate('created_at', today())->count())
                ->badgeColor('info')
                ->modifyQueryUsing(fn ($query) => $query->whereDate('created_at', today())),

            'this_week' => Tab::make('Cette semaine')
                ->icon('heroicon-o-calendar')
                ->badge(VendorRequest::whereBetween('created_at', [now()->startOfWeek(), now()->endOfWeek()])->count())
                ->badgeColor('purple')
                ->modifyQueryUsing(fn ($query) => $query->whereBetween('created_at', [now()->startOfWeek(), now()->endOfWeek()])),

            'this_month' => Tab::make('Ce mois')
                ->icon('heroicon-o-calendar-date-range')
                ->badge(VendorRequest::whereMonth('created_at', now()->month)->whereYear('created_at', now()->year)->count())
                ->badgeColor('emerald')
                ->modifyQueryUsing(fn ($query) => $query->whereMonth('created_at', now()->month)->whereYear('created_at', now()->year)),

            'trashed' => Tab::make('Corbeille')
                ->icon('heroicon-o-trash')
                ->badge(VendorRequest::onlyTrashed()->count())
                ->badgeColor('gray')
                ->modifyQueryUsing(fn ($query) => $query->onlyTrashed()),
        ];
    }
}
