<?php

namespace Database\Seeders;

use App\Enums\OrderStatus;
use App\Models\Order;
use Illuminate\Database\Seeder;

class OrderSeeder extends Seeder
{
    public function run(): void
    {
        $orders = [
            ['number' => 'TEST-000001', 'status' => OrderStatus::DELIVERED, 'amount' => 1990, 'paid_at' => now()->subDays(4), 'delivered_at' => now()->subDays(4)->addSeconds(12)],
            ['number' => 'TEST-000002', 'status' => OrderStatus::PAID, 'amount' => 500, 'paid_at' => now()->subHours(5), 'delivered_at' => null],
            ['number' => 'TEST-000003', 'status' => OrderStatus::OUT_OF_STOCK, 'amount' => 2990, 'paid_at' => now()->subHours(3), 'delivered_at' => null],
            ['number' => 'TEST-000004', 'status' => OrderStatus::DELIVERY_FAILED, 'amount' => 1490, 'paid_at' => now()->subHours(2), 'delivered_at' => null],
            ['number' => 'TEST-000005', 'status' => OrderStatus::PAYMENT_FAILED, 'amount' => 999, 'paid_at' => null, 'delivered_at' => null],
            ['number' => 'TEST-000006', 'status' => OrderStatus::CREATED, 'amount' => 1000, 'paid_at' => null, 'delivered_at' => null],
        ];

        foreach ($orders as $order) {
            Order::updateOrCreate(
                ['number' => $order['number']],
                [
                    'status' => $order['status'],
                    'amount' => $order['amount'],
                    'currency' => 'RUB',
                    'paid_at' => $order['paid_at'],
                    'delivered_at' => $order['delivered_at'],
                ],
            );
        }
    }
}
