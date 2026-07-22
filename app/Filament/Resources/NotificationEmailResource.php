<?php

namespace App\Filament\Resources;

use App\Filament\Resources\NotificationEmailResource\Pages;
use App\Filament\Resources\NotificationEmailResource\Schemas\NotificationEmailForm;
use App\Models\NotificationEmail;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\ToggleColumn;
use Filament\Tables\Table;
use BackedEnum;
use UnitEnum;

class NotificationEmailResource extends Resource
{
    protected static ?string $model = NotificationEmail::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedBellAlert;

    protected static string|UnitEnum|null $navigationGroup = 'Settings & System';

    protected static ?int $navigationSort = 2;

    public static function form(Schema $schema): Schema
    {
        return NotificationEmailForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')
                    ->label('Name')
                    ->searchable()
                    ->sortable()
                    ->placeholder('—'),

                TextColumn::make('email')
                    ->label('Email Address')
                    ->searchable()
                    ->sortable(),

                ToggleColumn::make('is_active')
                    ->label('Active Alert Recipient')
                    ->sortable(),

                TextColumn::make('updated_at')
                    ->label('Last Updated')
                    ->dateTime('d M Y H:i')
                    ->sortable(),
            ])
            ->recordActions([
                EditAction::make(),
                DeleteAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ])
            ->defaultPaginationPageOption(20)
            ->paginationPageOptions([10, 20, 25, 50, 100]);
    }

    public static function getPages(): array
    {
        return [
            'index'  => Pages\ListNotificationEmails::route('/'),
            'create' => Pages\CreateNotificationEmail::route('/create'),
            'edit'   => Pages\EditNotificationEmail::route('/{record}/edit'),
        ];
    }
}
