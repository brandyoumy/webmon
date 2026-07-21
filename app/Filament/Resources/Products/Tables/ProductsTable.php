<?php

namespace App\Filament\Resources\Products\Tables;

use Filament\Actions\Action;
use Filament\Actions\BulkAction;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

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
