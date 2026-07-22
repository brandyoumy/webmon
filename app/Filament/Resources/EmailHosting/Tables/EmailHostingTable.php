<?php

namespace App\Filament\Resources\EmailHosting\Tables;

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

class EmailHostingTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')
                    ->label('Client Name')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('url')
                    ->label('Webmail / Domain')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('company_name')
                    ->label('Company')
                    ->searchable()
                    ->sortable()
                    ->placeholder('—'),

                TextColumn::make('product.name')
                    ->label('Package')
                    ->searchable()
                    ->sortable()
                    ->placeholder('—'),

                TextColumn::make('server.name')
                    ->label('Server')
                    ->searchable()
                    ->sortable()
                    ->placeholder('—'),

                TextColumn::make('pic_phone')
                    ->label('WhatsApp')
                    ->searchable()
                    ->sortable()
                    ->placeholder('—'),

                TextColumn::make('pic_email')
                    ->label('PIC Email')
                    ->searchable()
                    ->sortable()
                    ->placeholder('—'),
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
                        $records = \App\Models\Website::emailHosting()->with(['product', 'server'])->get();
                        
                        $headers = [
                            'Content-Type' => 'text/csv',
                            'Content-Disposition' => 'attachment; filename="all-email-hosting.csv"',
                        ];

                        $callback = function () use ($records) {
                            $file = fopen('php://output', 'w');
                            fprintf($file, chr(0xEF).chr(0xBB).chr(0xBF));
                            
                            fputcsv($file, [
                                'Client Name',
                                'URL',
                                'Company',
                                'Package',
                                'Server',
                                'WhatsApp',
                                'PIC Email',
                                'Remark'
                            ]);
                            
                            foreach ($records as $record) {
                                fputcsv($file, [
                                    $record->name,
                                    $record->url,
                                    $record->company_name,
                                    $record->product?->name,
                                    $record->server?->name,
                                    $record->pic_phone,
                                    $record->pic_email,
                                    $record->remark
                                ]);
                            }
                            fclose($file);
                        };

                        return response()->streamDownload($callback, 'all-email-hosting-' . now()->format('Y-m-d') . '.csv', $headers);
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
                            ->label('If Duplicate URLs Found')
                            ->options([
                                'skip' => 'Skip the duplicate row',
                                'overwrite' => 'Overwrite existing data with CSV values',
                            ])
                            ->default('skip')
                            ->required(),
                    ])
                    ->action(function (array $data) {
                        $filePath = Storage::path($data['file']);

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

                            $nameIdx = array_search('client name', $headers);
                            $urlIdx = array_search('url', $headers);
                            $companyIdx = array_search('company', $headers);
                            $packageIdx = array_search('package', $headers);
                            $serverIdx = array_search('server', $headers);
                            $whatsappIdx = array_search('whatsapp', $headers);
                            $emailIdx = array_search('pic email', $headers);
                            $remarkIdx = array_search('remark', $headers);

                            if ($urlIdx === false || $nameIdx === false) {
                                fclose($handle);
                                Storage::delete($data['file']);
                                Notification::make()
                                    ->title('Import Failed')
                                    ->body('CSV must contain at least "Client Name" and "URL" columns.')
                                    ->danger()
                                    ->send();
                                return;
                            }

                            $imported = 0;
                            $updated = 0;
                            $skipped = 0;
                            $behavior = $data['duplicate_behavior'];

                            while (($row = fgetcsv($handle, 1000, ',')) !== false) {
                                $url = trim($row[$urlIdx] ?? '');
                                $name = trim($row[$nameIdx] ?? '');

                                if (empty($url) || empty($name)) {
                                    $skipped++;
                                    continue;
                                }

                                $existing = \App\Models\Website::where('url', $url)->first();

                                $productId = null;
                                if ($packageIdx !== false && !empty($row[$packageIdx])) {
                                    $packageName = trim($row[$packageIdx]);
                                    $product = \App\Models\Product::where('name', $packageName)->first();
                                    if ($product) {
                                        $productId = $product->id;
                                    }
                                }

                                $serverId = null;
                                if ($serverIdx !== false && !empty($row[$serverIdx])) {
                                    $serverName = trim($row[$serverIdx]);
                                    $server = \App\Models\Server::where('name', $serverName)->first();
                                    if ($server) {
                                        $serverId = $server->id;
                                    }
                                }

                                $recordData = [
                                    'name' => $name,
                                    'company_name' => $companyIdx !== false ? trim($row[$companyIdx] ?? '') : null,
                                    'product_id' => $productId,
                                    'server_id' => $serverId,
                                    'pic_phone' => $whatsappIdx !== false ? trim($row[$whatsappIdx] ?? '') : null,
                                    'pic_email' => $emailIdx !== false ? trim($row[$emailIdx] ?? '') : null,
                                    'remark' => $remarkIdx !== false ? trim($row[$remarkIdx] ?? '') : null,
                                ];

                                if ($existing) {
                                    if ($behavior === 'skip') {
                                        $skipped++;
                                    } else {
                                        $existing->update($recordData);
                                        $updated++;
                                    }
                                } else {
                                    $recordData['url'] = $url;
                                    \App\Models\Website::create($recordData);
                                    $imported++;
                                }
                            }

                            fclose($handle);
                            Storage::delete($data['file']);

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
                                'Content-Disposition' => 'attachment; filename="email-hosting-export.csv"',
                            ];

                            $callback = function () use ($records) {
                                $file = fopen('php://output', 'w');
                                fprintf($file, chr(0xEF).chr(0xBB).chr(0xBF));
                                
                                fputcsv($file, [
                                    'Client Name',
                                    'URL',
                                    'Company',
                                    'Package',
                                    'Server',
                                    'WhatsApp',
                                    'PIC Email',
                                    'Remark'
                                ]);
                                
                                foreach ($records as $record) {
                                    fputcsv($file, [
                                        $record->name,
                                        $record->url,
                                        $record->company_name,
                                        $record->product?->name,
                                        $record->server?->name,
                                        $record->pic_phone,
                                        $record->pic_email,
                                        $record->remark
                                    ]);
                                }
                                fclose($file);
                            };

                            return response()->streamDownload($callback, 'email-hosting-export-' . now()->format('Y-m-d') . '.csv', $headers);
                        }),
                ]),
            ])
            ->defaultPaginationPageOption(20)
            ->paginationPageOptions([10, 20, 25, 50, 100]);
    }
}
