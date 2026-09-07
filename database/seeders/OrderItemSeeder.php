<?php

namespace Database\Seeders;

use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use Illuminate\Database\Seeder;

class OrderItemSeeder extends Seeder
{
    public function run(): void
    {
        $items = [
            ['order' => 'TEST-000001', 'sku' => 'MINECRAFT-JAVA', 'quantity' => 1],
            ['order' => 'TEST-000002', 'sku' => 'STEAM-500', 'quantity' => 1],
            ['order' => 'TEST-000003', 'sku' => 'EA-FC-26', 'quantity' => 1],
            ['order' => 'TEST-000004', 'sku' => 'CYBERPUNK-2077', 'quantity' => 1],
            ['order' => 'TEST-000005', 'sku' => 'PS-PLUS-1M', 'quantity' => 1],
            ['order' => 'TEST-000006', 'sku' => 'STEAM-1000', 'quantity' => 1],
        ];

        foreach ($items as $data) {
            $order = Order::where('number', $data['order'])->firstOrFail();
            $product = Product::where('sku', $data['sku'])->firstOrFail();

            OrderItem::updateOrCreate(
                [
                    'order_id' => $order->id,
                    'product_id' => $product->id,
                ],
                [
                    'sku' => $product->sku,
                    'product_name' => $product->name,
                    'quantity' => $data['quantity'],
                    'unit_price' => $product->price,
                    'total_price' => $product->price * $data['quantity'],
                    'currency' => $product->currency,
                ],
            );
        }
    }
}
