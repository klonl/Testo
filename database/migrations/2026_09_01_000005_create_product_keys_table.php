<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('product_keys', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_id')->constrained()->restrictOnDelete();
            $table->foreignId('supplier_id')->nullable()->constrained()->nullOnDelete();
            $table->text('code')->unique();
            $table->string('status', 32)->default('available')->index();
            $table->string('request_id')->nullable()->unique();
            $table->timestampTz('used_at')->nullable();
            $table->timestamps();

            $table->index(['product_id', 'status']);
            $table->index(['supplier_id', 'product_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('product_keys');
    }
};
