<?php

namespace App\Http\Controllers;

use App\Models\Client;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class ClientController extends Controller
{
    public function index(Request $request): View
    {
        $recherche = $request->string('recherche')->trim()->toString();

        $clients = Client::query()
            ->when($recherche !== '', function ($query) use ($recherche) {
                $query->where(function ($q) use ($recherche) {
                    $q->where('nom', 'like', "%{$recherche}%")
                        ->orWhere('telephone', 'like', "%{$recherche}%")
                        ->orWhere('code_client', 'like', "%{$recherche}%");
                });
            })
            ->where('actif', true)
            ->latest()
            ->paginate(10)
            ->withQueryString();

        return view('clients.index', compact('clients', 'recherche'));
    }

    public function create(): View
    {
        return view('clients.create');
    }

    public function store(Request $request): RedirectResponse
    {
        $donnees = $request->validate([
            'nom' => ['required', 'string', 'max:150'],
            'telephone' => ['required', 'string', 'max:30'],
            'telephone_secondaire' => ['nullable', 'string', 'max:30'],
            'type_client' => [
                'required',
                Rule::in(['particulier', 'revendeur', 'restaurant', 'grossiste', 'autre']),
            ],
            'quartier' => ['nullable', 'string', 'max:100'],
            'adresse' => ['nullable', 'string', 'max:2000'],
            'notes' => ['nullable', 'string', 'max:2000'],
        ]);

        // Génération d'un code client unique.
        do {
            $code = 'BF-' . now()->format('ymd') . '-' . strtoupper(
                substr(bin2hex(random_bytes(4)), 0, 6)
            );
        } while (Client::where('code_client', $code)->exists());

        $donnees['code_client'] = $code;
        $donnees['actif'] = true;

        $client = Client::create($donnees);

        return redirect()
            ->route('clients.show', $client)
            ->with('success', 'Client ajouté avec succès.');
    }

    public function show(Client $client): View
    {
        $client->load([
            'commandes' => fn ($query) => $query
                ->with('paiements')
                ->latest('date_commande'),
        ]);

        return view('clients.show', compact('client'));
    }

    public function edit(Client $client): View
    {
        return view('clients.edit', compact('client'));
    }

    public function update(Request $request, Client $client): RedirectResponse
    {
        $donnees = $request->validate([
            'nom' => ['required', 'string', 'max:150'],
            'telephone' => ['required', 'string', 'max:30'],
            'telephone_secondaire' => ['nullable', 'string', 'max:30'],
            'type_client' => [
                'required',
                Rule::in(['particulier', 'revendeur', 'restaurant', 'grossiste', 'autre']),
            ],
            'quartier' => ['nullable', 'string', 'max:100'],
            'adresse' => ['nullable', 'string', 'max:2000'],
            'notes' => ['nullable', 'string', 'max:2000'],
        ]);

        $client->update($donnees);

        return redirect()
            ->route('clients.show', $client)
            ->with('success', 'Informations du client mises à jour.');
    }

    public function destroy(Client $client): RedirectResponse
    {
        // Archivage : les commandes historiques sont conservées.
        $client->update(['actif' => false]);

        return redirect()
            ->route('clients.index')
            ->with('success', 'Client archivé avec succès.');
    }
}
