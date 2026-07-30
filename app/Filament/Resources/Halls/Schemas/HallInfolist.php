<?php

namespace App\Filament\Resources\Halls\Schemas;

use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class HallInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('مشخصات سالن')->schema([
                    TextEntry::make('name')->label('نام سالن'),
                    TextEntry::make('venue.name')->label('مجموعه'),
                    TextEntry::make('capacity')->label('ظرفیت')->suffix(' صندلی'),
                ])->columns(3),
            ]);
    }
}
