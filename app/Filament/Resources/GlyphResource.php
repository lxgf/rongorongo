<?php

namespace App\Filament\Resources;

use App\Filament\Resources\GlyphResource\Pages;
use App\Filament\Resources\GlyphResource\RelationManagers;
use App\Models\Glyph;
use Filament\Actions;
use Filament\Forms;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables;
use Filament\Tables\Table;

class GlyphResource extends Resource
{
    protected static ?string $model = Glyph::class;

    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-language';

    protected static ?int $navigationSort = 2;

    public static function getModelLabel(): string
    {
        return __('models.glyph.label');
    }

    public static function getPluralModelLabel(): string
    {
        return __('models.glyph.plural');
    }

    public static function getNavigationGroup(): ?string
    {
        return __('models.nav.group');
    }

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Forms\Components\TextInput::make('barthel_code')
                    ->required()
                    ->maxLength(10)
                    ->unique(ignoreRecord: true)
                    ->label(__('models.glyph.fields.barthel_code')),
                Forms\Components\Textarea::make('description')
                    ->label(__('models.glyph.fields.description'))
                    ->columnSpanFull(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('barthel_code')
                    ->label(__('models.glyph.fields.barthel_code'))
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('renderings_count')
                    ->counts('renderings')
                    ->label(__('models.rendering.plural'))
                    ->sortable(),
                Tables\Columns\TextColumn::make('description')
                    ->label(__('models.glyph.fields.description'))
                    ->limit(50)
                    ->toggleable(),
            ])
            ->defaultSort('barthel_code')
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

    public static function getRelations(): array
    {
        return [
            RelationManagers\RenderingsRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListGlyphs::route('/'),
            'create' => Pages\CreateGlyph::route('/create'),
            'edit' => Pages\EditGlyph::route('/{record}/edit'),
            'view' => Pages\ViewGlyph::route('/{record}'),
        ];
    }
}
