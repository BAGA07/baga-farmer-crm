<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class LigneCommande extends Model
{
    protected $table = 'lignes_commande';

    protected $fillable = [
        'tenant_id',
        'commande_id',
        'produit_id',
        'nom_produit',
        'unite',
        'quantite',
        'prix_unitaire',
        'remise',
    ];

    protected function casts(): array
    {
        return [
            'quantite' => 'decimal:3',
            'prix_unitaire' => 'decimal:2',
            'remise' => 'decimal:2',
        ];
    }

    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }

    public function commande(): BelongsTo
    {
        return $this->belongsTo(Commande::class, 'commande_id');
    }

    public function produit(): BelongsTo
    {
        return $this->belongsTo(Produit::class, 'produit_id');
    }
}