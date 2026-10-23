<?php

namespace App\Http\Controllers;

use App\Models\Categorie;
use App\Models\Produit;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class ProduitController extends Controller
{
    private function tenantId(Request $request): int
    {
        $tenant = $request->attributes->get('tenant');

        abort_unless($tenant, 403, 'Aucune ferme sélectionnée.');

        return (int) $tenant->id;
    }

    private function verifierProduit(Produit $produit, int $tenantId): void
    {
        abort_unless(
            (int) $produit->tenant_id === $tenantId,
            404,
            'Produit introuvable.'
        );
    }

    public function index(Request $request)
    {
        $tenantId = $this->tenantId($request);

        $query = Produit::query()
            ->where('tenant_id', $tenantId)
            ->with([
                'categorie' => fn ($q) => $q
                    ->where('tenant_id', $tenantId),
            ]);

        if ($request->filled('recherche')) {
            $recherche = $request->string('recherche')->trim()->toString();

            $query->where(function ($q) use ($recherche) {
                $q->where('nom', 'like', "%{$recherche}%")
                    ->orWhere('reference', 'like', "%{$recherche}%");
            });
        }

        if ($request->filled('categorie_id')) {
            $query->where('categorie_id', $request->input('categorie_id'));
        }

        if ($request->filled('statut')) {
            $query->where('actif', $request->input('statut') === 'actif');
        }

        $produits = $query->latest()->paginate(10)->withQueryString();

        $categories = Categorie::query()
            ->where('tenant_id', $tenantId)
            ->orderBy('nom')
            ->get();

        return view('produits.index', compact('produits', 'categories'));
    }

    public function create(Request $request)
    {
        $tenantId = $this->tenantId($request);

        $categories = Categorie::query()
            ->where('tenant_id', $tenantId)
            ->orderBy('nom')
            ->get();

        return view('produits.create', compact('categories'));
    }

    public function store(Request $request)
    {
        $tenantId = $this->tenantId($request);

        $validated = $request->validate([
            'categorie_id' => [
                'nullable',
                Rule::exists('categories', 'id')
                    ->where(fn ($q) => $q->where('tenant_id', $tenantId)),
            ],
            // La colonne reference est actuellement unique dans la base.
            'reference' => [
                'required',
                'string',
                'max:30',
                'unique:produits,reference',
            ],
            'nom' => ['required', 'string', 'max:150'],
            'unite' => ['required', 'string', 'max:30'],
            'prix_gros' => ['required', 'numeric', 'min:0'],
            'prix_detail' => ['required', 'numeric', 'min:0'],
            'stock_suivi' => ['nullable', 'boolean'],
            'quantite_disponible' => ['nullable', 'numeric', 'min:0'],
        ]);

        $validated['tenant_id'] = $tenantId;
        $validated['stock_suivi'] = $request->boolean('stock_suivi');
        $validated['actif'] = true;

        if (!$validated['stock_suivi']) {
            $validated['quantite_disponible'] = null;
        }

        Produit::create($validated);

        return redirect()
            ->route('produits.index')
            ->with('success', 'Produit ajouté avec succès.');
    }

    public function edit(Request $request, Produit $produit)
    {
        $tenantId = $this->tenantId($request);
        $this->verifierProduit($produit, $tenantId);

        $categories = Categorie::query()
            ->where('tenant_id', $tenantId)
            ->orderBy('nom')
            ->get();

        return view('produits.edit', compact('produit', 'categories'));
    }

    public function update(Request $request, Produit $produit)
    {
        $tenantId = $this->tenantId($request);
        $this->verifierProduit($produit, $tenantId);

        $validated = $request->validate([
            'categorie_id' => [
                'nullable',
                Rule::exists('categories', 'id')
                    ->where(fn ($q) => $q->where('tenant_id', $tenantId)),
            ],
            'reference' => [
                'required',
                'string',
                'max:30',
                Rule::unique('produits', 'reference')->ignore($produit->id),
            ],
            'nom' => ['required', 'string', 'max:150'],
            'unite' => ['required', 'string', 'max:30'],
            'prix_gros' => ['required', 'numeric', 'min:0'],
            'prix_detail' => ['required', 'numeric', 'min:0'],
            'stock_suivi' => ['nullable', 'boolean'],
            'quantite_disponible' => ['nullable', 'numeric', 'min:0'],
            'actif' => ['required', 'boolean'],
        ]);

        $validated['stock_suivi'] = $request->boolean('stock_suivi');

        if (!$validated['stock_suivi']) {
            $validated['quantite_disponible'] = null;
        }

        // Ne jamais autoriser le changement de ferme via le formulaire.
        unset($validated['tenant_id']);

        $produit->update($validated);

        return redirect()
            ->route('produits.index')
            ->with('success', 'Produit modifié avec succès.');
    }

    public function destroy(Request $request, Produit $produit)
    {
        $tenantId = $this->tenantId($request);
        $this->verifierProduit($produit, $tenantId);

        // Désactivation : les anciennes lignes de commande sont conservées.
        $produit->update(['actif' => false]);

        return redirect()
            ->route('produits.index')
            ->with('success', 'Produit désactivé avec succès.');
    }
}