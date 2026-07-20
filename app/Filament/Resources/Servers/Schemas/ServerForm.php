<?php

namespace App\Filament\Resources\Servers\Schemas;

use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Schemas\Schema;

class ServerForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('name')
                    ->required()
                    ->label('Server Name')
                    ->placeholder('e.g. Production VPS'),

                TextInput::make('ip_address')
                    ->label('IP Address')
                    ->placeholder('e.g. 192.168.1.1')
                    ->ip(),

                TextInput::make('provider')
                    ->label('Provider')
                    ->placeholder('e.g. AWS, DigitalOcean, Linode'),

                Textarea::make('description')
                    ->label('Description')
                    ->rows(3)
                    ->placeholder('Enter server description or notes...'),
            ]);
    }
}
