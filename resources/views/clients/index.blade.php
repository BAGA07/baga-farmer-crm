@extends('layouts.tailadmin.app')

@php
    $title = 'Gestion des clients';
@endphp

@section('content')
<div class="mx-auto max-w-screen-2xl">
    <div class="mb-6 flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
        <div>
            <h1 class="text-2xl font-semibold text-gray-900 dark:text-white">
                Gestion des clients
            </h1>
            <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                Gérez les contacts et les informations de vos clients Baga Farmer.
            </p>
        </div>

        <a href="{{ route('clients.create') }}"
           class="inline-flex items-center justify-center rounded-lg bg-green-600 px-4 py-3 text-sm font-medium text-white transition hover:bg-green-700">
            + Nouveau client
        </a>
    </div>

    @if (session('success'))
        <div class="mb-5 rounded-lg border border-green-200 bg-green-50 p-4 text-sm text-green-800 dark:border-green-800 dark:bg-green-900/20 dark:text-green-300">
            {{ session('success') }}
        </div>
    @endif

    @if ($errors->any())
        <div class="mb-5 rounded-lg border border-red-200 bg-red-50 p-4 text-sm text-red-700 dark:border-red-800 dark:bg-red-900/20 dark:text-red-300">
            <ul class="list-inside list-disc">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <div class="mb-6 rounded-2xl border border-gray-200 bg-white p-5 dark:border-gray-800 dark:bg-white/[0.03]">
        <form method="GET" action="{{ route('clients.index') }}"
              class="flex flex-col gap-3 sm:flex-row sm:items-end">
            <div class="flex-1">
                <label for="recherche"
                       class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300">
                    Rechercher un client
                </label>

                <input
                    id="recherche"
                    type="search"
                    name="recherche"
                    value="{{ $recherche }}"
                    placeholder="Nom, téléphone ou code client"
                    class="h-11 w-full rounded-lg border border-gray-300 bg-transparent px-4 text-sm text-gray-900 outline-none placeholder:text-gray-400 focus:border-green-500 focus:ring-2 focus:ring-green-500/20 dark:border-gray-700 dark:text-white"
                >
            </div>

            <div class="flex gap-2">
                <button type="submit"
                        class="inline-flex h-11 items-center justify-center rounded-lg bg-green-600 px-5 text-sm font-medium text-white hover:bg-green-700">
                    Rechercher
                </button>

                <a href="{{ route('clients.index') }}"
                   class="inline-flex h-11 items-center justify-center rounded-lg border border-gray-300 px-4 text-sm font-medium text-gray-700 hover:bg-gray-50 dark:border-gray-700 dark:text-gray-300 dark:hover:bg-white/5">
                    Réinitialiser
                </a>
            </div>
        </form>
    </div>

    <div class="overflow-hidden rounded-2xl border border-gray-200 bg-white dark:border-gray-800 dark:bg-white/[0.03]">
        <div class="flex flex-col gap-2 border-b border-gray-200 px-5 py-4 sm:flex-row sm:items-center sm:justify-between dark:border-gray-800">
            <div>
                <h2 class="text-lg font-semibold text-gray-900 dark:text-white">
                    Liste des clients
                </h2>
                <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                    {{ $clients->total() }} client(s) trouvé(s)
                </p>
            </div>
        </div>

        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-800">
                <thead class="bg-gray-50 dark:bg-white/[0.02]">
                    <tr>
                        <th scope="col" class="px-5 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500 dark:text-gray-400">
                            Client
                        </th>
                        <th scope="col" class="px-5 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500 dark:text-gray-400">
                            Code client
                        </th>
                        <th scope="col" class="px-5 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500 dark:text-gray-400">
                            Téléphone
                        </th>
                        <th scope="col" class="px-5 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500 dark:text-gray-400">
                            Type
                        </th>
                        <th scope="col" class="px-5 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500 dark:text-gray-400">
                            Quartier
                        </th>
                        <th scope="col" class="px-5 py-3 text-right text-xs font-medium uppercase tracking-wider text-gray-500 dark:text-gray-400">
                            Actions
                        </th>
                    </tr>
                </thead>

                <tbody class="divide-y divide-gray-200 dark:divide-gray-800">
                    @forelse ($clients as $client)
                        <tr class="transition hover:bg-gray-50 dark:hover:bg-white/[0.02]">
                            <td class="whitespace-nowrap px-5 py-4">
                                <div class="font-medium text-gray-900 dark:text-white">
                                    {{ $client->nom }}
                                </div>
                            </td>

                            <td class="whitespace-nowrap px-5 py-4 text-sm text-gray-600 dark:text-gray-300">
                                {{ $client->code_client ?: '—' }}
                            </td>

                            <td class="whitespace-nowrap px-5 py-4 text-sm text-gray-600 dark:text-gray-300">
                                {{ $client->telephone ?: '—' }}
                            </td>

                            <td class="whitespace-nowrap px-5 py-4 text-sm text-gray-600 dark:text-gray-300">
                                {{ $client->type_client ?: '—' }}
                            </td>

                            <td class="whitespace-nowrap px-5 py-4 text-sm text-gray-600 dark:text-gray-300">
                                {{ $client->quartier ?: '—' }}
                            </td>

                            <td class="whitespace-nowrap px-5 py-4">
                                <div class="flex items-center justify-end gap-3 text-sm font-medium">
                                    <a href="{{ route('clients.show', $client) }}"
                                       class="text-green-700 hover:text-green-900 dark:text-green-400">
                                        Voir
                                    </a>

                                    <a href="{{ route('clients.edit', $client) }}"
                                       class="text-blue-600 hover:text-blue-800 dark:text-blue-400">
                                        Modifier
                                    </a>

                                    <form method="POST"
                                          action="{{ route('clients.destroy', $client) }}"
                                          onsubmit="return confirm('Archiver ce client ?')">
                                        @csrf
                                        @method('DELETE')

                                        <button type="submit"
                                                class="text-red-600 hover:text-red-800 dark:text-red-400">
                                            Archiver
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-5 py-12 text-center">
                                <p class="font-medium text-gray-900 dark:text-white">
                                    Aucun client trouvé
                                </p>
                                <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                                    Essayez une autre recherche ou ajoutez votre premier client.
                                </p>
                                <a href="{{ route('clients.create') }}"
                                   class="mt-4 inline-flex rounded-lg bg-green-600 px-4 py-2 text-sm font-medium text-white hover:bg-green-700">
                                    Ajouter un client
                                </a>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if ($clients->hasPages())
            <div class="border-t border-gray-200 px-5 py-4 dark:border-gray-800">
                {{ $clients->links() }}
            </div>
        @endif
    </div>
</div>
@endsection
