<?php

namespace App\Filament\Resources\Halls;

use App\Filament\Resources\Halls\Pages\CreateHall;
use App\Filament\Resources\Halls\Pages\EditHall;
use App\Filament\Resources\Halls\Pages\ListHalls;
use App\Filament\Resources\Halls\Pages\ViewHall;
use App\Filament\Resources\Halls\RelationManagers\SeatsRelationManager;
use App\Filament\Resources\Halls\Schemas\HallForm;
use App\Filament\Resources\Halls\Schemas\HallInfolist;
use App\Filament\Resources\Halls\Tables\HallsTable;
use App\Models\Hall;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use UnitEnum;

class HallResource extends Resource
{
    protected static ?string $model = Hall::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedBuildingOffice;

    protected static string|UnitEnum|null $navigationGroup = 'مدیریت سالن‌ها';

    protected static ?string $modelLabel = 'سالن';

    protected static ?string $pluralModelLabel = 'سالن‌ها';

    protected static ?string $recordTitleAttribute = 'name';

    protected static ?int $navigationSort = 4;

    public static function form(Schema $schema): Schema
    {
        return HallForm::configure($schema);
    }

    public static function infolist(Schema $schema): Schema
    {
        return HallInfolist::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return HallsTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [
            SeatsRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListHalls::route('/'),
            'create' => CreateHall::route('/create'),
            'view' => ViewHall::route('/{record}'),
            'edit' => EditHall::route('/{record}/edit'),
        ];
    }
}
