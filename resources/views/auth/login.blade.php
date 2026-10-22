
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Connexion | Baga Farmer CRM</title>

    @vite([
        'resources/css/tailadmin.css',
        'resources/js/tailadmin/app.js'
    ])
</head>

<body class="min-h-screen bg-gray-50 dark:bg-gray-900">
    <div class="flex min-h-screen flex-col items-center justify-center px-4 py-10">

        <div class="mb-8 text-center">
            <div class="mx-auto mb-4 flex h-16 w-16 items-center justify-center rounded-2xl bg-brand-500 text-3xl font-bold text-white">
                B
            </div>

            <h1 class="text-2xl font-bold text-gray-900 dark:text-white">
                Baga Farmer CRM
            </h1>

            <p class="mt-2 text-sm text-gray-500 dark:text-gray-400">
                Gestion de vos clients et de vos commandes
            </p>
        </div>

        <div class="mx-auto w-full max-w-sm rounded-2xl border border-gray-200 bg-white p-6 shadow-sm sm:p-8 dark:border-gray-800 dark:bg-gray-800">

            <h2 class="text-xl font-semibold text-gray-900 dark:text-white">
                Connexion
            </h2>

            <p class="mt-2 text-sm text-gray-500 dark:text-gray-400">
                Connectez-vous pour accéder à votre espace de gestion.
            </p>

            @if ($errors->any())
                <div class="mt-5 rounded-lg border border-red-200 bg-red-50 p-3 text-sm text-red-700">
                    {{ $errors->first() }}
                </div>
            @endif

            <form method="POST" action="{{ route('login.store') }}" class="mt-6 space-y-5">
                @csrf

                <div>
                    <label for="email" class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300">
                        Adresse e-mail
                    </label>

                    <input
                        id="email"
                        name="email"
                        type="email"
                        value="{{ old('email') }}"
                        required
                        autofocus
                        autocomplete="username"
                        placeholder="nom@exemple.com"
                        class="w-full rounded-lg border border-gray-300 bg-transparent px-4 py-3 text-sm text-gray-900 outline-none focus:border-brand-500 focus:ring-2 focus:ring-brand-500/20 dark:border-gray-700 dark:text-white"
                    >
                </div>

                <div>
                    <label for="password" class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300">
                        Mot de passe
                    </label>

                    <input
                        id="password"
                        name="password"
                        type="password"
                        required
                        autocomplete="current-password"
                        placeholder="Votre mot de passe"
                        class="w-full rounded-lg border border-gray-300 bg-transparent px-4 py-3 text-sm text-gray-900 outline-none focus:border-brand-500 focus:ring-2 focus:ring-brand-500/20 dark:border-gray-700 dark:text-white"
                    >
                </div>

                <div class="flex items-center">
                    <input
                        id="remember"
                        name="remember"
                        type="checkbox"
                        value="1"
                        class="h-4 w-4 rounded border-gray-300 text-brand-500 focus:ring-brand-500"
                    >

                    <label for="remember" class="ml-2 text-sm text-gray-600 dark:text-gray-400">
                        Se souvenir de moi
                    </label>
                </div>

                <button
                    type="submit"
                    class="w-full rounded-lg bg-brand-500 px-4 py-3 text-sm font-semibold text-white transition hover:bg-brand-600 focus:outline-none focus:ring-2 focus:ring-brand-500 focus:ring-offset-2"
                >
                    Se connecter
                </button>
            </form>
        </div>

        <p class="mt-6 text-center text-xs text-gray-500 dark:text-gray-400">
            © {{ date('Y') }} Baga Farmer — Tous droits réservés.
        </p>
    </div>
</body>
</html>
