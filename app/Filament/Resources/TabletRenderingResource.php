<?php

namespace App\Filament\Resources;

use App\Filament\Resources\TabletRenderingResource\Pages;
use App\Models\TabletRendering;
use Filament\Actions;
use Filament\Forms;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables;
use Filament\Tables\Table;

class TabletRenderingResource extends Resource
{
    protected static ?string $model = TabletRendering::class;

    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-eye';

    protected static ?int $navigationSort = 6;

    public static function getModelLabel(): string
    {
        return __('models.tablet_rendering.label');
    }

    public static function getPluralModelLabel(): string
    {
        return __('models.tablet_rendering.plural');
    }

    public static function getNavigationGroup(): ?string
    {
        return __('models.nav.group');
    }

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Forms\Components\Select::make('tablet_line_id')
                    ->relationship('tabletLine', 'id')
                    ->searchable()
                    ->preload()
                    ->required()
                    ->label(__('models.tablet_rendering.fields.tablet_line')),
                Forms\Components\Select::make('rendering_id')
                    ->relationship('rendering', 'code')
                    ->searchable()
                    ->preload()
                    ->nullable()
                    ->label(__('models.tablet_rendering.fields.rendering')),
                Forms\Components\Select::make('compound_glyph_id')
                    ->relationship('compoundGlyph', 'code')
                    ->searchable()
                    ->preload()
                    ->nullable()
                    ->label(__('models.tablet_rendering.fields.compound_glyph')),
                Forms\Components\TextInput::make('position')
                    ->numeric()
                    ->required()
                    ->label(__('models.tablet_rendering.fields.position')),
                Forms\Components\Toggle::make('is_inverted')->label(__('models.tablet_rendering.fields.is_inverted')),
                Forms\Components\Toggle::make('is_mirrored')->label(__('models.tablet_rendering.fields.is_mirrored')),
                Forms\Components\Toggle::make('is_small')->label(__('models.tablet_rendering.fields.is_small')),
                Forms\Components\Toggle::make('is_enlarged')->label(__('models.tablet_rendering.fields.is_enlarged')),
                Forms\Components\Toggle::make('is_truncated')->label(__('models.tablet_rendering.fields.is_truncated')),
                Forms\Components\Toggle::make('is_distorted')->label(__('models.tablet_rendering.fields.is_distorted')),
                Forms\Components\Toggle::make('is_uncertain')->label(__('models.tablet_rendering.fields.is_uncertain')),
                Forms\Components\Toggle::make('is_nonstandard')->label(__('models.tablet_rendering.fields.is_nonstandard')),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('tabletLine.tablet.name')
                    ->label(__('models.tablet.label'))
                    ->sortable(),
                Tables\Columns\TextColumn::make('tabletLine.line')
                    ->label(__('models.tablet_line.fields.line'))
                    ->sortable(),
                Tables\Columns\TextColumn::make('position')
                    ->label(__('models.tablet_rendering.fields.position'))
                    ->sortable(),
                Tables\Columns\TextColumn::make('rendering.code')
                    ->label(__('models.tablet_rendering.fields.rendering'))
                    ->searchable(),
                Tables\Columns\TextColumn::make('compoundGlyph.code')
                    ->label(__('models.tablet_rendering.fields.compound_glyph'))
                    ->searchable(),
                Tables\Columns\IconColumn::make('is_uncertain')->boolean()->label('?')->toggleable(),
                Tables\Columns\IconColumn::make('is_nonstandard')->boolean()->label('x')->toggleable(),
            ])
            ->defaultSort('id')
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
            'index' => Pages\ListTabletRenderings::route('/'),
            'create' => Pages\CreateTabletRendering::route('/create'),
            'edit' => Pages\EditTabletRendering::route('/{record}/edit'),
        ];
    }
}
