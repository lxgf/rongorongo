<?php

namespace App\Filament\Resources;

use App\Filament\Resources\TabletLineResource\Pages;
use App\Models\TabletLine;
use Filament\Actions;
use Filament\Forms;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables;
use Filament\Tables\Table;

class TabletLineResource extends Resource
{
    protected static ?string $model = TabletLine::class;

    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-bars-3';

    protected static ?int $navigationSort = 5;

    public static function getModelLabel(): string
    {
        return __('models.tablet_line.label');
    }

    public static function getPluralModelLabel(): string
    {
        return __('models.tablet_line.plural');
    }

    public static function getNavigationGroup(): ?string
    {
        return __('models.nav.group');
    }

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Forms\Components\Select::make('tablet_id')
                    ->relationship('tablet', 'name')
                    ->searchable()
                    ->preload()
                    ->required()
                    ->label(__('models.tablet_line.fields.tablet')),
                Forms\Components\Select::make('side')
                    ->options(fn () => [
                        0 => __('models.tablet_line.fields.sides.recto'),
                        1 => __('models.tablet_line.fields.sides.verso'),
                    ])
                    ->required()
                    ->label(__('models.tablet_line.fields.side')),
                Forms\Components\TextInput::make('line')
                    ->numeric()
                    ->required()
                    ->label(__('models.tablet_line.fields.line')),
                Forms\Components\Select::make('direction')
                    ->options(fn () => [
                        'ltr' => __('models.tablet_line.fields.directions.ltr'),
                        'rtl' => __('models.tablet_line.fields.directions.rtl'),
                    ])
                    ->default('ltr')
                    ->required()
                    ->label(__('models.tablet_line.fields.direction')),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('tablet.name')
                    ->label(__('models.tablet_line.fields.tablet'))
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('side')
                    ->label(__('models.tablet_line.fields.side'))
                    ->formatStateUsing(fn (int $state) => $state === 0
                        ? __('models.tablet_line.fields.sides.recto')
                        : __('models.tablet_line.fields.sides.verso'))
                    ->sortable(),
                Tables\Columns\TextColumn::make('line')
                    ->label(__('models.tablet_line.fields.line'))
                    ->sortable(),
                Tables\Columns\TextColumn::make('direction')
                    ->label(__('models.tablet_line.fields.direction'))
                    ->badge()
                    ->color(fn (string $state) => $state === 'ltr' ? 'success' : 'warning'),
                Tables\Columns\TextColumn::make('tablet_renderings_count')
                    ->counts('tabletRenderings')
                    ->label(__('models.tablet_rendering.plural'))
                    ->sortable(),
            ])
            ->defaultSort('tablet_id')
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
            'index' => Pages\ListTabletLines::route('/'),
            'create' => Pages\CreateTabletLine::route('/create'),
            'edit' => Pages\EditTabletLine::route('/{record}/edit'),
        ];
    }
}
