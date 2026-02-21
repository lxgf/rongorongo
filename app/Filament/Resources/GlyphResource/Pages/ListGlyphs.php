<?php

namespace App\Filament\Resources\GlyphResource\Pages;

use App\Filament\Resources\GlyphResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListGlyphs extends ListRecords
{
    protected static string $resource = GlyphResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
