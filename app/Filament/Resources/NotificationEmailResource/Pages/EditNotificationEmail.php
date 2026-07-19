<?php

namespace App\Filament\Resources\NotificationEmailResource\Pages;

use App\Filament\Resources\NotificationEmailResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditNotificationEmail extends EditRecord
{
    protected static string $resource = NotificationEmailResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
