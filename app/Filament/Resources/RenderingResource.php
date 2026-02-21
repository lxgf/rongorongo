<?php

namespace App\Filament\Resources;

use App\Filament\Resources\RenderingResource\Pages;
use App\Models\Rendering;
use Filament\Actions;
use Filament\Forms;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables;
use Filament\Tables\Table;

class RenderingResource extends Resource
{
    protected static ?string $model = Rendering::class;

    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-pencil';

    protected static ?int $navigationSort = 4;

    public static function getModelLabel(): string
    {
        return __('models.rendering.label');
    }

    public static function getPluralModelLabel(): string
    {
        return __('models.rendering.plural');
    }

    public static function getNavigationGroup(): ?string
    {
        return __('models.nav.group');
    }

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Forms\Components\Select::make('glyph_id')
                    ->relationship('glyph', 'barthel_code')
                    ->searchable()
                    ->preload()
                    ->required()
                    ->label(__('models.rendering.fields.glyph')),
                Forms\Components\TextInput::make('code')
                    ->required()
                    ->maxLength(10)
                    ->unique(ignoreRecord: true)
                    ->label(__('models.rendering.fields.code')),
                Forms\Components\Textarea::make('description')
                    ->label(__('models.rendering.fields.description'))
                    ->columnSpanFull(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('code')
                    ->label(__('models.rendering.fields.code'))
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('glyph.barthel_code')
                    ->label(__('models.rendering.fields.glyph'))
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('tablet_renderings_count')
                    ->counts('tabletRenderings')
                    ->label(__('models.tablet_rendering.plural'))
                    ->sortable(),
                Tables\Columns\TextColumn::make('description')
                    ->label(__('models.rendering.fields.description'))
                    ->limit(50)
                    ->toggleable(),
            ])
            ->defaultSort('code')
            ->actions([
                Actions\EditAction::make(),
                Actions\ViewAction::make(),
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
            'index' => Pages\ListRenderings::route('/'),
            'create' => Pages\CreateRendering::route('/create'),
            'edit' => Pages\EditRendering::route('/{record}/edit'),
        ];
    }
}
