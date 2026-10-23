<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        $now = now();

        // Tenant initial : toutes les données historiques appartiennent
        // à l'exploitation Baga Farmer.
        $tenantId = DB::table('tenants')
            ->where('slug', 'baga-farmer')
            ->value('id');

        if (!$tenantId) {
            $tenantId = DB::table('tenants')->insertGetId([
                'name' => 'Baga Farmer',
                'slug' => 'baga-farmer',
                'city' => 'Bamako',
                'status' => 'active',
                'created_at' => $now,
                'updated_at' => $now,
            ]);
        }

        // Plan initial : les tarifs pourront être configurés ensuite.
        $planId = DB::table('plans')
            ->where('slug', 'starter')
            ->value('id');

        if (!$planId) {
            $planId = DB::table('plans')->insertGetId([
                'name' => 'Starter',
                'slug' => 'starter',
                'description' => 'Plan de démarrage pour une exploitation agricole.',
                'price_monthly' => 0,
                'price_quarterly' => 0,
                'price_yearly' => 0,
                'max_users' => 1,
                'is_active' => true,
                'created_at' => $now,
                'updated_at' => $now,
            ]);
        }

        // Le premier compte existant devient propriétaire.
        // Les autres comptes historiques sont rattachés comme gestionnaires.
        $users = DB::table('users')->orderBy('id')->get();

        foreach ($users as $index => $user) {
            DB::table('tenant_user')->updateOrInsert(
                [
                    'tenant_id' => $tenantId,
                    'user_id' => $user->id,
                ],
                [
                    'role' => $index === 0 ? 'owner' : 'manager',
                    'created_at' => $now,
                    'updated_at' => $now,
                ]
            );
        }
    }

    public function down(): void
    {
        $tenant = DB::table('tenants')
            ->where('slug', 'baga-farmer')
            ->first();

        if (!$tenant) {
            return;
        }

        DB::table('tenant_user')
            ->where('tenant_id', $tenant->id)
            ->delete();

        // Ne pas supprimer le tenant, le plan ou les données métier
        // automatiquement lors d'un rollback.
    }
};
