
@extends('layouts.tailadmin.app')

@section('content')
<div class="space-y-6">

    <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
        <div>
            <h1 class="text-2xl font-bold text-gray-800 dark:text-white">
                Catégories de produits
            </h1>
            <p class="mt-1 text-sm text-gray-500">
                Classez les produits de Baga Farmer.
            </p>
        </div>

        <a href="{{ route('categories.create') }}"
           class="inline-flex items-center justify-center rounded-lg bg-green-600 px-5 py-3 text-sm font-medium text-white hover:bg-green-700">
            + Nouvelle catégorie
        </a>
    </div>

    @if(session('success'))
        <div class="rounded-lg border border-green-200 bg-green-50 p-4 text-green-700">
            {{ session('success') }}
        </div>
    @endif

    @if(session('error'))
        <div class="rounded-lg border border-red-200 bg-red-50 p-4 text-red-700">
            {{ session('error') }}
        </div>
    @endif

    @if($errors->any())
        <div class="rounded-lg border border-red-200 bg-red-50 p-4 text-red-700">
            <ul class="list-inside list-disc">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <div class="overflow-hidden rounded-xl border border-gray-200 bg-white dark:border-gray-800 dark:bg-white/[0.03]">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200 text-left text-sm dark:divide-gray-800">
                <thead class="bg-gray-50 dark:bg-gray-800">
                    <tr>
                        <th class="px-6 py-4 font-semibold text-gray-700 dark:text-gray-200">Catégorie</th>
                        <th class="px-6 py-4 font-semibold text-gray-700 dark:text-gray-200">Description</th>
                        <th class="px-6 py-4 font-semibold text-gray-700 dark:text-gray-200">Produits</th>
                        <th class="px-6 py-4 text-right font-semibold text-gray-700 dark:text-gray-200">Actions</th>
                    </tr>
                </thead>

                <tbody class="divide-y divide-gray-100 dark:divide-gray-800">
                    @forelse($categories as $categorie)
                        <tr>
                            <td class="px-6 py-4 font-medium text-gray-800 dark:text-white">
                                {{ $categorie->nom }}
                            </td>

                            <td class="px-6 py-4 text-gray-600 dark:text-gray-300">
                                {{ $categorie->description ?: 'Aucune description' }}
                            </td>

                            <td class="px-6 py-4">
                                <span class="rounded-full bg-green-50 px-3 py-1 text-green-700">
                                    {{ $categorie->produits_count }}
                                </span>
                            </td>

                            <td class="px-6 py-4">
                                <div class="flex justify-end gap-3">
                                    <a href="{{ route('categories.edit', $categorie) }}"
                                       class="font-medium text-blue-600 hover:text-blue-800">
                                        Modifier
                                    </a>

                                    <form action="{{ route('categories.destroy', $categorie) }}"
                                          method="POST"
                                          onsubmit="return confirm('Confirmer la suppression de cette catégorie ?')">
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
                            <td colspan="4" class="px-6 py-12 text-center text-gray-500">
                                Aucune catégorie pour le moment.
                                Ajoutez votre première catégorie.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="border-t border-gray-100 p-4 dark:border-gray-800">
            {{ $categories->links() }}
        </div>
    </div>
</div>
@endsection
