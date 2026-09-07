#!/usr/bin/env bash
set -euo pipefail
BASE_URL="${BASE_URL:-http://127.0.0.1:8000}"
ORDER_ID="${ORDER_ID:-ord_out_of_order_demo}"
SKU="${SKU:-MINECRAFT-JAVA}"
AMOUNT="${AMOUNT:-1990}"
IDEMPOTENCY_KEY="${IDEMPOTENCY_KEY:-out-of-order-demo-key}"
EVENT_ID="${EVENT_ID:-out_of_order_demo_event}"

payload="{\"event_id\":\"$EVENT_ID\",\"order_id\":\"$ORDER_ID\",\"status\":\"paid\",\"amount\":$AMOUNT,\"currency\":\"RUB\",\"created_at\":\"$(date -u +%Y-%m-%dT%H:%M:%SZ)\"}"

echo '1. Webhook BEFORE order creation:'
curl -sS -X POST "$BASE_URL/webhooks/payment" -H 'Content-Type: application/json' -H 'Accept: application/json' --data "$payload"
echo

echo '2. Create order with the same external order number:'
curl -sS -X POST "$BASE_URL/orders" -H 'Content-Type: application/json' -H 'Accept: application/json' -H "Idempotency-Key: $IDEMPOTENCY_KEY" --data "{\"sku\":\"$SKU\",\"order_number\":\"$ORDER_ID\"}"
echo

echo '3. Status:'
curl -sS -H 'Accept: application/json' "$BASE_URL/orders/$ORDER_ID/status"
echo
