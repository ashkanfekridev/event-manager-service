<?php

namespace App\Filament\Resources\Performances;

use App\Filament\Resources\Performances\Pages\CreatePerformance;
use App\Filament\Resources\Performances\Pages\EditPerformance;
use App\Filament\Resources\Performances\Pages\ListPerformances;
use App\Filament\Resources\Performances\Pages\ViewPerformance;
use App\Filament\Resources\Performances\Schemas\PerformanceForm;
use App\Filament\Resources\Performances\Schemas\PerformanceInfolist;
use App\Filament\Resources\Performances\Tables\PerformancesTable;
use App\Models\Performance;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use UnitEnum;

class PerformanceResource extends Resource
{
    protected static ?string $model = Performance::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedCalendarDays;

    protected static string|UnitEnum|null $navigationGroup = 'مدیریت رویدادها';

    protected static ?string $modelLabel = 'سانس';

    protected static ?string $pluralModelLabel = 'سانس‌ها';

    protected static ?int $navigationSort = 2;

    public static function form(Schema $schema): Schema
    {
        return PerformanceForm::configure($schema);
    }

    public static function infolist(Schema $schema): Schema
    {
        return PerformanceInfolist::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return PerformancesTable::configure($table);
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
            'index' => ListPerformances::route('/'),
            'create' => CreatePerformance::route('/create'),
            'view' => ViewPerformance::route('/{record}'),
            'edit' => EditPerformance::route('/{record}/edit'),
        ];
    }
}
