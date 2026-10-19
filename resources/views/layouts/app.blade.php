
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="theme-color" content="#166534">

    <title>@yield('title', 'Baga Farmer CRM')</title>

    <link rel="stylesheet" href="{{ asset('css/app.css') }}">
</head>
<body>
    <header class="app-header">
        <div class="header-inner">
            <a href="{{ route('clients.index') }}" class="brand">
                <span class="brand-mark">🌱</span>
                <span>
                    Baga Farmer
                    <small>Gestion commerciale</small>
                </span>
            </a>

            <nav class="main-nav">
                <a href="{{ route('clients.index') }}"
                   class="{{ request()->routeIs('clients.*') ? 'active' : '' }}">
                    Clients
                </a>

                <a href="{{ route('clients.create') }}">
                    + Nouveau client
                </a>
            </nav>
        </div>
    </header>

    <main class="page-container">
        @if (session('success'))
            <div class="alert-success">
                ✓ {{ session('success') }}
            </div>
        @endif

        @if ($errors->any())
            <div class="alert-error">
                <strong>Vérifie les informations saisies :</strong>
                <ul>
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        @yield('content')
    </main>

    <nav class="mobile-nav" aria-label="Navigation principale">
        <a href="{{ route('clients.index') }}"
           class="{{ request()->routeIs('clients.index', 'clients.show', 'clients.edit') ? 'active' : '' }}">
            <span class="nav-icon">♙</span>
            Clients
        </a>

        <a href="{{ route('clients.create') }}"
           class="{{ request()->routeIs('clients.create') ? 'active' : '' }}">
            <span class="nav-icon">＋</span>
            Ajouter
        </a>
    </nav>
</body>
</html>
