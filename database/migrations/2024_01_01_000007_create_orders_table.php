<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('orders', function (Blueprint $table) {
            $table->id();
            $table->string('order_id', 30)->unique();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->foreignId('service_id')->constrained()->onDelete('cascade');
            $table->string('link', 500);
            $table->integer('quantity');
            $table->decimal('charge', 15, 4);
            $table->decimal('cost', 15, 4)->default(0);
            $table->decimal('profit', 15, 4)->default(0);
            $table->enum('status', ['pending','processing','completed','partial','cancelled','refunded'])->default('pending');
            $table->integer('start_count')->default(0);
            $table->integer('remains')->default(0);
            $table->integer('drip_id')->nullable();
            $table->string('provider_order_id', 100)->nullable();
            $table->text('api_response')->nullable();
            $table->timestamps();
            $table->index(['user_id', 'created_at']);
            $table->index(['status', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('orders');
    }
};
