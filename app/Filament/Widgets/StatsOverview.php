<?php

namespace App\Filament\Widgets;

use App\Models\Event;
use App\Models\Hall;
use App\Models\Order;
use App\Models\Venue;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Illuminate\Support\Number;

class StatsOverview extends StatsOverviewWidget
{
    protected function getStats(): array
    {
        $paidOrders = Order::query()->where('status', 'paid');

        return [
            Stat::make('رویدادهای فعال', Event::query()->published()->count())
                ->description(Event::query()->where('published_at', '>', now())->count().' رویداد زمان‌بندی‌شده')
                ->color('success'),
            Stat::make('سفارش‌های پرداخت‌شده', (clone $paidOrders)->count())
                ->description(Number::format((float) (clone $paidOrders)->sum('total_amount')).' تومان فروش')
                ->color('primary'),
            Stat::make('مجموعه‌ها و سالن‌ها', Venue::query()->count().' مجموعه')
                ->description(Hall::query()->count().' سالن تعریف‌شده')
                ->color('info'),
        ];
    }
}
