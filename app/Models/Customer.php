<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;

class Customer extends Model
{
    protected $fillable = ['name', 'phone', 'email', 'address'];

    public function sales(): HasMany
    {
        return $this->hasMany(Sale::class);
    }

    public function saleReturns(): HasManyThrough
    {
        // Use string class name to keep IDE/linter happy during early module scaffolding.
        return $this->hasManyThrough(SaleReturn::class, Sale::class, 'customer_id', 'sale_id');
    }
}

