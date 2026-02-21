<?php

namespace App\Filament\Resources\GlyphResource\Pages;

use App\Filament\Resources\GlyphResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditGlyph extends EditRecord
{
    protected static string $resource = GlyphResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\ViewAction::make(),
            Actions\DeleteAction::make(),
        ];
    }
}
