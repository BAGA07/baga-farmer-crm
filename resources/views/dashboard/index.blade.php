
@extends('layouts.tailadmin.app')

@php
    $title = 'Tableau de bord';
@endphp

@section('content')
<div class="mx-auto max-w-screen-2xl">

    <div class="mb-6">
        <h1 class="text-2xl font-semibold text-gray-900 dark:text-white">
            Tableau de bord
        </h1>

        <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
            Bienvenue dans votre espace de gestion Baga Farmer.
        </p>
    </div>

    <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 xl:grid-cols-4">

        <div class="rounded-2xl border border-gray-200 bg-white p-5 dark:border-gray-800 dark:bg-white/[0.03]">
            <p class="text-sm text-gray-500 dark:text-gray-400">
                Clients actifs
            </p>

            <p class="mt-3 text-3xl font-bold text-gray-900 dark:text-white">
                {{ number_format($totalClients, 0, ',', ' ') }}
            </p>

            <a href="{{ route('clients.index') }}"
               class="mt-4 inline-block text-sm font-medium text-green-700 hover:text-green-900 dark:text-green-400">
                Voir les clients →
            </a>
        </div>

        <div class="rounded-2xl border border-gray-200 bg-white p-5 dark:border-gray-800 dark:bg-white/[0.03]">
            <p class="text-sm text-gray-500 dark:text-gray-400">
                Commandes
            </p>

            <p class="mt-3 text-lg font-semibold text-gray-400 dark:text-gray-500">
                À intégrer
            </p>

            <p class="mt-2 text-sm text-gray-500 dark:text-gray-400">
                Statistiques réelles à connecter.
            </p>
        </div>

        <div class="rounded-2xl border border-gray-200 bg-white p-5 dark:border-gray-800 dark:bg-white/[0.03]">
            <p class="text-sm text-gray-500 dark:text-gray-400">
                Ventes
            </p>

            <p class="mt-3 text-lg font-semibold text-gray-400 dark:text-gray-500">
                À intégrer
            </p>

            <p class="mt-2 text-sm text-gray-500 dark:text-gray-400">
                Montants calculés depuis les commandes.
            </p>
        </div>

        <div class="rounded-2xl border border-gray-200 bg-white p-5 dark:border-gray-800 dark:bg-white/[0.03]">
            <p class="text-sm text-gray-500 dark:text-gray-400">
                Paiements reçus
            </p>

            <p class="mt-3 text-lg font-semibold text-gray-400 dark:text-gray-500">
                À intégrer
            </p>

            <p class="mt-2 text-sm text-gray-500 dark:text-gray-400">
                Montants calculés depuis les paiements.
            </p>
        </div>

    </div>

    <div class="mt-6 rounded-2xl border border-gray-200 bg-white p-6 dark:border-gray-800 dark:bg-white/[0.03]">
        <h2 class="text-lg font-semibold text-gray-900 dark:text-white">
            Accès rapide
        </h2>

        <p class="mt-2 text-sm text-gray-500 dark:text-gray-400">
            Retrouvez rapidement les principales fonctionnalités de votre CRM.
        </p>

        <div class="mt-5 flex flex-wrap gap-3">
            <a href="{{ route('clients.index') }}"
               class="rounded-lg bg-green-600 px-4 py-3 text-sm font-medium text-white hover:bg-green-700">
                Gérer les clients
            </a>

            <a href="{{ route('clients.create') }}"
               class="rounded-lg border border-gray-300 px-4 py-3 text-sm font-medium text-gray-700 hover:bg-gray-50 dark:border-gray-700 dark:text-gray-300 dark:hover:bg-white/5">
                + Nouveau client
            </a>
        </div>
    </div>

</div>
@endsection
