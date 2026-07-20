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
                TextColumn::make('name')->label('Client Name')->searchable()->sortable(),

                TextColumn::make('url')
                    ->label('URL')
                    ->searchable()
                    ->url(fn ($record) => $record->url)
                    ->openUrlInNewTab()
                    ->color('primary'),

                TextColumn::make('company_name')->label('Company')->searchable()->sortable(),

                TextColumn::make('product.name')->label('Package')->searchable()->sortable(),

                TextColumn::make('pic_phone')
                    ->label('WhatsApp')
                    ->searchable()
                    ->sortable()
                    ->html()
                    ->formatStateUsing(function ($state) {
                        if (!$state) {
                            return '<span class="text-gray-400 dark:text-gray-600">—</span>';
                        }
                        $cleanPhone = preg_replace('/[^0-9]/', '', $state);
                        $whatsappUrl = "https://wa.me/{$cleanPhone}";
                        return '
                            <a href="' . $whatsappUrl . '" target="_blank" class="inline-flex items-center gap-1 px-2.5 py-1 text-xs font-semibold text-emerald-700 bg-emerald-50 dark:bg-emerald-950 dark:text-emerald-300 rounded-full border border-emerald-200 dark:border-emerald-800 hover:bg-emerald-100 dark:hover:bg-emerald-900 transition shadow-sm">
                                <svg class="w-3.5 h-3.5 fill-current text-emerald-600 dark:text-emerald-400" viewBox="0 0 448 512" xmlns="http://www.w3.org/2000/svg">
                                    <path d="M380.9 97.1C339 55.1 283.2 32 223.9 32c-122.4 0-222 99.6-222 222 0 39.1 10.2 77.3 29.6 111L0 480l117.7-30.9c32.4 17.7 68.9 27 106.1 27h.1c122.3 0 224.1-99.6 224.1-222 0-59.3-25.2-115-67.1-157zm-157 341.6c-33.2 0-65.7-8.9-94-25.7l-6.7-4-69.8 18.3L72 359.2l-4.4-7c-18.5-29.4-28.2-63.3-28.2-98.2 0-101.7 82.8-184.5 184.6-184.5 49.3 0 95.6 19.2 130.4 54.1 34.8 34.9 56.2 81.2 56.1 130.5 0 101.8-84.9 184.6-186.6 184.6zm101.2-138.2c-5.5-2.8-32.8-16.2-37.9-18-5.1-1.9-8.8-2.8-12.5 2.8-3.7 5.6-14.3 18-17.6 21.8-3.2 3.7-6.5 4.2-12 1.4-32.6-16.3-54-29.1-75.5-66-5.7-9.8 5.7-9.1 16.3-30.3 1.8-3.7.9-6.9-.5-9.7-1.4-2.8-12.5-30.1-17.1-41.2-4.5-10.8-9.1-9.3-12.5-9.5-3.2-.2-6.9-.2-10.6-.2-3.7 0-9.7 1.4-14.8 6.9-5.1 5.6-19.4 19-19.4 46.3 0 27.3 19.9 53.7 22.6 57.4 2.8 3.7 39.1 59.7 94.8 83.8 35.2 15.2 49 16.5 66.6 13.9 10.7-1.6 32.8-13.4 37.4-26.4 4.6-13 4.6-24.1 3.2-26.4-1.3-2.5-5-3.9-10.5-6.6z"/>
                                </svg>
                                Chat
                            </a>
                        ';
                    }),

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

                TextColumn::make('remark')
                    ->label('Remark')
                    ->limit(50)
                    ->searchable(),
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
