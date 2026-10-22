<?php

namespace App\Http\Controllers;

use App\Models\Categorie;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class CategorieController extends Controller
{
    public function index()
    {
        $categories = Categorie::withCount('produits')
            ->orderBy('nom')
            ->paginate(10);

        return view('categories.index', compact('categories'));
    }

    public function create()
    {
        return view('categories.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'nom' => ['required', 'string', 'max:100', 'unique:categories,nom'],
            'description' => ['nullable', 'string', 'max:2000'],
        ]);

        Categorie::create($validated);

        return redirect()->route('categories.index')
            ->with('success', 'Catégorie ajoutée avec succès.');
    }

    
public function edit(Categorie $category)
{
    return view('categories.edit', [
        'categorie' => $category,
    ]);
}

public function update(Request $request, Categorie $category)
{
    $validated = $request->validate([
        'nom' => [
            'required',
            'string',
            'max:100',
            \Illuminate\Validation\Rule::unique('categories', 'nom')
                ->ignore($category->id),
        ],
        'description' => ['nullable', 'string', 'max:2000'],
    ]);

    $category->update($validated);

    return redirect()->route('categories.index')
        ->with('success', 'Catégorie modifiée avec succès.');
}

public function destroy(Categorie $category)
{
    if ($category->produits()->exists()) {
        return redirect()->route('categories.index')
            ->with('error', 'Impossible de supprimer cette catégorie : elle est utilisée par des produits.');
    }

    $category->delete();

    return redirect()->route('categories.index')
        ->with('success', 'Catégorie supprimée avec succès.');
}


   /*  public function destroy(Categorie $categorie)
    {
        if ($categorie->produits()->exists()) {
            return redirect()->route('categories.index')
                ->with('error', 'Impossible de supprimer cette catégorie : elle est utilisée par des produits.');
        }

        $categorie->delete();

        return redirect()->route('categories.index')
            ->with('success', 'Catégorie supprimée avec succès.');
    } */
}
