
@extends('layouts.tailadmin.app')

@section('content')
<div class="mx-auto max-w-3xl space-y-6">

    <div>
        <h1 class="text-2xl font-bold text-gray-800 dark:text-white">
            Nouvelle catégorie
        </h1>
        <p class="mt-1 text-sm text-gray-500">
            Créez une catégorie pour organiser vos produits.
        </p>
    </div>

    @if($errors->any())
        <div class="rounded-lg bg-red-50 p-4 text-red-700">
            <ul class="list-inside list-disc">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form action="{{ route('categories.store') }}" method="POST"
          class="space-y-5 rounded-xl border border-gray-200 bg-white p-6 dark:border-gray-800 dark:bg-white/[0.03]">
        @csrf

        <div>
            <label for="nom" class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-200">
                Nom de la catégorie *
            </label>
            <input id="nom" name="nom" type="text" required maxlength="100"
                   value="{{ old('nom') }}"
                   placeholder="Ex. : Légumes, Fruits, Plants..."
                   class="w-full rounded-lg border border-gray-300 px-4 py-3 dark:border-gray-700 dark:bg-gray-900 dark:text-white">
        </div>

        <div>
            <label for="description" class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-200">
                Description
            </label>
            <textarea id="description" name="description" rows="4" maxlength="2000"
                      placeholder="Décrivez cette catégorie (facultatif)."
                      class="w-full rounded-lg border border-gray-300 px-4 py-3 dark:border-gray-700 dark:bg-gray-900 dark:text-white">{{ old('description') }}</textarea>
        </div>

        <div class="flex flex-wrap gap-3 border-t border-gray-100 pt-5 dark:border-gray-800">
            <button type="submit"
                    class="rounded-lg bg-green-600 px-5 py-3 font-medium text-white hover:bg-green-700">
                Enregistrer
            </button>

            <a href="{{ route('categories.index') }}"
               class="rounded-lg border border-gray-300 px-5 py-3 font-medium text-gray-700 hover:bg-gray-50 dark:border-gray-700 dark:text-gray-200">
                Annuler
            </a>
        </div>
    </form>
</div>
@endsection
