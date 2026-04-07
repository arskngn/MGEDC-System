<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * @property int $id
 * @property string $name
 * @property string $phone
 * @property string|null $email
 * @property string|null $company_name
 * @property string|null $address
 * @property \Carbon\Carbon|null $created_at
 * @property \Carbon\Carbon|null $updated_at
 * @property \Carbon\Carbon|null $deleted_at
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\Purchase[] $purchases
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\PurchaseReturn[] $purchaseReturns
 */
class Supplier extends Model
{
    use SoftDeletes;
    protected $fillable = ['name', 'phone', 'email', 'company_name', 'address', 'credit_limit'];

    protected $casts = [
        'credit_limit' => 'decimal:2',
    ];

    public function purchases(): HasMany
    {
        return $this->hasMany(Purchase::class);
    }

    public function purchaseReturns(): HasManyThrough
    {
        return $this->hasManyThrough(PurchaseReturn::class, Purchase::class, 'supplier_id', 'purchase_id');
    }
}
