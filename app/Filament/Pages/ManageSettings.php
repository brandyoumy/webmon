<?php

namespace App\Filament\Pages;

use App\Models\Setting;
use Filament\Forms\Components\Select;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Schemas\Components\Fieldset;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use BackedEnum;
use UnitEnum;

class ManageSettings extends Page
{
    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedClock;

    protected static string|UnitEnum|null $navigationGroup = 'Settings & System';

    protected static ?int $navigationSort = 1;

    protected string $view = 'filament.pages.manage-settings';

    public ?array $data = [];

    public function mount(): void
    {
        $this->form->fill([
            'cron_interval' => (int) Setting::get('cron_interval', 5),
        ]);
    }

    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Fieldset::make('Website Monitoring Schedule')
                    ->schema([
                        Select::make('cron_interval')
                            ->label('Monitoring Check Interval')
                            ->options([
                                1  => 'Every 1 Minute (High Frequency)',
                                5  => 'Every 5 Minutes (Recommended Default)',
                                10 => 'Every 10 Minutes',
                                15 => 'Every 15 Minutes',
                                30 => 'Every 30 Minutes',
                                60 => 'Every 60 Minutes (Hourly)',
                            ])
                            ->required()
                            ->selectablePlaceholder(false),
                    ]),
            ])
            ->statePath('data');
    }

    public function save(): void
    {
        $state = $this->form->getState();

        Setting::set('cron_interval', $state['cron_interval']);

        Notification::make()
            ->title('Settings Saved Successfully')
            ->body('Monitoring cron job interval set to every ' . $state['cron_interval'] . ' minutes.')
            ->success()
            ->send();
    }
}
