<?php

namespace App\Filament\Resources\Performances\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class PerformancesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('event.title')->label('رویداد')->searchable()->sortable(),
                TextColumn::make('hall.name')->label('سالن')->searchable()->sortable(),
                TextColumn::make('starts_at')->label('زمان اجرا')->dateTime('Y/m/d H:i')->sortable(),
                TextColumn::make('sales_start_at')->label('شروع فروش')->dateTime('Y/m/d H:i')->placeholder('فوری')->sortable(),
                TextColumn::make('status')
                    ->label('وضعیت')
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'scheduled' => 'برنامه‌ریزی‌شده',
                        'cancelled' => 'لغوشده',
                        'completed' => 'برگزارشده',
                        default => $state,
                    })
                    ->color(fn (string $state): string => match ($state) {
                        'scheduled' => 'success',
                        'cancelled' => 'danger',
                        default => 'gray',
                    })
                    ->badge(),
                TextColumn::make('seats_count')->label('موجودی')->counts('seats')->numeric()->sortable(),
            ])
            ->filters([
                SelectFilter::make('status')
                    ->label('وضعیت')
                    ->options([
                        'scheduled' => 'برنامه‌ریزی‌شده',
                        'cancelled' => 'لغوشده',
                        'completed' => 'برگزارشده',
                    ]),
                SelectFilter::make('event_id')->label('رویداد')->relationship('event', 'title')->searchable()->preload(),
                SelectFilter::make('hall_id')->label('سالن')->relationship('hall', 'name')->searchable()->preload(),
            ])
            ->recordActions([
                ViewAction::make(),
                EditAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ])
            ->defaultSort('starts_at', 'desc')
            ->persistFiltersInSession();
    }
}
