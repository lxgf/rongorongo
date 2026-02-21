<?php

namespace App\Filament\Resources\TabletLineResource\Pages;

use App\Filament\Resources\TabletLineResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditTabletLine extends EditRecord
{
    protected static string $resource = TabletLineResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
