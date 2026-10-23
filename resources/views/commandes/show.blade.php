
@extends('layouts.tailadmin.app')

@section('content')

@if (session('success'))
    <div class="mb-6 flex items-center gap-3 rounded-xl border border-green-200 bg-green-50 p-4 text-sm font-medium text-green-800">
        <svg class="h-5 w-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                  d="M5 13l4 4L19 7" />
        </svg>
        {{ session('success') }}
    </div>
@endif

@if (session('error'))
    <div class="mb-6 rounded-xl border border-red-200 bg-red-50 p-4 text-sm font-medium text-red-800">
        {{ session('error') }}
    </div>
@endif

@php
    $sousTotal = $commande->lignes->sum(function ($ligne) {
        return ((float) $ligne->quantite * (float) $ligne->prix_unitaire)
            - (float) $ligne->remise;
    });

    $fraisLivraison = (float) $commande->frais_livraison;
    $remiseGlobale = (float) $commande->remise;
    $total = max(0, $sousTotal + $fraisLivraison - $remiseGlobale);

    $statuts = [
        'recue' => 'Reçue',
        'confirmee' => 'Confirmée',
        'en_preparation' => 'En préparation',
        'livree' => 'Livrée',
        'annulee' => 'Annulée',
    ];

    $messageWhatsapp = "BAGA FARMER\n";
    $messageWhatsapp .= "RECU DE COMMANDE\n\n";
    $messageWhatsapp .= "Commande : " . $commande->numero . "\n";
    $messageWhatsapp .= "Client : " . $commande->client->nom . "\n";
    $messageWhatsapp .= "Date : " . \Carbon\Carbon::parse($commande->date_commande)->format('d/m/Y') . "\n\n";
    $messageWhatsapp .= "DETAILS DE LA COMMANDE\n";

    foreach ($commande->lignes as $ligne) {
        $montantLigne = ((float) $ligne->quantite * (float) $ligne->prix_unitaire)
            - (float) $ligne->remise;

        $messageWhatsapp .= "- " . $ligne->nom_produit
            . " : " . rtrim(rtrim(number_format((float) $ligne->quantite, 3, ',', ' '), '0'), ',')
            . " " . $ligne->unite
            . " x " . number_format((float) $ligne->prix_unitaire, 0, ',', ' ')
            . " FCFA = " . number_format($montantLigne, 0, ',', ' ')
            . " FCFA\n";
    }

    $messageWhatsapp .= "\nSous-total : " . number_format($sousTotal, 0, ',', ' ') . " FCFA\n";
    $messageWhatsapp .= "Livraison : " . number_format($fraisLivraison, 0, ',', ' ') . " FCFA\n";
    $messageWhatsapp .= "Remise globale : " . number_format($remiseGlobale, 0, ',', ' ') . " FCFA\n";
    $messageWhatsapp .= "TOTAL : " . number_format($total, 0, ',', ' ') . " FCFA\n\n";
    $messageWhatsapp .= "Merci pour votre confiance !\nBaga Farmer";

    $urlWhatsapp = 'https://wa.me/?text=' . urlencode($messageWhatsapp);

    
    // Paiements confirmés et solde restant
    $paiementsConfirmes = $commande->paiements
        ->where('statut', 'confirme');

    $montantPaye = (float) $paiementsConfirmes->sum('montant');

    $resteAPayer = max(0, $total - $montantPaye);

    if ($resteAPayer <= 0) {
        $etatPaiement = 'Payée';
    } elseif ($montantPaye > 0) {
        $etatPaiement = 'Partiellement payée';
    } else {
        $etatPaiement = 'Impayée';
    }

@endphp

