<?php

namespace App\Filament\Clients\Resources\Users\Schemas;

use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;

class UserForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('name')
                    ->required(),
                TextInput::make('first_name'),
                TextInput::make('last_name'),
                TextInput::make('email')
                    ->label('Email address')
                    ->email()
                    ->required(),
                DateTimePicker::make('email_verified_at'),
                Toggle::make('email_verified')
                    ->required(),
                TextInput::make('password')
                    ->password()
                    ->required(),
                Toggle::make('is_active')
                    ->required(),
                TextInput::make('preferences'),
                Textarea::make('two_factor_secret')
                    ->columnSpanFull(),
                Textarea::make('two_factor_recovery_codes')
                    ->columnSpanFull(),
                DateTimePicker::make('two_factor_confirmed_at'),
                TextInput::make('stripe_id'),
                TextInput::make('pm_type'),
                TextInput::make('pm_last_four'),
                DateTimePicker::make('trial_ends_at'),
                Toggle::make('email_verifie')
                    ->required(),
                TextInput::make('provider_id'),
                TextInput::make('provider'),
                TextInput::make('avatar'),
            ]);
    }
}
