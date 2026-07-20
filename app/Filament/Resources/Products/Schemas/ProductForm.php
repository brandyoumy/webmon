<?php

namespace App\Filament\Resources\Products\Schemas;

use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Schemas\Schema;

class ProductForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('name')
                    ->required()
                    ->label('Product Name')
                    ->placeholder('e.g. Basic Package'),

                TextInput::make('price')
                    ->numeric()
                    ->label('Price')
                    ->placeholder('e.g. 99.00')
                    ->prefix('RM'),

                Textarea::make('description')
                    ->label('Description')
                    ->rows(3)
                    ->placeholder('Enter product/package description...'),
            ]);
    }
}
