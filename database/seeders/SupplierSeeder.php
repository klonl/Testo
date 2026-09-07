<?php

namespace Database\Seeders;

use App\Models\Supplier;
use Illuminate\Database\Seeder;

class SupplierSeeder extends Seeder
{
    public function run(): void
    {
        $suppliers = [
            [
                'name' => 'Supplier A',
                'is_active' => true,
                'failure_rate' => 5,
                'timeout_rate' => 3,
            ],
            [
                'name' => 'Supplier B',
                'is_active' => true,
                'failure_rate' => 8,
                'timeout_rate' => 5,
            ],
            [
                'name' => 'Supplier Test',
                'is_active' => true,
                'failure_rate' => 0,
                'timeout_rate' => 0,
            ],
        ];

        foreach ($suppliers as $supplier) {
            Supplier::updateOrCreate(
                ['name' => $supplier['name']],
                $supplier,
            );
        }
    }
}
