<?php

namespace App\Filament\Resources\Venues\Schemas;

use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class VenueForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('مشخصات مجموعه')->schema([
                    TextInput::make('name')->label('نام مجموعه')->required()->maxLength(255),
                    TextInput::make('city')->label('شهر')->required()->maxLength(255),
                    Textarea::make('address')->label('آدرس')->required()->columnSpanFull(),
                ])->columns(2),
            ]);
    }
}
