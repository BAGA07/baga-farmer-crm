
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

        Schema::table('clients', function (Blueprint $table) {
            $table->foreignId('tenant_id')
                ->nullable()
                ->after('id')
                ->constrained('tenants')
                ->restrictOnDelete();
        });

        DB::table('clients')->whereNull('tenant_id')
            ->update(['tenant_id' => $tenantId]);

        Schema::table('clients', function (Blueprint $table) {
            $table->dropUnique('clients_code_client_unique');
            $table->unsignedBigInteger('tenant_id')->nullable(false)->change();
            $table->unique(['tenant_id', 'code_client']);
        });
    }

    public function down(): void
    {
        Schema::table('clients', function (Blueprint $table) {
            $table->dropUnique(['tenant_id', 'code_client']);
            $table->dropForeign(['tenant_id']);
            $table->dropColumn('tenant_id');
            $table->unique('code_client');
        });
    }
};
