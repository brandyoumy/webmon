<?php

namespace App\Filament\Resources\NotificationEmailResource\Pages;

use App\Filament\Resources\NotificationEmailResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListNotificationEmails extends ListRecords
{
    protected static string $resource = NotificationEmailResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make()->label('Add Notification Email'),
        ];
    }
}
