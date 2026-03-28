<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Supplier extends Model
{
    protected $fillable = ['name', 'phone', 'email', 'company_name', 'address'];

    public function purchases(): HasMany
    {
        return $this->hasMany(Purchase::class);
    }

    public function purchaseReturns(): HasManyThrough
    {
        return $this->hasManyThrough(PurchaseReturn::class, Purchase::class, 'supplier_id', 'purchase_id');
    }
}
