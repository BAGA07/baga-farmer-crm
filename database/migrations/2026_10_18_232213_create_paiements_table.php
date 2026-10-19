
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('paiements', function (Blueprint $table) {
            $table->id();

            $table->foreignId('commande_id')
                ->constrained('commandes')
                ->restrictOnDelete();

            $table->foreignId('user_id')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();

            $table->string('reference', 50)->nullable()->unique();

            $table->dateTime('date_paiement');
            $table->decimal('montant', 12, 2);

            $table->string('methode', 30)->default('especes');
            $table->string('statut', 30)->default('confirme');

            $table->text('notes')->nullable();

            $table->timestamps();

            $table->index(['statut', 'date_paiement']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('paiements');
    }
};
