<?php

namespace App\Http\Controllers;

use App\Models\Client;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class ClientController extends Controller
{
    /**
     * Identifiant de la ferme courante.
     */
    private function tenantId(Request $request): int
    {
        $tenant = $request->attributes->get('tenant');

        abort_unless($tenant, 403, 'Aucune ferme sélectionnée.');

        return (int) $tenant->id;
    }

    /**
     * Vérifie qu'un client appartient à la ferme courante.
     */
    private function verifierClient(Client $client, int $tenantId): void
    {
        abort_unless(
            (int) $client->tenant_id === $tenantId,
            404,
            'Client introuvable.'
        );
    }

    public function index(Request $request): View
    {
        $tenantId = $this->tenantId($request);
        $recherche = $request->string('recherche')->trim()->toString();

        $clients = Client::query()
            ->where('tenant_id', $tenantId)
            ->where('actif', true)
            ->when($recherche !== '', function ($query) use ($recherche) {
                $query->where(function ($q) use ($recherche) {
                    $q->where('nom', 'like', "%{$recherche}%")
                        ->orWhere('telephone', 'like', "%{$recherche}%")
                        ->orWhere('code_client', 'like', "%{$recherche}%");
                });
            })
            ->latest()
            ->paginate(10)
            ->withQueryString();

        return view('clients.index', compact('clients', 'recherche'));
    }

    public function create(Request $request): View
    {
        $this->tenantId($request);

        return view('clients.create');
    }

    public function store(Request $request): RedirectResponse
    {
        $tenantId = $this->tenantId($request);

        $donnees = $request->validate([
            'nom' => ['required', 'string', 'max:150'],
            'telephone' => ['required', 'string', 'max:30'],
            'telephone_secondaire' => ['nullable', 'string', 'max:30'],
            'type_client' => [
                'required',
                Rule::in([
                    'particulier',
                    'revendeur',
                    'restaurant',
                    'grossiste',
                    'autre',
                ]),
            ],
            'quartier' => ['nullable', 'string', 'max:100'],
            'adresse' => ['nullable', 'string', 'max:2000'],
            'notes' => ['nullable', 'string', 'max:2000'],
        ]);

        do {
            $code = 'BF-' . now()->format('ymd') . '-' .
                strtoupper(substr(bin2hex(random_bytes(4)), 0, 6));
        } while (
            Client::where('tenant_id', $tenantId)
                ->where('code_client', $code)
                ->exists()
        );

        $donnees['tenant_id'] = $tenantId;
        $donnees['code_client'] = $code;
        $donnees['actif'] = true;

        $client = Client::create($donnees);

        return redirect()
            ->route('clients.show', $client)
            ->with('success', 'Client ajouté avec succès.');
    }

    public function show(Request $request, Client $client): View
    {
        $tenantId = $this->tenantId($request);
        $this->verifierClient($client, $tenantId);

        $client->load([
            'commandes' => fn ($query) => $query
                ->where('tenant_id', $tenantId)
                ->with([
                    'paiements' => fn ($paiements) => $paiements
                        ->where('tenant_id', $tenantId),
                ])
                ->latest('date_commande'),
        ]);

        return view('clients.show', compact('client'));
    }

    public function edit(Request $request, Client $client): View
    {
        $tenantId = $this->tenantId($request);
        $this->verifierClient($client, $tenantId);

        return view('clients.edit', compact('client'));
    }

    public function update(
        Request $request,
        Client $client
    ): RedirectResponse {
        $tenantId = $this->tenantId($request);
        $this->verifierClient($client, $tenantId);

        $donnees = $request->validate([
            'nom' => ['required', 'string', 'max:150'],
            'telephone' => ['required', 'string', 'max:30'],
            'telephone_secondaire' => ['nullable', 'string', 'max:30'],
            'type_client' => [
                'required',
                Rule::in([
                    'particulier',
                    'revendeur',
                    'restaurant',
                    'grossiste',
                    'autre',
                ]),
            ],
            'quartier' => ['nullable', 'string', 'max:100'],
            'adresse' => ['nullable', 'string', 'max:2000'],
            'notes' => ['nullable', 'string', 'max:2000'],
        ]);

        // Ne jamais accepter tenant_id depuis le formulaire.
        unset($donnees['tenant_id']);

        $client->update($donnees);

        return redirect()
            ->route('clients.show', $client)
            ->with('success', 'Informations du client mises à jour.');
    }

    public function destroy(
        Request $request,
        Client $client
    ): RedirectResponse {
        $tenantId = $this->tenantId($request);
        $this->verifierClient($client, $tenantId);

        // Archivage : les commandes historiques sont conservées.
        $client->update(['actif' => false]);

        return redirect()
            ->route('clients.index')
            ->with('success', 'Client archivé avec succès.');
    }
}
