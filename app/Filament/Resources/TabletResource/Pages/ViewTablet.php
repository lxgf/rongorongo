<?php

namespace App\Filament\Resources\TabletResource\Pages;

use App\Filament\Resources\TabletResource;
use Filament\Actions;
use Filament\Resources\Pages\ViewRecord;

class ViewTablet extends ViewRecord
{
    protected static string $resource = TabletResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\EditAction::make(),
        ];
    }
}
