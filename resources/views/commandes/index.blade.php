
@extends('layouts.tailadmin.app')

@section('content')
<div class="space-y-6">

    <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
        <div>
            <h1 class="text-2xl font-bold text-gray-800 dark:text-white">
                Commandes
            </h1>
            <p class="mt-1 text-sm text-gray-500">
                Gérez les commandes et suivez leur évolution.
            </p>
        </div>

        <a href="{{ route('commandes.create') }}"
           class="inline-flex items-center justify-center rounded-lg bg-green-600 px-5 py-3 text-sm font-medium text-white hover:bg-green-700">
            + Nouvelle commande
        </a>
    </div>

    @if (session('success'))
        <div class="rounded-lg border border-green-200 bg-green-50 p-4 text-green-800">
            {{ session('success') }}
        </div>
    @endif

    @if (session('error'))
        <div class="rounded-lg border border-red-200 bg-red-50 p-4 text-red-800">
            {{ session('error') }}
        </div>
    @endif

    @if ($errors->any())
        <div class="rounded-lg border border-red-200 bg-red-50 p-4 text-red-800">
            <p class="font-semibold">Une erreur est survenue :</p>
            <ul class="mt-2 list-inside list-disc">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <div class="overflow-hidden rounded-xl border border-gray-200 bg-white dark:border-gray-800 dark:bg-white/[0.03]">

        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200 text-sm dark:divide-gray-800">

                <thead class="bg-gray-50 dark:bg-white/[0.02]">
                    <tr>
                        <th class="px-5 py-4 text-left font-medium text-gray-500">N° commande</th>
                        <th class="px-5 py-4 text-left font-medium text-gray-500">Client</th>
                        <th class="px-5 py-4 text-left font-medium text-gray-500">Date</th>
                        <th class="px-5 py-4 text-center font-medium text-gray-500">Produits</th>
                        <th class="px-5 py-4 text-right font-medium text-gray-500">Total</th>
                        <th class="px-5 py-4 text-center font-medium text-gray-500">Statut</th>
                        <th class="px-5 py-4 text-center font-medium text-gray-500">Action</th>
                    </tr>
                </thead>

                <tbody class="divide-y divide-gray-100 dark:divide-gray-800">

                    @forelse ($commandes as $commande)
                        @php
                            $sousTotal = $commande->lignes->sum(
                                fn ($ligne) =>
                                    (float) $ligne->quantite
                                    * (float) $ligne->prix_unitaire
                                    - (float) $ligne->remise
                            );

                            $total = max(
                                0,
                                $sousTotal
                                + (float) $commande->frais_livraison
                                - (float) $commande->remise
                            );

                            $statuts = [
                                'recue' => 'Reçue',
                                'confirmee' => 'Confirmée',
                                'en_preparation' => 'En préparation',
                                'livree' => 'Livrée',
                                'annulee' => 'Annulée',
                            ];

                            $couleurs = [
                                'recue' => 'bg-blue-100 text-blue-700',
                                'confirmee' => 'bg-indigo-100 text-indigo-700',
                                'en_preparation' => 'bg-yellow-100 text-yellow-800',
                                'livree' => 'bg-green-100 text-green-700',
                                'annulee' => 'bg-red-100 text-red-700',
                            ];
                        @endphp

                        <tr class="hover:bg-gray-50 dark:hover:bg-white/[0.02]">

                            <td class="whitespace-nowrap px-5 py-4 font-medium text-gray-800 dark:text-white">
                                {{ $commande->numero }}
                            </td>

                            <td class="px-5 py-4 text-gray-700 dark:text-gray-300">
                                {{ $commande->client?->nom ?? 'Client supprimé' }}
                            </td>

                            <td class="whitespace-nowrap px-5 py-4 text-gray-600 dark:text-gray-400">
                                {{ $commande->date_commande?->format('d/m/Y H:i') }}
                            </td>

                            <td class="px-5 py-4 text-center text-gray-700 dark:text-gray-300">
                                {{ $commande->lignes->count() }}
                            </td>

                            <td class="whitespace-nowrap px-5 py-4 text-right font-semibold text-gray-800 dark:text-white">
                                {{ number_format($total, 0, ',', ' ') }} FCFA
                            </td>

                            <td class="px-5 py-4 text-center">
                                <span class="inline-flex rounded-full px-3 py-1 text-xs font-medium
                                    {{ $couleurs[$commande->statut] ?? 'bg-gray-100 text-gray-700' }}">
                                    {{ $statuts[$commande->statut] ?? ucfirst(str_replace('_', ' ', $commande->statut)) }}
                                </span>
                            </td>

                            
<td class="whitespace-nowrap px-5 py-4">
    <div class="flex flex-wrap items-center justify-center gap-3">

        <a href="{{ route('commandes.show', ['commande' => $commande->id]) }}"
           class="font-medium text-green-700 hover:text-green-900">
            Voir le détail
        </a>

        <a href="{{ route('commandes.edit', $commande) }}"
           class="font-medium text-blue-600 hover:text-blue-800">
            Modifier
        </a>

        <form action="{{ route('commandes.destroy', $commande) }}"
              method="POST"
              onsubmit="return confirm('Voulez-vous vraiment supprimer cette commande ? Cette action est irréversible.');">
            @csrf
            @method('DELETE')

            <button type="submit"
                    class="font-medium text-red-600 hover:text-red-800">
                Supprimer
            </button>
        </form>

    </div>
</td>


                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="px-5 py-12 text-center">
                                <p class="font-medium text-gray-700 dark:text-white">
                                    Aucune commande enregistrée
                                </p>
                                <p class="mt-1 text-gray-500">
                                    Créez votre première commande pour commencer.
                                </p>
                            </td>
                        </tr>
                    @endforelse

                </tbody>
            </table>
        </div>

        <div class="border-t border-gray-200 px-5 py-4 dark:border-gray-800">
            {{ $commandes->links() }}
        </div>

    </div>
</div>
@endsection
