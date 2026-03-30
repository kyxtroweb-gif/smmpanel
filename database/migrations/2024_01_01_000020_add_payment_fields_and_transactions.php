<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Extend users table
        Schema::table('users', function (Blueprint $table) {
            $table->decimal('total_deposited', 15, 4)->default(0)->after('balance');
        });

        // Extend payment_methods table
        Schema::table('payment_methods', function (Blueprint $table) {
            $table->string('type', 20)->default('manual')->after('slug');
            $table->text('description')->nullable()->after('type');
            $table->string('logo')->nullable()->after('description');
            $table->string('qr_image')->nullable()->after('logo');
            $table->boolean('is_automatic')->default(false)->after('is_active');
            $table->decimal('bonus_threshold', 10, 2)->default(0)->after('bonus_percent');
            $table->json('fields')->nullable()->after('instructions');
            $table->boolean('requires_admin_approval')->default(true)->after('credentials');
        });

        // Extend payments table
        Schema::table('payments', function (Blueprint $table) {
            $table->foreignId('payment_method_id')->nullable()->after('user_id')->constrained()->nullOnDelete();
            $table->decimal('bonus_percent', 5, 2)->default(0)->after('amount_bonus');
            $table->string('user_txn_id', 200)->nullable()->after('transaction_id');
            $table->decimal('user_amount', 15, 4)->nullable()->after('user_txn_id');
            $table->json('payment_data')->nullable()->after('note');
            $table->timestamp('completed_at')->nullable()->after('payment_data');
            $table->timestamp('expires_at')->nullable()->after('completed_at');
        });

        // Create transactions table
        Schema::create('transactions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->foreignId('payment_id')->nullable()->constrained()->onDelete('set null');
            $table->string('type', 30); // deposit, order, refund, bonus
            $table->decimal('amount', 15, 4);
            $table->decimal('balance', 15, 4)->comment('Balance after transaction');
            $table->string('reference', 100)->nullable();
            $table->string('description', 255)->nullable();
            $table->timestamps();
            $table->index(['user_id', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('transactions');

        Schema::table('payments', function (Blueprint $table) {
            $table->dropColumn(['payment_method_id', 'bonus_percent', 'user_txn_id', 'user_amount', 'payment_data', 'completed_at', 'expires_at']);
        });

        Schema::table('payment_methods', function (Blueprint $table) {
            $table->dropColumn(['type', 'description', 'logo', 'qr_image', 'is_automatic', 'bonus_threshold', 'fields', 'requires_admin_approval']);
        });

        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('total_deposited');
        });
    }
};
