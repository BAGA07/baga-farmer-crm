<?php

namespace App\Http\Controllers;

use App\Models\Client;
use App\Models\Commande;
use App\Models\Produit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class CommandeController extends Controller
{
    /**
     * Afficher la liste des commandes.
     */
    public function index()
    {
        $commandes = Commande::with(['client', 'lignes', 'paiements'])
            ->latest('date_commande')
            ->paginate(10);

        return view('commandes.index', compact('commandes'));
    }

    /**
     * Afficher le formulaire de création.
     */
    public function create()
    {
        $clients = Client::where('actif', true)
            ->orderBy('nom')
            ->get();

        $produits = Produit::where('actif', true)
            ->orderBy('nom')
            ->get();

        return view('commandes.create', compact('clients', 'produits'));
    }

    /**
     * Enregistrer une nouvelle commande.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'client_id' => [
                'required',
                'integer',
                'exists:clients,id',
            ],
            'date_commande' => [
                'required',
                'date',
            ],
            'date_livraison_prevue' => [
                'nullable',
                'date',
                'after_or_equal:date_commande',
            ],
            'adresse_livraison' => [
                'nullable',
                'string',
                'max:2000',
            ],
            'frais_livraison' => [
                'required',
                'numeric',
                'min:0',
            ],
            'remise' => [
                'required',
                'numeric',
                'min:0',
            ],
            'notes' => [
                'nullable',
                'string',
                'max:5000',
            ],
            'produits' => [
                'required',
                'array',
                'min:1',
            ],
            'produits.*.produit_id' => [
                'required',
                'integer',
                'distinct',
                'exists:produits,id',
            ],
            'produits.*.quantite' => [
                'required',
                'numeric',
                'gt:0',
            ],
            'produits.*.remise' => [
                'nullable',
                'numeric',
                'min:0',
            ],
        ]);

        $client = Client::where('actif', true)
            ->findOrFail($validated['client_id']);

        $produitsSelectionnes = collect($validated['produits']);

        DB::transaction(function () use (
            $validated,
            $client,
            $produitsSelectionnes
        ) {
            $commande = Commande::create([
                'numero' => 'CMD-' . now()->format('Ymd') . '-'
                    . Str::upper(Str::random(6)),
                'client_id' => $client->id,
                'user_id' => auth()->id(),
                'date_commande' => $validated['date_commande'],
                'date_livraison_prevue' =>
                    $validated['date_livraison_prevue'] ?? null,
                'statut' => 'recue',
                'adresse_livraison' =>
                    $validated['adresse_livraison'] ?? null,
                'frais_livraison' => $validated['frais_livraison'],
                'remise' => $validated['remise'],
                'notes' => $validated['notes'] ?? null,
            ]);

            foreach ($produitsSelectionnes as $ligne) {
                $produit = Produit::where('actif', true)
                    ->findOrFail($ligne['produit_id']);

                // Le prix est déterminé côté serveur.
                // On ne fait pas confiance au prix envoyé par le navigateur.
                $typeClient = Str::lower((string) $client->type_client);

                $prixUnitaire = in_array(
                    $typeClient,
                    ['gros', 'grossiste', 'revendeur'],
                    true
                )
                    ? $produit->prix_gros
                    : $produit->prix_detail;

                $quantite = (float) $ligne['quantite'];
                $remiseLigne = (float) ($ligne['remise'] ?? 0);

                if ($remiseLigne > $quantite * (float) $prixUnitaire) {
                    throw ValidationException::withMessages([
                        'produits' =>
                            'La remise d’une ligne ne peut pas dépasser son montant.',
                    ]);
                }

                $commande->lignes()->create([
                    'produit_id' => $produit->id,
                    'nom_produit' => $produit->nom,
                    'unite' => $produit->unite,
                    'quantite' => $quantite,
                    'prix_unitaire' => $prixUnitaire,
                    'remise' => $remiseLigne,
                ]);
            }
        });

        return redirect()
            ->route('commandes.index')
            ->with('success', 'Commande enregistrée avec succès.');
    }

    /**
     * Consulter une commande.
     */
    public function show(Commande $commande)
    {
        $commande->load([
            'client',
            'lignes.produit',
            'paiements',
            'utilisateur',
        ]);

        return view('commandes.show', compact('commande'));
    }
}
