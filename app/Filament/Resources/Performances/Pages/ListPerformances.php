<?php

namespace App\Filament\Resources\Performances\Pages;

use App\Filament\Resources\Performances\PerformanceResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListPerformances extends ListRecords
{
    protected static string $resource = PerformanceResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
