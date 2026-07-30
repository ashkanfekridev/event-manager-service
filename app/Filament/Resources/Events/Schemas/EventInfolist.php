<?php

namespace App\Filament\Resources\Events\Schemas;

use App\Models\Event;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class EventInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('اطلاعات رویداد')->schema([
                    TextEntry::make('title')->label('عنوان'),
                    TextEntry::make('type')->label('نوع')->formatStateUsing(fn (string $state) => $state === 'concert' ? 'کنسرت' : 'تئاتر')->badge(),
                    TextEntry::make('description')->label('توضیحات')->columnSpanFull(),
                    TextEntry::make('publication_status')->label('وضعیت انتشار')->state(fn (Event $record) => match ($record->publicationStatus()) {
                        'published' => 'فعال',
                        'scheduled' => 'زمان‌بندی‌شده',
                        default => 'پیش‌نویس',
                    })->badge(),
                    TextEntry::make('published_at')->label('زمان انتشار')->dateTime('Y/m/d H:i')->placeholder('پیش‌نویس'),
                ])->columns(2),
            ]);
    }
}
