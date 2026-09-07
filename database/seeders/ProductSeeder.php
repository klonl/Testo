<?php

namespace Database\Seeders;

use App\Enums\ProductType;
use App\Models\Product;
use Illuminate\Database\Seeder;

class ProductSeeder extends Seeder
{
    public function run(): void
    {
        $products = [
            ['sku' => 'STEAM-500', 'name' => 'Пополнение Steam 500 ₽', 'type' => ProductType::TOPUP, 'price' => 500, 'image' => null],
            ['sku' => 'STEAM-1000', 'name' => 'Пополнение Steam 1000 ₽', 'type' => ProductType::TOPUP, 'price' => 1000, 'image' => null],
            ['sku' => 'STEAM-2500', 'name' => 'Пополнение Steam 2500 ₽', 'type' => ProductType::TOPUP, 'price' => 2500, 'image' => null],
            ['sku' => 'MINECRAFT-JAVA', 'name' => 'Minecraft Java & Bedrock Edition', 'type' => ProductType::KEY, 'price' => 1990, 'image' => null],
            ['sku' => 'EA-FC-26', 'name' => 'EA SPORTS FC 26', 'type' => ProductType::KEY, 'price' => 2990, 'image' => null],
            ['sku' => 'CYBERPUNK-2077', 'name' => 'Cyberpunk 2077', 'type' => ProductType::KEY, 'price' => 1490, 'image' => null],
            ['sku' => 'PS-PLUS-1M', 'name' => 'PlayStation Plus Essential — 1 месяц', 'type' => ProductType::SUBSCRIPTION, 'price' => 999, 'image' => null],
            ['sku' => 'XBOX-GAMEPASS-1M', 'name' => 'Xbox Game Pass — 1 месяц', 'type' => ProductType::SUBSCRIPTION, 'price' => 1290, 'image' => null],
            ['sku' => 'TELEGRAM-PREMIUM-3M', 'name' => 'Telegram Premium — 3 месяца', 'type' => ProductType::SUBSCRIPTION, 'price' => 890, 'image' => null],
            ['sku' => 'ROBLOX-1000', 'name' => 'Roblox 1000 Robux', 'type' => ProductType::TOPUP, 'price' => 1090, 'image' => null],
            ['sku' => 'APPLE-1000', 'name' => 'App Store Gift Card 1000 ₽', 'type' => ProductType::GIFTCARD, 'price' => 1190, 'image' => null],
            ['sku' => 'PSN-1000', 'name' => 'PlayStation Store 1000 ₽', 'type' => ProductType::GIFTCARD, 'price' => 1190, 'image' => null],
        ];

        foreach ($products as $product) {
            Product::updateOrCreate(
                ['sku' => $product['sku']],
                [
                    'name' => $product['name'],
                    'type' => $product['type'],
                    'price' => $product['price'],
                    'currency' => 'RUB',
                    'image' => $product['image'],
                    'is_active' => true,
                ],
            );
        }
    }
}
