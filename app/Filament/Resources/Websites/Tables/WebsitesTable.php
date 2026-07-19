<?php

namespace App\Filament\Resources\Websites\Tables;

use App\Models\UptimeLogs;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\BadgeColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class WebsitesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')->searchable()->sortable(),

                TextColumn::make('url')
                    ->label('URL')
                    ->searchable()
                    ->url(fn ($record) => $record->url)
                    ->openUrlInNewTab()
                    ->color('primary'),

                TextColumn::make('company_name')->label('Company')->searchable()->sortable(),

                BadgeColumn::make('ssl_status')
                    ->label('SSL Cert')
                    ->getStateUsing(function ($record) {
                        if (!$record->check_ssl) {
                            return 'Disabled';
                        }
                        return $record->ssl_valid ? 'Active' : 'Expired/Invalid';
                    })
                    ->colors([
                        'success' => fn ($state) => $state === 'Active',
                        'danger'  => fn ($state) => $state === 'Expired/Invalid',
                        'gray'    => fn ($state) => $state === 'Disabled',
                    ])
                    ->sortable(query: fn (Builder $query, string $direction) => $query->orderBy('ssl_valid', $direction)),

                BadgeColumn::make('status')
                    ->label('Status')
                    ->getStateUsing(fn ($record) => $record->is_up ? 'OK' : 'DOWN')
                    ->colors([
                        'success' => fn ($state) => $state === 'OK',
                        'danger'  => fn ($state) => $state === 'DOWN',
                    ])
                    ->sortable(query: fn (Builder $query, string $direction) => $query->orderBy('is_up', $direction)),

                TextColumn::make('domain_expires_at')
                    ->label('Domain Expiry')
                    ->badge()
                    ->getStateUsing(function ($record) {
                        if (!$record->domain_expires_at) return 'N/A';
                        $days = (int) now()->diffInDays($record->domain_expires_at, false);
                        return $record->domain_expires_at->format('d M Y') . " ({$days}d)";
                    })
                    ->color(function ($record) {
                        if (!$record->domain_expires_at) return 'gray';
                        $days = (int) now()->diffInDays($record->domain_expires_at, false);
                        return $days <= 30 ? 'danger' : 'success';
                    })
                    ->sortable(),
            ])
            ->filters([
                SelectFilter::make('website_status')
                    ->label('Website Status')
                    ->options([
                        'up'   => 'Up',
                        'down' => 'Down',
                    ])
                    ->query(fn (Builder $query, array $data): Builder => match ($data['value'] ?? null) {
                        'up'    => $query->where('is_up', true),
                        'down'  => $query->where('is_up', false),
                        default => $query,
                    }),

                SelectFilter::make('ssl_status')
                    ->label('SSL Certificate')
                    ->options([
                        'active'   => 'Active',
                        'inactive' => 'Inactive / Expired',
                        'disabled' => 'Disabled',
                    ])
                    ->query(fn (Builder $query, array $data): Builder => match ($data['value'] ?? null) {
                        'active'   => $query->where('ssl_valid', true),
                        'inactive' => $query->where('ssl_valid', false),
                        'disabled' => $query->where('check_ssl', false),
                        default    => $query,
                    }),
            ])
            ->recordActions([
                EditAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
