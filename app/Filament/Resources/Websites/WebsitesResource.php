<?php

namespace App\Filament\Resources\Websites;

use App\Filament\Resources\Websites\Pages\CreateWebsites;
use App\Filament\Resources\Websites\Pages\EditWebsites;
use App\Filament\Resources\Websites\Pages\ListWebsites;
use App\Filament\Resources\Websites\Schemas\WebsitesForm;
use App\Filament\Resources\Websites\Tables\WebsitesTable;
use App\Models\Website;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class WebsitesResource extends Resource
{
    protected static ?string $model = Website::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedGlobeAlt;

    protected static ?string $recordTitleAttribute = 'name';

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()->activeWebsites();
    }

    public static function form(Schema $schema): Schema
    {
        return WebsitesForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return WebsitesTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListWebsites::route('/'),
            'create' => CreateWebsites::route('/create'),
            'edit' => EditWebsites::route('/{record}/edit'),
        ];
    }
}
