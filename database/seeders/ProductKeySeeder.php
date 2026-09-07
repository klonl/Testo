<?php

namespace Database\Seeders;

use App\Enums\ProductKeyStatus;
use App\Models\Product;
use App\Models\ProductKey;
use App\Models\Supplier;
use Illuminate\Database\Seeder;

class ProductKeySeeder extends Seeder
{
    public function run(): void
    {
        $supplierA = Supplier::where('name', 'Supplier A')->firstOrFail();
        $supplierB = Supplier::where('name', 'Supplier B')->firstOrFail();
        $supplierTest = Supplier::where('name', 'Supplier Test')->firstOrFail();

        $keys = [
            'MINECRAFT-JAVA' => [
                ['code' => 'MINECRAFT-DEMO-0001', 'supplier_id' => $supplierA->id],
                ['code' => 'MINECRAFT-DEMO-0002', 'supplier_id' => $supplierA->id],
                ['code' => 'MINECRAFT-DEMO-0003', 'supplier_id' => $supplierB->id],
            ],
            'EA-FC-26' => [
                ['code' => 'EAFC26-DEMO-0001', 'supplier_id' => $supplierA->id],
                ['code' => 'EAFC26-DEMO-0002', 'supplier_id' => $supplierB->id],
            ],
            'CYBERPUNK-2077' => [
                ['code' => 'CYBERPUNK-DEMO-0001', 'supplier_id' => $supplierB->id],
                ['code' => 'CYBERPUNK-DEMO-0002', 'supplier_id' => $supplierTest->id],
            ],
            'PS-PLUS-1M' => [
                ['code' => 'PSPLUS-DEMO-0001', 'supplier_id' => $supplierA->id],
                ['code' => 'PSPLUS-DEMO-0002', 'supplier_id' => $supplierB->id],
            ],
            'XBOX-GAMEPASS-1M' => [
                ['code' => 'GAMEPASS-DEMO-0001', 'supplier_id' => $supplierA->id],
                ['code' => 'GAMEPASS-DEMO-0002', 'supplier_id' => $supplierB->id],
            ],
            'TELEGRAM-PREMIUM-3M' => [
                ['code' => 'TG-PREMIUM-DEMO-0001', 'supplier_id' => $supplierTest->id],
            ],
            'APPLE-1000' => [
                ['code' => 'APPLE-DEMO-0001', 'supplier_id' => $supplierA->id],
            ],
            'PSN-1000' => [
                ['code' => 'PSN-DEMO-0001', 'supplier_id' => $supplierB->id],
            ],
        ];

        foreach ($keys as $sku => $productKeys) {
            $product = Product::where('sku', $sku)->firstOrFail();

            foreach ($productKeys as $key) {
                ProductKey::updateOrCreate(
                    ['code' => $key['code']],
                    [
                        'product_id' => $product->id,
                        'supplier_id' => $key['supplier_id'],
                        'status' => ProductKeyStatus::AVAILABLE,
                        'request_id' => null,
                        'used_at' => null,
                    ],
                );
            }
        }
    }
}
