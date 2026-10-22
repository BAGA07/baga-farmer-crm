
@extends('layouts.tailadmin.app')

@section('content')
<div class="mx-auto max-w-5xl px-4 py-6 sm:px-6 lg:px-8">

    <div class="mb-6">
        <h1 class="text-2xl font-bold text-gray-800 dark:text-white">
            Mon profil
        </h1>
        <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
            Gérez vos informations personnelles et la sécurité de votre compte.
        </p>
    </div>

    @if (session('success'))
        <div class="mb-5 rounded-lg border border-green-200 bg-green-50 px-4 py-3 text-sm text-green-700 dark:border-green-800 dark:bg-green-900/20 dark:text-green-400">
            {{ session('success') }}
        </div>
    @endif

    @if (session('password_success'))
        <div class="mb-5 rounded-lg border border-green-200 bg-green-50 px-4 py-3 text-sm text-green-700 dark:border-green-800 dark:bg-green-900/20 dark:text-green-400">
            {{ session('password_success') }}
        </div>
    @endif

    <div class="grid grid-cols-1 gap-6 lg:grid-cols-3">

        {{-- Informations du compte --}}
        <div class="rounded-2xl border border-gray-200 bg-white p-6 dark:border-gray-800 dark:bg-white/[0.03]">
            <div class="flex flex-col items-center text-center">

                <div class="flex h-24 w-24 items-center justify-center rounded-full bg-green-100 text-3xl font-bold text-green-700 dark:bg-green-900/30 dark:text-green-400">
                    {{ strtoupper(substr($user->name, 0, 1)) }}
                </div>

                <h2 class="mt-4 text-lg font-semibold text-gray-800 dark:text-white">
                    {{ $user->name }}
                </h2>

                <p class="mt-1 break-all text-sm text-gray-500 dark:text-gray-400">
                    {{ $user->email }}
                </p>

                <span class="mt-4 inline-flex items-center rounded-full bg-green-50 px-3 py-1 text-xs font-medium text-green-700 dark:bg-green-900/30 dark:text-green-400">
                    Compte connecté
                </span>
            </div>

            <div class="mt-6 border-t border-gray-200 pt-5 dark:border-gray-800">
                <p class="text-sm text-gray-500 dark:text-gray-400">
                    Membre depuis
                </p>
                <p class="mt-1 font-medium text-gray-800 dark:text-white">
                    {{ $user->created_at?->format('d/m/Y') ?? 'Non disponible' }}
                </p>
            </div>
        </div>

        <div class="space-y-6 lg:col-span-2">

            {{-- Modifier les informations --}}
            <section class="rounded-2xl border border-gray-200 bg-white p-6 dark:border-gray-800 dark:bg-white/[0.03]">

                <div class="mb-6">
                    <h2 class="text-lg font-semibold text-gray-800 dark:text-white">
                        Informations personnelles
                    </h2>
                    <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                        Modifiez votre nom et votre adresse e-mail.
                    </p>
                </div>

                @if ($errors->any() && !session('password_form'))
                    <div class="mb-5 rounded-lg border border-red-200 bg-red-50 p-4 text-sm text-red-700 dark:border-red-900 dark:bg-red-900/20 dark:text-red-400">
                        <ul class="list-inside list-disc space-y-1">
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                <form method="POST" action="{{ route('profile.update') }}" class="space-y-5">
                    @csrf
                    @method('PUT')

                    <div>
                        <label for="name" class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300">
                            Nom complet
                        </label>
                        <input
                            type="text"
                            id="name"
                            name="name"
                            value="{{ old('name', $user->name) }}"
                            required
                            maxlength="255"
                            autocomplete="name"
                            class="w-full rounded-lg border border-gray-300 bg-transparent px-4 py-3 text-sm text-gray-800 outline-none transition focus:border-green-500 focus:ring-2 focus:ring-green-500/20 dark:border-gray-700 dark:text-white"
                        >
                        @error('name')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label for="email" class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300">
                            Adresse e-mail
                        </label>
                        <input
                            type="email"
                            id="email"
                            name="email"
                            value="{{ old('email', $user->email) }}"
                            required
                            maxlength="255"
                            autocomplete="email"
                            class="w-full rounded-lg border border-gray-300 bg-transparent px-4 py-3 text-sm text-gray-800 outline-none transition focus:border-green-500 focus:ring-2 focus:ring-green-500/20 dark:border-gray-700 dark:text-white"
                        >
                        @error('email')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <div class="flex justify-end">
                        <button
                            type="submit"
                            class="rounded-lg bg-green-600 px-5 py-3 text-sm font-medium text-white transition hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-green-500 focus:ring-offset-2"
                        >
                            Enregistrer les modifications
                        </button>
                    </div>
                </form>
            </section>

            {{-- Modifier le mot de passe --}}
            <section class="rounded-2xl border border-gray-200 bg-white p-6 dark:border-gray-800 dark:bg-white/[0.03]">

                <div class="mb-6">
                    <h2 class="text-lg font-semibold text-gray-800 dark:text-white">
                        Sécurité du compte
                    </h2>
                    <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                        Utilisez un mot de passe d'au moins 8 caractères.
                    </p>
                </div>

                @if ($errors->has('current_password') || $errors->has('password') || $errors->has('password_confirmation'))
                    <div class="mb-5 rounded-lg border border-red-200 bg-red-50 p-4 text-sm text-red-700 dark:border-red-900 dark:bg-red-900/20 dark:text-red-400">
                        <ul class="list-inside list-disc space-y-1">
                            @foreach (['current_password', 'password', 'password_confirmation'] as $field)
                                @error($field)
                                    <li>{{ $message }}</li>
                                @enderror
                            @endforeach
                        </ul>
                    </div>
                @endif

                <form method="POST" action="{{ route('profile.password') }}" class="space-y-5">
                    @csrf
                    @method('PUT')

                    <div>
                        <label for="current_password" class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300">
                            Mot de passe actuel
                        </label>
                        <input
                            type="password"
                            id="current_password"
                            name="current_password"
                            required
                            autocomplete="current-password"
                            class="w-full rounded-lg border border-gray-300 bg-transparent px-4 py-3 text-sm text-gray-800 outline-none transition focus:border-green-500 focus:ring-2 focus:ring-green-500/20 dark:border-gray-700 dark:text-white"
                        >
                        @error('current_password')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label for="password" class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300">
                            Nouveau mot de passe
                        </label>
                        <input
                            type="password"
                            id="password"
                            name="password"
                            required
                            minlength="8"
                            autocomplete="new-password"
                            class="w-full rounded-lg border border-gray-300 bg-transparent px-4 py-3 text-sm text-gray-800 outline-none transition focus:border-green-500 focus:ring-2 focus:ring-green-500/20 dark:border-gray-700 dark:text-white"
                        >
                        @error('password')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label for="password_confirmation" class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300">
                            Confirmer le nouveau mot de passe
                        </label>
                        <input
                            type="password"
                            id="password_confirmation"
                            name="password_confirmation"
                            required
                            minlength="8"
                            autocomplete="new-password"
                            class="w-full rounded-lg border border-gray-300 bg-transparent px-4 py-3 text-sm text-gray-800 outline-none transition focus:border-green-500 focus:ring-2 focus:ring-green-500/20 dark:border-gray-700 dark:text-white"
                        >
                    </div>

                    <div class="flex justify-end">
                        <button
                            type="submit"
                            class="rounded-lg bg-green-600 px-5 py-3 text-sm font-medium text-white transition hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-green-500 focus:ring-offset-2"
                        >
                            Modifier le mot de passe
                        </button>
                    </div>
                </form>
            </section>

        </div>
    </div>
</div>
@endsection
