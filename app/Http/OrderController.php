<?php

namespace App\Http\Controllers;

use App\Enums\OrderStatus;
use App\Enums\PaymentStatus;
use App\Models\Order;
use App\Models\PaymentEvent;
use App\Services\PaymentService;
use App\Models\Product;
use App\Services\DeliveryService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\View\View;
use RuntimeException;

class OrderController extends Controller
{
    public function store(Request $request, DeliveryService $deliveryService): JsonResponse
    {
        $data = $request->validate([
            'sku' => ['required', 'string', 'exists:products,sku'],
            'order_number' => ['nullable', 'string', 'max:64'],
        ]);

        $idempotencyKey = $request->header('Idempotency-Key');
        if (!$idempotencyKey) {
            $idempotencyKey = 'web_' . Str::uuid()->toString();
        }
        if (strlen($idempotencyKey) > 100) {
            return response()->json(['message' => 'Idempotency-Key слишком длинный.'], 422);
        }

        $deliveryOrder = null;

        $order = DB::transaction(function () use ($data, $idempotencyKey, &$deliveryOrder) {
            // PostgreSQL advisory transaction lock serializes concurrent requests
            // using the same idempotency key before an order row exists.
            DB::select('select pg_advisory_xact_lock(hashtext(?))', [$idempotencyKey]);

            $existing = Order::query()->where('request_id', $idempotencyKey)->first();
            if ($existing) {
                return $existing;
            }

            $product = Product::query()
                ->where('sku', $data['sku'])
                ->where('is_active', true)
                ->firstOrFail();

            $order = Order::create([
                'number' => $data['order_number'] ?? ('ord_' . Str::lower(Str::random(12))),
                'request_id' => $idempotencyKey,
                'status' => OrderStatus::CREATED,
                'amount' => $product->price,
                'currency' => $product->currency,
            ]);

            $order->items()->create([
                'product_id' => $product->id,
                'sku' => $product->sku,
                'product_name' => $product->name,
                'quantity' => 1,
                'unit_price' => $product->price,
                'total_price' => $product->price,
                'currency' => $product->currency,
            ]);

            // Reconcile a webhook that arrived before the order was created.
            $event = PaymentEvent::query()
                ->where('external_order_id', $order->number)
                ->whereNull('order_id')
                ->latest('id')
                ->lockForUpdate()
                ->first();

            if ($event && $event->amount === $order->amount && $event->currency === $order->currency) {
                $event->update(['order_id' => $order->id, 'processed_at' => now()]);

                if ($event->status === PaymentStatus::PAID) {
                    $order->update(['status' => OrderStatus::PAID, 'paid_at' => now()]);
                    $deliveryOrder = $order->fresh();
                } elseif ($event->status === PaymentStatus::FAILED) {
                    $order->update(['status' => OrderStatus::PAYMENT_FAILED]);
                }
            }

            return $order->fresh();
        });

        // If an earlier paid webhook was reconciled during creation, finish
        // delivery now. The delivery service itself is idempotent and locked.
        if ($deliveryOrder) {
            try {
                $deliveryService->deliver($deliveryOrder);
            } catch (RuntimeException $e) {
                return response()->json([
                    'message' => $e->getMessage(),
                    'order' => $order->fresh()->load('items', 'delivery.productKey'),
                ], 422);
            }
        }

        return response()->json([
            'order' => $order->fresh()->load('items', 'delivery.productKey'),
            'idempotent' => (bool) $order->request_id,
        ], 201);
    }

    public function payTest(Order $order, PaymentService $paymentService, DeliveryService $deliveryService): JsonResponse
    {
        try {
            $result = $paymentService->createTestPayment($order, $deliveryService);
        } catch (\InvalidArgumentException $e) {
            return response()->json(['message' => $e->getMessage()], 422);
        } catch (RuntimeException $e) {
            return response()->json([
                'message' => $e->getMessage(),
                'order' => $order->fresh()->load('items', 'delivery.productKey'),
            ], 422);
        }

        return response()->json(['order' => $result['order']]);
    }

    public function show(Order $order): View
    {
        return view('orders.show', ['order' => $order->load('items', 'delivery.productKey')]);
    }

    public function status(Order $order): JsonResponse
    {
        return response()->json($order->load('items', 'delivery.productKey'));
    }
}
