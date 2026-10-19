<?php

namespace App\Http\Controllers;

use App\Models\Client;
use App\Models\Commande;
use App\Models\Paiement;

class DashboardController extends Controller
{
    public function index()
    {
        // Clients actifs
        $totalClients = Client::where('actif', true)->count();

        // Nombre total de commandes
        $totalCommandes = Commande::count();

        // Calcul du montant des ventes à partir des lignes de commande
        $commandes = Commande::with('lignes')
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

        // Paiements considérés comme validés
        $totalPaiements = Paiement::whereIn('statut', [
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
        ])->sum('montant');

        // Solde global restant à encaisser
        $resteAPayer = max(0, $totalVentes - (float) $totalPaiements);

        return view('dashboard.index', compact(
            'totalClients',
            'totalCommandes',
            'totalVentes',
            'totalPaiements',
            'resteAPayer'
        ));
    }
}
