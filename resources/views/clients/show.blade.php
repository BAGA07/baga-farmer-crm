 @extends('layouts.tailadmin.app')

@section('title', $client->nom . ' - Baga Farmer')

@section('content')
<div class="space-y-6">

    {{-- En-tête --}}
    <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
        <div>
            <p class="text-sm text-gray-500 dark:text-gray-400">
                Gestion des clients / Détails
            </p>
            <h1 class="mt-1 text-2xl font-bold text-gray-900 dark:text-white">
                {{ $client->nom }}
            </h1>
            <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                Code client : {{ $client->code_client }}
            </p>
        </div>

        <div class="flex flex-wrap gap-3">
            <a href="{{ route('clients.index') }}"
               class="inline-flex items-center justify-center rounded-lg border border-gray-300 px-4 py-2.5 text-sm font-medium text-gray-700 hover:bg-gray-50 dark:border-gray-700 dark:text-gray-300 dark:hover:bg-white/[0.03]">
                Retour aux clients
            </a>

            <a href="{{ route('clients.edit', $client) }}"
               class="inline-flex items-center justify-center rounded-lg bg-brand-500 px-4 py-2.5 text-sm font-medium text-white hover:bg-brand-600">
                Modifier le client
            </a>
        </div>
    </div>

    {{-- Informations du client --}}
    <div class="rounded-2xl border border-gray-200 bg-white p-5 dark:border-gray-800 dark:bg-white/[0.03] sm:p-6">
        <h2 class="mb-5 text-lg font-semibold text-gray-900 dark:text-white">
            Informations du client
        </h2>

        <div class="grid grid-cols-1 gap-5 sm:grid-cols-2 lg:grid-cols-3">

            <div>
                <p class="text-sm text-gray-500 dark:text-gray-400">Nom complet</p>
                <p class="mt-1 font-medium text-gray-900 dark:text-white">
                    {{ $client->nom }}
                </p>
            </div>

            <div>
                <p class="text-sm text-gray-500 dark:text-gray-400">Téléphone principal</p>
                <p class="mt-1 font-medium text-gray-900 dark:text-white">
                    {{ $client->telephone ?: 'Non renseigné' }}
                </p>
            </div>

            <div>
                <p class="text-sm text-gray-500 dark:text-gray-400">Téléphone secondaire</p>
                <p class="mt-1 font-medium text-gray-900 dark:text-white">
                    {{ $client->telephone_secondaire ?: 'Non renseigné' }}
                </p>
            </div>

            <div>
                <p class="text-sm text-gray-500 dark:text-gray-400">Type de client</p>
                <p class="mt-1 font-medium text-gray-900 dark:text-white">
                    {{ $client->type_client ?: 'Non renseigné' }}
                </p>
            </div>

            <div>
                <p class="text-sm text-gray-500 dark:text-gray-400">Quartier</p>
                <p class="mt-1 font-medium text-gray-900 dark:text-white">
                    {{ $client->quartier ?: 'Non renseigné' }}
                </p>
            </div>

            <div>
                <p class="text-sm text-gray-500 dark:text-gray-400">Adresse</p>
                <p class="mt-1 font-medium text-gray-900 dark:text-white">
                    {{ $client->adresse ?: 'Non renseignée' }}
                </p>
            </div>

        </div>

        <div class="mt-5 border-t border-gray-200 pt-5 dark:border-gray-800">
            <p class="text-sm text-gray-500 dark:text-gray-400">Notes</p>
            <p class="mt-2 whitespace-pre-line text-gray-800 dark:text-gray-200">
                {{ $client->notes ?: 'Aucune note enregistrée.' }}
            </p>
        </div>
    </div>

    {{-- Historique des commandes --}}
    <div class="overflow-hidden rounded-2xl border border-gray-200 bg-white dark:border-gray-800 dark:bg-white/[0.03]">

        <div class="border-b border-gray-200 px-5 py-4 dark:border-gray-800 sm:px-6">
            <h2 class="text-lg font-semibold text-gray-900 dark:text-white">
                Historique des commandes
            </h2>
            <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                Commandes associées à ce client.
            </p>
        </div>

        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-800">
                <thead class="bg-gray-50 dark:bg-white/[0.02]">
                    <tr>
                        <th class="px-5 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">
                            Numéro
                        </th>
                        <th class="px-5 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">
                            Date
                        </th>
                        <th class="px-5 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">
                            Statut
                        </th>
                        <th class="px-5 py-3 text-right text-xs font-medium uppercase tracking-wider text-gray-500">
                            Montant
                        </th>
                    </tr>
                </thead>

                <tbody class="divide-y divide-gray-200 dark:divide-gray-800">
                    @forelse ($client->commandes as $commande)
                        @php
                            $sousTotal = $commande->lignes->sum(function ($ligne) {
                                return max(
                                    0,
                                    (float) $ligne->quantite * (float) $ligne->prix_unitaire
                                    - (float) ($ligne->remise ?? 0)
                                );
                            });

                            $montantCommande = max(
                                0,
                                $sousTotal
                                + (float) ($commande->frais_livraison ?? 0)
                                - (float) ($commande->remise ?? 0)
                            );
                        @endphp

                        <tr>
                            <td class="whitespace-nowrap px-5 py-4 text-sm font-medium text-gray-900 dark:text-white">
                                {{ $commande->numero }}
                            </td>

                            <td class="whitespace-nowrap px-5 py-4 text-sm text-gray-600 dark:text-gray-300">
                                {{ $commande->date_commande?->format('d/m/Y H:i') ?? 'Non renseignée' }}
                            </td>

                            <td class="whitespace-nowrap px-5 py-4 text-sm text-gray-600 dark:text-gray-300">
                                {{ $commande->statut ?: 'Non défini' }}
                            </td>

                            <td class="whitespace-nowrap px-5 py-4 text-right text-sm font-semibold text-gray-900 dark:text-white">
                                {{ number_format($montantCommande, 0, ',', ' ') }} FCFA
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4" class="px-5 py-10 text-center text-sm text-gray-500 dark:text-gray-400">
                                Ce client n'a pas encore de commande.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

</div>
@endsection