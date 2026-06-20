<?php

use Spatie\LaravelSettings\Migrations\SettingsMigration;

return new class extends SettingsMigration
{
    public function up(): void
    {
        $this->migrator->add('app.address', '123 Avenue de l’Immobilier, Kinshasa');
        $this->migrator->add('app.phone', '+243 123 456 789');
        $this->migrator->add('app.email', 'contact@immo-rdc.cd');
    }
};
