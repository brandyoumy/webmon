<?php

namespace App\Filament\Resources\Servers\Schemas;

use Filament\Forms\Components\TagsInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
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

                TagsInput::make('ip_address')
                    ->label('IP Address(es)')
                    ->placeholder('e.g. 192.168.1.1 (press Enter)')
                    ->rules([
                        function () {
                            return function (string $attribute, $value, \Closure $fail) {
                                if (! is_array($value)) {
                                    return;
                                }
                                foreach ($value as $ip) {
                                    if (! filter_var($ip, FILTER_VALIDATE_IP)) {
                                        $fail("The IP address '{$ip}' is invalid.");
                                    }
                                }
                            };
                        },
                    ]),

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
