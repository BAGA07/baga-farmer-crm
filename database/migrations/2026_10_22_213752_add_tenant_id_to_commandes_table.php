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

        Schema::table('commandes', function (Blueprint $table) {
            $table->foreignId('tenant_id')
                ->nullable()
                ->after('id')
                ->constrained('tenants')
                ->restrictOnDelete();
        });

        DB::table('commandes')->whereNull('tenant_id')
            ->update(['tenant_id' => $tenantId]);

        Schema::table('commandes', function (Blueprint $table) {
            $table->dropUnique('commandes_numero_unique');
            $table->unsignedBigInteger('tenant_id')->nullable(false)->change();
            $table->unique(['tenant_id', 'numero']);
        });
    }

    public function down(): void
    {
        Schema::table('commandes', function (Blueprint $table) {
            $table->dropUnique(['tenant_id', 'numero']);
            $table->dropForeign(['tenant_id']);
            $table->dropColumn('tenant_id');
            $table->unique('numero');
        });
    }
};
