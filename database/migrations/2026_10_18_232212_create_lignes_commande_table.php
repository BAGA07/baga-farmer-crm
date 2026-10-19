
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('lignes_commande', function (Blueprint $table) {
            $table->id();

            $table->foreignId('commande_id')
                ->constrained('commandes')
                ->cascadeOnDelete();

            $table->foreignId('produit_id')
                ->constrained('produits')
                ->restrictOnDelete();

            $table->string('nom_produit', 150);
            $table->string('unite', 30);

            $table->decimal('quantite', 12, 3);
            $table->decimal('prix_unitaire', 12, 2);
            $table->decimal('remise', 12, 2)->default(0);

            $table->timestamps();

            $table->index('produit_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('lignes_commande');
    }
};
