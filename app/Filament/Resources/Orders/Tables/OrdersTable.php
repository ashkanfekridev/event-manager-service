<?php

namespace App\Filament\Resources\Orders\Tables;

use Filament\Actions\ViewAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Support\Number;

class OrdersTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('reference')->label('شماره پیگیری')->searchable()->copyable()->limit(14),
                TextColumn::make('customer_name')->label('خریدار')->searchable()->sortable(),
                TextColumn::make('customer_phone')->label('موبایل')->searchable(),
                TextColumn::make('customer_email')->label('ایمیل')->searchable()->toggleable(),
                TextColumn::make('items_count')->label('بلیط')->counts('items')->numeric()->sortable(),
                TextColumn::make('total_amount')
                    ->label('مبلغ کل')
                    ->formatStateUsing(fn (string $state): string => Number::format((float) $state).' تومان')
                    ->sortable(),
                TextColumn::make('status')
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
                TextColumn::make('created_at')->label('زمان سفارش')->dateTime('Y/m/d H:i')->sortable(),
            ])
            ->filters([
                SelectFilter::make('status')
                    ->label('وضعیت')
                    ->options([
                        'paid' => 'پرداخت‌شده',
                        'pending' => 'در انتظار پرداخت',
                        'expired' => 'منقضی‌شده',
                        'cancelled' => 'لغوشده',
                    ]),
            ])
            ->recordActions([
                ViewAction::make(),
            ])
            ->defaultSort('created_at', 'desc')
            ->persistFiltersInSession()
            ->persistSearchInSession();
    }
}
