
@extends('layouts.app')

@section('title', 'Modifier le client - Baga Farmer')

@section('content')
<div class="card">
    <h1>Modifier le client</h1>
    <p class="muted">{{ $client->code_client }}</p>

    <form method="POST" action="{{ route('clients.update', $client) }}">
        @csrf
        @method('PUT')

        <div class="grid">
            <label>Nom complet ou entreprise *
                <input name="nom" value="{{ old('nom', $client->nom) }}"
                       required maxlength="150">
            </label>

            <label>Téléphone *
                <input name="telephone" type="tel"
                       value="{{ old('telephone', $client->telephone) }}"
                       required maxlength="30">
            </label>

            <label>Téléphone secondaire
                <input name="telephone_secondaire"
                       value="{{ old('telephone_secondaire', $client->telephone_secondaire) }}"
                       maxlength="30">
            </label>

            <label>Type de client *
                <select name="type_client" required>
                    @foreach ([
                        'particulier' => 'Particulier',
                        'revendeur' => 'Revendeur',
                        'restaurant' => 'Restaurant',
                        'grossiste' => 'Grossiste',
                        'autre' => 'Autre',
                    ] as $value => $label)
                        <option value="{{ $value }}"
                            @selected(old('type_client', $client->type_client) === $value)>
                            {{ $label }}
                        </option>
                    @endforeach
                </select>
            </label>

            <label>Quartier
                <input name="quartier" value="{{ old('quartier', $client->quartier) }}"
                       maxlength="100">
            </label>
        </div>

        <label>Adresse
            <textarea name="adresse" rows="3">{{ old('adresse', $client->adresse) }}</textarea>
        </label>

        <label>Notes
            <textarea name="notes" rows="3">{{ old('notes', $client->notes) }}</textarea>
        </label>

        <div class="actions">
            <button class="btn" type="submit">Enregistrer les modifications</button>
            <a class="btn btn-secondary"
               href="{{ route('clients.show', $client) }}">Annuler</a>
        </div>
    </form>
</div>
@endsection
