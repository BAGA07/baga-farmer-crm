<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Produit extends Model
{
    protected $table = 'produits';

    protected $fillable = [
        'categorie_id',
        'reference',
        'nom',
        'unite',
        'prix_gros',
        'prix_detail',
        'stock_suivi',
        'quantite_disponible',
        'actif',
    ];

    protected function casts(): array
    {
        return [
            'prix_gros' => 'decimal:2',
            'prix_detail' => 'decimal:2',
            'quantite_disponible' => 'decimal:3',
            'stock_suivi' => 'boolean',
            'actif' => 'boolean',
        ];
    }

    public function categorie(): BelongsTo
    {
        return $this->belongsTo(Categorie::class, 'categorie_id');
    }

    public function lignesCommande(): HasMany
    {
        return $this->hasMany(
            LigneCommande::class,
            'produit_id'
        );
    }
}
