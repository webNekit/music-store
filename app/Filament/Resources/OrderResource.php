<?php

namespace App\Filament\Resources;

use App\Filament\Resources\OrderResource\Pages;
use App\Filament\Resources\OrderResource\RelationManagers;
use App\Filament\Resources\OrderResource\RelationManagers\AddressRelationManager;
use App\Models\Order;
use App\Models\Product;
use Filament\Actions\ActionGroup;
use Filament\Actions\ViewAction;
use Filament\Actions\EditAction;
use Filament\Actions\DeleteAction;
use Filament\Forms;
use Filament\Forms\Components\Group;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\Placeholder;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\ToggleButtons;
use Filament\Forms\Form;
use Filament\Forms\Get;
use Filament\Forms\Set;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\SelectColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Illuminate\Support\Number;

class OrderResource extends Resource
{
    protected static ?string $model = Order::class;

    protected static ?string $navigationIcon = 'heroicon-o-shopping-bag';

    protected static ?string $navigationGroup = 'Работа с покупками';

    public static function getModelLabel(): string
    {
        return "Заказ";
    }

    public static function getPluralModelLabel(): string
    {
        return "Заказы";
    }

    protected static ?int $navigationSort = 5;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Group::make()->schema([
                    Section::make('Информация о заказе')->schema([
                        Select::make('user_id')
                            ->label('Клиент')
                            ->relationship('user', 'name')
                            ->searchable()
                            ->preload()
                            ->required(),

                        Select::make('payment_method')
                            ->label('Способ оплаты')
                            ->options([
                                'stripe' => 'Перевод по реквизитам',
                                'cod' => 'Наличные при получении',
                            ])
                            ->required(),

                        Select::make('payment_status')
                            ->label('Статус оплаты')
                            ->options([
                                'pending' => 'Ожидается',
                                'paid' => 'Оплачено',
                                'failed' => 'Ошибка',
                            ])
                            ->default('pending')
                            ->required(),

                        ToggleButtons::make('status')
                            ->label('Статус заказа')
                            ->inline()
                            ->default('new')
                            ->required()
                            ->options([
                                'new' => 'Новый',
                                'processing' => 'В обработке',
                                'shipped' => 'Отправлен',
                                'delivered' => 'Доставлен',
                                'cancelled' => 'Отменён',
                            ])
                            ->colors([
                                'new' => 'info',
                                'processing' => 'warning',
                                'shipped' => 'success',
                                'delivered' => 'success',
                                'cancelled' => 'danger',
                            ])
                            ->icons([
                                'new' => 'heroicon-m-sparkles',
                                'processing' => 'heroicon-m-arrow-path',
                                'shipped' => 'heroicon-m-truck',
                                'delivered' => 'heroicon-m-check-badge',
                                'cancelled' => 'heroicon-m-x-circle',
                            ]),

                        Select::make('currency')
                            ->label('Валюта')
                            ->options([
                                'rub' => 'RUB',
                                'usd' => 'USD',
                                'eur' => 'EUR',
                            ])
                            ->default('rub')
                            ->required(),

                        Select::make('shipping_method')
                            ->label('Способ доставки')
                            ->options([
                                'pek' => 'ПЭК',
                                'dl' => 'Деловые линии',
                                'pr' => 'Почта России',
                                'sdek' => 'СДЭК',
                            ]),

                        Textarea::make('notes')
                            ->label('Примечания')
                            ->columnSpanFull()
                    ])->columns(2),

                    Section::make('Товары заказа')->schema([
                        Repeater::make('items')
                            ->relationship()
                            ->schema([
                                Select::make('product_id')
                                    ->label('Продукт')
                                    ->relationship('product', 'name')
                                    ->searchable()
                                    ->preload()
                                    ->required()
                                    ->distinct()
                                    ->disableOptionsWhenSelectedInSiblingRepeaterItems()
                                    ->columnSpan(4)
                                    ->reactive()
                                    ->afterStateUpdated(fn($state, Set $set) => $set('unit_amount', Product::find($state)?->price ?? 0))
                                    ->afterStateUpdated(fn($state, Set $set) => $set('total_amount', Product::find($state)?->price ?? 0)),

                                TextInput::make('quantity')
                                    ->label('Количество')
                                    ->numeric()
                                    ->required()
                                    ->default(1)
                                    ->minValue(1)
                                    ->columnSpan(2)
                                    ->reactive()
                                    ->afterStateUpdated(fn($state, Set $set, Get $get) => $set('total_amount', $state * $get('unit_amount'))),

                                TextInput::make('unit_amount')
                                    ->label('Цена за единицу')
                                    ->numeric()
                                    ->required()
                                    ->disabled()
                                    ->dehydrated()
                                    ->columnSpan(3),

                                TextInput::make('total_amount')
                                    ->label('Общая сумма')
                                    ->numeric()
                                    ->required()
                                    ->dehydrated()
                                    ->columnSpan(3),
                            ])->columns(12),

                        Placeholder::make('grand_total_placeholder')
                            ->label('Общая стоимость')
                            ->content(function (Get $get, Set $set) {
                                $total = 0;
                                if (!$repeaters = $get('items')) {
                                    return $total;
                                }

                                foreach ($repeaters as $key => $repeater) {
                                    $total += $get("items.{$key}.total_amount");
                                }

                                $set('grand_total', $total);
                                return Number::currency($total, 'RUB');
                            }),

                        Hidden::make('grand_total')
                            ->default(0)
                    ])
                ])->columnSpanFull()
            ]);
    }


    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('user.name')
                    ->label('Клиент')
                    ->sortable()
                    ->searchable(),

                TextColumn::make('grand_total')
                    ->label('Общая стоимость')
                    ->numeric()
                    ->sortable()
                    ->money('RUB'),

                TextColumn::make('payment_method')
                    ->label('Способ оплаты')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('payment_status')
                    ->label('Статус оплаты')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('currency')
                    ->label('Валюта')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('shipping_method')
                    ->label('Способ доставки')
                    ->searchable()
                    ->sortable(),

                SelectColumn::make('status')
                    ->label('Статус заказа')
                    ->options([
                        'new' => 'Новый',
                        'processing' => 'В обработке',
                        'shipped' => 'Отправлен',
                        'delivered' => 'Доставлен',
                        'cancelled' => 'Отменён',
                    ])
                    ->searchable()
                    ->sortable(),

                TextColumn::make('created_at')
                    ->label('Создан')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),

                TextColumn::make('updated_at')
                    ->label('Обновлён')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\ActionGroup::make([
                    Tables\Actions\ViewAction::make()
                        ->label('Просмотр'),
                    Tables\Actions\EditAction::make()
                        ->label('Редактировать'),
                    Tables\Actions\DeleteAction::make()
                        ->label('Удалить'),
                ])
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make()
                        ->label('Удалить выбранное'),
                ]),
            ]);
    }


    public static function getRelations(): array
    {
        return [
            AddressRelationManager::class
        ];
    }

    public static function getNavigationBadge(): ?string
    {
        return static::getModel()::count();
    }

    public static function getNavigationBadgeColor(): string|array|null
    {
        return static::getModel()::count() > 10 ? 'danger' : 'success';
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListOrders::route('/'),
            'create' => Pages\CreateOrder::route('/create'),
            'view' => Pages\ViewOrder::route('/{record}'),
            'edit' => Pages\EditOrder::route('/{record}/edit'),
        ];
    }
}
