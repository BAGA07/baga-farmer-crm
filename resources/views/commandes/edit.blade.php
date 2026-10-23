
@extends('layouts.tailadmin.app')

@section('content')
<div class="mx-auto max-w-7xl space-y-6">

    <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
        <div>
            <h1 class="text-2xl font-bold text-gray-800 dark:text-white">
                Modifier la commande {{ $commande->numero }}
            </h1>
            <p class="mt-1 text-sm text-gray-500">
                Modifiez les informations et les produits de cette commande.
            </p>
        </div>

        <a href="{{ route('commandes.show', $commande) }}"
           class="rounded-lg border border-gray-300 px-4 py-2 text-center text-sm font-medium text-gray-700 hover:bg-gray-50">
            Retour au détail
        </a>
    </div>

    @if ($errors->any())
        <div class="rounded-lg border border-red-200 bg-red-50 p-4 text-red-800">
            <p class="font-semibold">Veuillez corriger les erreurs :</p>
            <ul class="mt-2 list-inside list-disc">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    @if ($clients->isEmpty() || $produits->isEmpty())
        <div class="rounded-xl border border-yellow-200 bg-yellow-50 p-5 text-yellow-900">
            Impossible de modifier cette commande : il faut au moins un client actif et un produit actif.
            <a href="{{ route('commandes.index') }}" class="font-semibold underline">
                Retour aux commandes
            </a>
        </div>
    @else

    @php
        $lignesInitiales = old('produits');

        if (!is_array($lignesInitiales)) {
            $lignesInitiales = $commande->lignes->map(function ($ligne) {
                return [
                    'produit_id' => $ligne->produit_id,
                    'quantite' => $ligne->quantite,
                    'remise' => $ligne->remise,
                ];
            })->values()->all();
        }
    @endphp

    <form action="{{ route('commandes.update', $commande) }}"
          method="POST"
          id="commande-form"
          class="space-y-6">
        @csrf
        @method('PUT')

        <div class="rounded-xl border border-gray-200 bg-white p-5 dark:border-gray-800 dark:bg-white/[0.03]">
            <h2 class="mb-5 text-lg font-semibold text-gray-800 dark:text-white">
                Informations générales
            </h2>

            <div class="grid grid-cols-1 gap-5 md:grid-cols-2">

                <div>
                    <label for="client_id" class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300">
                        Client *
                    </label>
                    <select id="client_id" name="client_id" required
                            class="w-full rounded-lg border border-gray-300 bg-transparent px-4 py-3 text-sm dark:border-gray-700 dark:text-white">
                        <option value="">Sélectionner un client</option>
                        @foreach ($clients as $client)
                            <option value="{{ $client->id }}"
                                    data-type="{{ $client->type_client }}"
                                    @selected((string) old('client_id', $commande->client_id) === (string) $client->id)>
                                {{ $client->nom }} — {{ $client->telephone }}
                                ({{ ucfirst($client->type_client) }})
                            </option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label for="date_commande" class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300">
                        Date de commande *
                    </label>
                    <input id="date_commande" name="date_commande"
                           type="datetime-local" required
                           value="{{ old('date_commande', $commande->date_commande ? \Carbon\Carbon::parse($commande->date_commande)->format('Y-m-d\TH:i') : '') }}"
                           class="w-full rounded-lg border border-gray-300 bg-transparent px-4 py-3 text-sm dark:border-gray-700 dark:text-white">
                </div>

                <div>
                    <label for="date_livraison_prevue" class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300">
                        Livraison prévue
                    </label>
                    <input id="date_livraison_prevue" name="date_livraison_prevue"
                           type="datetime-local"
                           value="{{ old('date_livraison_prevue', $commande->date_livraison_prevue ? \Carbon\Carbon::parse($commande->date_livraison_prevue)->format('Y-m-d\TH:i') : '') }}"
                           class="w-full rounded-lg border border-gray-300 bg-transparent px-4 py-3 text-sm dark:border-gray-700 dark:text-white">
                </div>

                <div>
                    <label for="adresse_livraison" class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300">
                        Adresse de livraison
                    </label>
                    <input id="adresse_livraison" name="adresse_livraison"
                           maxlength="2000"
                           value="{{ old('adresse_livraison', $commande->adresse_livraison) }}"
                           class="w-full rounded-lg border border-gray-300 bg-transparent px-4 py-3 text-sm dark:border-gray-700 dark:text-white">
                </div>

            </div>
        </div>

        <div class="rounded-xl border border-gray-200 bg-white p-5 dark:border-gray-800 dark:bg-white/[0.03]">
            <div class="mb-5 flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                <h2 class="text-lg font-semibold text-gray-800 dark:text-white">
                    Produits commandés
                </h2>
                <button type="button" id="ajouter-produit"
                        class="rounded-lg bg-green-600 px-4 py-2.5 text-sm font-medium text-white hover:bg-green-700">
                    + Ajouter un produit
                </button>
            </div>

            <p id="type-prix" class="mb-4 text-sm text-gray-500"></p>
            <div id="lignes-produits" class="space-y-4"></div>

            <p id="erreur-produits" class="mt-3 hidden text-sm text-red-600">
                Ajoutez au moins un produit.
            </p>
        </div>

        <div class="grid grid-cols-1 gap-6 lg:grid-cols-2">

            <div class="rounded-xl border border-gray-200 bg-white p-5 dark:border-gray-800 dark:bg-white/[0.03]">
                <h2 class="mb-5 text-lg font-semibold text-gray-800 dark:text-white">
                    Livraison et remise
                </h2>

                <div class="space-y-4">
                    <div>
                        <label for="frais_livraison" class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300">
                            Frais de livraison (FCFA)
                        </label>
                        <input id="frais_livraison" name="frais_livraison"
                               type="number" min="0" step="0.01" required
                               value="{{ old('frais_livraison', $commande->frais_livraison) }}"
                               class="w-full rounded-lg border border-gray-300 bg-transparent px-4 py-3 text-sm dark:border-gray-700 dark:text-white">
                    </div>

                    <div>
                        <label for="remise" class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300">
                            Remise globale (FCFA)
                        </label>
                        <input id="remise" name="remise"
                               type="number" min="0" step="0.01" required
                               value="{{ old('remise', $commande->remise) }}"
                               class="w-full rounded-lg border border-gray-300 bg-transparent px-4 py-3 text-sm dark:border-gray-700 dark:text-white">
                    </div>

                    <div>
                        <label for="notes" class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300">
                            Notes
                        </label>
                        <textarea id="notes" name="notes" rows="3" maxlength="5000"
                                  class="w-full rounded-lg border border-gray-300 bg-transparent px-4 py-3 text-sm dark:border-gray-700 dark:text-white">{{ old('notes', $commande->notes) }}</textarea>
                    </div>
                </div>
            </div>

            <div class="rounded-xl border border-gray-200 bg-white p-5 dark:border-gray-800 dark:bg-white/[0.03]">
                <h2 class="mb-5 text-lg font-semibold text-gray-800 dark:text-white">
                    Récapitulatif estimatif
                </h2>

                <div class="space-y-4 text-sm">
                    <div class="flex justify-between gap-4">
                        <span class="text-gray-500">Sous-total des produits</span>
                        <span id="sous-total" class="font-medium">0 FCFA</span>
                    </div>
                    <div class="flex justify-between gap-4">
                        <span class="text-gray-500">Frais de livraison</span>
                        <span id="afficher-livraison">0 FCFA</span>
                    </div>
                    <div class="flex justify-between gap-4">
                        <span class="text-gray-500">Remise globale</span>
                        <span id="afficher-remise">0 FCFA</span>
                    </div>
                    <div class="border-t border-gray-200 pt-4 dark:border-gray-700">
                        <div class="flex items-center justify-between gap-4">
                            <span class="text-base font-semibold text-gray-800 dark:text-white">
                                Total de la commande
                            </span>
                            <span id="total-commande" class="text-xl font-bold text-green-700">
                                0 FCFA
                            </span>
                        </div>
                    </div>
                </div>
                <p class="mt-4 text-xs text-gray-500">
                    Le serveur recalcule les prix lors de l'enregistrement.
                </p>
            </div>

        </div>

        <div class="flex flex-col-reverse gap-3 sm:flex-row sm:justify-end">
            <a href="{{ route('commandes.show', $commande) }}"
               class="rounded-lg border border-gray-300 px-5 py-3 text-center text-sm font-medium text-gray-700 hover:bg-gray-50">
                Annuler
            </a>
            <button type="submit"
                    class="rounded-lg bg-green-600 px-6 py-3 text-sm font-semibold text-white hover:bg-green-700">
                Enregistrer les modifications
            </button>
        </div>

    </form>
    @endif
