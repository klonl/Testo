<?php

namespace App\Services;

use App\Services\JsonStorage;
use RuntimeException;

class OrderService
{
    protected JsonStorage $orderStorage;
    protected JsonStorage $keyStorage;

    public function __construct()
    {
        $this->orderStorage = new JsonStorage('orders.json');
        $this->keyStorage   = new JsonStorage('keys.json');
    }

    /**
     * Создание нового заказа.
     */
    public function createOrder(string $productSku): string
    {
        $orders = $this->orderStorage->read();
        $orderId = 'ord_' . uniqid();
        $orders[$orderId] = [
            'id'          => $orderId,
            'product_sku' => $productSku,
            'status'      => 'created',
            'key'         => null,
            'event_ids'   => [], // для идемпотентности вебхуков
            'created_at'  => now()->toISOString(),
            'updated_at'  => now()->toISOString(),
        ];
        $this->orderStorage->write($orders);
        return $orderId;
    }

    /**
     * Получение заказа по ID.
     */
    public function getOrder(string $orderId): ?array
    {
        $orders = $this->orderStorage->read();
        return $orders[$orderId] ?? null;
    }

    /**
     * Обновление статуса заказа (атомарно).
     */
    public function updateOrder(string $orderId, array $data): void
    {
        $this->orderStorage->update(function ($orders) use ($orderId, $data) {
            if (isset($orders[$orderId])) {
                $orders[$orderId] = array_merge($orders[$orderId], $data);
                $orders[$orderId]['updated_at'] = now()->toISOString();
            }
            return $orders;
        });
    }

    /**
     * Идемпотентная обработка вебхука.
     * Возвращает true, если событие обработано впервые, иначе false.
     */
    public function handleWebhook(string $orderId, string $eventId, string $status): bool
    {
        $order = $this->getOrder($orderId);
        if (!$order) {
            return false;
        }

        // Проверяем, не обрабатывали ли уже это событие
        if (in_array($eventId, $order['event_ids'] ?? [])) {
            return false; // уже обработано
        }

        // Если заказ уже финальный (delivered, payment_failed), игнорируем
        if (in_array($order['status'], ['delivered', 'payment_failed'])) {
            return false;
        }

        // Если статус paid, запускаем выдачу
        if ($status === 'paid') {
            // Добавляем event_id в список обработанных
            $this->updateOrder($orderId, [
                'event_ids' => array_merge($order['event_ids'] ?? [], [$eventId]),
                'status' => 'paid',
            ]);
            $this->processDelivery($orderId);
            return true;
        }

        // Если failed — помечаем как payment_failed
        if ($status === 'failed') {
            $this->updateOrder($orderId, [
                'event_ids' => array_merge($order['event_ids'] ?? [], [$eventId]),
                'status' => 'payment_failed',
            ]);
            return true;
        }

        return false;
    }

    /**
     * Процесс выдачи ключа (вызывается после оплаты).
     * Использует атомарность для избежания гонок.
     */
    protected function processDelivery(string $orderId): void
    {
        // Сначала проверяем, не выдан ли уже ключ
        $order = $this->getOrder($orderId);
        if (!$order || $order['status'] === 'delivered') {
            return;
        }

        // Если статус не paid или delivering, пропускаем (защита от повторных вызовов)
        if (!in_array($order['status'], ['paid', 'delivering', 'out_of_stock', 'delivery_failed'])) {
            return;
        }

        // Пытаемся атомарно выдать ключ
        $this->orderStorage->update(function ($orders) use ($orderId) {
            if (!isset($orders[$orderId])) {
                return $orders;
            }
            $order = &$orders[$orderId];

            // Если уже delivered — ничего не делаем
            if ($order['status'] === 'delivered') {
                return $orders;
            }

            // Проверяем наличие ключей
            $keys = $this->keyStorage->read();
            if (empty($keys)) {
                $order['status'] = 'out_of_stock';
                return $orders;
            }

            // Берём первый ключ и удаляем его из пула
            $key = array_shift($keys);
            $this->keyStorage->write($keys);

            // Присваиваем ключ заказу
            $order['key'] = $key;
            $order['status'] = 'delivered';

            return $orders;
        });
    }

    /**
     * Повторная выдача для восстановимых статусов (out_of_stock, delivery_failed).
     * Идемпотентна.
     */
    public function retryDelivery(string $orderId): bool
    {
        $order = $this->getOrder($orderId);
        if (!$order) {
            return false;
        }

        // Разрешено только для out_of_stock или delivery_failed
        if (!in_array($order['status'], ['out_of_stock', 'delivery_failed'])) {
            return false;
        }

        // Переводим в состояние "delivering" и запускаем выдачу
        $this->updateOrder($orderId, ['status' => 'delivering']);
        $this->processDelivery($orderId);
        return true;
    }

    /**
     * Получение списка заказов в статусе "оплачен, но не выдан" (для админки).
     */
    public function getRecoverableOrders(): array
    {
        $orders = $this->orderStorage->read();
        return array_filter($orders, function ($order) {
            return in_array($order['status'], ['out_of_stock', 'delivery_failed']);
        });
    }
}
