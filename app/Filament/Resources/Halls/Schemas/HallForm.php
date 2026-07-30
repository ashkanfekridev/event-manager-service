<?php

namespace App\Filament\Resources\Halls\Schemas;

use App\Livewire\HallSeatLayoutEditor;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Livewire;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class HallForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('مشخصات سالن')->schema([
                    Select::make('venue_id')->label('مجموعه')->relationship('venue', 'name')->searchable()->preload()->required(),
                    TextInput::make('name')->label('نام سالن')->required()->maxLength(255),
                    TextInput::make('capacity')->label('ظرفیت')->disabled()->dehydrated(false)->suffix('صندلی'),
                ])->columns(2),
                Livewire::make(HallSeatLayoutEditor::class)
                    ->visibleOn('edit')
                    ->columnSpanFull(),
            ]);
    }
}
