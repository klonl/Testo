<?php

namespace Database\Seeders;

use App\Models\DeliveryAttempt;
use App\Models\Delivery;
use App\Models\Supplier;
use Illuminate\Database\Seeder;

class DeliveryAttemptSeeder extends Seeder
{
    public function run(): void
    {
        $supplierA = Supplier::where('name', 'Supplier A')->firstOrFail();
        $supplierB = Supplier::where('name', 'Supplier B')->firstOrFail();
        $supplierTest = Supplier::where('name', 'Supplier Test')->firstOrFail();

        $attempts = [
            [
                'delivery' => 'TEST-000001',
                'supplier_id' => $supplierA->id,
                'request_id' => 'delivery_seed_TEST_000001_attempt_1',
                'status' => 'delivered',
                'response_code' => 200,
                'response_payload' => ['code' => 'MINECRAFT-DEMO-0001'],
                'error' => null,
            ],
            [
                'delivery' => 'TEST-000003',
                'supplier_id' => $supplierB->id,
                'request_id' => 'delivery_seed_TEST_000003_attempt_1',
                'status' => 'out_of_stock',
                'response_code' => 409,
                'response_payload' => ['reason' => 'out_of_stock'],
                'error' => 'No available keys.',
            ],
            [
                'delivery' => 'TEST-000003',
                'supplier_id' => $supplierA->id,
                'request_id' => 'delivery_seed_TEST_000003_attempt_2',
                'status' => 'out_of_stock',
                'response_code' => 409,
                'response_payload' => ['reason' => 'out_of_stock'],
                'error' => 'No available keys.',
            ],
            [
                'delivery' => 'TEST-000004',
                'supplier_id' => $supplierTest->id,
                'request_id' => 'delivery_seed_TEST_000004_attempt_1',
                'status' => 'timeout',
                'response_code' => null,
                'response_payload' => ['reason' => 'timeout'],
                'error' => 'Supplier did not respond within timeout.',
            ],
            [
                'delivery' => 'TEST-000004',
                'supplier_id' => $supplierA->id,
                'request_id' => 'delivery_seed_TEST_000004_attempt_2',
                'status' => 'failed',
                'response_code' => 500,
                'response_payload' => ['reason' => 'internal_error'],
                'error' => 'Supplier returned HTTP 500.',
            ],
        ];

        foreach ($attempts as $data) {
            $delivery = Delivery::whereHas('order', fn ($query) => $query->where('number', $data['delivery']))->firstOrFail();
            $startedAt = now()->subHours(1);

            DeliveryAttempt::updateOrCreate(
                ['request_id' => $data['request_id']],
                [
                    'delivery_id' => $delivery->id,
                    'supplier_id' => $data['supplier_id'],
                    'status' => $data['status'],
                    'response_code' => $data['response_code'],
                    'response_payload' => $data['response_payload'],
                    'error' => $data['error'],
                    'started_at' => $startedAt,
                    'finished_at' => $startedAt->copy()->addSeconds(2),
                ],
            );
        }
    }
}
