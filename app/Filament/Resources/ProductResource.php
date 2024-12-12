<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ProductResource\Pages;
use App\Filament\Resources\ProductResource\RelationManagers;
use App\Models\Product;
use Filament\Forms;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Group;
use Filament\Forms\Components\MarkdownEditor;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Form;
use Filament\Forms\Set;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Illuminate\Support\Str;

class ProductResource extends Resource
{
    protected static ?string $model = Product::class;

    protected static ?string $navigationIcon = 'heroicon-o-squares-2x2';

    protected static ?string $navigationGroup = 'Каталог';

    public static function getModelLabel(): string
    {
        return "Товар";
    }

    public static function getPluralModelLabel(): string
    {
        return "Товары";
    }

    protected static ?int $navigationSort = 4;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Group::make()->schema([
                    Section::make('Информация о продукте')->schema([
                        TextInput::make('name')
                            ->label('Название') // Метка на русском
                            ->helperText('Введите название продукта.') // Подсказка на русском
                            ->required()
                            ->maxLength(255)
                            ->live(onBlur: true)
                            ->afterStateUpdated(function (string $operation, $state, Set $set) {
                                if ($operation !== 'create') {
                                    return;
                                }
                                $set('slug', Str::slug($state));
                            }),

                        TextInput::make('slug')
                            ->label('Слаг') // Метка на русском
                            ->helperText('Автоматически генерируется из названия.') // Подсказка на русском
                            ->required()
                            ->maxLength(255)
                            ->disabled()
                            ->dehydrated()
                            ->unique(Product::class, 'slug', ignoreRecord: true),

                        MarkdownEditor::make('description')
                            ->label('Описание') // Метка на русском
                            ->helperText('Добавьте подробное описание продукта.') // Подсказка на русском
                            ->columnSpanFull()
                            ->fileAttachmentsDirectory('products')
                    ])->columns(2),

                    Section::make('Изображения')->schema([
                        FileUpload::make('images')
                            ->label('Изображения') // Метка на русском
                            ->helperText('Загрузите до 5 изображений продукта.') // Подсказка на русском
                            ->multiple()
                            ->directory('products')
                            ->maxFiles(5)
                            ->reorderable()
                    ])
                ])->columnSpan(2),

                Group::make()->schema([
                    Section::make('Цена')->schema([
                        TextInput::make('price')
                            ->label('Цена') // Метка на русском
                            ->helperText('Введите цену в рублях.') // Подсказка на русском
                            ->numeric()
                            ->required()
                            ->prefix('Руб.')
                    ]),

                    Section::make('Ассоциации')->schema([
                        Select::make('category_id')
                            ->label('Категория') // Метка на русском
                            ->helperText('Выберите категорию продукта.') // Подсказка на русском
                            ->required()
                            ->searchable()
                            ->preload()
                            ->relationship('category', 'name'),

                        Select::make('brand_id')
                            ->label('Бренд') // Метка на русском
                            ->helperText('Выберите бренд продукта.') // Подсказка на русском
                            ->required()
                            ->searchable()
                            ->preload()
                            ->relationship('brand', 'name'),
                    ]),

                    Section::make('Статус')->schema([
                        Toggle::make('in_stock')
                            ->label('В наличии') // Метка на русском
                            ->helperText('Отметьте, если продукт есть в наличии.') // Подсказка на русском
                            ->required()
                            ->default(true),
                        Toggle::make('is_active')
                            ->label('Активен') // Метка на русском
                            ->helperText('Определяет, отображается ли продукт на сайте.') // Подсказка на русском
                            ->required()
                            ->default(true),
                        Toggle::make('is_featured')
                            ->label('Рекомендуемый') // Метка на русском
                            ->helperText('Отметьте, если продукт следует выделить на главной странице.') // Подсказка на русском
                            ->required(),
                        Toggle::make('on_sale')
                            ->label('На распродаже') // Метка на русском
                            ->helperText('Отметьте, если продукт участвует в распродаже.') // Подсказка на русском
                            ->required(),
                    ])
                ])->columnSpan(1)
            ])->columns(3);
    }


    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')
                    ->label('Название') // Метка на русском
                    ->searchable(),

                TextColumn::make('category.name')
                    ->label('Категория') // Метка на русском
                    ->sortable(),

                TextColumn::make('brand.name')
                    ->label('Бренд') // Метка на русском
                    ->sortable(),

                TextColumn::make('price')
                    ->label('Цена') // Метка на русском
                    ->money('RUB')
                    ->sortable(),

                IconColumn::make('is_featured')
                    ->label('Рекомендуемый') // Метка на русском
                    ->boolean(),

                IconColumn::make('on_sale')
                    ->label('На распродаже') // Метка на русском
                    ->boolean(),

                IconColumn::make('in_stock')
                    ->label('В наличии') // Метка на русском
                    ->boolean(),

                IconColumn::make('is_active')
                    ->label('Активен') // Метка на русском
                    ->boolean(),

                TextColumn::make('created_at')
                    ->label('Дата создания') // Метка на русском
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),

                TextColumn::make('updated_at')
                    ->label('Дата изменения') // Метка на русском
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                SelectFilter::make('category')
                    ->label('Категория') // Метка на русском
                    ->relationship('category', 'name'),

                SelectFilter::make('brand')
                    ->label('Бренд') // Метка на русском
                    ->relationship('brand', 'name'),
            ])
            ->actions([
                Tables\Actions\ActionGroup::make([
                    Tables\Actions\ViewAction::make(),
                    Tables\Actions\EditAction::make(),
                    Tables\Actions\DeleteAction::make()
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
            'index' => Pages\ListProducts::route('/'),
            'create' => Pages\CreateProduct::route('/create'),
            'edit' => Pages\EditProduct::route('/{record}/edit'),
        ];
    }
}
