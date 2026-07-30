<?php

namespace App\Filament\Resources\Performances\Schemas;

use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class PerformanceForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('اطلاعات سانس')->schema([
                    Select::make('event_id')->label('رویداد')->relationship('event', 'title')->searchable()->preload()->required(),
                    Select::make('hall_id')->label('سالن')->relationship('hall', 'name')->searchable()->preload()->required(),
                    DateTimePicker::make('starts_at')->label('زمان اجرا')->required()->after('now')->seconds(false)->native(false),
                    DateTimePicker::make('sales_start_at')->label('شروع فروش')->beforeOrEqual('starts_at')->seconds(false)->native(false),
                    DateTimePicker::make('sales_end_at')->label('پایان فروش')->seconds(false)->native(false)->beforeOrEqual('starts_at'),
                    Select::make('status')->label('وضعیت')->options(['scheduled' => 'برنامه‌ریزی‌شده', 'cancelled' => 'لغوشده', 'completed' => 'برگزارشده'])->default('scheduled')->required(),
                    TextInput::make('default_price')->label('قیمت پایه')->numeric()->minValue(0)->suffix('تومان')->required()->visibleOn('create')->helperText('این قیمت برای موجودی تمام صندلی‌های فعال سالن ثبت می‌شود.'),
                ])->columns(2),
            ]);
    }
}
