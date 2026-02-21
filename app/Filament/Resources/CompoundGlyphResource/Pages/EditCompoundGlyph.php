<?php

namespace App\Filament\Resources\CompoundGlyphResource\Pages;

use App\Filament\Resources\CompoundGlyphResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditCompoundGlyph extends EditRecord
{
    protected static string $resource = CompoundGlyphResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\ViewAction::make(),
            Actions\DeleteAction::make(),
        ];
    }
}
