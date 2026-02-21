<?php

namespace App\Filament\Resources\CompoundGlyphResource\Pages;

use App\Filament\Resources\CompoundGlyphResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListCompoundGlyphs extends ListRecords
{
    protected static string $resource = CompoundGlyphResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
