<?php

namespace App\Filament\Resources\GlyphResource\RelationManagers;

use Filament\Actions;
use Filament\Forms;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Schema;
use Filament\Tables;
use Filament\Tables\Table;

class RenderingsRelationManager extends RelationManager
{
    protected static string $relationship = 'renderings';

    public static function getTitle(\Illuminate\Database\Eloquent\Model $ownerRecord, string $pageClass): string
    {
        return __('models.rendering.plural');
    }

    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Forms\Components\TextInput::make('code')
                    ->required()
                    ->maxLength(10)
                    ->label(__('models.rendering.fields.code')),
                Forms\Components\Textarea::make('description')
                    ->label(__('models.rendering.fields.description')),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('code')
                    ->label(__('models.rendering.fields.code'))
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('tablet_renderings_count')
                    ->counts('tabletRenderings')
                    ->label(__('models.tablet_rendering.plural')),
                Tables\Columns\TextColumn::make('description')
                    ->label(__('models.rendering.fields.description'))
                    ->limit(50)
                    ->toggleable(),
            ])
            ->defaultSort('code')
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
