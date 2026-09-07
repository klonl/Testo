<?php

namespace App\Services;

use App\Enums\DeliveryStatus;
use App\Enums\OrderStatus;
use App\Enums\ProductKeyStatus;
use App\Enums\ProductType;
use App\Models\Delivery;
use App\Models\DeliveryAttempt;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\ProductKey;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use RuntimeException;

class DeliveryService
{
    public function deliver(Order $order): Delivery
    {
        return DB::transaction(function () use ($order) {
            $order = Order::query()->whereKey($order->id)->lockForUpdate()->firstOrFail();
            $item = OrderItem::query()->where('order_id', $order->id)->lockForUpdate()->firstOrFail();

            $existing = Delivery::query()
                ->where('order_id', $order->id)
                ->where('order_item_id', $item->id)
                ->where('status', DeliveryStatus::DELIVERED->value)
                ->first();

            if ($existing) {
                return $existing;
            }

            if ($order->status !== OrderStatus::PAID && $order->status !== OrderStatus::DELIVERING) {
                throw new RuntimeException('Заказ не оплачен.');
            }

            $requestId = 'delivery_' . $order->id . '_' . Str::lower(Str::random(16));

            $delivery = Delivery::query()
                ->where('order_id', $order->id)
                ->where('order_item_id', $item->id)
                ->latest('id')
                ->first();

            if (!$delivery) {
                $delivery = Delivery::create([
                    'order_id' => $order->id,
                    'order_item_id' => $item->id,
                    'status' => DeliveryStatus::PROCESSING,
                    'request_id' => $requestId,
                ]);
            } else {
                $delivery->update([
                    'status' => DeliveryStatus::PROCESSING,
                    'request_id' => $requestId,
                    'error' => null,
                ]);
            }

            $order->update(['status' => OrderStatus::DELIVERING]);

            $startedAt = now();
            $attempt = DeliveryAttempt::create([
                'delivery_id' => $delivery->id,
                'supplier_id' => null,
                'request_id' => $requestId,
                'status' => 'processing',
                'started_at' => $startedAt,
            ]);

            if ($item->product?->type === ProductType::KEY) {
                /** @var ProductKey|null $key */
                $key = ProductKey::query()
                    ->where('product_id', $item->product_id)
                    ->where('status', ProductKeyStatus::AVAILABLE->value)
                    ->orderBy('id')
                    ->lockForUpdate()
                    ->first();

                if (!$key) {
                    $error = 'Нет доступных ключей для этого товара.';
                    $delivery->update([
                        'status' => DeliveryStatus::FAILED,
                        'error' => $error,
                    ]);
                    $attempt->update([
                        'status' => 'out_of_stock',
                        'response_code' => 409,
                        'error' => $error,
                        'finished_at' => now(),
                    ]);
                    $order->update(['status' => OrderStatus::OUT_OF_STOCK]);

                    return $delivery->fresh();
                }

                $key->update([
                    'status' => ProductKeyStatus::USED,
                    'request_id' => $requestId,
                    'used_at' => now(),
                ]);

                $delivery->update([
                    'product_key_id' => $key->id,
                    'supplier_id' => $key->supplier_id,
                    'status' => DeliveryStatus::DELIVERED,
                    'code' => $key->code,
                    'delivered_at' => now(),
                ]);

                $attempt->update([
                    'supplier_id' => $key->supplier_id,
                    'status' => 'delivered',
                    'response_code' => 200,
                    'response_payload' => ['type' => 'product_key'],
                    'finished_at' => now(),
                ]);
            } else {
                $code = 'DEMO-' . strtoupper(Str::random(12));

                $delivery->update([
                    'status' => DeliveryStatus::DELIVERED,
                    'code' => $code,
                    'delivered_at' => now(),
                ]);

                $attempt->update([
                    'status' => 'delivered',
                    'response_code' => 200,
                    'response_payload' => ['type' => 'demo_delivery'],
                    'finished_at' => now(),
                ]);
            }

            $order->update([
                'status' => OrderStatus::DELIVERED,
                'delivered_at' => now(),
            ]);

            return $delivery->fresh();
        });
    }
}
