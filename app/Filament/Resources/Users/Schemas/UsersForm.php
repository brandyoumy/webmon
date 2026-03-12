<?php

namespace App\Filament\Resources\Users\Schemas;

use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;

class UsersForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('username')->label('Username')
                ->required()->unique(),
                TextInput::make('password')
                ->label('Password')
                ->password()
                ->revealable()
                ->required(fn ($context) => $context === 'create')
                ->dehydrated(fn ($state) => filled($state)),
                TextInput::make('name')->label('Full Name')->required(),
                TextInput::make('email')->email()->label("Email")->required(),
                Select::make('access_level')->label('Roles')->options([
                    'user' => 'User',
                    'admin' => 'Admin',
                ])->default('user')->required()
            ]);
    }
}
