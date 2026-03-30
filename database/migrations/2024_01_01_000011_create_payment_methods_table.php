<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('payment_methods', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('slug', 50)->unique();
            $table->boolean('is_active')->default(true);
            $table->decimal('min_amount', 10, 2)->default(1);
            $table->decimal('max_amount', 10, 2)->default(10000);
            $table->decimal('fixed_charge', 10, 2)->default(0);
            $table->decimal('percent_charge', 5, 2)->default(0);
            $table->decimal('bonus_percent', 5, 2)->default(0);
            $table->text('instructions')->nullable();
            $table->text('credentials')->nullable();
            $table->integer('sort_order')->default(0);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('payment_methods');
    }
};
