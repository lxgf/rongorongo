<?php

namespace App\Filament\Resources\GlyphResource\Pages;

use App\Filament\Resources\GlyphResource;
use Filament\Actions;
use Filament\Resources\Pages\ViewRecord;

class ViewGlyph extends ViewRecord
{
    protected static string $resource = GlyphResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\EditAction::make(),
        ];
    }
}
