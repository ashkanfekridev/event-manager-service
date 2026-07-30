<?php

namespace App\Filament\Resources\Performances\Schemas;

use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class PerformanceInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('جزئیات سانس')->schema([
                    TextEntry::make('event.title')->label('رویداد'),
                    TextEntry::make('hall.name')->label('سالن'),
                    TextEntry::make('starts_at')->label('زمان اجرا')->dateTime('Y/m/d H:i'),
                    TextEntry::make('status')->label('وضعیت')->badge(),
                    TextEntry::make('seats_count')->label('تعداد صندلی')->state(fn ($record) => $record->seats()->count()),
                ])->columns(2),
            ]);
    }
}
