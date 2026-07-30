<?php

namespace App\Filament\Resources\Venues\Schemas;

use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class VenueInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('مشخصات مجموعه')->schema([
                    TextEntry::make('name')->label('نام'),
                    TextEntry::make('city')->label('شهر'),
                    TextEntry::make('address')->label('آدرس')->columnSpanFull(),
                    TextEntry::make('halls_count')->label('تعداد سالن')->state(fn ($record) => $record->halls()->count()),
                ])->columns(2),
            ]);
    }
}
