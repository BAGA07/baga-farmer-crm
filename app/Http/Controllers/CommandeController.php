<?php

namespace App\Http\Controllers;

use App\Models\Client;
use App\Models\Commande;
use App\Models\Produit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class CommandeController extends Controller
{
    private function tenantId(Request $request): int
    {
        $tenant = $request->attributes->get('tenant');

        abort_unless($tenant, 403, 'Aucune ferme sélectionnée.');

        return (int) $tenant->id;
    }

    private function verifierCommande(Commande $commande, int $tenantId): void
    {
        abort_unless(
            (int) $commande->tenant_id === $tenantId,
            404,
            'Commande introuvable.'
        );
    }

    /**
     * Afficher uniquement les commandes de la ferme active.
     */
    public function index(Request $request)
    {
        $tenantId = $this->tenantId($request);

        $commandes = Commande::query()
            ->where('tenant_id', $tenantId)
            ->with([
                'client' => fn ($query) => $query
                    ->where('tenant_id', $tenantId),
                'lignes' => fn ($query) => $query
                    ->where('tenant_id', $tenantId),
                'paiements' => fn ($query) => $query
                    ->where('tenant_id', $tenantId),
            ])
            ->latest('date_commande')
            ->paginate(10);

        return view('commandes.index', compact('commandes'));
    }

    /**
     * Afficher le formulaire de création.
     */
    public function create(Request $request)
    {
        $tenantId = $this->tenantId($request);

        $clients = Client::query()
            ->where('tenant_id', $tenantId)
            ->where('actif', true)
            ->orderBy('nom')
            ->get();

        $produits = Produit::query()
            ->where('tenant_id', $tenantId)
            ->where('actif', true)
            ->orderBy('nom')
            ->get();

        return view('commandes.create', compact('clients', 'produits'));
    }

    /**
     * Enregistrer une nouvelle commande.
     */
    public function store(Request $request)
    {
        $tenantId = $this->tenantId($request);

        $validated = $request->validate([
            'client_id' => [
                'required',
                'integer',
                Rule::exists('clients', 'id')
                    ->where(fn ($query) => $query
                        ->where('tenant_id', $tenantId)
                        ->where('actif', true)),
            ],
            'date_commande' => ['required', 'date'],
            'date_livraison_prevue' => [
                'nullable',
                'date',
                'after_or_equal:date_commande',
            ],
            'adresse_livraison' => ['nullable', 'string', 'max:2000'],
            'frais_livraison' => ['required', 'numeric', 'min:0'],
            'remise' => ['required', 'numeric', 'min:0'],
            'notes' => ['nullable', 'string', 'max:5000'],
            'produits' => ['required', 'array', 'min:1'],
            'produits.*.produit_id' => [
                'required',
                'integer',
                'distinct',
                Rule::exists('produits', 'id')
                    ->where(fn ($query) => $query
                        ->where('tenant_id', $tenantId)
                        ->where('actif', true)),
            ],
            'produits.*.quantite' => ['required', 'numeric', 'gt:0'],
            'produits.*.remise' => ['nullable', 'numeric', 'min:0'],
        ]);

        $client = Client::query()
            ->where('tenant_id', $tenantId)
            ->where('actif', true)
            ->findOrFail($validated['client_id']);

        DB::transaction(function () use ($validated, $client, $tenantId) {
            $commande = Commande::create([
                'tenant_id' => $tenantId,
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

            foreach ($validated['produits'] as $ligne) {
                // Recontrôler l'appartenance à la ferme côté serveur.
                $produit = Produit::query()
                    ->where('tenant_id', $tenantId)
                    ->where('actif', true)
                    ->findOrFail($ligne['produit_id']);

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
                $montantBrut = $quantite * (float) $prixUnitaire;

                if ($remiseLigne > $montantBrut) {
                    throw ValidationException::withMessages([
                        'produits' =>
                            'La remise d’une ligne ne peut pas dépasser son montant.',
                    ]);
                }

                $commande->lignes()->create([
                    'tenant_id' => $tenantId,
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
     * Afficher le formulaire de modification d'une commande.
     */
    public function edit(Request $request, Commande $commande)
    {
        $tenantId = $this->tenantId($request);
        $this->verifierCommande($commande, $tenantId);

        $paiementConfirme = $commande->paiements()
            ->where('tenant_id', $tenantId)
            ->where('statut', 'confirme')
            ->exists();

        if ($paiementConfirme) {
            return redirect()
                ->route('commandes.show', $commande)
                ->with(
                    'error',
                    'Cette commande possède un paiement confirmé. Sa modification est bloquée pour préserver l’historique comptable.'
                );
        }

        $commande->load([
            'lignes' => fn ($query) => $query
                ->where('tenant_id', $tenantId),
        ]);

        $clients = Client::query()
            ->where('tenant_id', $tenantId)
            ->where('actif', true)
            ->orderBy('nom')
            ->get();

        $produits = Produit::query()
            ->where('tenant_id', $tenantId)
            ->where('actif', true)
            ->orderBy('nom')
            ->get();

        return view(
            'commandes.edit',
            compact('commande', 'clients', 'produits')
        );
    }

    /**
     * Enregistrer les modifications d'une commande.
     */
    public function update(Request $request, Commande $commande)
    {
        $tenantId = $this->tenantId($request);
        $this->verifierCommande($commande, $tenantId);

        return DB::transaction(function () use (
            $request,
            $commande,
            $tenantId
        ) {
            // Verrouiller la commande pendant la vérification et la mise à jour.
            $commandeVerrouillee = Commande::query()
                ->where('tenant_id', $tenantId)
                ->whereKey($commande->id)
                ->lockForUpdate()
                ->firstOrFail();

            $paiementConfirme = $commandeVerrouillee->paiements()
                ->where('tenant_id', $tenantId)
                ->where('statut', 'confirme')
                ->exists();

            if ($paiementConfirme) {
                return redirect()
                    ->route('commandes.show', $commandeVerrouillee)
                    ->with(
                        'error',
                        'Modification impossible : cette commande possède un paiement confirmé.'
                    );
            }

            $validated = $request->validate([
                'client_id' => [
                    'required',
                    'integer',
                    Rule::exists('clients', 'id')
                        ->where(fn ($query) => $query
                            ->where('tenant_id', $tenantId)
                            ->where('actif', true)),
                ],
                'date_commande' => ['required', 'date'],
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
                'remise' => ['required', 'numeric', 'min:0'],
                'notes' => ['nullable', 'string', 'max:5000'],
                'produits' => ['required', 'array', 'min:1'],
                'produits.*.produit_id' => [
                    'required',
                    'integer',
                    'distinct',
                    Rule::exists('produits', 'id')
                        ->where(fn ($query) => $query
                            ->where('tenant_id', $tenantId)
                            ->where('actif', true)),
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

            $client = Client::query()
                ->where('tenant_id', $tenantId)
                ->where('actif', true)
                ->findOrFail($validated['client_id']);

            // Mettre à jour les informations générales.
            $commandeVerrouillee->update([
                'client_id' => $client->id,
                'date_commande' => $validated['date_commande'],
                'date_livraison_prevue' =>
                    $validated['date_livraison_prevue'] ?? null,
                'adresse_livraison' =>
                    $validated['adresse_livraison'] ?? null,
                'frais_livraison' => $validated['frais_livraison'],
                'remise' => $validated['remise'],
                'notes' => $validated['notes'] ?? null,
            ]);

            // Remplacer les anciennes lignes dans la même transaction.
            $commandeVerrouillee->lignes()
                ->where('tenant_id', $tenantId)
                ->delete();

            foreach ($validated['produits'] as $ligne) {
                $produit = Produit::query()
                    ->where('tenant_id', $tenantId)
                    ->where('actif', true)
                    ->findOrFail($ligne['produit_id']);

                $typeClient = Str::lower(
                    (string) $client->type_client
                );

                $prixUnitaire = in_array(
                    $typeClient,
                    ['gros', 'grossiste', 'revendeur'],
                    true
                )
                    ? $produit->prix_gros
                    : $produit->prix_detail;

                $quantite = (float) $ligne['quantite'];
                $remiseLigne = (float) ($ligne['remise'] ?? 0);
                $montantBrut = $quantite * (float) $prixUnitaire;

                if ($remiseLigne > $montantBrut) {
                    throw ValidationException::withMessages([
                        'produits' =>
                            'La remise d’une ligne ne peut pas dépasser son montant.',
                    ]);
                }

                $commandeVerrouillee->lignes()->create([
                    'tenant_id' => $tenantId,
                    'produit_id' => $produit->id,
                    'nom_produit' => $produit->nom,
                    'unite' => $produit->unite,
                    'quantite' => $quantite,
                    'prix_unitaire' => $prixUnitaire,
                    'remise' => $remiseLigne,
                ]);
            }

            return redirect()
                ->route('commandes.show', $commandeVerrouillee)
                ->with('success', 'Commande modifiée avec succès.');
        });
    }


    /**
     * Consulter une commande appartenant à la ferme active.
     */
    public function show(Request $request, Commande $commande)
    {
        $tenantId = $this->tenantId($request);
        $this->verifierCommande($commande, $tenantId);

        $commande->load([
            'client' => fn ($query) => $query
                ->where('tenant_id', $tenantId),
            'lignes' => fn ($query) => $query
                ->where('tenant_id', $tenantId)
                ->with([
                    'produit' => fn ($produitQuery) => $produitQuery
                        ->where('tenant_id', $tenantId),
                ]),
            'paiements' => fn ($query) => $query
                ->where('tenant_id', $tenantId),
            'utilisateur',
        ]);

        return view('commandes.show', compact('commande'));
    }

    
    /**
     * Supprimer une commande uniquement si elle ne possède
     * aucun paiement. Protection du tenant actif.
     */
    public function destroy(Request $request, Commande $commande)
    {
        $tenantId = $this->tenantId($request);

        $this->verifierCommande($commande, $tenantId);

        return DB::transaction(function () use (
            $commande,
            $tenantId
        ) {
            $commandeVerrouillee = Commande::query()
                ->where('tenant_id', $tenantId)
                ->whereKey($commande->id)
                ->lockForUpdate()
                ->firstOrFail();

            // Ne jamais supprimer une commande avec un paiement.
            $aDesPaiements = $commandeVerrouillee->paiements()
                ->where('tenant_id', $tenantId)
                ->exists();

            if ($aDesPaiements) {
                return redirect()
                    ->route('commandes.show', $commandeVerrouillee)
                    ->with(
                        'error',
                        'Suppression impossible : cette commande possède un historique de paiements. Annulez-la plutôt que de la supprimer.'
                    );
            }

            // Les lignes de commande seront supprimées par
            // la contrainte cascade définie dans la base.
            $commandeVerrouillee->delete();

            return redirect()
                ->route('commandes.index')
                ->with('success', 'Commande supprimée avec succès.');
        });
    }

}