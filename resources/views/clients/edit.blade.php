@extends('layouts.tailadmin.app')

@section('title', 'Modifier le client - Baga Farmer')

@section('content')
<div class="space-y-6">

    {{-- En-tête --}}
    <div>
        <p class="text-sm text-gray-500 dark:text-gray-400">
            Gestion des clients / Modification
        </p>

        <h1 class="mt-1 text-2xl font-bold text-gray-900 dark:text-white">
            Modifier le client
        </h1>

        <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
            Code client : {{ $client->code_client }}
        </p>
    </div>

    {{-- Erreurs de validation --}}
    @if ($errors->any())
        <div class="rounded-xl border border-red-200 bg-red-50 p-4 dark:border-red-800 dark:bg-red-900/20">
            <p class="font-medium text-red-700 dark:text-red-400">
                Veuillez corriger les erreurs suivantes :
            </p>

            <ul class="mt-2 list-inside list-disc text-sm text-red-600 dark:text-red-400">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    {{-- Formulaire --}}
    <div class="rounded-2xl border border-gray-200 bg-white p-5 dark:border-gray-800 dark:bg-white/[0.03] sm:p-6">

        <form method="POST" action="{{ route('clients.update', $client) }}">
            @csrf
            @method('PUT')

            {{-- Informations principales --}}
            <div class="mb-6">
                <h2 class="text-lg font-semibold text-gray-900 dark:text-white">
                    Informations générales
                </h2>

                <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                    Les champs marqués d'un astérisque sont obligatoires.
                </p>
            </div>

            <div class="grid grid-cols-1 gap-5 sm:grid-cols-2">

                {{-- Nom --}}
                <div>
                    <label for="nom" class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300">
                        Nom complet ou entreprise <span class="text-red-500">*</span>
                    </label>

                    <input
                        id="nom"
                        name="nom"
                        type="text"
                        value="{{ old('nom', $client->nom) }}"
                        required
                        maxlength="150"
                        class="w-full rounded-lg border border-gray-300 bg-transparent px-4 py-3 text-sm text-gray-900 outline-none transition focus:border-brand-500 focus:ring-2 focus:ring-brand-500/20 dark:border-gray-700 dark:text-white"
                        placeholder="Nom du client ou de l'entreprise"
                    >
                </div>

                {{-- Téléphone --}}
                <div>
                    <label for="telephone" class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300">
                        Téléphone principal <span class="text-red-500">*</span>
                    </label>

                    <input
                        id="telephone"
                        name="telephone"
                        type="tel"
                        value="{{ old('telephone', $client->telephone) }}"
                        required
                        maxlength="30"
                        class="w-full rounded-lg border border-gray-300 bg-transparent px-4 py-3 text-sm text-gray-900 outline-none transition focus:border-brand-500 focus:ring-2 focus:ring-brand-500/20 dark:border-gray-700 dark:text-white"
                        placeholder="Ex. : 70 00 00 00"
                    >
                </div>

                {{-- Téléphone secondaire --}}
                <div>
                    <label for="telephone_secondaire" class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300">
                        Téléphone secondaire
                    </label>

                    <input
                        id="telephone_secondaire"
                        name="telephone_secondaire"
                        type="tel"
                        value="{{ old('telephone_secondaire', $client->telephone_secondaire) }}"
                        maxlength="30"
                        class="w-full rounded-lg border border-gray-300 bg-transparent px-4 py-3 text-sm text-gray-900 outline-none transition focus:border-brand-500 focus:ring-2 focus:ring-brand-500/20 dark:border-gray-700 dark:text-white"
                        placeholder="Numéro secondaire (facultatif)"
                    >
                </div>

                {{-- Type --}}
                <div>
                    <label for="type_client" class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300">
                        Type de client <span class="text-red-500">*</span>
                    </label>

                    <select
                        id="type_client"
                        name="type_client"
                        required
                        class="w-full rounded-lg border border-gray-300 bg-white px-4 py-3 text-sm text-gray-900 outline-none transition focus:border-brand-500 focus:ring-2 focus:ring-brand-500/20 dark:border-gray-700 dark:bg-gray-900 dark:text-white"
                    >
                        @foreach ([
                            'particulier' => 'Particulier',
                            'revendeur' => 'Revendeur',
                            'restaurant' => 'Restaurant',
                            'grossiste' => 'Grossiste',
                            'autre' => 'Autre',
                        ] as $value => $label)
                            <option
                                value="{{ $value }}"
                                @selected(old('type_client', $client->type_client) === $value)
                            >
                                {{ $label }}
                            </option>
                        @endforeach
                    </select>
                </div>

                {{-- Quartier --}}
                <div class="sm:col-span-2">
                    <label for="quartier" class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300">
                        Quartier
                    </label>

                    <input
                        id="quartier"
                        name="quartier"
                        type="text"
                        value="{{ old('quartier', $client->quartier) }}"
                        maxlength="100"
                        class="w-full rounded-lg border border-gray-300 bg-transparent px-4 py-3 text-sm text-gray-900 outline-none transition focus:border-brand-500 focus:ring-2 focus:ring-brand-500/20 dark:border-gray-700 dark:text-white"
                        placeholder="Ex. : Badialan, ACI 2000..."
                    >
                </div>

                {{-- Adresse --}}
                <div class="sm:col-span-2">
                    <label for="adresse" class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300">
                        Adresse complète
                    </label>

                    <textarea
                        id="adresse"
                        name="adresse"
                        rows="3"
                        class="w-full rounded-lg border border-gray-300 bg-transparent px-4 py-3 text-sm text-gray-900 outline-none transition focus:border-brand-500 focus:ring-2 focus:ring-brand-500/20 dark:border-gray-700 dark:text-white"
                        placeholder="Adresse ou indications pour la livraison"
                    >{{ old('adresse', $client->adresse) }}</textarea>
                </div>

                {{-- Notes --}}
                <div class="sm:col-span-2">
                    <label for="notes" class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300">
                        Notes internes
                    </label>

                    <textarea
                        id="notes"
                        name="notes"
                        rows="4"
                        class="w-full rounded-lg border border-gray-300 bg-transparent px-4 py-3 text-sm text-gray-900 outline-none transition focus:border-brand-500 focus:ring-2 focus:ring-brand-500/20 dark:border-gray-700 dark:text-white"
                        placeholder="Préférences, informations utiles, consignes particulières..."
                    >{{ old('notes', $client->notes) }}</textarea>
                </div>

            </div>

            {{-- Actions --}}
            <div class="mt-7 flex flex-col-reverse gap-3 border-t border-gray-200 pt-5 dark:border-gray-800 sm:flex-row sm:justify-end">

                <a
                    href="{{ route('clients.show', $client) }}"
                    class="inline-flex items-center justify-center rounded-lg border border-gray-300 px-5 py-3 text-sm font-medium text-gray-700 transition hover:bg-gray-50 dark:border-gray-700 dark:text-gray-300 dark:hover:bg-white/[0.03]"
                >
                    Annuler
                </a>

                <button
                    type="submit"
                    class="inline-flex items-center justify-center rounded-lg bg-brand-500 px-5 py-3 text-sm font-medium text-white transition hover:bg-brand-600"
                >
                    Enregistrer les modifications
                </button>

            </div>
        </form>
    </div>
</div>
@endsection