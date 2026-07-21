<?php

namespace App\Filament\Resources\EmailHosting\Pages;

use App\Filament\Resources\EmailHosting\EmailHostingResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListEmailHostings extends ListRecords
{
    protected static string $resource = EmailHostingResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make()->label('Add Email Hosting'),
        ];
    }
}
