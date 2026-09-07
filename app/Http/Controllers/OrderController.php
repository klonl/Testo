<?php

namespace App\Http\Controllers;

use App\Enums\OrderStatus;
use App\Models\Order;
use App\Models\Product;
use App\Services\DeliveryService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use RuntimeException;

class OrderController extends Controller
{
    public function store(Request $request): JsonResponse
    {
        $data = $request->validate([
            'sku' => ['required', 'string', 'exists:products,sku'],
        ]);

        $product = Product::query()
            ->where('sku', $data['sku'])
            ->where('is_active', true)
            ->firstOrFail();

        $order = Order::create([
            'number' => 'ord_' . Str::lower(Str::random(12)),
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

        return response()->json([
            'order_id' => $order->id,
            'number' => $order->number,
            'status' => $order->status->value,
        ], 201);
    }

    public function payTest(Order $order, DeliveryService $deliveryService): JsonResponse
    {
        if ($order->status === OrderStatus::DELIVERED) {
            return response()->json($order->load('delivery'));
        }

        if ($order->status !== OrderStatus::CREATED) {
            return response()->json([
                'message' => 'Этот заказ нельзя оплатить в текущем статусе.',
                'status' => $order->status->value,
            ], 422);
        }

        $order->update([
            'status' => OrderStatus::PAID,
            'paid_at' => now(),
        ]);

        try {
            $deliveryService->deliver($order);
        } catch (RuntimeException $e) {
            return response()->json([
                'message' => $e->getMessage(),
                'status' => $order->fresh()->status->value,
            ], 422);
        }

        return response()->json(
            $order->fresh()->load('items', 'delivery.productKey')
        );
    }

    public function status(Order $order): JsonResponse
    {
        return response()->json(
            $order->load('items', 'delivery.productKey')
        );
    }

    public function statusApi(string $order): JsonResponse
    {
        $order = Order::query()
            ->where('number', $order)
            ->with('items', 'delivery.productKey')
            ->firstOrFail();

        return response()->json($order);
    }

}
