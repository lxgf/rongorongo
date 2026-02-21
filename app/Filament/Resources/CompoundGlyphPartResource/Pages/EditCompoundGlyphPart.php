<?php

namespace App\Filament\Resources\CompoundGlyphPartResource\Pages;

use App\Filament\Resources\CompoundGlyphPartResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditCompoundGlyphPart extends EditRecord
{
    protected static string $resource = CompoundGlyphPartResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