</div>

@if ($clients->isNotEmpty() && $produits->isNotEmpty())
<script>
document.addEventListener('DOMContentLoaded', function () {
    const clientSelect = document.getElementById('client_id');
    const lignesContainer = document.getElementById('lignes-produits');
    const fraisInput = document.getElementById('frais_livraison');
    const remiseGlobaleInput = document.getElementById('remise');

    const produits = {{ Illuminate\Support\Js::from(
        $produits->map(function ($p) {
            return [
                'id' => $p->id,
                'nom' => $p->nom,
                'unite' => $p->unite,
                'prix_gros' => (float) $p->prix_gros,
                'prix_detail' => (float) $p->prix_detail,
                'stock_suivi' => (bool) $p->stock_suivi,
                'quantite_disponible' => $p->quantite_disponible === null
                    ? null
                    : (float) $p->quantite_disponible,
            ];
        })->values()
    ) }};

    const lignesInitiales = {{ Illuminate\Support\Js::from($lignesInitiales) }};
    let prochainIndex = 0;

    function montant(valeur) {
        return new Intl.NumberFormat('fr-FR', {
            maximumFractionDigits: 2
        }).format(valeur || 0) + ' FCFA';
    }

    function typeClient() {
        const option = clientSelect.options[clientSelect.selectedIndex];
        return option ? (option.dataset.type || 'particulier').toLowerCase() : 'particulier';
    }

    function prixProduit(produit) {
        return ['gros', 'grossiste', 'revendeur'].includes(typeClient())
            ? produit.prix_gros
            : produit.prix_detail;
    }

    function mettreAJourPrix() {
        const typeGros = ['gros', 'grossiste', 'revendeur'].includes(typeClient());

        document.getElementById('type-prix').textContent =
            'Tarif appliqué : prix de ' + (typeGros ? 'gros' : 'détail') + '.';

        lignesContainer.querySelectorAll('.ligne-produit').forEach(ligne => {
            const select = ligne.querySelector('.produit-select');
            const produit = produits.find(p => String(p.id) === select.value);

            ligne.querySelector('.prix-affiche').textContent =
                produit ? montant(prixProduit(produit)) : '0 FCFA';

            calculerTotal();
        });
    }

    function ajouterLigne(valeurs = {}) {
        const index = prochainIndex++;
        const ligne = document.createElement('div');

        ligne.className =
            'ligne-produit rounded-lg border border-gray-200 p-4 dark:border-gray-700';

        ligne.innerHTML = `
            <div class="mb-4 flex items-center justify-between gap-3">
                <span class="font-medium text-gray-800 dark:text-white">Produit</span>
                <button type="button"
                        class="supprimer-ligne text-sm font-medium text-red-600 hover:text-red-800">
                    Retirer
                </button>
            </div>

            <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
                <div>
                    <label class="mb-2 block text-sm text-gray-600 dark:text-gray-300">
                        Produit *
                    </label>
                    <select name="produits[${index}][produit_id]" required
                            class="produit-select w-full rounded-lg border border-gray-300 bg-transparent px-3 py-3 text-sm dark:border-gray-700 dark:text-white">
                        <option value="">Sélectionner un produit</option>
                        ${produits.map(p => `
                            <option value="${p.id}">
                                ${p.nom} (${p.unite})
                            </option>
                        `).join('')}
                    </select>
                    <p class="stock-info mt-1 text-xs text-gray-500"></p>
                </div>

                <div>
                    <label class="mb-2 block text-sm text-gray-600 dark:text-gray-300">
                        Prix unitaire
                    </label>
                    <div class="rounded-lg bg-gray-50 px-3 py-3 text-sm dark:bg-white/[0.04]">
                        <span class="prix-affiche">0 FCFA</span>
                    </div>
                </div>

                <div>
                    <label class="mb-2 block text-sm text-gray-600 dark:text-gray-300">
                        Quantité *
                    </label>
                    <input name="produits[${index}][quantite]"
                           type="number" min="0.001" step="0.001"
                           required
                           class="quantite-input w-full rounded-lg border border-gray-300 bg-transparent px-3 py-3 text-sm dark:border-gray-700 dark:text-white">
                </div>

                <div>
                    <label class="mb-2 block text-sm text-gray-600 dark:text-gray-300">
                        Remise sur cette ligne (FCFA)
                    </label>
                    <input name="produits[${index}][remise]"
                           type="number" min="0" step="0.01"
                           class="remise-ligne w-full rounded-lg border border-gray-300 bg-transparent px-3 py-3 text-sm dark:border-gray-700 dark:text-white">
                </div>
            </div>

            <div class="mt-4 flex justify-between border-t border-gray-100 pt-3 text-sm dark:border-gray-700">
                <span class="text-gray-500">Total de la ligne</span>
                <strong class="total-ligne text-gray-800 dark:text-white">0 FCFA</strong>
            </div>
        `;

        const select = ligne.querySelector('.produit-select');
        const quantite = ligne.querySelector('.quantite-input');
        const remise = ligne.querySelector('.remise-ligne');

        select.value = valeurs.produit_id ?? '';
        quantite.value = valeurs.quantite ?? 1;
        remise.value = valeurs.remise ?? 0;

        lignesContainer.appendChild(ligne);

        function mettreAJourLigne() {
            const produit = produits.find(p => String(p.id) === select.value);

            ligne.querySelector('.prix-affiche').textContent =
                produit ? montant(prixProduit(produit)) : '0 FCFA';

            ligne.querySelector('.stock-info').textContent =
                produit && produit.stock_suivi
                    ? 'Stock disponible : ' + (produit.quantite_disponible ?? 'non renseigné') + ' ' + produit.unite
                    : '';

            calculerTotal();
        }

        select.addEventListener('change', mettreAJourLigne);
        quantite.addEventListener('input', calculerTotal);
        remise.addEventListener('input', calculerTotal);

        ligne.querySelector('.supprimer-ligne').addEventListener('click', function () {
            ligne.remove();
            calculerTotal();
        });

        mettreAJourLigne();
    }

    function calculerTotal() {
        let sousTotal = 0;

        lignesContainer.querySelectorAll('.ligne-produit').forEach(ligne => {
            const produit = produits.find(
                p => String(p.id) === ligne.querySelector('.produit-select').value
            );

            const quantite = Math.max(
                0,
                parseFloat(ligne.querySelector('.quantite-input').value) || 0
            );

            const remise = Math.max(
                0,
                parseFloat(ligne.querySelector('.remise-ligne').value) || 0
            );

            const prix = produit ? prixProduit(produit) : 0;
            const totalLigne = Math.max(0, quantite * prix - remise);

            ligne.querySelector('.total-ligne').textContent = montant(totalLigne);
            sousTotal += totalLigne;
        });

        const frais = Math.max(0, parseFloat(fraisInput.value) || 0);
        const remiseGlobale = Math.max(
            0,
            parseFloat(remiseGlobaleInput.value) || 0
        );

        document.getElementById('sous-total').textContent = montant(sousTotal);
        document.getElementById('afficher-livraison').textContent = montant(frais);
        document.getElementById('afficher-remise').textContent = montant(remiseGlobale);
        document.getElementById('total-commande').textContent =
            montant(Math.max(0, sousTotal + frais - remiseGlobale));
    }

    clientSelect.addEventListener('change', mettreAJourPrix);
    fraisInput.addEventListener('input', calculerTotal);
    remiseGlobaleInput.addEventListener('input', calculerTotal);

    document.getElementById('ajouter-produit').addEventListener('click', function () {
        ajouterLigne();
    });

    lignesInitiales.forEach(ligne => ajouterLigne(ligne));

    if (lignesInitiales.length === 0) {
        ajouterLigne();
    }

    document.getElementById('commande-form').addEventListener('submit', function (event) {
        if (lignesContainer.querySelectorAll('.ligne-produit').length === 0) {
            event.preventDefault();
            document.getElementById('erreur-produits').classList.remove('hidden');
        }
    });

    mettreAJourPrix();
});
</script>
@endif
@endsection
