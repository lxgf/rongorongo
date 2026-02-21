<?php

namespace App\Filament\Resources\CompoundGlyphResource\RelationManagers;

use Filament\Actions;
use Filament\Forms;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Schema;
use Filament\Tables;
use Filament\Tables\Table;

class PartsRelationManager extends RelationManager
{
    protected static string $relationship = 'parts';

    public static function getTitle(\Illuminate\Database\Eloquent\Model $ownerRecord, string $pageClass): string
    {
        return __('models.compound_glyph_part.plural');
    }

    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Forms\Components\Select::make('glyph_id')
                    ->relationship('glyph', 'barthel_code')
                    ->searchable()
                    ->preload()
                    ->required()
                    ->label(__('models.compound_glyph_part.fields.glyph')),
                Forms\Components\TextInput::make('order')
                    ->numeric()
                    ->required()
                    ->label(__('models.compound_glyph_part.fields.order')),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('order')
                    ->label(__('models.compound_glyph_part.fields.order'))
                    ->sortable(),
                Tables\Columns\TextColumn::make('glyph.barthel_code')
                    ->label(__('models.compound_glyph_part.fields.glyph'))
                    ->searchable(),
            ])
            ->defaultSort('order')
            ->headerActions([
                Actions\CreateAction::make(),
            ])
            ->actions([
                Actions\EditAction::make(),
                Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Actions\BulkActionGroup::make([
                    Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }
}