<div class="mx-auto max-w-7xl space-y-6">

    {{-- En-tête et actions --}}
    <div class="no-print flex flex-col justify-between gap-4 sm:flex-row sm:items-center">
        <div>
            <a href="{{ route('commandes.index') }}"
               class="mb-2 inline-flex items-center gap-2 text-sm text-gray-500 hover:text-green-600 dark:text-gray-400">
                ← Retour aux commandes
            </a>

            <h1 class="text-2xl font-bold text-gray-900 dark:text-white">
                Commande {{ $commande->numero }}
            </h1>

            <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                Consultez les détails, imprimez le reçu ou partagez-le sur WhatsApp.
            </p>
        </div>

        <div class="flex flex-wrap gap-3">
            <button type="button"
                    onclick="window.print()"
                    class="inline-flex items-center justify-center gap-2 rounded-lg border border-gray-300 bg-white px-4 py-2.5 text-sm font-medium text-gray-700 shadow-sm hover:bg-gray-50 dark:border-gray-600 dark:bg-gray-800 dark:text-gray-200 dark:hover:bg-gray-700">
                🖨️ Imprimer le reçu
            </button>

            <a href="{{ $urlWhatsapp }}"
               target="_blank"
               rel="noopener noreferrer"
               class="inline-flex items-center justify-center gap-2 rounded-lg bg-green-600 px-4 py-2.5 text-sm font-semibold text-white shadow-sm hover:bg-green-700">
                Envoyer par WhatsApp
            </a>
        </div>
    </div>

    {{-- Reçu imprimable --}}
    <div id="ticket-impression"
         class="rounded-2xl border border-gray-200 bg-white p-5 shadow-sm dark:border-gray-700 dark:bg-gray-900 sm:p-8">

        {{-- Identité de l'entreprise --}}
        <div class="flex flex-col justify-between gap-4 border-b border-dashed border-gray-300 pb-6 sm:flex-row">
            <div>
                <h2 class="text-2xl font-extrabold tracking-wide text-green-700">
                    BAGA FARMER
                </h2>
                <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                    Production et commercialisation agricoles
                </p>
                <p class="mt-2 text-xs text-gray-500 dark:text-gray-400">
                    Reçu de commande
                </p>
            </div>

            <div class="sm:text-right">
                <p class="text-xs font-semibold uppercase tracking-wider text-gray-500 dark:text-gray-400">
                    Numéro de commande
                </p>
                <p class="mt-1 text-lg font-bold text-gray-900 dark:text-white">
                    {{ $commande->numero }}
                </p>
                <p class="mt-1 text-sm text-gray-600 dark:text-gray-300">
                    Date :
                    {{ \Carbon\Carbon::parse($commande->date_commande)->format('d/m/Y') }}
                </p>
                @if($commande->date_livraison_prevue)
                    <p class="mt-1 text-sm text-gray-600 dark:text-gray-300">
                        Livraison prévue :
                        {{ \Carbon\Carbon::parse($commande->date_livraison_prevue)->format('d/m/Y') }}
                    </p>
                @endif
            </div>
        </div>

        

        {{-- Informations client et statut --}}
        <div class="grid grid-cols-1 gap-5 border-b border-gray-200 py-6 dark:border-gray-700 sm:grid-cols-2">
            <div>
                <h3 class="mb-2 text-xs font-bold uppercase tracking-wider text-gray-500 dark:text-gray-400">
                    Informations du client
                </h3>

                <p class="font-semibold text-gray-900 dark:text-white">
                    {{ $commande->client->nom }}
                </p>

                @if($commande->client->telephone)
                    <p class="mt-1 text-sm text-gray-600 dark:text-gray-300">
                        Téléphone : {{ $commande->client->telephone }}
                    </p>
                @endif

                @if($commande->adresse_livraison)
                    <p class="mt-1 text-sm text-gray-600 dark:text-gray-300">
                        Livraison : {{ $commande->adresse_livraison }}
                    </p>
                @elseif($commande->client->adresse)
                    <p class="mt-1 text-sm text-gray-600 dark:text-gray-300">
                        Adresse : {{ $commande->client->adresse }}
                    </p>
                @endif
            </div>

            <div class="sm:text-right">
                <h3 class="mb-2 text-xs font-bold uppercase tracking-wider text-gray-500 dark:text-gray-400">
                    Statut de la commande
                </h3>

                <span class="inline-flex rounded-full bg-green-100 px-3 py-1 text-sm font-semibold text-green-800">
                    {{ $statuts[$commande->statut] ?? ucfirst(str_replace('_', ' ', $commande->statut)) }}
                </span>
            </div>
        </div>

        {{-- Tableau des produits --}}
        <div class="py-6">
            <h3 class="mb-4 text-sm font-bold uppercase tracking-wider text-gray-700 dark:text-gray-200">
                Détail des produits
            </h3>

            <div class="overflow-x-auto">
                <table class="w-full text-left text-sm">
                    <thead>
                        <tr class="border-y border-gray-200 bg-gray-50 text-xs uppercase text-gray-600 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-300">
                            <th class="px-3 py-3">Produit</th>
                            <th class="px-3 py-3 text-right">Quantité</th>
                            <th class="px-3 py-3 text-right">Prix unitaire</th>
                            <th class="px-3 py-3 text-right">Remise</th>
                            <th class="px-3 py-3 text-right">Montant</th>
                        </tr>
                    </thead>

                    <tbody class="divide-y divide-gray-100 dark:divide-gray-800">
                        @foreach($commande->lignes as $ligne)
                            @php
                                $montantBrut = (float) $ligne->quantite * (float) $ligne->prix_unitaire;
                                $montantLigne = $montantBrut - (float) $ligne->remise;
                            @endphp

                            <tr>
                                <td class="px-3 py-4 font-medium text-gray-900 dark:text-white">
                                    {{ $ligne->nom_produit }}
                                </td>

                                <td class="px-3 py-4 text-right text-gray-700 dark:text-gray-300">
                                    {{ rtrim(rtrim(number_format((float) $ligne->quantite, 3, ',', ' '), '0'), ',') }}
                                    {{ $ligne->unite }}
                                </td>

                                <td class="px-3 py-4 text-right text-gray-700 dark:text-gray-300">
                                    {{ number_format((float) $ligne->prix_unitaire, 0, ',', ' ') }}
                                </td>

                                <td class="px-3 py-4 text-right text-gray-700 dark:text-gray-300">
                                    {{ number_format((float) $ligne->remise, 0, ',', ' ') }}
                                </td>

                                <td class="px-3 py-4 text-right font-semibold text-gray-900 dark:text-white">
                                    {{ number_format($montantLigne, 0, ',', ' ') }} FCFA
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>

        {{-- Totaux --}}
        <div class="ml-auto max-w-md space-y-3 border-t border-gray-200 pt-5 dark:border-gray-700">
            <div class="flex justify-between gap-4 text-sm">
                <span class="text-gray-600 dark:text-gray-300">Sous-total</span>
                <span class="font-medium text-gray-900 dark:text-white">
                    {{ number_format($sousTotal, 0, ',', ' ') }} FCFA
                </span>
            </div>

            <div class="flex justify-between gap-4 text-sm">
                <span class="text-gray-600 dark:text-gray-300">Frais de livraison</span>
                <span class="font-medium text-gray-900 dark:text-white">
                    {{ number_format($fraisLivraison, 0, ',', ' ') }} FCFA
                </span>
            </div>

            <div class="flex justify-between gap-4 text-sm">
                <span class="text-gray-600 dark:text-gray-300">Remise globale</span>
                <span class="font-medium text-gray-900 dark:text-white">
                    − {{ number_format($remiseGlobale, 0, ',', ' ') }} FCFA
                </span>
            </div>

            <div class="flex justify-between gap-4 rounded-lg bg-green-50 p-4 dark:bg-green-950">
                <span class="font-bold text-green-800 dark:text-green-300">
                    TOTAL À PAYER
                </span>
                <span class="text-lg font-extrabold text-green-800 dark:text-green-300">
                    {{ number_format($total, 0, ',', ' ') }} FCFA
                </span>
            </div>
        </div>

        {{-- Notes --}}
        @if($commande->notes)
            <div class="mt-6 border-t border-dashed border-gray-300 pt-5 dark:border-gray-700">
                <h3 class="mb-2 text-xs font-bold uppercase tracking-wider text-gray-500 dark:text-gray-400">
                    Notes
                </h3>
                <p class="whitespace-pre-line text-sm text-gray-700 dark:text-gray-300">
                    {{ $commande->notes }}
                </p>
            </div>
        @endif

        {{-- Pied du reçu --}}
        <div class="mt-8 border-t border-dashed border-gray-300 pt-5 text-center dark:border-gray-700">
            <p class="font-semibold text-gray-800 dark:text-white">
                Merci pour votre confiance !
            </p>
            <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                Baga Farmer — Votre partenaire agricole
            </p>
            <p class="mt-4 text-xs text-gray-400">
                Document généré le {{ now()->format('d/m/Y à H:i') }}
            </p>
        </div>
    </div>
</div>


{{-- Gestion des paiements --}}
<div class="no-print mt-6 space-y-6 rounded-2xl border border-gray-200 bg-white p-5 shadow-sm dark:border-gray-700 dark:bg-gray-900 sm:p-8">

    <div class="flex flex-col justify-between gap-3 sm:flex-row sm:items-center">
        <div>
            <h2 class="text-xl font-bold text-gray-900 dark:text-white">
                Paiements de la commande
            </h2>
            <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                Suivez les règlements et le solde de votre client.
            </p>
        </div>

        <span class="inline-flex w-fit rounded-full px-3 py-1 text-sm font-semibold
            {{ $resteAPayer <= 0 ? 'bg-green-100 text-green-800' : ($montantPaye > 0 ? 'bg-yellow-100 text-yellow-800' : 'bg-red-100 text-red-800') }}">
            {{ $etatPaiement }}
        </span>
    </div>

    {{-- Résumé financier --}}
    <div class="grid grid-cols-1 gap-4 sm:grid-cols-3">
        <div class="rounded-xl bg-gray-50 p-4 dark:bg-gray-800">
            <p class="text-sm text-gray-500 dark:text-gray-400">Total commande</p>
            <p class="mt-2 text-lg font-bold text-gray-900 dark:text-white">
                {{ number_format($total, 0, ',', ' ') }} FCFA
            </p>
        </div>

        <div class="rounded-xl bg-green-50 p-4 dark:bg-green-950">
            <p class="text-sm text-green-700 dark:text-green-300">Montant payé</p>
            <p class="mt-2 text-lg font-bold text-green-800 dark:text-green-200">
                {{ number_format($montantPaye, 0, ',', ' ') }} FCFA
            </p>
        </div>

        <div class="rounded-xl bg-orange-50 p-4 dark:bg-orange-950">
            <p class="text-sm text-orange-700 dark:text-orange-300">Reste à payer</p>
            <p class="mt-2 text-lg font-bold text-orange-800 dark:text-orange-200">
                {{ number_format($resteAPayer, 0, ',', ' ') }} FCFA
            </p>
        </div>
    </div>

    {{-- Messages de confirmation et erreurs --}}
    @if(session('success'))
        <div class="rounded-lg bg-green-50 p-4 text-sm text-green-800 dark:bg-green-950 dark:text-green-200">
            {{ session('success') }}
        </div>
    @endif

    @if($errors->any())
        <div class="rounded-lg bg-red-50 p-4 text-sm text-red-800 dark:bg-red-950 dark:text-red-200">
            <p class="font-semibold">Le paiement n'a pas pu être enregistré :</p>
            <ul class="mt-2 list-inside list-disc">
                @foreach($errors->all() as $erreur)
                    <li>{{ $erreur }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    {{-- Formulaire --}}
    @if($resteAPayer > 0 && $commande->statut !== 'annulee')
        <div class="border-t border-gray-200 pt-6 dark:border-gray-700">
            <h3 class="mb-4 text-lg font-semibold text-gray-900 dark:text-white">
                Enregistrer un paiement
            </h3>

            <form action="{{ route('commandes.paiements.store', $commande) }}"
                  method="POST"
                  class="space-y-5">
                @csrf

                <div class="grid grid-cols-1 gap-5 sm:grid-cols-2">

                    <div>
                        <label for="montant" class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300">
                            Montant payé (FCFA) *
                        </label>
                        <input
                            type="number"
                            name="montant"
                            id="montant"
                            min="1"
                            max="{{ $resteAPayer }}"
                            step="1"
                            value="{{ old('montant') }}"
                            required
                            class="w-full rounded-lg border border-gray-300 bg-white px-4 py-3 text-gray-900 focus:border-green-500 focus:ring-green-500 dark:border-gray-600 dark:bg-gray-800 dark:text-white"
                            placeholder="Ex. : 10000">
                        <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">
                            Maximum : {{ number_format($resteAPayer, 0, ',', ' ') }} FCFA
                        </p>
                    </div>

                    <div>
                        <label for="date_paiement" class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300">
                            Date du paiement *
                        </label>
                        <input
                            type="date"
                            name="date_paiement"
                            id="date_paiement"
                            value="{{ old('date_paiement', now()->format('Y-m-d')) }}"
                            required
                            class="w-full rounded-lg border border-gray-300 bg-white px-4 py-3 text-gray-900 focus:border-green-500 focus:ring-green-500 dark:border-gray-600 dark:bg-gray-800 dark:text-white">
                    </div>

                    <div>
                        <label for="methode" class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300">
                            Mode de paiement *
                        </label>
                        <select
                            name="methode"
                            id="methode"
                            required
                            class="w-full rounded-lg border border-gray-300 bg-white px-4 py-3 text-gray-900 focus:border-green-500 focus:ring-green-500 dark:border-gray-600 dark:bg-gray-800 dark:text-white">
                            <option value="especes" @selected(old('methode', 'especes') === 'especes')>Espèces</option>
                            <option value="orange_money" @selected(old('methode') === 'orange_money')>Orange Money</option>
                            <option value="moov_money" @selected(old('methode') === 'moov_money')>Moov Money</option>
                            <option value="virement" @selected(old('methode') === 'virement')>Virement bancaire</option>
                            <option value="autre" @selected(old('methode') === 'autre')>Autre</option>
                        </select>
                    </div>

                    <div>
                        <label for="reference" class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300">
                            Référence de transaction
                        </label>
                        <input
                            type="text"
                            name="reference"
                            id="reference"
                            value="{{ old('reference') }}"
                            maxlength="100"
                            class="w-full rounded-lg border border-gray-300 bg-white px-4 py-3 text-gray-900 focus:border-green-500 focus:ring-green-500 dark:border-gray-600 dark:bg-gray-800 dark:text-white"
                            placeholder="Facultatif">
                    </div>

                    <div class="sm:col-span-2">
                        <label for="notes_paiement" class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300">
                            Notes
                        </label>
                        <textarea
                            name="notes"
                            id="notes_paiement"
                            rows="2"
                            maxlength="2000"
                            class="w-full rounded-lg border border-gray-300 bg-white px-4 py-3 text-gray-900 focus:border-green-500 focus:ring-green-500 dark:border-gray-600 dark:bg-gray-800 dark:text-white"
                            placeholder="Commentaire facultatif">{{ old('notes') }}</textarea>
                    </div>
                </div>

                <div class="flex justify-end">
                    <button
                        type="submit"
                        class="rounded-lg bg-green-600 px-5 py-3 text-sm font-semibold text-white hover:bg-green-700 focus:outline-none focus:ring-4 focus:ring-green-300">
                        Enregistrer le paiement
                    </button>
                </div>
            </form>
        </div>
    @endif

    {{-- Historique des paiements --}}
    <div class="border-t border-gray-200 pt-6 dark:border-gray-700">
        <h3 class="mb-4 text-lg font-semibold text-gray-900 dark:text-white">
            Historique des paiements
        </h3>

        @forelse($commande->paiements->sortByDesc('date_paiement') as $paiement)
            <div class="flex flex-col justify-between gap-3 border-b border-gray-100 py-4 last:border-0 dark:border-gray-800 sm:flex-row sm:items-center">
                <div>
                    <p class="font-semibold text-gray-900 dark:text-white">
                        {{ number_format((float) $paiement->montant, 0, ',', ' ') }} FCFA
                    </p>
                    <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                        {{ \Carbon\Carbon::parse($paiement->date_paiement)->format('d/m/Y') }}
                        ·
                        @switch($paiement->methode)
                            @case('especes') Espèces @break
                            @case('orange_money') Orange Money @break
                            @case('moov_money') Moov Money @break
                            @case('virement') Virement bancaire @break
                            @default Autre
                        @endswitch
                    </p>
                    @if($paiement->reference)
                        <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">
                            Référence : {{ $paiement->reference }}
                        </p>
                    @endif
                    @if($paiement->notes)
                        <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                            {{ $paiement->notes }}
                        </p>
                    @endif
                </div>

                <span class="w-fit rounded-full px-3 py-1 text-xs font-semibold
                    {{ $paiement->statut === 'confirme' ? 'bg-green-100 text-green-800' : 'bg-gray-100 text-gray-700' }}">
                    {{ $paiement->statut === 'confirme' ? 'Confirmé' : ucfirst($paiement->statut) }}
                </span>
            </div>
        @empty
            <p class="rounded-lg bg-gray-50 p-4 text-sm text-gray-500 dark:bg-gray-800 dark:text-gray-400">
                Aucun paiement enregistré pour cette commande.
            </p>
        @endforelse
    </div>
</div>


{{-- Styles d'impression --}}
<style>
    @media print {
        @page {
            size: A4;
            margin: 12mm;
        }

        body * {
            visibility: hidden !important;
        }

        #ticket-impression,
        #ticket-impression * {
            visibility: visible !important;
        }

        #ticket-impression {
            position: absolute !important;
            left: 0 !important;
            top: 0 !important;
            width: 100% !important;
            max-width: none !important;
            margin: 0 !important;
            padding: 0 !important;
            border: none !important;
            border-radius: 0 !important;
            box-shadow: none !important;
            background: white !important;
            color: black !important;
        }

        #ticket-impression table {
            width: 100% !important;
            border-collapse: collapse !important;
        }

        #ticket-impression th,
        #ticket-impression td {
            color: black !important;
            border-color: #d1d5db !important;
        }

        #ticket-impression th {
            background: #f3f4f6 !important;
        }

        #ticket-impression .text-green-700,
        #ticket-impression .text-green-800,
        #ticket-impression .text-green-300 {
            color: #166534 !important;
        }

        #ticket-impression .bg-green-50,
        #ticket-impression .bg-green-950 {
            background: #f0fdf4 !important;
        }

        #ticket-impression .text-gray-500,
        #ticket-impression .text-gray-600,
        #ticket-impression .text-gray-700,
        #ticket-impression .text-gray-900,
        #ticket-impression .text-white {
            color: black !important;
        }

        #ticket-impression tr {
            break-inside: avoid;
        }
    }
</style>

@endsection
