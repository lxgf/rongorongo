<?php

namespace App\Filament\Resources\TabletResource\RelationManagers;

use Filament\Actions;
use Filament\Forms;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Schema;
use Filament\Tables;
use Filament\Tables\Table;

class LinesRelationManager extends RelationManager
{
    protected static string $relationship = 'lines';

    public static function getTitle(\Illuminate\Database\Eloquent\Model $ownerRecord, string $pageClass): string
    {
        return __('models.tablet_line.plural');
    }

    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
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

    public function table(Table $table): Table
    {
        return $table
            ->columns([
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
                    ->label(__('models.tablet_rendering.plural')),
            ])
            ->defaultSort('side')
            ->defaultSort('line')
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
