<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('subscriptions', function (Blueprint $table) {
            $table->id();
            $table->string('sub_id', 30)->unique();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->foreignId('service_id')->constrained()->onDelete('cascade');
            $table->string('link', 500);
            $table->integer('posts')->default(-1);
            $table->integer('quantity');
            $table->integer('delay')->default(0);
            $table->timestamp('expiry')->nullable();
            $table->decimal('total_charged', 15, 4)->default(0);
            $table->enum('status', ['active','paused','completed','cancelled'])->default('active');
            $table->integer('posts_completed')->default(0);
            $table->timestamp('last_order_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('subscriptions');
    }
};
