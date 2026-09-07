#!/usr/bin/env bash
set -euo pipefail

BASE_URL="${BASE_URL:-http://127.0.0.1}"
ORDER_ID="${ORDER_ID:-}"
AMOUNT="${AMOUNT:-1990}"
CURRENCY="${CURRENCY:-RUB}"
COUNT="${COUNT:-50}"

if [[ -z "$ORDER_ID" ]]; then
  echo "Usage: ORDER_ID=ord_xxx AMOUNT=1990 ./scripts/race_webhooks.sh"
  exit 1
fi

TMP_DIR="$(mktemp -d)"
trap 'rm -rf "$TMP_DIR"' EXIT

for i in $(seq 1 "$COUNT"); do
  (
    curl -sS -o "$TMP_DIR/$i.json" -w "%{http_code}" \
      -X POST "$BASE_URL/api/webhooks/payment" \
      -H 'Content-Type: application/json' \
      -H 'Accept: application/json' \
      --data "{\"event_id\":\"race_${i}_$(date +%s%N)\",\"order_id\":\"$ORDER_ID\",\"status\":\"paid\",\"amount\":$AMOUNT,\"currency\":\"$CURRENCY\",\"created_at\":\"$(date -u +%Y-%m-%dT%H:%M:%SZ)\"}" \
      > "$TMP_DIR/$i.status"
  ) &
done
wait

echo "HTTP statuses:"
sort "$TMP_DIR"/*.status | uniq -c

echo
echo "Order status:"
curl -sS -H 'Accept: application/json' "$BASE_URL/api/orders/$ORDER_ID/status"
echo

echo
echo "Expected: one delivered delivery and one used product key for this order."
echo "Verify in PostgreSQL with:"
echo "  select id, number, status from orders where number='$ORDER_ID';"
echo "  select id, product_key_id, status, code from deliveries d join orders o on o.id=d.order_id where o.number='$ORDER_ID';"
echo "  select status, count(*) from product_keys where product_id=(select product_id from order_items oi join orders o on o.id=oi.order_id where o.number='$ORDER_ID') group by status;"
