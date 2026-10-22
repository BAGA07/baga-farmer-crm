<?php

namespace App\Http\Controllers;

use App\Models\Categorie;
use App\Models\Produit;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class ProduitController extends Controller
{
    public function index(Request $request)
    {
        $query = Produit::with('categorie');

        if ($request->filled('recherche')) {
            $recherche = $request->recherche;

            $query->where(function ($q) use ($recherche) {
                $q->where('nom', 'like', "%{$recherche}%")
                  ->orWhere('reference', 'like', "%{$recherche}%");
            });
        }

        if ($request->filled('categorie_id')) {
            $query->where('categorie_id', $request->categorie_id);
        }

        if ($request->filled('statut')) {
            $query->where('actif', $request->statut === 'actif');
        }

        $produits = $query->latest()->paginate(10)->withQueryString();

        $categories = Categorie::orderBy('nom')->get();

        return view('produits.index', compact(
            'produits',
            'categories'
        ));
    }

    public function create()
    {
        $categories = Categorie::orderBy('nom')->get();

        return view('produits.create', compact('categories'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'categorie_id' => ['nullable', 'exists:categories,id'],
            'reference' => ['required', 'string', 'max:30', 'unique:produits,reference'],
            'nom' => ['required', 'string', 'max:150'],
            'unite' => ['required', 'string', 'max:30'],
            'prix_gros' => ['required', 'numeric', 'min:0'],
            'prix_detail' => ['required', 'numeric', 'min:0'],
            'stock_suivi' => ['nullable', 'boolean'],
            'quantite_disponible' => ['nullable', 'numeric', 'min:0'],
        ]);

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

    public function edit(Produit $produit)
    {
        $categories = Categorie::orderBy('nom')->get();

        return view('produits.edit', compact(
            'produit',
            'categories'
        ));
    }

    public function update(Request $request, Produit $produit)
    {
        $validated = $request->validate([
            'categorie_id' => ['nullable', 'exists:categories,id'],
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

        $produit->update($validated);

        return redirect()
            ->route('produits.index')
            ->with('success', 'Produit modifié avec succès.');
    }

    public function destroy(Produit $produit)
    {
        // Désactivation plutôt que suppression définitive :
        // les anciennes lignes de commande restent préservées.
        $produit->update(['actif' => false]);

        return redirect()
            ->route('produits.index')
            ->with('success', 'Produit désactivé avec succès.');
    }
}
