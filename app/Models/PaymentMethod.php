<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * @property int $id
 * @property string $name
 * @property \Carbon\Carbon $created_at
 * @property \Carbon\Carbon $updated_at
 */
class PaymentMethod extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
    ];

    /**
     * Get the purchases that use this payment method.
     */
    public function purchases(): HasMany
    {
        return $this->hasMany(CarPurchase::class);
    }
}
