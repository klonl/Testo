<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('payment_events', function (Blueprint $table) {
            $table->id();
            $table->string('event_id')->unique();
            $table->foreignId('order_id')->nullable()->constrained()->nullOnDelete();
            $table->string('external_order_id')->index();
            $table->string('status', 16);
            $table->unsignedBigInteger('amount');
            $table->string('currency', 3);
            $table->jsonb('payload');
            $table->timestampTz('processed_at')->nullable();
            $table->timestamps();

            $table->index(['external_order_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('payment_events');
    }
};
