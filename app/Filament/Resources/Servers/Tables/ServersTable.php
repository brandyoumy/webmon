<?php

namespace App\Filament\Resources\Servers\Tables;

use Filament\Actions\Action;
use Filament\Actions\BulkAction;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Radio;
use Filament\Notifications\Notification;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Support\Facades\Storage;

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

                Action::make('import_csv')
                    ->label('Import from CSV')
                    ->icon('heroicon-o-arrow-up-tray')
                    ->color('info')
                    ->form([
                        FileUpload::make('file')
                            ->label('CSV File')
                            ->acceptedFileTypes(['text/csv', 'text/plain', 'application/vnd.ms-excel'])
                            ->required(),
                        Radio::make('duplicate_behavior')
                            ->label('If Duplicate Servers Found')
                            ->options([
                                'skip' => 'Skip the duplicate row',
                                'overwrite' => 'Overwrite existing data with CSV values',
                            ])
                            ->default('skip')
                            ->required(),
                    ])
                    ->action(function (array $data) {
                        $filePath = Storage::disk('public')->path($data['file']);

                        if (($handle = fopen($filePath, 'r')) !== false) {
                            $bom = fread($handle, 3);
                            if ($bom !== chr(0xEF) . chr(0xBB) . chr(0xBF)) {
                                rewind($handle);
                            }

                            $headers = fgetcsv($handle, 1000, ',');
                            if (!$headers) {
                                fclose($handle);
                                Notification::make()->title('Invalid CSV file structure.')->danger()->send();
                                return;
                            }

                            $headers = array_map(fn($h) => strtolower(trim($h)), $headers);

                            $nameIdx = array_search('server name', $headers);
                            $ipIdx = array_search('ip address', $headers);
                            $providerIdx = array_search('provider', $headers);
                            $descIdx = array_search('description', $headers);

                            if ($nameIdx === false) {
                                fclose($handle);
                                Storage::disk('public')->delete($data['file']);
                                Notification::make()
                                    ->title('Import Failed')
                                    ->body('CSV must contain at least a "Server Name" column.')
                                    ->danger()
                                    ->send();
                                return;
                            }

                            $imported = 0;
                            $updated = 0;
                            $skipped = 0;
                            $behavior = $data['duplicate_behavior'];

                            while (($row = fgetcsv($handle, 1000, ',')) !== false) {
                                $name = trim($row[$nameIdx] ?? '');
                                $ipAddress = $ipIdx !== false ? trim($row[$ipIdx] ?? '') : null;

                                if (empty($name)) {
                                    $skipped++;
                                    continue;
                                }

                                // Check for duplicates (by name or by IP address if provided)
                                $existing = null;
                                if (!empty($name)) {
                                    $existing = \App\Models\Server::where('name', $name)->first();
                                }
                                if (!$existing && !empty($ipAddress)) {
                                    $existing = \App\Models\Server::where('ip_address', $ipAddress)->first();
                                }

                                $recordData = [
                                    'name' => $name,
                                    'ip_address' => $ipAddress,
                                    'provider' => $providerIdx !== false ? trim($row[$providerIdx] ?? '') : null,
                                    'description' => $descIdx !== false ? trim($row[$descIdx] ?? '') : null,
                                ];

                                if ($existing) {
                                    if ($behavior === 'skip') {
                                        $skipped++;
                                    } else {
                                        $existing->update($recordData);
                                        $updated++;
                                    }
                                } else {
                                    \App\Models\Server::create($recordData);
                                    $imported++;
                                }
                            }

                            fclose($handle);
                            Storage::disk('public')->delete($data['file']);

                            Notification::make()
                                ->title('Import Completed')
                                ->body("Successfully imported: {$imported} new, updated: {$updated}, skipped: {$skipped} records.")
                                ->success()
                                ->send();
                        } else {
                            Notification::make()->title('Could not open the uploaded file.')->danger()->send();
                        }
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
