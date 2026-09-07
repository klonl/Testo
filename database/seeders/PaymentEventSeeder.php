<?php

namespace Database\Seeders;

use App\Enums\PaymentStatus;
use App\Models\Order;
use App\Models\PaymentEvent;
use Illuminate\Database\Seeder;

class PaymentEventSeeder extends Seeder
{
    public function run(): void
    {
        $events = [
            [
                'event_id' => 'evt_test_000001',
                'order' => 'TEST-000001',
                'external_order_id' => 'TEST-000001',
                'status' => PaymentStatus::PAID,
                'amount' => 1990,
                'currency' => 'RUB',
                'processed_at' => now()->subDays(4),
            ],
            [
                'event_id' => 'evt_test_000002',
                'order' => 'TEST-000002',
                'external_order_id' => 'TEST-000002',
                'status' => PaymentStatus::PAID,
                'amount' => 500,
                'currency' => 'RUB',
                'processed_at' => now()->subHours(5),
            ],
            [
                'event_id' => 'evt_test_000003',
                'order' => 'TEST-000003',
                'external_order_id' => 'TEST-000003',
                'status' => PaymentStatus::PAID,
                'amount' => 2990,
                'currency' => 'RUB',
                'processed_at' => now()->subHours(3),
            ],
            [
                'event_id' => 'evt_test_000004',
                'order' => 'TEST-000004',
                'external_order_id' => 'TEST-000004',
                'status' => PaymentStatus::PAID,
                'amount' => 1490,
                'currency' => 'RUB',
                'processed_at' => now()->subHours(2),
            ],
            [
                'event_id' => 'evt_test_000005',
                'order' => 'TEST-000005',
                'external_order_id' => 'TEST-000005',
                'status' => PaymentStatus::FAILED,
                'amount' => 999,
                'currency' => 'RUB',
                'processed_at' => now()->subHours(1),
            ],
            [
                // Demonstrates a webhook received before the local order exists.
                'event_id' => 'evt_test_unknown_001',
                'order' => null,
                'external_order_id' => 'EXTERNAL-UNKNOWN-001',
                'status' => PaymentStatus::PAID,
                'amount' => 777,
                'currency' => 'RUB',
                'processed_at' => null,
            ],
        ];

        foreach ($events as $event) {
            $order = $event['order']
                ? Order::where('number', $event['order'])->firstOrFail()
                : null;

            PaymentEvent::updateOrCreate(
                ['event_id' => $event['event_id']],
                [
                    'order_id' => $order?->id,
                    'external_order_id' => $event['external_order_id'],
                    'status' => $event['status'],
                    'amount' => $event['amount'],
                    'currency' => $event['currency'],
                    'payload' => [
                        'event_id' => $event['event_id'],
                        'external_order_id' => $event['external_order_id'],
                        'status' => $event['status']->value,
                        'amount' => $event['amount'],
                        'currency' => $event['currency'],
                        'source' => 'database_seeder',
                    ],
                    'processed_at' => $event['processed_at'],
                ],
            );
        }
    }
}
