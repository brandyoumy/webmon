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
            ->query(fn (): Builder => Website::where('is_up', false)
                ->orderByDesc('last_checked_at')
                ->limit(5)
            )
            ->columns([
                TextColumn::make('name')->label('Client Name'),
          
                TextColumn::make('last_checked_at')
                    ->label('Last Checked')
                    ->dateTime('Y-m-d H:i:s')
                    ->sortable(),
                BadgeColumn::make('is_up')
                ->label('Status')
                ->getStateUsing(fn ($record) => $record->is_up ? 'Up' : 'Down')
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