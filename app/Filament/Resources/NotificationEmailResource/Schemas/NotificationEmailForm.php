<?php

namespace App\Filament\Resources\NotificationEmailResource\Schemas;

use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;

class NotificationEmailForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('name')
                    ->label('Recipient Name')
                    ->placeholder('e.g. Clement / IT Support')
                    ->maxLength(255),

                TextInput::make('email')
                    ->label('Email Address')
                    ->email()
                    ->required()
                    ->unique(ignoreRecord: true)
                    ->maxLength(255),

                Toggle::make('is_active')
                    ->label('Active (Receive Alerts)')
                    ->default(true),
            ]);
    }
}
