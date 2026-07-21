<?php

namespace App\Filament\Resources\Servers\Tables;

use Filament\Actions\Action;
use Filament\Actions\BulkAction;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class ServersTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')
                    ->label('Server Name')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('ip_address')
                    ->label('IP Address')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('provider')
                    ->label('Provider')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('description')
                    ->label('Description')
                    ->limit(50)
                    ->searchable(),
            ])
            ->filters([
                //
            ])
            ->headerActions([
                Action::make('export_all')
                    ->label('Export All to CSV')
                    ->icon('heroicon-o-arrow-down-tray')
                    ->color('gray')
                    ->action(function () {
                        $records = \App\Models\Server::all();
                        
                        $headers = [
                            'Content-Type' => 'text/csv',
                            'Content-Disposition' => 'attachment; filename="all-servers.csv"',
                        ];

                        $callback = function () use ($records) {
                            $file = fopen('php://output', 'w');
                            fprintf($file, chr(0xEF).chr(0xBB).chr(0xBF));
                            
                            fputcsv($file, [
                                'Server Name',
                                'IP Address',
                                'Provider',
                                'Description',
                                'Created At'
                            ]);
                            
                            foreach ($records as $record) {
                                fputcsv($file, [
                                    $record->name,
                                    $record->ip_address,
                                    $record->provider,
                                    $record->description,
                                    $record->created_at?->format('Y-m-d H:i:s') ?? 'N/A'
                                ]);
                            }
                            fclose($file);
                        };

                        return response()->streamDownload($callback, 'all-servers-' . now()->format('Y-m-d') . '.csv', $headers);
                    }),
            ])
            ->recordActions([
                EditAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                    BulkAction::make('export_selected')
                        ->label('Export Selected to CSV')
                        ->icon('heroicon-o-arrow-down-tray')
                        ->action(function (\Illuminate\Database\Eloquent\Collection $records) {
                            $headers = [
                                'Content-Type' => 'text/csv',
                                'Content-Disposition' => 'attachment; filename="servers-export.csv"',
                            ];

                            $callback = function () use ($records) {
                                $file = fopen('php://output', 'w');
                                fprintf($file, chr(0xEF).chr(0xBB).chr(0xBF));
                                
                                fputcsv($file, [
                                    'Server Name',
                                    'IP Address',
                                    'Provider',
                                    'Description',
                                    'Created At'
                                ]);
                                
                                foreach ($records as $record) {
                                    fputcsv($file, [
                                        $record->name,
                                        $record->ip_address,
                                        $record->provider,
                                        $record->description,
                                        $record->created_at?->format('Y-m-d H:i:s') ?? 'N/A'
                                    ]);
                                }
                                fclose($file);
                            };

                            return response()->streamDownload($callback, 'servers-export-' . now()->format('Y-m-d') . '.csv', $headers);
                        }),
                ]),
            ]);
    }
}
