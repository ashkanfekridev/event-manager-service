<?php

namespace App\Filament\Resources\Halls\RelationManagers;

use App\Models\Hall;
use App\Models\Seat;
use Filament\Actions\Action;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\CreateAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Notifications\Notification;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class SeatsRelationManager extends RelationManager
{
    protected static string $relationship = 'seats';

    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('code')
                    ->label('کد صندلی')
                    ->required()
                    ->maxLength(255),
                TextInput::make('section')->label('بخش')->required()->maxLength(100)->default('main'),
                TextInput::make('row_label')->label('ردیف')->required()->maxLength(20),
                TextInput::make('number')->label('شماره')->required()->maxLength(20),
                Select::make('type')
                    ->label('نوع')
                    ->options([
                        'standard' => 'عادی',
                        'vip' => 'ویژه',
                        'wheelchair' => 'ویلچر',
                    ])
                    ->default('standard')
                    ->required(),
                Toggle::make('is_active')->label('فعال')->default(true),
                Toggle::make('aisle_after')->label('راهرو بعد از این صندلی')->default(false),
                Toggle::make('aisle_after_row')->label('راهرو بعد از این ردیف')->default(false),
                TextInput::make('default_price')->label('قیمت پایه')->numeric()->minValue(0)->maxValue(9999999999.99)->suffix('تومان'),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('code')
            ->columns([
                TextColumn::make('code')
                    ->label('کد')
                    ->searchable(),
                TextColumn::make('section')->label('بخش')->searchable(),
                TextColumn::make('row_label')->label('ردیف')->sortable(),
                TextColumn::make('number')->label('شماره')->sortable(),
                TextColumn::make('type')
                    ->label('نوع')
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'standard' => 'عادی',
                        'vip' => 'ویژه',
                        'wheelchair' => 'ویلچر',
                        default => $state,
                    })
                    ->badge(),
                TextColumn::make('is_active')->label('فعال')->formatStateUsing(fn (bool $state): string => $state ? 'بله' : 'خیر')->badge(),
                TextColumn::make('aisle_after')->label('راهرو بعد از صندلی')->formatStateUsing(fn (bool $state): string => $state ? 'بله' : '—')->badge(),
                TextColumn::make('aisle_after_row')->label('راهرو بعد از ردیف')->formatStateUsing(fn (bool $state): string => $state ? 'بله' : '—')->badge(),
                TextColumn::make('default_price')->label('قیمت پایه')->numeric()->suffix(' تومان')->placeholder('قیمت سانس'),
            ])
            ->headerActions([
                CreateAction::make(),
                Action::make('generateLayout')
                    ->label('ساخت گروهی صندلی‌ها')
                    ->schema([
                        TextInput::make('section')->label('نام بخش')->required()->maxLength(100)->default('main'),
                        TextInput::make('rows')->label('تعداد ردیف')->required()->integer()->minValue(1)->maxValue(26),
                        TextInput::make('seats_per_row')->label('صندلی در هر ردیف')->required()->integer()->minValue(1)->maxValue(100),
                        TextInput::make('aisles_after')
                            ->label('راهرو بعد از شماره‌های')
                            ->placeholder('مثلاً 4,8')
                            ->helperText('شماره صندلی‌ها را با ویرگول جدا کنید.'),
                        Select::make('type')
                            ->label('نوع صندلی')
                            ->options([
                                'standard' => 'عادی',
                                'vip' => 'ویژه',
                                'wheelchair' => 'ویلچر',
                            ])
                            ->default('standard')
                            ->required(),
                        TextInput::make('default_price')
                            ->label('قیمت پایه صندلی‌ها')
                            ->numeric()
                            ->minValue(0)
                            ->maxValue(9999999999.99)
                            ->suffix('تومان'),
                    ])
                    ->action(function (array $data): void {
                        /** @var Hall $hall */
                        $hall = $this->getOwnerRecord();

                        $aisleNumbers = Str::of((string) ($data['aisles_after'] ?? ''))
                            ->explode(',')
                            ->map(fn (string $number): int => (int) trim($number))
                            ->filter(fn (int $number): bool => $number > 0)
                            ->unique();

                        DB::transaction(function () use ($aisleNumbers, $data, $hall): void {
                            Seat::withoutEvents(function () use ($aisleNumbers, $data, $hall): void {
                                foreach (array_slice(range('A', 'Z'), 0, (int) $data['rows']) as $rowLabel) {
                                    foreach (range(1, (int) $data['seats_per_row']) as $number) {
                                        $hall->seats()->updateOrCreate(
                                            ['code' => $data['section'].'-'.$rowLabel.'-'.$number],
                                            [
                                                'section' => $data['section'],
                                                'row_label' => $rowLabel,
                                                'number' => (string) $number,
                                                'type' => $data['type'],
                                                'is_active' => true,
                                                'aisle_after' => $aisleNumbers->contains($number),
                                                'default_price' => $data['default_price'] ?? null,
                                            ],
                                        );
                                    }
                                }
                            });

                            $hall->update(['capacity' => $hall->seats()->where('is_active', true)->count()]);
                        });

                        Notification::make()->success()->title('چیدمان صندلی‌ها ساخته شد')->send();
                    }),
                Action::make('configureAisles')
                    ->label('تنظیم راهروها')
                    ->schema([
                        Select::make('section')
                            ->label('بخش سالن')
                            ->options(fn (): array => $this->getOwnerRecord()->seats()
                                ->distinct()
                                ->orderBy('section')
                                ->pluck('section', 'section')
                                ->all())
                            ->required(),
                        TextInput::make('aisles_after')
                            ->label('راهرو بعد از شماره‌های')
                            ->placeholder('مثلاً 4,8')
                            ->helperText('برای حذف همه راهروهای این بخش، این فیلد را خالی بگذارید.'),
                    ])
                    ->action(function (array $data): void {
                        /** @var Hall $hall */
                        $hall = $this->getOwnerRecord();
                        $aisleNumbers = Str::of((string) ($data['aisles_after'] ?? ''))
                            ->explode(',')
                            ->map(fn (string $number): string => (string) (int) trim($number))
                            ->filter(fn (string $number): bool => $number !== '0')
                            ->unique();

                        DB::transaction(function () use ($aisleNumbers, $data, $hall): void {
                            $sectionSeats = $hall->seats()->where('section', $data['section']);
                            (clone $sectionSeats)->update(['aisle_after' => false]);

                            if ($aisleNumbers->isNotEmpty()) {
                                $sectionSeats->whereIn('number', $aisleNumbers)->update(['aisle_after' => true]);
                            }
                        });

                        Notification::make()->success()->title('راهروهای بخش به‌روزرسانی شد')->send();
                    }),
            ])
            ->recordActions([
                EditAction::make(),
                DeleteAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ])
            ->defaultSort('id');
    }
}
