<?php

namespace App\Filament\Resources\Halls\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class HallsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')->label('نام سالن')->searchable()->sortable(),
                TextColumn::make('venue.name')->label('مجموعه')->searchable()->sortable(),
                TextColumn::make('venue.city')->label('شهر')->searchable(),
                TextColumn::make('capacity')->label('ظرفیت')->numeric()->suffix(' صندلی')->sortable(),
                TextColumn::make('performances_count')->label('سانس‌ها')->counts('performances')->sortable(),
            ])
            ->filters([
                SelectFilter::make('venue_id')
                    ->label('مجموعه')
                    ->relationship('venue', 'name')
                    ->searchable()
                    ->preload(),
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
            ->defaultSort('created_at', 'desc');
    }
}
