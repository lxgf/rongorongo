<?php

namespace App\Filament\Resources\RenderingResource\Pages;

use App\Filament\Resources\RenderingResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditRendering extends EditRecord
{
    protected static string $resource = RenderingResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
