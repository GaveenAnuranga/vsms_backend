<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

/**
 * @property int $id
 * @property int|null $tenant_id
 * @property int $vehicle_id
 * @property string $purchase_date
 * @property float $purchase_price
 * @property int $payment_method_id
 * @property string $invoice_number
 * @property float $tax_amount
 * @property string|null $branch
 * @property string|null $document_path
 * @property string|null $tax_details
 * @property \Carbon\Carbon $created_at
 * @property \Carbon\Carbon $updated_at
 */
class CarPurchase extends Model
{
    use HasFactory;

    protected $fillable = [
        'tenant_id',
        'vehicle_id',
        'purchase_date',
        'purchase_price',
        'payment_method_id',
        'invoice_number',
        'tax_amount',
        'branch',
        'document_path',
        'tax_details',
    ];

    protected $casts = [
        'purchase_date' => 'date',
        'purchase_price' => 'decimal:2',
        'tax_amount' => 'decimal:2',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * Get the vehicle that was purchased.
     */
    public function vehicle(): BelongsTo
    {
        return $this->belongsTo(Vehicle::class);
    }

    /**
     * Get the payment method used.
     */
    public function paymentMethod(): BelongsTo
    {
        return $this->belongsTo(PaymentMethod::class, 'payment_method_id');
    }

    /**
     * Get the sellers associated with this purchase.
     */
    public function sellers(): BelongsToMany
    {
        return $this->belongsToMany(Seller::class, 'purchase_sellers')
            ->withTimestamps();
    }

    /**
     * Get the tenant that owns this purchase.
     */
    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }
}
