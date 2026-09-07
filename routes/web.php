<?php

use Illuminate\Support\Facades\Route;

Route::get('/', ['App\Http\Controllers\StoreController', 'index'])->name('store.index');
Route::get('/orders', fn () => redirect()->route('store.index'))->name('orders.index');

Route::post('/orders', ['App\Http\Controllers\OrderController', 'store'])->name('orders.store');
Route::get('/orders/{order}/status', ['App\Http\Controllers\OrderController', 'status'])->name('orders.status');

Route::post('/orders/{order}/pay-test', ['App\Http\Controllers\OrderController', 'payTest'])->name('orders.pay-test');
Route::post('/webhooks/payment', ['App\Http\Controllers\PaymentWebhookController', 'handle'])->name('webhooks.payment');
