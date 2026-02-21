<?php

namespace App\Filament\Resources\TabletLineResource\Pages;

use App\Filament\Resources\TabletLineResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListTabletLines extends ListRecords
{
    protected static string $resource = TabletLineResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
