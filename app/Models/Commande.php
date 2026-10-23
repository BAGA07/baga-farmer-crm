<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Commande extends Model
{
    protected $table = 'commandes';

    protected $fillable = [
        'tenant_id',
        'numero',
        'client_id',
        'user_id',
        'date_commande',
        'date_livraison_prevue',
        'statut',
        'adresse_livraison',
        'frais_livraison',
        'remise',
        'notes',
    ];

    protected function casts(): array
    {
        return [
            'date_commande' => 'datetime',
            'date_livraison_prevue' => 'datetime',
            'frais_livraison' => 'decimal:2',
            'remise' => 'decimal:2',
        ];
    }

    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }

    public function client(): BelongsTo
    {
        return $this->belongsTo(Client::class, 'client_id');
    }

    public function utilisateur(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function lignes(): HasMany
    {
        return $this->hasMany(LigneCommande::class, 'commande_id');
    }

    public function paiements(): HasMany
    {
        return $this->hasMany(Paiement::class, 'commande_id');
    }
}
