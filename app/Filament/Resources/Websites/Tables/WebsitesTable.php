<?php

namespace App\Filament\Resources\Websites\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\BadgeColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class WebsitesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')->searchable()->sortable(),
                
                TextColumn::make('url')->label('URL')->searchable(),

                TextColumn::make('company_name')->label('Company')->searchable()->sortable(),
                
                BadgeColumn::make('check_ssl')
                ->label('SSL Cert')
                ->getStateUsing(fn ($record) => $record->check_ssl ? 'Active' : 'Inactive')
                ->colors([
                    'success' => fn ($state) => $state === 'Active', // green for Active
                    'danger'  => fn ($state) => $state === 'Inactive', // red for Inactive
                ])->sortable(),
                BadgeColumn::make('status')
                ->label('Status')
                ->getStateUsing(fn ($record) => $record->latestLog?->is_up ? 'OK' : 'DOWN')
                ->colors([
                    'success' => fn ($state) => $state === 'OK',
                    'danger'  => fn ($state) => $state === 'DOWN',
                ]),
            ])
            ->filters([
                //
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
