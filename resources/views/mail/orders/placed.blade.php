<x-mail::message>
    # Заказ успешно оформлен!

    Благодарим вас за заказ. Номер вашего заказа: {{ $order->id }}.

    <x-mail::button :url="$url">
        Просмотреть заказ
    </x-mail::button>

    Спасибо,<br>
    {{ config('app.name') }}
</x-mail::message>
