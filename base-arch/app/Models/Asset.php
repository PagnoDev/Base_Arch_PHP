<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Asset extends Model
{
    use HasFactory;

    protected $fillable = [
        'symbol',
        'name',
        'asset_class',
        'risk_level',
        'active',
    ];

    protected function casts(): array
    {
        return [
            'active' => 'boolean',
        ];
    }

    public function positions(): HasMany
    {
        return $this->hasMany(PortfolioPosition::class);
    }

    public function prices(): HasMany
    {
        return $this->hasMany(PriceHistory::class);
    }
}
