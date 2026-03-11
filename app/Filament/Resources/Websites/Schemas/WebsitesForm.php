<?php

namespace App\Filament\Resources\Websites\Schemas;

use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Fieldset;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\ToggleColumn;

class WebsitesForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
               

                TextInput::make('url')
                ->url()
                ->required()
                ->label('Website Url'),

                Toggle::make('check_ssl')
                ->label('SSL Cert')
                ->default(true),

                 TextInput::make('name')
                ->required()
                ->label('Website Name'),

                TextInput::make('company_name')
                ->label('Company'),
                
                Fieldset::make('Person In Charge')
                ->schema([
                    TextInput::make('pic_email')
                    ->email()
                    ->label('Email')
                    ->columnSpan(6),

                    TextInput::make('pic_phone')
                    ->tel()
                    ->label('Phone No')
                    ->columnSpan(6)
                ])->columns(12),

            ]);
    }
}
