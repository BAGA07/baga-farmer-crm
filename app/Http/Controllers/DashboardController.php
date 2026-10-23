<?php

namespace App\Http\Controllers;

use App\Models\Client;
use App\Models\Commande;
use App\Models\Paiement;
use Illuminate\Http\Request;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function index(Request $request): View
    {
        $tenant = $request->attributes->get('tenant');

        abort_unless($tenant, 403, 'Aucune ferme sélectionnée.');

        $tenantId = (int) $tenant->id;

        // Nombre de clients actifs de cette ferme.
        $totalClients = Client::query()
            ->where('tenant_id', $tenantId)
            ->where('actif', true)
            ->count();

        // Nombre de commandes de cette ferme.
        $totalCommandes = Commande::query()
            ->where('tenant_id', $tenantId)
            ->count();

        // Ventes calculées uniquement à partir des commandes
        // et des lignes appartenant à cette ferme.
        $commandes = Commande::query()
            ->where('tenant_id', $tenantId)
            ->where(function ($query) {
                $query->whereNull('statut')
                    ->orWhereNotIn('statut', [
                        'annulee',
                        'annulée',
                        'annule',
                        'annulé',
                        'cancelled',
                        'canceled',
                    ]);
            })
            ->with([
                'lignes' => fn ($query) => $query
                    ->where('tenant_id', $tenantId),
            ])
            ->get();

        $totalVentes = $commandes->sum(function ($commande) {
            $sousTotal = $commande->lignes->sum(function ($ligne) {
                $montantLigne =
                    (float) $ligne->quantite
                    * (float) $ligne->prix_unitaire;

                return max(
                    0,
                    $montantLigne - (float) ($ligne->remise ?? 0)
                );
            });

            return max(
                0,
                $sousTotal
                    + (float) ($commande->frais_livraison ?? 0)
                    - (float) ($commande->remise ?? 0)
            );
        });

        // Paiements commerciaux validés de cette ferme uniquement.
        $totalPaiements = Paiement::query()
            ->where('tenant_id', $tenantId)
            ->whereIn('statut', [
                'valide',
                'validé',
                'confirme',
                'confirmé',
                'effectue',
                'effectué',
                'paye',
                'payé',
                'termine',
                'terminé',
            ])
            ->sum('montant');

        $resteAPayer = max(
            0,
            $totalVentes - (float) $totalPaiements
        );

        return view('dashboard.index', compact(
            'totalClients',
            'totalCommandes',
            'totalVentes',
            'totalPaiements',
            'resteAPayer'
        ));
    }
}
