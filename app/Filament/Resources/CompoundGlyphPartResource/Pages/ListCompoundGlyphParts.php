<?php

namespace App\Filament\Resources\CompoundGlyphPartResource\Pages;

use App\Filament\Resources\CompoundGlyphPartResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListCompoundGlyphParts extends ListRecords
{
    protected static string $resource = CompoundGlyphPartResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
