<?php

namespace App\Filament\Widgets;

use Filament\Tables;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\BadgeColumn;
use Illuminate\Database\Eloquent\Builder;
use App\Models\Website;
use App\Models\UptimeLogs;

class LatestSSL extends TableWidget
{
    protected static ?string $heading = 'Latest Websites with Invalid SSL';
    
    
    protected static ?int $sort = 2;

    public function table(Table $table): Table
    {
        return $table
            ->query(fn (): Builder => Website::with('latestLog')
                ->whereHas('latestLog', fn ($query) => $query->where('ssl_valid', false))
                ->orderByDesc(
                    UptimeLogs::select('checked_at')
                        ->whereColumn('websites.id', 'uptime_logs.websites_id')
                        ->latest()
                        ->limit(1)
                )
                ->limit(5)
            )
            ->columns([
                TextColumn::make('name')
                    ->label('Website Name')
                    ->sortable(),

                TextColumn::make('latestLog.checked_at')
                    ->label('Last Checked')
                    ->dateTime('Y-m-d H:i:s')
                    ->sortable(),

                BadgeColumn::make('latestLog.ssl_valid')
                    ->label('SSL Status')
                    ->getStateUsing(fn ($record) => $record->latestLog->ssl_valid ? 'Valid' : 'Invalid')
                    ->colors([
                        'success' => fn ($state) => $state === 'Valid',
                        'danger' => fn ($state) => $state === 'Invalid',
                    ]),
            ])
            ->paginated(false)
            ->filters([])
            ->headerActions([])
            ->recordActions([])
            ->toolbarActions([]);
    }
}