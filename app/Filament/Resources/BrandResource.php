<?php

namespace App\Filament\Resources;

use App\Filament\Resources\BrandResource\Pages;
use App\Filament\Resources\BrandResource\RelationManagers;
use App\Models\Brand;
use Filament\Forms;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Grid;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Form;
use Filament\Forms\Set;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Support\Str;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class BrandResource extends Resource
{
    protected static ?string $model = Brand::class;

    protected static ?string $navigationIcon = 'heroicon-o-musical-note';

    protected static ?string $navigationGroup = 'Каталог';

    protected static ?string $recordTitleAttribute = 'name';

    public static function getModelLabel(): string
    {
        return "Бренд";
    }

    public static function getPluralModelLabel(): string
    {
        return "Бренды";
    }

    protected static ?int $navigationSort = 2;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Section::make('Основная информация') // Заголовок секции
                ->schema([
                    Grid::make()
                        ->schema([
                            TextInput::make('name')
                                ->label('Название') // Метка на русском
                                ->helperText('Введите название категории') // Вспомогательный текст
                                ->maxLength(255)
                                ->required()
                                ->live(onBlur: true)
                                ->afterStateUpdated(fn(string $operation, $state, Set $set) => $operation
                                === 'create' ? $set('slug', Str::slug($state)) : null),

                            TextInput::make('slug')
                                ->label('Слаг') // Метка на русском
                                ->helperText('Автоматически создаётся на основе названия') // Вспомогательный текст
                                ->maxLength(255)
                                ->disabled()
                                ->required()
                                ->dehydrated()
                                ->unique(Brand::class, 'slug', ignoreRecord: true)
                        ]),
                    FileUpload::make('image')
                        ->label('Изображение') // Метка на русском
                        ->helperText('Загрузите изображение категории') // Вспомогательный текст
                        ->image()
                        ->directory('categories'),

                    Toggle::make('is_active')
                        ->label('Активна') // Метка на русском
                        ->helperText('Включите, если категория активна') // Вспомогательный текст
                        ->required()
                        ->default(true)
                ])
            ]);
    }


    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->label('Название') // Метка на русском
                    ->searchable(),
                Tables\Columns\ImageColumn::make('image')
                    ->label('Изображение'), // Метка на русском
                Tables\Columns\TextColumn::make('slug')
                    ->label('Слаг') // Метка на русском
                    ->searchable(),
                Tables\Columns\IconColumn::make('is_active')
                    ->label('Активна') // Метка на русском
                    ->boolean(),
                Tables\Columns\TextColumn::make('created_at')
                    ->label('Создано') // Метка на русском
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('updated_at')
                    ->label('Обновлено') // Метка на русском
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\ActionGroup::make([
                    Tables\Actions\ViewAction::make(),
                    Tables\Actions\EditAction::make(),
                    Tables\Actions\DeleteAction::make(),
                ])
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListBrands::route('/'),
            'create' => Pages\CreateBrand::route('/create'),
            'edit' => Pages\EditBrand::route('/{record}/edit'),
        ];
    }
}
