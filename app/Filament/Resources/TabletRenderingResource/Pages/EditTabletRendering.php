<?php

namespace App\Filament\Resources\TabletRenderingResource\Pages;

use App\Filament\Resources\TabletRenderingResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditTabletRendering extends EditRecord
{
    protected static string $resource = TabletRenderingResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
