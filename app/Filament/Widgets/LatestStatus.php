<?php

namespace App\Filament\Widgets;

use App\Models\UptimeLogs;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\BadgeColumn;
use Illuminate\Database\Eloquent\Builder;
use App\Models\Website;

class LatestStatus extends TableWidget
{
    protected static ?string $heading = 'Websites Down (Latest)';

    protected static ?int $sort = 1;

    public function table(Table $table): Table
    {
        return $table
            ->query(fn (): Builder => Website::with('latestLog')
                ->whereHas('latestLog', function ($query) {
                    $query->where('is_up', false); // Only websites that are down
                })
                ->orderByDesc(
                    UptimeLogs::select('checked_at')
                        ->whereColumn('websites.id', 'uptime_logs.websites_id')
                        ->latest()
                        ->limit(1)

                )->limit(5)
            )
            ->columns([
                TextColumn::make('name')->label('Website Name'),
          
                TextColumn::make('latestLog.checked_at')
                    ->label('Last Checked')
                    ->dateTime('Y-m-d H:i:s')
                    ->sortable(),
                BadgeColumn::make('latestLog.is_up')
                ->label('Status')
                ->getStateUsing(fn ($record) => $record->latestLog->is_up ? 'Up' : 'Down')
                ->colors([
                    'success' => fn ($state) => $state === 'Up',
                    'danger' => fn ($state) => $state === 'Down',
                ]),
            ])
            ->paginated(false)
            ->filters([
                //
            ])
            ->headerActions([
                //
            ])
            ->recordActions([
                //
            ])
            ->toolbarActions([]);
    }
    
}