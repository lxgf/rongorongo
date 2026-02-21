<?php

namespace App\Filament\Resources;

use App\Filament\Resources\TabletResource\Pages;
use App\Filament\Resources\TabletResource\RelationManagers;
use App\Models\Tablet;
use Filament\Actions;
use Filament\Forms;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables;
use Filament\Tables\Table;

class TabletResource extends Resource
{
    protected static ?string $model = Tablet::class;

    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-rectangle-stack';

    protected static ?int $navigationSort = 1;

    public static function getModelLabel(): string
    {
        return __('models.tablet.label');
    }

    public static function getPluralModelLabel(): string
    {
        return __('models.tablet.plural');
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
                    ->maxLength(5)
                    ->label(__('models.tablet.fields.code')),
                Forms\Components\TextInput::make('name')
                    ->required()
                    ->maxLength(100)
                    ->label(__('models.tablet.fields.name')),
                Forms\Components\TextInput::make('location')
                    ->maxLength(200)
                    ->label(__('models.tablet.fields.location')),
                Forms\Components\Textarea::make('description')
                    ->label(__('models.tablet.fields.description'))
                    ->columnSpanFull(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('code')
                    ->label(__('models.tablet.fields.code'))
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('name')
                    ->label(__('models.tablet.fields.name'))
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('location')
                    ->label(__('models.tablet.fields.location'))
                    ->searchable()
                    ->toggleable(),
                Tables\Columns\TextColumn::make('lines_count')
                    ->counts('lines')
                    ->label(__('models.tablet_line.plural'))
                    ->sortable(),
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
            RelationManagers\LinesRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListTablets::route('/'),
            'create' => Pages\CreateTablet::route('/create'),
            'edit' => Pages\EditTablet::route('/{record}/edit'),
            'view' => Pages\ViewTablet::route('/{record}'),
        ];
    }
}
