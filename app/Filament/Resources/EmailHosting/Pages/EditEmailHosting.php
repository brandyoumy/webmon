<?php

namespace App\Filament\Resources\EmailHosting\Pages;

use App\Filament\Resources\EmailHosting\EmailHostingResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditEmailHosting extends EditRecord
{
    protected static string $resource = EmailHostingResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
