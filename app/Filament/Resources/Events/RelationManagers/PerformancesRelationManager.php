<?php

namespace App\Filament\Resources\Events\RelationManagers;

use App\Filament\Resources\Performances\PerformanceResource;
use App\Models\Performance;
use Filament\Actions\Action;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class PerformancesRelationManager extends RelationManager
{
    protected static string $relationship = 'performances';

    public function form(Schema $schema): Schema
    {
        return $schema->components([]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('starts_at')
            ->columns([
                TextColumn::make('hall.name')->label('سالن'),
                TextColumn::make('starts_at')->label('زمان اجرا')->dateTime('Y/m/d H:i')->sortable(),
                TextColumn::make('sales_start_at')->label('شروع فروش')->dateTime('Y/m/d H:i')->placeholder('فوری'),
                TextColumn::make('status')
                    ->label('وضعیت')
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'scheduled' => 'برنامه‌ریزی‌شده',
                        'cancelled' => 'لغوشده',
                        'completed' => 'برگزارشده',
                        default => $state,
                    })
                    ->badge(),
                TextColumn::make('seats_count')->label('موجودی')->counts('seats'),
            ])
            ->recordActions([
                Action::make('openPerformance')
                    ->label('مشاهده سانس')
                    ->icon(Heroicon::OutlinedArrowTopRightOnSquare)
                    ->url(fn (Performance $record): string => PerformanceResource::getUrl('view', ['record' => $record])),
            ])
            ->defaultSort('starts_at', 'desc');
    }
}
