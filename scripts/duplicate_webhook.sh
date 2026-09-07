#!/usr/bin/env bash
set -euo pipefail
BASE_URL="${BASE_URL:-http://127.0.0.1:80}"
ORDER_ID="${ORDER_ID:?ORDER_ID is required}"
AMOUNT="${AMOUNT:-1990}"
EVENT_ID="${EVENT_ID:-duplicate_test_001}"

payload="{\"event_id\":\"$EVENT_ID\",\"order_id\":\"$ORDER_ID\",\"status\":\"paid\",\"amount\":$AMOUNT,\"currency\":\"RUB\",\"created_at\":\"$(date -u +%Y-%m-%dT%H:%M:%SZ)\"}"

echo 'First webhook:'
curl -sS -X POST "$BASE_URL/webhooks/payment" -H 'Content-Type: application/json' -H 'Accept: application/json' --data "$payload"
echo
echo 'Same event_id again:'
curl -sS -X POST "$BASE_URL/webhooks/payment" -H 'Content-Type: application/json' -H 'Accept: application/json' --data "$payload"
echo
