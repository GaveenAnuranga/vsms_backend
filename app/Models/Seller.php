<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

/**
 * @property int $id
 * @property int|null $tenant_id
 * @property string $name
 * @property string $nic_or_reg
 * @property string $address
 * @property string $phone
 * @property string $email
 * @property string $seller_type
 * @property \Carbon\Carbon $created_at
 * @property \Carbon\Carbon $updated_at
 */
class Seller extends Model
{
    use HasFactory;

    protected $fillable = [
        'tenant_id',
        'name',
        'nic_or_reg',
        'address',
        'phone',
        'email',
        'seller_type',
    ];

    /**
     * Get the tenant that owns this seller.
     */
    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }

    /**
     * Get the purchases associated with this seller.
     */
    public function purchases(): BelongsToMany
    {
        return $this->belongsToMany(CarPurchase::class, 'purchase_sellers')
            ->withTimestamps();
    }
}
