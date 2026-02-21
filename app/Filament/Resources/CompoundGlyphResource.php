<?php

namespace App\Filament\Resources;

use App\Filament\Resources\CompoundGlyphResource\Pages;
use App\Filament\Resources\CompoundGlyphResource\RelationManagers;
use App\Models\CompoundGlyph;
use Filament\Actions;
use Filament\Forms;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables;
use Filament\Tables\Table;

class CompoundGlyphResource extends Resource
{
    protected static ?string $model = CompoundGlyph::class;

    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-puzzle-piece';

    protected static ?int $navigationSort = 3;

    public static function getModelLabel(): string
    {
        return __('models.compound_glyph.label');
    }

    public static function getPluralModelLabel(): string
    {
        return __('models.compound_glyph.plural');
    }

    public static function getNavigationGroup(): ?string
    {
        return __('models.nav.group');
    }

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Forms\Components\TextInput::make('code')
                    ->required()
                    ->maxLength(30)
                    ->unique(ignoreRecord: true)
                    ->label(__('models.compound_glyph.fields.code'))
                    ->helperText('001.006.022'),
                Forms\Components\Textarea::make('description')
                    ->label(__('models.compound_glyph.fields.description'))
                    ->columnSpanFull(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('code')
                    ->label(__('models.compound_glyph.fields.code'))
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('parts_count')
                    ->counts('parts')
                    ->label(__('models.compound_glyph_part.plural'))
                    ->sortable(),
                Tables\Columns\TextColumn::make('tablet_renderings_count')
                    ->counts('tabletRenderings')
                    ->label(__('models.tablet_rendering.plural'))
                    ->sortable(),
                Tables\Columns\TextColumn::make('description')
                    ->label(__('models.compound_glyph.fields.description'))
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

    public static function getRelations(): array
    {
        return [
            RelationManagers\PartsRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListCompoundGlyphs::route('/'),
            'create' => Pages\CreateCompoundGlyph::route('/create'),
            'edit' => Pages\EditCompoundGlyph::route('/{record}/edit'),
            'view' => Pages\ViewCompoundGlyph::route('/{record}'),
        ];
    }
}
