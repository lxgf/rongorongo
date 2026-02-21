<?php

namespace App\Filament\Resources\TabletResource\Pages;

use App\Filament\Resources\TabletResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditTablet extends EditRecord
{
    protected static string $resource = TabletResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\ViewAction::make(),
            Actions\DeleteAction::make(),
        ];
    }
}
