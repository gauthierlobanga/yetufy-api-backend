<?php

namespace App\Filament\Vendeur\Resources\Contacts\Pages;

use App\Filament\Vendeur\Resources\Contacts\ContactResource;
use Filament\Resources\Pages\CreateRecord;

class CreateContact extends CreateRecord
{
    protected static string $resource = ContactResource::class;
}
