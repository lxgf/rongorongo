<?php

namespace App\Filament\Resources;

use App\Filament\Resources\CompoundGlyphPartResource\Pages;
use App\Models\CompoundGlyphPart;
use Filament\Actions;
use Filament\Forms;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables;
use Filament\Tables\Table;

class CompoundGlyphPartResource extends Resource
{
    protected static ?string $model = CompoundGlyphPart::class;

    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-square-3-stack-3d';

    protected static ?int $navigationSort = 7;

    public static function getModelLabel(): string
    {
        return __('models.compound_glyph_part.label');
    }

    public static function getPluralModelLabel(): string
    {
        return __('models.compound_glyph_part.plural');
    }

    public static function getNavigationGroup(): ?string
    {
        return __('models.nav.group');
    }

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Forms\Components\Select::make('compound_glyph_id')
                    ->relationship('compoundGlyph', 'code')
                    ->searchable()
                    ->preload()
                    ->required()
                    ->label(__('models.compound_glyph_part.fields.compound_glyph')),
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

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('compoundGlyph.code')
                    ->label(__('models.compound_glyph_part.fields.compound_glyph'))
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('glyph.barthel_code')
                    ->label(__('models.compound_glyph_part.fields.glyph'))
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('order')
                    ->label(__('models.compound_glyph_part.fields.order'))
                    ->sortable(),
            ])
            ->defaultSort('compound_glyph_id')
            ->actions([
                Actions\EditAction::make(),
            ])
            ->bulkActions([
                Actions\BulkActionGroup::make([
                    Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListCompoundGlyphParts::route('/'),
            'create' => Pages\CreateCompoundGlyphPart::route('/create'),
            'edit' => Pages\EditCompoundGlyphPart::route('/{record}/edit'),
        ];
    }
}
