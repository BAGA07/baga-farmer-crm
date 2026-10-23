<?php

namespace App\Http\Middleware;

use App\Models\Tenant;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class IdentifyTenant
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if (!$user) {
            abort(401, 'Authentification requise.');
        }

        // Le slug peut venir de la session web ou d'un en-tête API.
        $slug = $request->session()->get('tenant_slug')
            ?? $request->header('X-Tenant-Slug');

        // Pour un utilisateur qui n'a qu'une seule ferme,
        // sélectionner automatiquement cette ferme.
        if (!$slug) {
            $tenants = $user->tenants()
                ->where('tenants.status', 'active')
                ->get();

            if ($tenants->count() === 1) {
                $tenant = $tenants->first();
                $request->session()->put('tenant_slug', $tenant->slug);
            } elseif ($tenants->isEmpty()) {
                abort(403, 'Aucune ferme active associée à votre compte.');
            } else {
                abort(400, 'Veuillez sélectionner une ferme.');
            }
        } else {
            $tenant = Tenant::query()
                ->where('slug', $slug)
                ->where('status', 'active')
                ->first();

            if (!$tenant) {
                abort(404, 'Ferme introuvable ou inactive.');
            }
        }

        // Ne jamais se fier uniquement au slug fourni.
        $belongsToTenant = $user->tenants()
            ->where('tenants.id', $tenant->id)
            ->exists();

        if (!$belongsToTenant) {
            abort(403, 'Vous n’avez pas accès à cette ferme.');
        }

        $request->attributes->set('tenant', $tenant);

        return $next($request);
    }
}
