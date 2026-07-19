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
            ->query(fn (): Builder => Website::where('ssl_valid', false)
                ->orderByDesc('last_checked_at')
                ->limit(5)
            )
            ->columns([
                TextColumn::make('name')
                    ->label('Website Name')
                    ->sortable(),

                TextColumn::make('last_checked_at')
                    ->label('Last Checked')
                    ->dateTime('Y-m-d H:i:s')
                    ->sortable(),

                BadgeColumn::make('ssl_valid')
                    ->label('SSL Status')
                    ->getStateUsing(fn ($record) => $record->ssl_valid ? 'Valid' : 'Invalid')
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