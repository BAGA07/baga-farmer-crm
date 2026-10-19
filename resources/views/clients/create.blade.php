
@extends('layouts.app')

@section('title', 'Nouveau client - Baga Farmer')

@section('content')
<div class="card">
    <h1>Nouveau client</h1>
    <p class="muted">Les champs marqués d'un astérisque sont obligatoires.</p>

    <form method="POST" action="{{ route('clients.store') }}">
        @csrf

        <div class="grid">
            <label>Nom complet ou entreprise *
                <input name="nom" value="{{ old('nom') }}" required maxlength="150">
            </label>

            <label>Téléphone *
                <input name="telephone" type="tel" value="{{ old('telephone') }}"
                       required maxlength="30">
            </label>

            <label>Téléphone secondaire
                <input name="telephone_secondaire"
                       value="{{ old('telephone_secondaire') }}" maxlength="30">
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
                            @selected(old('type_client', 'particulier') === $value)>
                            {{ $label }}
                        </option>
                    @endforeach
                </select>
            </label>

            <label>Quartier
                <input name="quartier" value="{{ old('quartier') }}" maxlength="100">
            </label>
        </div>

        <label>Adresse
            <textarea name="adresse" rows="3">{{ old('adresse') }}</textarea>
        </label>

        <label>Notes
            <textarea name="notes" rows="3">{{ old('notes') }}</textarea>
        </label>

        <div class="actions">
            <button class="btn" type="submit">Enregistrer le client</button>
            <a class="btn btn-secondary" href="{{ route('clients.index') }}">Annuler</a>
        </div>
    </form>
</div>
@endsection
