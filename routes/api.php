<?php

use Illuminate\Support\Facades\Route;

Route::post('/webhooks/payment', ['App\Http\Controllers\PaymentWebhookController', 'handle'])->name('webhooks.payment');
Route::get('/orders/{order}/status', ['App\Http\Controllers\OrderController', 'statusApi'])->name('orders.status');
Route::post('/orders', ['App\Http\Controllers\OrderController', 'store'])->name('orders.store');
