<?php

namespace App\Filament\Resources\TabletRenderingResource\Pages;

use App\Filament\Resources\TabletRenderingResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListTabletRenderings extends ListRecords
{
    protected static string $resource = TabletRenderingResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
