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
       Schema::create('produits', function (Blueprint $table) {
            $table->id();

            $table->foreignId('categorie_id')
                ->nullable()
                ->constrained('categories')
                ->nullOnDelete();

            $table->string('reference', 30)->unique();
            $table->string('nom', 150);
            $table->string('unite', 30);

            $table->decimal('prix_gros', 12, 2)->default(0);
            $table->decimal('prix_detail', 12, 2)->default(0);

            $table->boolean('stock_suivi')->default(false);
            $table->decimal('quantite_disponible', 12, 3)
                ->nullable();

            $table->boolean('actif')->default(true);

            $table->timestamps();

            $table->index('nom');
        });
    }


    /**
     * Reverse the migrations.
     */
     public function down(): void
    {
        Schema::dropIfExists('produits');
    }
};
