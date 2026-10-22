<?php

namespace App\Http\Controllers;

use App\Models\Commande;
use App\Models\Paiement;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class PaiementController extends Controller
{
    /**
 * Afficher la liste de tous les paiements.
 */
public function index(Request $request)
{
    $query = Paiement::with([
        'commande.client',
        'utilisateur',
    ]);

    // Recherche par référence de paiement, commande ou client.
    if ($request->filled('recherche')) {
        $recherche = $request->recherche;

        $query->where(function ($q) use ($recherche) {
            $q->where('reference', 'like', "%{$recherche}%")
                ->orWhereHas('commande', function ($commandeQuery) use ($recherche) {
                    $commandeQuery->where('numero', 'like', "%{$recherche}%")
                        ->orWhereHas('client', function ($clientQuery) use ($recherche) {
                            $clientQuery->where('nom', 'like', "%{$recherche}%")
                                ->orWhere('telephone', 'like', "%{$recherche}%");
                        });
                });
        });
    }

    // Filtrer par statut.
    if ($request->filled('statut')) {
        $query->where('statut', $request->statut);
    }

    // Filtrer par mode de paiement.
    if ($request->filled('methode')) {
        $query->where('methode', $request->methode);
    }

    // Filtrer par période.
    if ($request->filled('date_debut')) {
        $query->whereDate('date_paiement', '>=', $request->date_debut);
    }

    if ($request->filled('date_fin')) {
        $query->whereDate('date_paiement', '<=', $request->date_fin);
    }

    $paiements = $query
        ->orderByDesc('date_paiement')
        ->orderByDesc('id')
        ->paginate(15)
        ->withQueryString();

    $totalConfirme = Paiement::where('statut', 'confirme')
        ->sum('montant');

    $totalJour = Paiement::where('statut', 'confirme')
        ->whereDate('date_paiement', today())
        ->sum('montant');

    $nombrePaiements = Paiement::where('statut', 'confirme')->count();

    return view('paiements.index', compact(
        'paiements',
        'totalConfirme',
        'totalJour',
        'nombrePaiements'
    ));
}

    /**
     * Enregistrer un paiement pour une commande.
     */
    public function store(Request $request, Commande $commande)
    {
        $validated = $request->validate([
            'date_paiement' => ['required', 'date'],
            'montant' => ['required', 'numeric', 'gt:0'],
            'methode' => [
                'required',
                'in:especes,orange_money,moov_money,virement,autre',
            ],
            'reference' => ['nullable', 'string', 'max:100', 'unique:paiements,reference'],
            'notes' => ['nullable', 'string', 'max:2000'],
        ]);

        DB::transaction(function () use ($validated, $commande) {
            // Verrouiller la commande pendant le calcul du solde.
            $commandeVerrouillee = Commande::whereKey($commande->id)
                ->lockForUpdate()
                ->firstOrFail();

            $sousTotal = $commandeVerrouillee->lignes()
                ->get()
                ->sum(function ($ligne) {
                    return ((float) $ligne->quantite
                        * (float) $ligne->prix_unitaire)
                        - (float) $ligne->remise;
                });

            $totalCommande = max(
                0,
                $sousTotal
                    + (float) $commandeVerrouillee->frais_livraison
                    - (float) $commandeVerrouillee->remise
            );

            $dejaPaye = $commandeVerrouillee->paiements()
                ->where('statut', 'confirme')
                ->sum('montant');

            $resteAPayer = max(0, $totalCommande - (float) $dejaPaye);

            if ((float) $validated['montant'] > $resteAPayer) {
                throw ValidationException::withMessages([
                    'montant' => 'Le paiement dépasse le solde restant de '
                        . number_format($resteAPayer, 0, ',', ' ')
                        . ' FCFA.',
                ]);
            }

            Paiement::create([
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
