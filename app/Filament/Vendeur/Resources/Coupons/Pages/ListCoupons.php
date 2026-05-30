<?php

namespace App\Filament\Vendeur\Resources\Coupons\Pages;

use App\Filament\Vendeur\Resources\Coupons\CouponResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListCoupons extends ListRecords
{
    protected static string $resource = CouponResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
