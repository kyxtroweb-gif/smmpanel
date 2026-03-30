<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('services', function (Blueprint $table) {
            $table->id();
            $table->foreignId('category_id')->constrained()->onDelete('cascade');
            $table->foreignId('provider_id')->nullable()->constrained()->onDelete('set null');
            $table->string('name');
            $table->text('description')->nullable();
            $table->string('provider_service_id')->nullable();
            $table->decimal('price_per_1k', 10, 4);
            $table->decimal('cost_per_1k', 10, 4)->default(0);
            $table->integer('min_order')->default(1);
            $table->integer('max_order')->default(10000);
            $table->boolean('dripfeed')->default(false);
            $table->boolean('refill')->default(false);
            $table->boolean('cancel')->default(false);
            $table->string('average_time', 100)->nullable();
            $table->text('description_extra')->nullable();
            $table->boolean('is_active')->default(true);
            $table->boolean('is_featured')->default(false);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('services');
    }
};
