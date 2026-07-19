<?php

namespace App\Filament\Widgets;

use App\Filament\Resources\Websites\WebsitesResource;
use App\Models\Website;
use Filament\Support\Enums\IconPosition;
use Filament\Support\Icons\Heroicon;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class StatsDashboard extends StatsOverviewWidget
{
    protected function getStats(): array
    {
        $upWebsite = Website::where('is_up', true)->count();
        $downWebsite = Website::where('is_up', false)->count();

        $activeSSL   = Website::where('ssl_valid', true)->count();
        $inactiveSSL = Website::where('ssl_valid', false)->count();

        $upUrl = WebsitesResource::getUrl('index') . '?'
            . http_build_query(['filters' => ['website_status' => ['value' => 'up']]]);

        $downUrl = WebsitesResource::getUrl('index') . '?'
            . http_build_query(['filters' => ['website_status' => ['value' => 'down']]]);

        $activeSslUrl = WebsitesResource::getUrl('index') . '?'
            . http_build_query(['filters' => ['ssl_status' => ['value' => 'active']]]);

        $inactiveSslUrl = WebsitesResource::getUrl('index') . '?'
            . http_build_query(['filters' => ['ssl_status' => ['value' => 'inactive']]]);

        return [
            Stat::make('Active Websites', $upWebsite)
                ->description('Click to see active websites up at hosting')
                ->descriptionIcon(Heroicon::OutlinedGlobeAlt, IconPosition::Before)
                ->color('success')
                ->url($upUrl),

            Stat::make('Inactive', $downWebsite)
                ->description('Click to see which domains are down')
                ->descriptionIcon(Heroicon::OutlinedGlobeAlt, IconPosition::Before)
                ->color('danger')
                ->url($downUrl),

            Stat::make('Active SSL Certification', $activeSSL)
                ->description('Click to see websites with active SSL')
                ->descriptionIcon(Heroicon::OutlinedDocumentCheck, IconPosition::Before)
                ->color('success')
                ->url($activeSslUrl),

            Stat::make('Expired/Invalid SSL Certification', $inactiveSSL)
                ->description('Click to see which domains have invalid SSL')
                ->descriptionIcon(Heroicon::OutlinedXCircle, IconPosition::Before)
                ->color('danger')
                ->url($inactiveSslUrl),
        ];
    }
}
