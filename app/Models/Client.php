<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Client extends Model
{
    protected $table = 'clients';

    protected $fillable = [
        'code_client',
        'nom',
        'telephone',
        'telephone_secondaire',
        'type_client',
        'quartier',
        'adresse',
        'notes',
        'actif',
    ];

    protected function casts(): array
    {
        return [
            'actif' => 'boolean',
        ];
    }

    public function commandes(): HasMany
    {
        return $this->hasMany(Commande::class, 'client_id');
    }
}
