
@extends('layouts.tailadmin.app')

@section('content')
<div class="space-y-6">

    <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
        <div>
            <h1 class="text-2xl font-bold text-gray-900 dark:text-white">
                Gestion des produits
            </h1>
            <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                Gérez le catalogue et les prix de Baga Farmer.
            </p>
        </div>

        <a href="{{ route('produits.create') }}"
           class="inline-flex items-center justify-center rounded-lg bg-green-600 px-5 py-3 text-sm font-medium text-white hover:bg-green-700">
            + Nouveau produit
        </a>
    </div>

    @if(session('success'))
        <div class="rounded-lg border border-green-200 bg-green-50 px-4 py-3 text-green-800">
            {{ session('success') }}
        </div>
    @endif

    @if($errors->any())
        <div class="rounded-lg border border-red-200 bg-red-50 px-4 py-3 text-red-700">
            <ul class="list-inside list-disc">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <div class="rounded-2xl border border-gray-200 bg-white p-5 dark:border-gray-800 dark:bg-white/[0.03]">

        <form method="GET" action="{{ route('produits.index') }}"
              class="grid grid-cols-1 gap-4 md:grid-cols-4">

            <div class="md:col-span-2">
                <label for="recherche" class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300">
                    Rechercher
                </label>
                <input type="text"
                       id="recherche"
                       name="recherche"
                       value="{{ request('recherche') }}"
                       placeholder="Nom ou référence du produit..."
                       class="w-full rounded-lg border border-gray-300 px-4 py-3 text-sm focus:border-green-500 focus:outline-none dark:border-gray-700 dark:bg-gray-900 dark:text-white">
            </div>

            <div>
                <label for="categorie_id" class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300">
                    Catégorie
                </label>
                <select id="categorie_id"
                        name="categorie_id"
                        class="w-full rounded-lg border border-gray-300 px-3 py-3 text-sm dark:border-gray-700 dark:bg-gray-900 dark:text-white">
                    <option value="">Toutes les catégories</option>
                    @foreach($categories as $categorie)
                        <option value="{{ $categorie->id }}"
                            @selected((string) request('categorie_id') === (string) $categorie->id)>
                            {{ $categorie->nom }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div>
                <label for="statut" class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300">
                    Statut
                </label>
                <select id="statut"
                        name="statut"
                        class="w-full rounded-lg border border-gray-300 px-3 py-3 text-sm dark:border-gray-700 dark:bg-gray-900 dark:text-white">
                    <option value="">Tous les statuts</option>
                    <option value="actif" @selected(request('statut') === 'actif')>Actifs</option>
                    <option value="inactif" @selected(request('statut') === 'inactif')>Désactivés</option>
                </select>
            </div>

            <div class="flex flex-wrap gap-2 md:col-span-4">
                <button type="submit"
                        class="rounded-lg bg-green-600 px-5 py-2.5 text-sm font-medium text-white hover:bg-green-700">
                    Rechercher
                </button>
                <a href="{{ route('produits.index') }}"
                   class="rounded-lg border border-gray-300 px-5 py-2.5 text-sm font-medium text-gray-700 hover:bg-gray-50 dark:border-gray-700 dark:text-gray-300">
                    Réinitialiser
                </a>
            </div>
        </form>
    </div>

    <div class="overflow-hidden rounded-2xl border border-gray-200 bg-white dark:border-gray-800 dark:bg-white/[0.03]">

        <div class="flex flex-col gap-1 border-b border-gray-200 px-5 py-4 sm:flex-row sm:items-center sm:justify-between dark:border-gray-800">
            <h2 class="font-semibold text-gray-900 dark:text-white">Catalogue des produits</h2>
            <p class="text-sm text-gray-500 dark:text-gray-400">
                {{ $produits->total() }} produit(s)
            </p>
        </div>

        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-800">
                <thead class="bg-gray-50 dark:bg-gray-900">
                    <tr>
                        <th class="px-5 py-3 text-left text-xs font-semibold uppercase text-gray-500">Produit</th>
                        <th class="px-5 py-3 text-left text-xs font-semibold uppercase text-gray-500">Catégorie</th>
                        <th class="px-5 py-3 text-left text-xs font-semibold uppercase text-gray-500">Unité</th>
                        <th class="px-5 py-3 text-right text-xs font-semibold uppercase text-gray-500">Prix gros</th>
                        <th class="px-5 py-3 text-right text-xs font-semibold uppercase text-gray-500">Prix détail</th>
                        <th class="px-5 py-3 text-left text-xs font-semibold uppercase text-gray-500">Stock</th>
                        <th class="px-5 py-3 text-center text-xs font-semibold uppercase text-gray-500">Statut</th>
                        <th class="px-5 py-3 text-right text-xs font-semibold uppercase text-gray-500">Actions</th>
                    </tr>
                </thead>

                <tbody class="divide-y divide-gray-100 dark:divide-gray-800">
                    @forelse($produits as $produit)
                        <tr class="hover:bg-gray-50 dark:hover:bg-white/[0.02]">
                            <td class="whitespace-nowrap px-5 py-4">
                                <p class="font-medium text-gray-900 dark:text-white">
                                    {{ $produit->nom }}
                                </p>
                                <p class="mt-1 text-xs text-gray-500">
                                    Réf. : {{ $produit->reference }}
                                </p>
                            </td>

                            <td class="whitespace-nowrap px-5 py-4 text-sm text-gray-600 dark:text-gray-300">
                                {{ $produit->categorie?->nom ?? 'Sans catégorie' }}
                            </td>

                            <td class="whitespace-nowrap px-5 py-4 text-sm text-gray-600 dark:text-gray-300">
                                {{ $produit->unite }}
                            </td>

                            <td class="whitespace-nowrap px-5 py-4 text-right text-sm text-gray-700 dark:text-gray-300">
                                {{ number_format((float) $produit->prix_gros, 0, ',', ' ') }} FCFA
                            </td>

                            <td class="whitespace-nowrap px-5 py-4 text-right text-sm font-medium text-gray-900 dark:text-white">
                                {{ number_format((float) $produit->prix_detail, 0, ',', ' ') }} FCFA
                            </td>

                            <td class="whitespace-nowrap px-5 py-4 text-sm text-gray-600 dark:text-gray-300">
                                @if($produit->stock_suivi)
                                    {{ number_format((float) ($produit->quantite_disponible ?? 0), 0, ',', ' ') }}
                                    {{ $produit->unite }}
                                @else
                                    <span class="text-gray-400">Non suivi</span>
                                @endif
                            </td>

                            <td class="whitespace-nowrap px-5 py-4 text-center">
                                @if($produit->actif)
                                    <span class="rounded-full bg-green-100 px-3 py-1 text-xs font-medium text-green-700">Actif</span>
                                @else
                                    <span class="rounded-full bg-gray-100 px-3 py-1 text-xs font-medium text-gray-600">Désactivé</span>
                                @endif
                            </td>

                            <td class="whitespace-nowrap px-5 py-4 text-right text-sm">
                                <div class="flex justify-end gap-3">
                                    <a href="{{ route('produits.edit', $produit) }}"
                                       class="font-medium text-blue-600 hover:text-blue-800">
                                        Modifier
                                    </a>

                                    @if($produit->actif)
                                        <form method="POST"
                                              action="{{ route('produits.destroy', $produit) }}"
                                              onsubmit="return confirm('Désactiver ce produit ?')">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit"
                                                    class="font-medium text-red-600 hover:text-red-800">
                                                Désactiver
                                            </button>
                                        </form>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="px-5 py-12 text-center">
                                <p class="font-medium text-gray-700 dark:text-gray-300">Aucun produit trouvé</p>
                                <p class="mt-1 text-sm text-gray-500">Ajoutez un produit ou modifiez vos filtres.</p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="border-t border-gray-200 px-5 py-4 dark:border-gray-800">
            {{ $produits->links() }}
        </div>
    </div>
</div>
@endsection
