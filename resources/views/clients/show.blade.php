
@extends('layouts.app')

@section('title', $client->nom . ' - Baga Farmer')

@section('content')
<div class="card">
    <div class="actions" style="justify-content: space-between;">
        <div>
            <p class="muted">{{ $client->code_client }}</p>
            <h1>{{ $client->nom }}</h1>
        </div>
        <a class="btn btn-secondary"
           href="{{ route('clients.edit', $client) }}">Modifier</a>
    </div>

    <p><strong>Téléphone :</strong> {{ $client->telephone }}</p>

    @if ($client->telephone_secondaire)
        <p><strong>Autre téléphone :</strong> {{ $client->telephone_secondaire }}</p>
    @endif

    <p><strong>Type :</strong> {{ $client->type_client }}</p>
    <p><strong>Quartier :</strong> {{ $client->quartier ?: 'Non renseigné' }}</p>
    <p><strong>Adresse :</strong> {{ $client->adresse ?: 'Non renseignée' }}</p>
    <p><strong>Notes :</strong> {{ $client->notes ?: 'Aucune note' }}</p>

    <a class="btn" href="{{ route('clients.index') }}">Retour aux clients</a>
</div>

<div class="card">
    <h2>Historique des commandes</h2>

    @forelse ($client->commandes as $commande)
        <div style="padding: 12px 0; border-bottom: 1px solid #e5e7eb;">
            <strong>{{ $commande->numero }}</strong>
            <p>
                Date : {{ $commande->date_commande?->format('d/m/Y H:i') }}
            </p>
            <p>Statut : {{ $commande->statut }}</p>
            <p>
                Produits :
                {{ number_format((float) $commande->lignes->sum(
                    fn ($ligne) => (float) $ligne->quantite * (float) $ligne->prix_unitaire
                    - (float) $ligne->remise
                ) + (float) $commande->frais_livraison - (float) $commande->remise, 0, ',', ' ') }}
                FCFA
            </p>
        </div>
    @empty
        <p>Ce client n'a pas encore de commande.</p>
    @endforelse
</div>
@endsection
