<?php

namespace App\Http\Controllers;

use App\Models\Product;
use Illuminate\View\View;

class StoreController extends Controller
{
    public function index(): View
    {
        $products = Product::query()->where('is_active', true)->orderBy('id')->get()->map(fn ($product) => [
            'sku' => $product->sku,
            'name' => $product->name,
            'type' => $product->type->value,
            'price' => $product->price,
            'currency' => $product->currency,
            'label' => match ($product->type->value) {
                'topup' => 'ПОПОЛНЕНИЕ', 'subscription' => 'ПОДПИСКА', 'giftcard' => 'GIFT CARD', default => 'КЛЮЧ',
            },
            'short' => match ($product->type->value) { 'topup' => 'STEAM', 'subscription' => 'SUB', 'giftcard' => 'GIFT', default => 'KEY' },
        ])->all();

        return view('store.index', [
            'products' => $products,
            'topups' => array_values(array_filter($products, fn ($p) => $p['type'] === 'topup')),
            'categories' => [
                ['title' => 'Steam', 'count' => 12], ['title' => 'Игровые ключи', 'count' => 24],
                ['title' => 'Подписки', 'count' => 18], ['title' => 'Gift Cards', 'count' => 16],
            ],
            'services' => [
                ['name' => 'Steam', 'icon' => 'S'], ['name' => 'Telegram', 'icon' => 'T'], ['name' => 'Roblox', 'icon' => 'R'],
                ['name' => 'Discord', 'icon' => 'D'], ['name' => 'PlayStation', 'icon' => 'P'], ['name' => 'Xbox', 'icon' => 'X'],
                ['name' => 'Spotify', 'icon' => '♪'], ['name' => 'YouTube', 'icon' => '▶'],
            ],
            'banners' => [
                ['eyebrow' => 'GGSTORE • DIGITAL', 'title' => 'Играй больше — плати меньше', 'text' => 'Ключи, подписки и пополнения с автоматической выдачей.', 'badge' => 'GAME ON'],
                ['eyebrow' => 'STEAM TOP UP', 'title' => 'Пополняй Steam за пару кликов', 'text' => 'Выбирай номинал, оформляй заказ и получай товар автоматически.', 'badge' => 'STEAM'],
                ['eyebrow' => 'DIGITAL GOODS', 'title' => 'Твои игры уже здесь', 'text' => 'Популярные цифровые товары в одном каталоге.', 'badge' => 'GG'],
            ],
        ]);
    }
}
