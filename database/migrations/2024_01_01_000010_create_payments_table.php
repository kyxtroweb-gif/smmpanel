<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('payments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->string('method', 50);
            $table->decimal('amount', 15, 4);
            $table->decimal('amount_bonus', 15, 4)->default(0);
            $table->decimal('net_amount', 15, 4);
            $table->string('transaction_id', 200)->nullable();
            $table->enum('status', ['pending','completed','failed'])->default('pending');
            $table->text('note')->nullable();
            $table->timestamps();
            $table->index(['user_id', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('payments');
    }
};
