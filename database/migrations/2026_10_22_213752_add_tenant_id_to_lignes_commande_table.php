
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        $tenantId = DB::table('tenants')
            ->where('slug', 'baga-farmer')
            ->value('id');

        if (!$tenantId) {
            throw new RuntimeException('Le tenant Baga Farmer est introuvable.');
        }

        Schema::table('lignes_commande', function (Blueprint $table) {
            $table->foreignId('tenant_id')
                ->nullable()
                ->after('id')
                ->constrained('tenants')
                ->restrictOnDelete();
        });

        DB::table('lignes_commande')->whereNull('tenant_id')
            ->update(['tenant_id' => $tenantId]);

        Schema::table('lignes_commande', function (Blueprint $table) {
            $table->unsignedBigInteger('tenant_id')->nullable(false)->change();
            $table->index(['tenant_id', 'commande_id']);
        });
    }

    public function down(): void
    {
        Schema::table('lignes_commande', function (Blueprint $table) {
            $table->dropIndex(['tenant_id', 'commande_id']);
            $table->dropForeign(['tenant_id']);
            $table->dropColumn('tenant_id');
        });
    }
};
