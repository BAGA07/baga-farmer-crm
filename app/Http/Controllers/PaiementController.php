<?php

namespace App\Http\Controllers;

use App\Models\Commande;
use App\Models\Paiement;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class PaiementController extends Controller
{
    private function tenantId(Request $request): int
    {
        $tenant = $request->attributes->get('tenant');

        abort_unless($tenant, 403, 'Aucune ferme sélectionnée.');

        return (int) $tenant->id;
    }

    /**
     * Afficher uniquement les paiements de la ferme active.
     */
    public function index(Request $request)
    {
        $tenantId = $this->tenantId($request);

        $query = Paiement::query()
            ->where('tenant_id', $tenantId)
            ->with([
                'commande' => fn ($commandeQuery) => $commandeQuery
                    ->where('tenant_id', $tenantId)
                    ->with([
                        'client' => fn ($clientQuery) => $clientQuery
                            ->where('tenant_id', $tenantId),
                    ]),
                'utilisateur',
            ]);

        if ($request->filled('recherche')) {
            $recherche = $request->string('recherche')->trim()->toString();

            $query->where(function ($q) use ($recherche, $tenantId) {
                $q->where('reference', 'like', "%{$recherche}%")
                    ->orWhereHas('commande', function ($commandeQuery) use (
                        $recherche,
                        $tenantId
                    ) {
                        $commandeQuery
                            ->where('tenant_id', $tenantId)
                            ->where(function ($commandeSearch) use ($recherche) {
                                $commandeSearch
                                    ->where('numero', 'like', "%{$recherche}%")
                                    ->orWhereHas('client', function ($clientQuery) use (
                                        $recherche
                                    ) {
                                        $clientQuery
                                            ->where('nom', 'like', "%{$recherche}%")
                                            ->orWhere(
                                                'telephone',
                                                'like',
                                                "%{$recherche}%"
                                            );
                                    });
                            });
                    });
            });
        }

        if ($request->filled('statut')) {
            $query->where('statut', $request->input('statut'));
        }

        if ($request->filled('methode')) {
            $query->where('methode', $request->input('methode'));
        }

        if ($request->filled('date_debut')) {
            $query->whereDate('date_paiement', '>=', $request->input('date_debut'));
        }

        if ($request->filled('date_fin')) {
            $query->whereDate('date_paiement', '<=', $request->input('date_fin'));
        }

        $paiements = $query
            ->orderByDesc('date_paiement')
            ->orderByDesc('id')
            ->paginate(15)
            ->withQueryString();

        $totalConfirme = Paiement::query()
            ->where('tenant_id', $tenantId)
            ->where('statut', 'confirme')
            ->sum('montant');

        $totalJour = Paiement::query()
            ->where('tenant_id', $tenantId)
            ->where('statut', 'confirme')
            ->whereDate('date_paiement', today())
            ->sum('montant');

        $nombrePaiements = Paiement::query()
            ->where('tenant_id', $tenantId)
            ->where('statut', 'confirme')
            ->count();

        return view('paiements.index', compact(
            'paiements',
            'totalConfirme',
            'totalJour',
            'nombrePaiements'
        ));
    }

    /**
     * Enregistrer un paiement pour une commande de la ferme active.
     */
    public function store(Request $request, Commande $commande)
    {
        $tenantId = $this->tenantId($request);

        abort_unless(
            (int) $commande->tenant_id === $tenantId,
            404,
            'Commande introuvable.'
        );

        $validated = $request->validate([
            'date_paiement' => ['required', 'date'],
            'montant' => ['required', 'numeric', 'gt:0'],
            'methode' => [
                'required',
                'in:especes,orange_money,moov_money,virement,autre',
            ],
            'reference' => [
                'nullable',
                'string',
                'max:100',
                'unique:paiements,reference',
            ],
            'notes' => ['nullable', 'string', 'max:2000'],
        ]);

        DB::transaction(function () use ($validated, $commande, $tenantId) {
            // Rechercher et verrouiller uniquement la commande de cette ferme.
            $commandeVerrouillee = Commande::query()
                ->where('tenant_id', $tenantId)
                ->whereKey($commande->id)
                ->lockForUpdate()
                ->firstOrFail();

            $lignes = $commandeVerrouillee->lignes()
                ->where('tenant_id', $tenantId)
                ->get();

            $sousTotal = $lignes->sum(function ($ligne) {
                return max(
                    0,
                    ((float) $ligne->quantite
                        * (float) $ligne->prix_unitaire)
                        - (float) ($ligne->remise ?? 0)
                );
            });

            $totalCommande = max(
                0,
                $sousTotal
                    + (float) ($commandeVerrouillee->frais_livraison ?? 0)
                    - (float) ($commandeVerrouillee->remise ?? 0)
            );

            $dejaPaye = $commandeVerrouillee->paiements()
                ->where('tenant_id', $tenantId)
                ->where('statut', 'confirme')
                ->sum('montant');

            $resteAPayer = max(
                0,
                $totalCommande - (float) $dejaPaye
            );

            if ((float) $validated['montant'] > $resteAPayer) {
                throw ValidationException::withMessages([
                    'montant' => 'Le paiement dépasse le solde restant de '
                        . number_format($resteAPayer, 0, ',', ' ')
                        . ' FCFA.',
                ]);
            }

            Paiement::create([
                'tenant_id' => $tenantId,
                'commande_id' => $commandeVerrouillee->id,
                'user_id' => auth()->id(),
                'reference' => $validated['reference'] ?? null,
                'date_paiement' => $validated['date_paiement'],
                'montant' => $validated['montant'],
                'methode' => $validated['methode'],
                'statut' => 'confirme',
                'notes' => $validated['notes'] ?? null,
            ]);
        });

        return redirect()
            ->route('commandes.show', $commande)
            ->with('success', 'Paiement enregistré avec succès.');
    }
}