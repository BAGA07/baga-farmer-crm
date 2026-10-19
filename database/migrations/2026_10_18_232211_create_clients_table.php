<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('clients', function (Blueprint $table) {
            $table->id();

            $table->string('code_client', 20)->unique();
            $table->string('nom', 150);
            $table->string('telephone', 30)->index();
            $table->string('telephone_secondaire', 30)->nullable();

            $table->string('type_client', 30)->default('particulier');
            $table->string('quartier', 100)->nullable();
            $table->text('adresse')->nullable();
            $table->text('notes')->nullable();

            $table->boolean('actif')->default(true);

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('clients');
    }
    
};
