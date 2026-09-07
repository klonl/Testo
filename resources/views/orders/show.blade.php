@extends('layouts.app')
@section('title', 'Заказ '.$order->number)
@section('content')
<div class="order-page">
    <a class="order-back" href="{{ route('store.index') }}">← Вернуться в магазин</a>
    <div class="order-card">
        <span class="order-label">ЗАКАЗ</span>
        <h1>{{ $order->number }}</h1>
        <div class="order-status status-{{ $order->status->value }}">{{ $order->status->value }}</div>
        <p>Сумма: <b>{{ number_format($order->amount, 0, ',', ' ') }} {{ $order->currency === 'RUB' ? '₽' : $order->currency }}</b></p>
        @if($order->delivery?->code)
            <div class="order-code"><span>Выданный товар</span><strong>{{ $order->delivery->code }}</strong></div>
        @elseif(in_array($order->status->value, ['out_of_stock', 'delivery_failed']))
            <p class="order-error">Оплата подтверждена, но выдача требует повторной обработки.</p>
        @else
            <p class="order-muted">Заказ обрабатывается. Обновите страницу через несколько секунд.</p>
        @endif
    </div>
</div>
@endsection
