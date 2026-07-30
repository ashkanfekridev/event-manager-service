<?php

namespace App\Filament\Resources\Events\Schemas;

use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class EventForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('اطلاعات رویداد')
                    ->schema([
                        TextInput::make('title')->label('عنوان')->required()->maxLength(255),
                        TextInput::make('slug')->label('شناسه URL')->required()->alphaDash()->unique(ignoreRecord: true)->maxLength(255),
                        Select::make('type')->label('نوع')->options(['concert' => 'کنسرت', 'theater' => 'تئاتر'])->required(),
                        TextInput::make('poster_url')->label('آدرس پوستر')->url()->maxLength(2048),
                        TextInput::make('duration_minutes')->label('مدت اجرا')->numeric()->minValue(1)->suffix('دقیقه'),
                        TextInput::make('age_limit')->label('محدودیت سنی')->numeric()->minValue(0)->maxValue(99)->suffix('سال'),
                        Textarea::make('description')->label('توضیحات')->rows(5)->columnSpanFull(),
                    ])->columns(2),
                Section::make('انتشار')
                    ->description('خالی بودن زمان انتشار یعنی رویداد در حالت پیش‌نویس باقی می‌ماند.')
                    ->schema([
                        DateTimePicker::make('published_at')->label('زمان انتشار')->seconds(false)->native(false),
                    ]),
            ]);
    }
}
