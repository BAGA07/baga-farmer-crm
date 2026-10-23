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

        Schema::table('categories', function (Blueprint $table) {
            $table->foreignId('tenant_id')
                ->nullable()
                ->after('id')
                ->constrained('tenants')
                ->restrictOnDelete();
        });

        DB::table('categories')->whereNull('tenant_id')
            ->update(['tenant_id' => $tenantId]);

        Schema::table('categories', function (Blueprint $table) {
            $table->dropUnique('categories_nom_unique');
            $table->unsignedBigInteger('tenant_id')->nullable(false)->change();
            $table->unique(['tenant_id', 'nom']);
        });
    }

    public function down(): void
    {
        Schema::table('categories', function (Blueprint $table) {
            $table->dropUnique(['tenant_id', 'nom']);
            $table->dropForeign(['tenant_id']);
            $table->dropColumn('tenant_id');
            $table->unique('nom');
        });
    }
};
