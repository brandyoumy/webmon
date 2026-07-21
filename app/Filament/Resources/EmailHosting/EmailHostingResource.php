<?php

namespace App\Filament\Resources\EmailHosting;

use App\Filament\Resources\EmailHosting\Pages\CreateEmailHosting;
use App\Filament\Resources\EmailHosting\Pages\EditEmailHosting;
use App\Filament\Resources\EmailHosting\Pages\ListEmailHostings;
use App\Filament\Resources\Websites\Schemas\WebsitesForm;
use App\Filament\Resources\EmailHosting\Tables\EmailHostingTable;
use App\Models\Website;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class EmailHostingResource extends Resource
{
    protected static ?string $model = Website::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedInboxStack;

    protected static ?string $recordTitleAttribute = 'name';

    protected static ?string $navigationLabel = 'Email Hosting';

    protected static ?string $pluralModelLabel = 'Email Hosting';

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()->emailHosting();
    }

    public static function form(Schema $schema): Schema
    {
        return WebsitesForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return EmailHostingTable::configure($table);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListEmailHostings::route('/'),
            'create' => CreateEmailHosting::route('/create'),
            'edit' => EditEmailHosting::route('/{record}/edit'),
        ];
    }
}
