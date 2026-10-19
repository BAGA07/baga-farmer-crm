
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('commandes', function (Blueprint $table) {
            $table->id();

            $table->string('numero', 30)->unique();

            $table->foreignId('client_id')
                ->constrained('clients')
                ->restrictOnDelete();

            $table->foreignId('user_id')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();

            $table->dateTime('date_commande');
            $table->dateTime('date_livraison_prevue')->nullable();

            $table->string('statut', 30)->default('recue');

            $table->text('adresse_livraison')->nullable();

            $table->decimal('frais_livraison', 12, 2)->default(0);
            $table->decimal('remise', 12, 2)->default(0);

            $table->text('notes')->nullable();

            $table->timestamps();

            $table->index(['statut', 'date_commande']);
            $table->index('date_livraison_prevue');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('commandes');
    }
};
