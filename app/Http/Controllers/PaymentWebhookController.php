<?php

namespace App\Http\Controllers;

use App\Enums\OrderStatus;
use App\Models\Order;
use App\Models\PaymentEvent;
use App\Services\DeliveryService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use RuntimeException;

class PaymentWebhookController extends Controller
{
    public function handle(Request $request, DeliveryService $deliveryService): JsonResponse
    {
        $data = $request->validate([
            'event_id' => ['required', 'string'],
            'order_id' => ['required'],
            'status' => ['required', 'in:paid,failed'],
            'amount' => ['required', 'integer'],
            'currency' => ['required', 'string'],
            'created_at' => ['nullable', 'date'],
        ]);

        $order = null;
        $isNew = false;

        DB::transaction(function () use ($data, $request, &$order, &$isNew) {
            $event = PaymentEvent::query()->where('event_id', $data['event_id'])->first();

            if (!$event) {
                $event = PaymentEvent::create([
                    'event_id' => $data['event_id'],
                    'external_order_id' => $data['order_id'],
                    'status' => $data['status'],
                    'amount' => $data['amount'],
                    'currency' => $data['currency'],
                    'payload' => $request->all(),
                    'processed_at' => now(),
                ]);
                $isNew = true;
            }

            $order = Order::query()
                ->where('number', $data['order_id'])
                ->lockForUpdate()
                ->first();

            if (!$order) {
                return;
            }

            $event->update(['order_id' => $order->id, 'processed_at' => now()]);

            if ($order->status === OrderStatus::CREATED) {
                $order->update([
                    'status' => $data['status'] === 'paid' ? OrderStatus::PAID : OrderStatus::PAYMENT_FAILED,
                    'paid_at' => $data['status'] === 'paid' ? now() : null,
                ]);
            }
        });

        if ($order && $isNew && $data['status'] === 'paid' && $order->status === OrderStatus::PAID) {
            try {
                $deliveryService->deliver($order);
            } catch (RuntimeException $e) {
                return response()->json([
                    'status' => 'accepted',
                    'delivery_error' => $e->getMessage(),
                ]);
            }
        }

        return response()->json([
            'status' => 'accepted',
            'duplicate' => !$isNew,
        ]);
    }
}
