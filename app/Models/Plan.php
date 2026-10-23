<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Plan extends Model
{
    protected $fillable = [
        'name',
        'slug',
        'description',
        'price_monthly',
        'price_quarterly',
        'price_yearly',
        'features',
        'max_users',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'price_monthly' => 'integer',
            'price_quarterly' => 'integer',
            'price_yearly' => 'integer',
            'features' => 'array',
            'max_users' => 'integer',
            'is_active' => 'boolean',
        ];
    }

    public function subscriptions(): HasMany
    {
        return $this->hasMany(Subscription::class);
    }
}
