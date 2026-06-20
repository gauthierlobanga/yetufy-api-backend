<?php

use Spatie\LaravelSettings\Migrations\SettingsMigration;

return new class extends SettingsMigration
{
    public function up(): void
    {
        $this->migrator->add('app.facebook_url', null);
        $this->migrator->add('app.instagram_url', null);
        $this->migrator->add('app.x_url', null);
        $this->migrator->add('app.linkedin_url', null);
        $this->migrator->add('app.youtube_url', null);
    }
};
