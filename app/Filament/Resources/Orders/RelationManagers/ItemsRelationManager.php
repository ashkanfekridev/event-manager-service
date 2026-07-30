<?php

namespace App\Filament\Resources\Orders\RelationManagers;

use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Support\Number;

class ItemsRelationManager extends RelationManager
{
    protected static string $relationship = 'items';

    public function form(Schema $schema): Schema
    {
        return $schema->components([]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('id')
            ->columns([
                TextColumn::make('performanceSeat.performance.event.title')->label('رویداد'),
                TextColumn::make('performanceSeat.performance.starts_at')->label('زمان اجرا')->dateTime('Y/m/d H:i'),
                TextColumn::make('performanceSeat.performance.hall.name')->label('سالن'),
                TextColumn::make('performanceSeat.seat.code')->label('صندلی')->badge(),
                TextColumn::make('unit_price')
                    ->label('مبلغ')
                    ->formatStateUsing(fn (string $state): string => Number::format((float) $state).' تومان'),
                TextColumn::make('ticket.code')->label('کد بلیط')->placeholder('صادر نشده')->copyable(),
            ]);
    }
}
