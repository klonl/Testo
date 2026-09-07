<?php

namespace App\Http\Controllers;

use App\Services\DeliveryService;
use App\Services\PaymentService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use InvalidArgumentException;

class PaymentWebhookController extends Controller
{
    public function handle(Request $request, PaymentService $paymentService, DeliveryService $deliveryService): JsonResponse
    {
        $data = $request->validate([
            'event_id' => ['required', 'string', 'max:150'],
            'order_id' => ['required', 'string', 'max:150'],
            'status' => ['required', 'in:paid,failed'],
            'amount' => ['required', 'integer', 'min:0'],
            'currency' => ['required', 'string', 'size:3'],
            'created_at' => ['nullable', 'date'],
        ]);

        try {
            $result = $paymentService->processWebhook($data, $request->all(), $deliveryService);
        } catch (InvalidArgumentException $e) {
            return response()->json(['message' => $e->getMessage()], 422);
        }

        return response()->json([
            'status' => 'accepted',
            'duplicate' => $result['duplicate'],
            'order' => $result['order'],
        ], 200);
    }
}
