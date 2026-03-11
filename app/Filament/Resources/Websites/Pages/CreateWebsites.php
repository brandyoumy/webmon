<?php

namespace App\Filament\Resources\Websites\Pages;

use App\Filament\Resources\Websites\WebsitesResource;
use Filament\Resources\Pages\CreateRecord;

class CreateWebsites extends CreateRecord
{
    protected static string $resource = WebsitesResource::class;

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
