<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ImageResource\Pages;
use App\Models\Image;
use Filament\Actions;
use Filament\Forms;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables;
use Filament\Tables\Table;

class ImageResource extends Resource
{
    protected static ?string $model = Image::class;

    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-photo';

    protected static ?int $navigationSort = 8;

    public static function getModelLabel(): string
    {
        return __('models.image.label');
    }

    public static function getPluralModelLabel(): string
    {
        return __('models.image.plural');
    }

    public static function getNavigationGroup(): ?string
    {
        return __('models.nav.group');
    }

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Forms\Components\FileUpload::make('path')
                    ->image()
                    ->disk('public_path')
                    ->directory('images')
                    ->required()
                    ->label(__('models.image.fields.path')),
                Forms\Components\TextInput::make('type')
                    ->maxLength(50)
                    ->label(__('models.image.fields.type'))
                    ->helperText('glyph, scan, photo'),
                Forms\Components\Select::make('imageable_type')
                    ->options([
                        'App\\Models\\Glyph' => 'Glyph',
                        'App\\Models\\Tablet' => 'Tablet',
                        'App\\Models\\Rendering' => 'Rendering',
                        'App\\Models\\CompoundGlyph' => 'CompoundGlyph',
                        'App\\Models\\TabletLine' => 'TabletLine',
                        'App\\Models\\TabletRendering' => 'TabletRendering',
                    ])
                    ->required()
                    ->label(__('models.image.fields.imageable_type')),
                Forms\Components\TextInput::make('imageable_id')
                    ->numeric()
                    ->required()
                    ->label(__('models.image.fields.imageable_id')),
                Forms\Components\TextInput::make('sort_order')
                    ->numeric()
                    ->default(0)
                    ->label(__('models.image.fields.sort_order')),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('id')
                    ->sortable(),
                Tables\Columns\ImageColumn::make('path')
                    ->label(__('models.image.fields.path'))
                    ->disk('public_path')
                    ->square()
                    ->size(40),
                Tables\Columns\TextColumn::make('type')
                    ->label(__('models.image.fields.type'))
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('imageable_type')
                    ->label(__('models.image.fields.imageable_type'))
                    ->formatStateUsing(fn (string $state) => class_basename($state))
                    ->sortable(),
                Tables\Columns\TextColumn::make('imageable_id')
                    ->label(__('models.image.fields.imageable_id'))
                    ->sortable(),
                Tables\Columns\TextColumn::make('sort_order')
                    ->label(__('models.image.fields.sort_order'))
                    ->sortable(),
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
            'index' => Pages\ListImages::route('/'),
            'create' => Pages\CreateImage::route('/create'),
            'edit' => Pages\EditImage::route('/{record}/edit'),
        ];
    }
}
