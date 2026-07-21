<?php

namespace App\Filament\Resources\Products\Tables;

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

class ProductsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')
                    ->label('Product Name')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('price')
                    ->label('Price')
                    ->money('MYR')
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
                        $records = \App\Models\Product::all();
                        
                        $headers = [
                            'Content-Type' => 'text/csv',
                            'Content-Disposition' => 'attachment; filename="all-products.csv"',
                        ];

                        $callback = function () use ($records) {
                            $file = fopen('php://output', 'w');
                            fprintf($file, chr(0xEF).chr(0xBB).chr(0xBF));
                            
                            fputcsv($file, [
                                'Product Name',
                                'Price (MYR)',
                                'Description',
                                'Created At'
                            ]);
                            
                            foreach ($records as $record) {
                                fputcsv($file, [
                                    $record->name,
                                    number_format($record->price, 2),
                                    $record->description,
                                    $record->created_at?->format('Y-m-d H:i:s') ?? 'N/A'
                                ]);
                            }
                            fclose($file);
                        };

                        return response()->streamDownload($callback, 'all-products-' . now()->format('Y-m-d') . '.csv', $headers);
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
                            ->label('If Duplicate Products Found')
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

                            $nameIdx = array_search('product name', $headers);
                            $priceIdx = array_search('price (myr)', $headers);
                            if ($priceIdx === false) {
                                $priceIdx = array_search('price', $headers);
                            }
                            $descIdx = array_search('description', $headers);

                            if ($nameIdx === false || $priceIdx === false) {
                                fclose($handle);
                                Storage::disk('public')->delete($data['file']);
                                Notification::make()
                                    ->title('Import Failed')
                                    ->body('CSV must contain at least "Product Name" and "Price" columns.')
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
                                $priceRaw = trim($row[$priceIdx] ?? '0');

                                if (empty($name)) {
                                    $skipped++;
                                    continue;
                                }

                                // Clean price value (keep only numbers and decimal point)
                                $price = (float) preg_replace('/[^\d.]/', '', $priceRaw);

                                $existing = \App\Models\Product::where('name', $name)->first();

                                $recordData = [
                                    'name' => $name,
                                    'price' => $price,
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
                                    \App\Models\Product::create($recordData);
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
                                'Content-Disposition' => 'attachment; filename="products-export.csv"',
                            ];

                            $callback = function () use ($records) {
                                $file = fopen('php://output', 'w');
                                fprintf($file, chr(0xEF).chr(0xBB).chr(0xBF));
                                
                                fputcsv($file, [
                                    'Product Name',
                                    'Price (MYR)',
                                    'Description',
                                    'Created At'
                                ]);
                                
                                foreach ($records as $record) {
                                    fputcsv($file, [
                                        $record->name,
                                        number_format($record->price, 2),
                                        $record->description,
                                        $record->created_at?->format('Y-m-d H:i:s') ?? 'N/A'
                                    ]);
                                }
                                fclose($file);
                            };

                            return response()->streamDownload($callback, 'products-export-' . now()->format('Y-m-d') . '.csv', $headers);
                        }),
                ]),
            ]);
    }
}
