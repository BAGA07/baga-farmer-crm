<?php

namespace App\Http\Controllers;

use App\Models\Categorie;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class CategorieController extends Controller
{
    private function tenantId(Request $request): int
    {
        $tenant = $request->attributes->get('tenant');

        abort_unless($tenant, 403, 'Aucune ferme sélectionnée.');

        return (int) $tenant->id;
    }

    private function verifierCategorie(Categorie $category, int $tenantId): void
    {
        abort_unless(
            (int) $category->tenant_id === $tenantId,
            404,
            'Catégorie introuvable.'
        );
    }

    public function index(Request $request)
    {
        $tenantId = $this->tenantId($request);

        $categories = Categorie::query()
            ->where('tenant_id', $tenantId)
            ->withCount([
                'produits' => fn ($query) => $query
                    ->where('tenant_id', $tenantId),
            ])
            ->orderBy('nom')
            ->paginate(10);

        return view('categories.index', compact('categories'));
    }

    public function create(Request $request)
    {
        $this->tenantId($request);

        return view('categories.create');
    }

    public function store(Request $request)
    {
        $tenantId = $this->tenantId($request);

        $validated = $request->validate([
            // Le nom est actuellement unique globalement dans la base.
            'nom' => [
                'required',
                'string',
                'max:100',
                'unique:categories,nom',
            ],
            'description' => ['nullable', 'string', 'max:2000'],
        ]);

        $validated['tenant_id'] = $tenantId;

        Categorie::create($validated);

        return redirect()
            ->route('categories.index')
            ->with('success', 'Catégorie ajoutée avec succès.');
    }

    public function edit(Request $request, Categorie $category)
    {
        $tenantId = $this->tenantId($request);
        $this->verifierCategorie($category, $tenantId);

        return view('categories.edit', [
            'categorie' => $category,
        ]);
    }

    public function update(Request $request, Categorie $category)
    {
        $tenantId = $this->tenantId($request);
        $this->verifierCategorie($category, $tenantId);

        $validated = $request->validate([
            'nom' => [
                'required',
                'string',
                'max:100',
                Rule::unique('categories', 'nom')->ignore($category->id),
            ],
            'description' => ['nullable', 'string', 'max:2000'],
        ]);

        // Le tenant_id ne peut pas être modifié par ce formulaire.
        unset($validated['tenant_id']);

        $category->update($validated);

        return redirect()
            ->route('categories.index')
            ->with('success', 'Catégorie modifiée avec succès.');
    }

    public function destroy(Request $request, Categorie $category)
    {
        $tenantId = $this->tenantId($request);
        $this->verifierCategorie($category, $tenantId);

        // Vérifier l'existence de produits avant toute suppression.
        if ($category->produits()->exists()) {
            return redirect()
                ->route('categories.index')
                ->with(
                    'error',
                    'Impossible de supprimer cette catégorie : elle est utilisée par des produits.'
                );
        }

        $category->delete();

        return redirect()
            ->route('categories.index')
            ->with('success', 'Catégorie supprimée avec succès.');
    }
}