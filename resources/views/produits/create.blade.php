
@extends('layouts.tailadmin.app')

@section('content')
<div class="mx-auto max-w-4xl space-y-6">

    <div>
        <a href="{{ route('produits.index') }}" class="text-sm font-medium text-green-700 hover:underline">
            ← Retour au catalogue
        </a>
        <h1 class="mt-3 text-2xl font-bold text-gray-900 dark:text-white">Ajouter un produit</h1>
        <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
            Renseignez les informations du produit.
        </p>
    </div>

    @if($errors->any())
        <div class="rounded-lg border border-red-200 bg-red-50 px-4 py-3 text-red-700">
            <ul class="list-inside list-disc">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form method="POST" action="{{ route('produits.store') }}"
          class="space-y-6 rounded-2xl border border-gray-200 bg-white p-6 dark:border-gray-800 dark:bg-white/[0.03]">
        @csrf

        <div class="grid grid-cols-1 gap-5 md:grid-cols-2">

            <div>
                <label for="nom" class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300">Nom du produit *</label>
                <input id="nom" name="nom" type="text" required maxlength="150"
                       value="{{ old('nom') }}"
                       class="w-full rounded-lg border border-gray-300 px-4 py-3 text-sm dark:border-gray-700 dark:bg-gray-900 dark:text-white">
            </div>

            <div>
                <label for="reference" class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300">Référence unique *</label>
                <input id="reference" name="reference" type="text" required maxlength="30"
                       value="{{ old('reference') }}"
                       class="w-full rounded-lg border border-gray-300 px-4 py-3 text-sm dark:border-gray-700 dark:bg-gray-900 dark:text-white">
            </div>

            <div>
                <label for="categorie_id" class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300">Catégorie</label>
                <select id="categorie_id" name="categorie_id"
                        class="w-full rounded-lg border border-gray-300 px-4 py-3 text-sm dark:border-gray-700 dark:bg-gray-900 dark:text-white">
                    <option value="">Sans catégorie</option>
                    @foreach($categories as $categorie)
                        <option value="{{ $categorie->id }}" @selected(old('categorie_id') == $categorie->id)>
                            {{ $categorie->nom }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div>
                <label for="unite" class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300">Unité de vente *</label>
                <input id="unite" name="unite" type="text" required maxlength="30"
                       value="{{ old('unite', 'unité') }}"
                       placeholder="Ex. : kg, sac, pièce, caisse"
                       class="w-full rounded-lg border border-gray-300 px-4 py-3 text-sm dark:border-gray-700 dark:bg-gray-900 dark:text-white">
            </div>

            <div>
                <label for="prix_gros" class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300">Prix de gros (FCFA) *</label>
                <input id="prix_gros" name="prix_gros" type="number" required min="0" step="0.01"
                       value="{{ old('prix_gros', 0) }}"
                       class="w-full rounded-lg border border-gray-300 px-4 py-3 text-sm dark:border-gray-700 dark:bg-gray-900 dark:text-white">
            </div>

            <div>
                <label for="prix_detail" class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300">Prix de détail (FCFA) *</label>
                <input id="prix_detail" name="prix_detail" type="number" required min="0" step="0.01"
                       value="{{ old('prix_detail', 0) }}"
                       class="w-full rounded-lg border border-gray-300 px-4 py-3 text-sm dark:border-gray-700 dark:bg-gray-900 dark:text-white">
            </div>

            <div class="md:col-span-2 rounded-xl border border-gray-200 p-4 dark:border-gray-700">
                <label class="flex items-center gap-3 text-sm font-medium text-gray-700 dark:text-gray-300">
                    <input id="stock_suivi" name="stock_suivi" type="checkbox" value="1"
                           @checked(old('stock_suivi'))
                           class="h-4 w-4 rounded border-gray-300 text-green-600">
                    Suivre le stock de ce produit
                </label>

                <div id="stock-fields" class="mt-4 hidden">
                    <label for="quantite_disponible" class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300">
                        Quantité actuellement disponible
                    </label>
                    <input id="quantite_disponible" name="quantite_disponible" type="number" min="0" step="0.001"
                           value="{{ old('quantite_disponible', 0) }}"
                           class="w-full rounded-lg border border-gray-300 px-4 py-3 text-sm dark:border-gray-700 dark:bg-gray-900 dark:text-white">
                </div>
            </div>
        </div>

        <div class="flex flex-col-reverse gap-3 border-t border-gray-200 pt-5 sm:flex-row sm:justify-end dark:border-gray-800">
            <a href="{{ route('produits.index') }}"
               class="rounded-lg border border-gray-300 px-5 py-3 text-center text-sm font-medium text-gray-700 dark:border-gray-700 dark:text-gray-300">
                Annuler
            </a>
            <button type="submit"
                    class="rounded-lg bg-green-600 px-5 py-3 text-sm font-medium text-white hover:bg-green-700">
                Enregistrer le produit
            </button>
        </div>
    </form>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function () {
        const checkbox = document.getElementById('stock_suivi');
        const fields = document.getElementById('stock-fields');
        const quantity = document.getElementById('quantite_disponible');

        function updateStockFields() {
            fields.classList.toggle('hidden', !checkbox.checked);
            quantity.disabled = !checkbox.checked;
        }

        checkbox.addEventListener('change', updateStockFields);
        updateStockFields();
    });
</script>
@endsection
