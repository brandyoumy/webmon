<?php

namespace App\Filament\Widgets;

use App\Models\Website;
use Filament\Support\Enums\IconPosition;
use Filament\Support\Icons\Heroicon;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class StatsDashboard extends StatsOverviewWidget
{
    protected function getStats(): array
    {

        $upWebsite = Website::with('latestLog')
        ->get()->filter(fn($website)=> $website->latestLog?->is_up)
        ->count();

        $downWebsite = Website::with('latestLog')
        ->get()->filter(fn($website)=> !$website->latestLog?->is_up)
        ->count();

        $activeSSL = Website::where('check_ssl',true)->count();

        $inactiveSSL = Website::where('check_ssl',false)->count();

        return [
            Stat::make('Active Websites', $upWebsite)
            ->description('Websites Still Up At The Hosting')
            ->descriptionIcon(Heroicon::OutlinedGlobeAlt,IconPosition::Before)
            ->color('success'),

            Stat::make('Inactive', $downWebsite)
            ->description('Websites Down At The Hosting')
            ->descriptionIcon(Heroicon::OutlinedGlobeAlt,IconPosition::Before)
            ->color('danger'),

            Stat::make('Active SSL Certification', $activeSSL)
            ->description('Websites still valid')
            ->descriptionIcon(Heroicon::OutlinedDocumentCheck,IconPosition::Before)
            ->color('success'),

            Stat::make('Expired/Invalid SSL Certification', $inactiveSSL)
            ->description('Website Already Expired/Invalid')
            ->descriptionIcon(Heroicon::OutlinedXCircle,IconPosition::Before)
            ->color('danger'),

        ];
    }

    // public function getColumns(): int | array
    // {
    //     return 2;
    // }
}
