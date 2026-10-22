 @extends('layouts.tailadmin.app')

@section('content')
<div class="space-y-6">

    {{-- En-tête --}}
    <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
        <div>
            <h1 class="text-2xl font-bold text-gray-800 dark:text-white">
                Gestion des paiements
            </h1>
            <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                Consultez les encaissements et suivez les règlements de vos commandes.
            </p>
        </div>

        <a href="{{ route('commandes.index') }}"
           class="inline-flex items-center justify-center gap-2 rounded-lg bg-green-600 px-4 py-2.5 text-sm font-medium text-white hover:bg-green-700">
            <span>+</span>
            Voir les commandes
        </a>
    </div>

    {{-- Message de succès --}}
    @if(session('success'))
        <div class="rounded-lg border border-green-200 bg-green-50 px-4 py-3 text-sm text-green-800">
            {{ session('success') }}
        </div>
    @endif

    {{-- Statistiques --}}
    <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 xl:grid-cols-3">

        <div class="rounded-2xl border border-gray-200 bg-white p-5 dark:border-gray-800 dark:bg-white/[0.03]">
            <p class="text-sm text-gray-500 dark:text-gray-400">Total encaissé confirmé</p>
            <p class="mt-3 text-2xl font-bold text-gray-800 dark:text-white">
                {{ number_format((float) $totalConfirme, 0, ',', ' ') }} FCFA
            </p>
            <p class="mt-2 text-xs text-gray-500">Tous les paiements confirmés</p>
        </div>

        <div class="rounded-2xl border border-gray-200 bg-white p-5 dark:border-gray-800 dark:bg-white/[0.03]">
            <p class="text-sm text-gray-500 dark:text-gray-400">Encaissé aujourd'hui</p>
            <p class="mt-3 text-2xl font-bold text-green-600">
                {{ number_format((float) $totalJour, 0, ',', ' ') }} FCFA
            </p>
            <p class="mt-2 text-xs text-gray-500">Paiements confirmés du jour</p>
        </div>

        <div class="rounded-2xl border border-gray-200 bg-white p-5 dark:border-gray-800 dark:bg-white/[0.03]">
            <p class="text-sm text-gray-500 dark:text-gray-400">Nombre de paiements confirmés</p>
            <p class="mt-3 text-2xl font-bold text-gray-800 dark:text-white">
                {{ number_format($nombrePaiements, 0, ',', ' ') }}
            </p>
            <p class="mt-2 text-xs text-gray-500">Opérations confirmées enregistrées</p>
        </div>

    </div>

    {{-- Filtres --}}
    <div class="rounded-2xl border border-gray-200 bg-white p-5 dark:border-gray-800 dark:bg-white/[0.03]">
        <h2 class="mb-4 text-base font-semibold text-gray-800 dark:text-white">
            Rechercher et filtrer
        </h2>

        <form method="GET" action="{{ route('paiements.index') }}">
            <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 xl:grid-cols-3">

                <div>
                    <label for="recherche" class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-300">
                        Recherche
                    </label>
                    <input
                        type="text"
                        id="recherche"
                        name="recherche"
                        value="{{ request('recherche') }}"
                        placeholder="Référence, commande, client ou téléphone"
                        class="w-full rounded-lg border border-gray-300 bg-transparent px-3 py-2.5 text-sm text-gray-800 outline-none focus:border-green-500 dark:border-gray-700 dark:text-white">
                </div>

                <div>
                    <label for="statut" class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-300">
                        Statut
                    </label>
                    <select
                        id="statut"
                        name="statut"
                        class="w-full rounded-lg border border-gray-300 bg-transparent px-3 py-2.5 text-sm text-gray-800 outline-none focus:border-green-500 dark:border-gray-700 dark:text-white">
                        <option value="">Tous les statuts</option>
                        <option value="confirme" @selected(request('statut') === 'confirme')>Confirmé</option>
                        <option value="en_attente" @selected(request('statut') === 'en_attente')>En attente</option>
                        <option value="annule" @selected(request('statut') === 'annule')>Annulé</option>
                    </select>
                </div>

                <div>
                    <label for="methode" class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-300">
                        Mode de paiement
                    </label>
                    <select
                        id="methode"
                        name="methode"
                        class="w-full rounded-lg border border-gray-300 bg-transparent px-3 py-2.5 text-sm text-gray-800 outline-none focus:border-green-500 dark:border-gray-700 dark:text-white">
                        <option value="">Tous les modes</option>
                        <option value="especes" @selected(request('methode') === 'especes')>Espèces</option>
                        <option value="orange_money" @selected(request('methode') === 'orange_money')>Orange Money</option>
                        <option value="moov_money" @selected(request('methode') === 'moov_money')>Moov Money</option>
                        <option value="virement" @selected(request('methode') === 'virement')>Virement</option>
                        <option value="autre" @selected(request('methode') === 'autre')>Autre</option>
                    </select>
                </div>

                <div>
                    <label for="date_debut" class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-300">
                        Du
                    </label>
                    <input
                        type="date"
                        id="date_debut"
                        name="date_debut"
                        value="{{ request('date_debut') }}"
                        class="w-full rounded-lg border border-gray-300 bg-transparent px-3 py-2.5 text-sm text-gray-800 outline-none focus:border-green-500 dark:border-gray-700 dark:text-white">
                </div>

                <div>
                    <label for="date_fin" class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-300">
                        Au
                    </label>
                    <input
                        type="date"
                        id="date_fin"
                        name="date_fin"
                        value="{{ request('date_fin') }}"
                        class="w-full rounded-lg border border-gray-300 bg-transparent px-3 py-2.5 text-sm text-gray-800 outline-none focus:border-green-500 dark:border-gray-700 dark:text-white">
                </div>

                <div class="flex flex-wrap items-end gap-2">
                    <button type="submit"
                        class="rounded-lg bg-green-600 px-4 py-2.5 text-sm font-medium text-white hover:bg-green-700">
                        Filtrer
                    </button>

                    <a href="{{ route('paiements.index') }}"
                       class="rounded-lg border border-gray-300 px-4 py-2.5 text-sm font-medium text-gray-700 hover:bg-gray-50 dark:border-gray-700 dark:text-gray-300">
                        Réinitialiser
                    </a>
                </div>

            </div>
        </form>
    </div>

    {{-- Tableau --}}
    <div class="overflow-hidden rounded-2xl border border-gray-200 bg-white dark:border-gray-800 dark:bg-white/[0.03]">
        <div class="flex flex-col gap-2 border-b border-gray-200 px-5 py-4 sm:flex-row sm:items-center sm:justify-between dark:border-gray-800">
            <div>
                <h2 class="text-lg font-semibold text-gray-800 dark:text-white">
                    Historique des paiements
                </h2>
                <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                    {{ $paiements->total() }} résultat(s)
                </p>
            </div>
        </div>

        <div class="overflow-x-auto">
            <table class="min-w-[1000px] w-full text-left text-sm">
                <thead class="bg-gray-50 text-xs uppercase text-gray-500 dark:bg-white/[0.02] dark:text-gray-400">
                    <tr>
                        <th class="px-5 py-4 font-medium">Date</th>
                        <th class="px-5 py-4 font-medium">Référence</th>
                        <th class="px-5 py-4 font-medium">Commande</th>
                        <th class="px-5 py-4 font-medium">Client</th>
                        <th class="px-5 py-4 text-right font-medium">Montant</th>
                        <th class="px-5 py-4 font-medium">Mode</th>
                        <th class="px-5 py-4 font-medium">Statut</th>
                        <th class="px-5 py-4 text-center font-medium">Détail</th>
                    </tr>
                </thead>

                <tbody class="divide-y divide-gray-100 dark:divide-gray-800">
                    @forelse($paiements as $paiement)
                        <tr class="hover:bg-gray-50 dark:hover:bg-white/[0.02]">
                            <td class="whitespace-nowrap px-5 py-4 text-gray-600 dark:text-gray-300">
                                {{ $paiement->date_paiement?->format('d/m/Y') ?? '—' }}
                            </td>

                            <td class="px-5 py-4">
                                <span class="font-medium text-gray-800 dark:text-white">
                                    {{ $paiement->reference ?: 'Sans référence' }}
                                </span>
                            </td>

                            <td class="whitespace-nowrap px-5 py-4">
                                @if($paiement->commande)
                                    <a href="{{ route('commandes.show', $paiement->commande) }}"
                                       class="font-medium text-green-700 hover:underline dark:text-green-400">
                                        {{ $paiement->commande->numero }}
                                    </a>
                                @else
                                    <span class="text-gray-400">Commande supprimée</span>
                                @endif
                            </td>

                            <td class="px-5 py-4">
                                <div class="font-medium text-gray-800 dark:text-white">
                                    {{ $paiement->commande?->client?->nom ?? 'Client indisponible' }}
                                </div>
                                @if($paiement->commande?->client?->telephone)
                                    <div class="mt-1 text-xs text-gray-500">
                                        {{ $paiement->commande->client->telephone }}
                                    </div>
                                @endif
                            </td>

                            <td class="whitespace-nowrap px-5 py-4 text-right font-semibold text-gray-800 dark:text-white">
                                {{ number_format((float) $paiement->montant, 0, ',', ' ') }} F
                            </td>

                            <td class="whitespace-nowrap px-5 py-4 text-gray-600 dark:text-gray-300">
                                @switch($paiement->methode)
                                    @case('especes') Espèces @break
                                    @case('orange_money') Orange Money @break
                                    @case('moov_money') Moov Money @break
                                    @case('virement') Virement @break
                                    @default Autre
                                @endswitch
                            </td>

                            <td class="whitespace-nowrap px-5 py-4">
                                @if($paiement->statut === 'confirme')
                                    <span class="rounded-full bg-green-100 px-2.5 py-1 text-xs font-medium text-green-700">
                                        Confirmé
                                    </span>
                                @elseif($paiement->statut === 'en_attente')
                                    <span class="rounded-full bg-yellow-100 px-2.5 py-1 text-xs font-medium text-yellow-700">
                                        En attente
                                    </span>
                                @else
                                    <span class="rounded-full bg-red-100 px-2.5 py-1 text-xs font-medium text-red-700">
                                        Annulé
                                    </span>
                                @endif
                            </td>

                            <td class="px-5 py-4 text-center">
                                @if($paiement->commande)
                                    <a href="{{ route('commandes.show', $paiement->commande) }}"
                                       class="inline-flex rounded-lg border border-gray-300 px-3 py-2 text-xs font-medium text-gray-700 hover:bg-gray-100 dark:border-gray-700 dark:text-gray-300">
                                        Voir
                                    </a>
                                @else
                                    <span class="text-gray-400">—</span>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="px-5 py-12 text-center">
                                <p class="font-medium text-gray-700 dark:text-gray-300">
                                    Aucun paiement trouvé
                                </p>
                                <p class="mt-1 text-sm text-gray-500">
                                    Modifiez les filtres ou enregistrez un paiement depuis une commande.
                                </p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{-- Pagination --}}
        @if($paiements->hasPages())
            <div class="border-t border-gray-200 px-5 py-4 dark:border-gray-800">
                {{ $paiements->links() }}
            </div>
        @endif
    </div>

</div>
@endsection