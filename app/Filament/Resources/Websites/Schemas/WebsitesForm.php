<?php

namespace App\Filament\Resources\Websites\Schemas;

use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
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
                ->unique(ignoreRecord: true)
                ->label('Website Url'),

                Toggle::make('check_ssl')
                ->label('SSL Cert')
                ->default(true),

                 TextInput::make('name')
                ->required()
                ->label('Client Name'),

                TextInput::make('company_name')
                ->label('Company'),

                Select::make('product_id')
                ->relationship('product', 'name')
                ->label('Package')
                ->placeholder('Select a package')
                ->searchable()
                ->preload(),

                Select::make('server_id')
                ->relationship('server', 'name')
                ->label('Server')
                ->placeholder('Select a server')
                ->searchable()
                ->preload(),
                
                Fieldset::make('Person In Charge')
                ->schema([
                    TextInput::make('pic_email')
                    ->email()
                    ->label('Email')
                    ->columnSpan(6),

                    TextInput::make('pic_phone')
                    ->tel()
                    ->label('Phone No')
                    ->placeholder('e.g. +6012-345 6789')
                    ->prefixIcon('heroicon-o-phone')
                    ->helperText('Formats to international standard on save (e.g. 012-345 6789 becomes +60123456789).')
                    ->dehydrateStateUsing(function ($state) {
                        if (!$state) {
                            return null;
                        }
                        // Remove all non-digit characters except +
                        $cleaned = preg_replace('/[^\d+]/', '', $state);
                        
                        // If it starts with 0, replace with +60 (Malaysia country code)
                        if (str_starts_with($cleaned, '0')) {
                            $cleaned = '+60' . substr($cleaned, 1);
                        }
                        
                        // If it doesn't start with +, add it (assuming it's a country code now)
                        if ($cleaned !== '' && !str_starts_with($cleaned, '+')) {
                            $cleaned = '+' . $cleaned;
                        }
                        
                        return $cleaned;
                    })
                    ->columnSpan(6)
                ])->columns(12),

                Textarea::make('remark')
                ->label('Remark')
                ->rows(3),

            ]);
    }
}
