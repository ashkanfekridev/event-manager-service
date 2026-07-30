<?php

namespace App\Filament\Resources\Orders\Schemas;

use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Illuminate\Support\Number;

class OrderInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('اطلاعات سفارش')
                    ->schema([
                        TextEntry::make('reference')->label('شماره پیگیری')->copyable(),
                        TextEntry::make('status')
                            ->label('وضعیت')
                            ->formatStateUsing(fn (string $state): string => match ($state) {
                                'paid' => 'پرداخت‌شده',
                                'pending' => 'در انتظار پرداخت',
                                'expired' => 'منقضی‌شده',
                                'cancelled' => 'لغوشده',
                                default => $state,
                            })
                            ->color(fn (string $state): string => match ($state) {
                                'paid' => 'success',
                                'pending' => 'warning',
                                'expired', 'cancelled' => 'danger',
                                default => 'gray',
                            })
                            ->badge(),
                        TextEntry::make('total_amount')
                            ->label('مبلغ کل')
                            ->formatStateUsing(fn (string $state): string => Number::format((float) $state).' تومان'),
                        TextEntry::make('created_at')->label('زمان سفارش')->dateTime('Y/m/d H:i'),
                        TextEntry::make('reserved_until')->label('مهلت پرداخت')->dateTime('Y/m/d H:i'),
                        TextEntry::make('paid_at')->label('زمان پرداخت')->dateTime('Y/m/d H:i')->placeholder('پرداخت نشده'),
                        TextEntry::make('payment_reference')->label('شناسه پرداخت')->placeholder('—')->copyable(),
                    ])
                    ->columns(3),
                Section::make('اطلاعات خریدار')
                    ->schema([
                        TextEntry::make('customer_name')->label('نام و نام خانوادگی'),
                        TextEntry::make('customer_phone')->label('شماره موبایل')->copyable(),
                        TextEntry::make('customer_email')->label('ایمیل')->copyable(),
                        TextEntry::make('user.name')->label('حساب کاربری')->placeholder('خرید مهمان'),
                    ])
                    ->columns(2),
            ]);
    }
}
