<?php

namespace App\Filament\Resources\OrderResource\Widgets;

use App\Models\Order;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Illuminate\Support\Number;

class OrderStats extends BaseWidget
{
    protected function getStats(): array
    {
        return [
            Stat::make('Новые заказы', Order::query()->where('status', 'new')->count()),
            Stat::make('Заказы в обработке', Order::query()->where('status', 'processing')->count()),
            Stat::make('Заказы отправлены', Order::query()->where('status', 'shipped')->count()),
            Stat::make('Общая сумма всех заказов', Number::currency(Order::query()->avg('grand_total') ?? 0, 'RUB'))
        ];
    }
}
