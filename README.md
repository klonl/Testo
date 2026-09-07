# Digital Store

Laravel + Blade + Vite + Vanilla JS.

## Реализовано

* Витрина по макету: каталог, баннер, сервисы, Steam-блок и товары.
* Карусель с автопереключением, стрелками и точками.
* Поиск и фильтрация товаров.
* Переключение валюты.
* Создание и оплата заказов.
* Автоматическая выдача ключей.
* Страница заказа `/orders/{order}`.
* Защита от повторного создания заказа.
* Защита от повторной обработки webhook.
* Защита от повторной выдачи ключа при одновременных запросах.
* Обработка webhook до создания заказа.
* Промокоды не реализованы, этап 4 исключён.

## Запуск

```bash
php artisan migrate:fresh --seed
npm install
npm run build
php artisan serve
```

Либо открыть через Docker

## Тесты

Для проверки одновременных webhook:

```bash
ORDER_ID=ord_dyp9cdjprcfl COUNT=50 ./scripts/race_webhooks.sh
```

Также есть:

```bash
BASE_URL=http://127.0.0.1:80 ORDER_ID=ord_dyp9cdjprcfl AMOUNT=1990 EVENT_ID=duplicate_test_001 bash scripts/duplicate_webhook.sh
```

`race_webhooks.sh` отправляет 50 webhook одновременно для одного заказа.

`duplicate_webhook.sh` дважды отправляет один и тот же webhook.
