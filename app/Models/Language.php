<?php

namespace App\Models;

use Nnjeim\World\Models\Country as BaseCountry;

class Language extends BaseCountry
{
    public function getConnectionName(): string
    {
        if (tenancy()->initialized) {
            return 'tenant';
        }

        return parent::getConnectionName();
    }
}
