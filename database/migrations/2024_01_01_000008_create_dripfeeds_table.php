<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('dripfeeds', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->string('order_id', 30)->nullable();
            $table->foreignId('service_id')->constrained()->onDelete('cascade');
            $table->string('link', 500);
            $table->integer('runs');
            $table->integer('interval');
            $table->integer('quantity');
            $table->integer('total_quantity');
            $table->decimal('total_charged', 15, 4)->default(0);
            $table->enum('status', ['active','paused','completed'])->default('active');
            $table->integer('runs_completed')->default(0);
            $table->timestamp('next_run_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('dripfeeds');
    }
};
