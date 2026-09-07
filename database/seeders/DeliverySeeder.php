<?php

namespace Database\Seeders;

use App\Enums\DeliveryStatus;
use App\Enums\ProductKeyStatus;
use App\Models\Delivery;
use App\Models\Order;
use App\Models\ProductKey;
use App\Models\Supplier;
use Illuminate\Database\Seeder;

class DeliverySeeder extends Seeder
{
    public function run(): void
    {
        $supplierA = Supplier::where('name', 'Supplier A')->firstOrFail();
        $supplierB = Supplier::where('name', 'Supplier B')->firstOrFail();
        $supplierTest = Supplier::where('name', 'Supplier Test')->firstOrFail();

        $orders = [
            'TEST-000001' => [
                'status' => DeliveryStatus::DELIVERED,
                'supplier_id' => $supplierA->id,
                'key_code' => 'MINECRAFT-DEMO-0001',
                'error' => null,
            ],
            'TEST-000002' => [
                'status' => DeliveryStatus::PENDING,
                'supplier_id' => $supplierA->id,
                'key_code' => null,
                'error' => null,
            ],
            'TEST-000003' => [
                'status' => DeliveryStatus::FAILED,
                'supplier_id' => $supplierB->id,
                'key_code' => null,
                'error' => 'Товар временно отсутствует у поставщика.',
            ],
            'TEST-000004' => [
                'status' => DeliveryStatus::FAILED,
                'supplier_id' => $supplierTest->id,
                'key_code' => null,
                'error' => 'Supplier timeout after 5 seconds.',
            ],
        ];

        foreach ($orders as $number => $data) {
            $order = Order::where('number', $number)->firstOrFail();
            $item = $order->items()->firstOrFail();

            $key = $data['key_code']
                ? ProductKey::where('code', $data['key_code'])->firstOrFail()
                : null;

            if ($key) {
                $key->update([
                    'status' => ProductKeyStatus::USED,
                    'used_at' => now()->subDays(4),
                ]);
            }

            Delivery::updateOrCreate(
                ['order_item_id' => $item->id],
                [
                    'order_id' => $order->id,
                    'product_key_id' => $key?->id,
                    'supplier_id' => $data['supplier_id'],
                    'status' => $data['status'],
                    'code' => $key?->code,
                    'request_id' => 'delivery_seed_' . str_replace('-', '_', $number),
                    'delivered_at' => $data['status'] === DeliveryStatus::DELIVERED ? now()->subDays(4)->addSeconds(12) : null,
                    'error' => $data['error'],
                ],
            );
        }
    }
}
