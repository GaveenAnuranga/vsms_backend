<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Sale Model - Represents vehicle sales transactions
 * 
 * @property int $id
 * @property int|null $tenant_id
 * @property int $vehicle_id
 * @property int $buyer_id
 * @property string $sale_date
 * @property float $sale_price
 * @property float $discount
 * @property float $final_amount
 * @property int $payment_method_id
 * @property string $invoice_number
 * @property float $commission
 * @property string $salesperson_name
 * @property \Carbon\Carbon $created_at
 * @property \Carbon\Carbon $updated_at
 */
class Sale extends Model
{
    use HasFactory;

    protected $table = 'sales';

    protected $fillable = [
        'tenant_id',
        'vehicle_id',
        'buyer_id',
        'sale_date',
        'sale_price',
        'discount',
        'final_amount',
        'payment_method_id',
        'invoice_number',
        'commission',
        'salesperson_name',
    ];

    protected $casts = [
        'sale_date' => 'date',
        'sale_price' => 'decimal:2',
        'discount' => 'decimal:2',
        'final_amount' => 'decimal:2',
        'commission' => 'decimal:2',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * Get the vehicle that was sold.
     */
    public function vehicle(): BelongsTo
    {
        return $this->belongsTo(Vehicle::class);
    }

    /**
     * Get the buyer of the vehicle.
     */
    public function buyer(): BelongsTo
    {
        return $this->belongsTo(Buyer::class);
    }

    /**
     * Get the payment method used.
     */
    public function paymentMethod(): BelongsTo
    {
        return $this->belongsTo(PaymentMethod::class, 'payment_method_id');
    }

    /**
     * Get the seller associated with this sale.
     * Note: Sales table uses salesperson_name directly, but we can link to sellers table
     */
    public function seller(): BelongsTo
    {
        return $this->belongsTo(Seller::class, 'salesperson_name', 'name');
    }

    /**
     * Get the tenant that owns this sale.
     */
    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }
}
