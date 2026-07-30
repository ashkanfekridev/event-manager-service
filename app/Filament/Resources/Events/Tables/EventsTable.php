<?php

namespace App\Filament\Resources\Events\Tables;

use App\Models\Event;
use Filament\Actions\Action;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Notifications\Notification;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class EventsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('title')
                    ->label('عنوان')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('type')
                    ->label('نوع')
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'concert' => 'کنسرت',
                        'theater' => 'تئاتر',
                        default => $state,
                    })
                    ->badge(),
                TextColumn::make('publication_status')
                    ->label('انتشار')
                    ->state(fn (Event $record): string => $record->publicationStatus())
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'published' => 'فعال',
                        'scheduled' => 'زمان‌بندی‌شده',
                        default => 'پیش‌نویس',
                    })
                    ->color(fn (string $state): string => match ($state) {
                        'published' => 'success',
                        'scheduled' => 'warning',
                        default => 'gray',
                    })
                    ->badge(),
                TextColumn::make('performances_count')
                    ->label('سانس‌ها')
                    ->counts('performances')
                    ->sortable(),
                TextColumn::make('published_at')
                    ->label('زمان انتشار')
                    ->dateTime('Y/m/d H:i')
                    ->placeholder('—')
                    ->sortable(),
            ])
            ->filters([
                SelectFilter::make('type')
                    ->label('نوع رویداد')
                    ->options([
                        'concert' => 'کنسرت',
                        'theater' => 'تئاتر',
                    ]),
                Filter::make('published')
                    ->label('فعال')
                    ->query(fn (Builder $query): Builder => $query->published()),
                Filter::make('draft')
                    ->label('پیش‌نویس')
                    ->query(fn (Builder $query): Builder => $query->whereNull('published_at')),
            ])
            ->recordActions([
                Action::make('togglePublication')
                    ->label(fn (Event $record): string => $record->isPublished() ? 'غیرفعال‌کردن' : 'فعال‌کردن')
                    ->icon(fn (Event $record): Heroicon => $record->isPublished() ? Heroicon::OutlinedEyeSlash : Heroicon::OutlinedEye)
                    ->color(fn (Event $record): string => $record->isPublished() ? 'gray' : 'success')
                    ->requiresConfirmation()
                    ->action(function (Event $record): void {
                        $isPublished = $record->isPublished();
                        $record->update(['published_at' => $isPublished ? null : now()]);

                        Notification::make()
                            ->success()
                            ->title($isPublished ? 'رویداد غیرفعال شد' : 'رویداد فعال شد')
                            ->send();
                    }),
                ViewAction::make(),
                EditAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ])
            ->defaultSort('created_at', 'desc')
            ->persistFiltersInSession()
            ->persistSearchInSession();
    }
}
