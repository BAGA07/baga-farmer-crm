<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('subscription_payments', function (Blueprint $table) {
            $table->id();

            $table->foreignId('subscription_id')
                ->constrained('subscriptions')
                ->restrictOnDelete();

            $table->unsignedInteger('amount');
            $table->string('currency', 3)->default('XOF');
            $table->string('provider', 50)->nullable();
            $table->string('method', 50)->nullable();
            $table->string('provider_reference', 150)->nullable()->unique();
            $table->string('transaction_reference', 150)->nullable();
            $table->string('status', 30)->default('pending');
            $table->timestamp('paid_at')->nullable();
            $table->json('provider_response')->nullable();
            $table->timestamps();

            $table->index(['subscription_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('subscription_payments');
    }
};