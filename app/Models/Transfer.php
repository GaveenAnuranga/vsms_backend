<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @property int $id
 * @property int|null $tenant_id
 * @property int $vehicle_id
 * @property int|null $from_dealer_id
 * @property int|null $to_dealer_id
 * @property string $transfer_date
 * @property float $transfer_price
 * @property float $transport_cost
 * @property string $status
 * @property string|null $responsible_person
 * @property \Carbon\Carbon $created_at
 * @property \Carbon\Carbon $updated_at
 */
class Transfer extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<string>
     */
    protected $fillable = [
        'tenant_id',
        'vehicle_id',
        'from_dealer_id',
        'to_dealer_id',
        'transfer_date',
        'transfer_price',
        'transport_cost',
        'status',
        'responsible_person',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'transfer_date' => 'date',
        'transfer_price' => 'decimal:2',
        'transport_cost' => 'decimal:2',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * Get the vehicle that belongs to this transfer.
     */
    public function vehicle(): BelongsTo
    {
        return $this->belongsTo(Vehicle::class);
    }

    /**
     * Get the dealer (from) that owns this transfer.
     */
    public function fromDealer(): BelongsTo
    {
        return $this->belongsTo(Dealer::class, 'from_dealer_id');
    }

    /**
     * Get the dealer (to) that owns this transfer.
     */
    public function toDealer(): BelongsTo
    {
        return $this->belongsTo(Dealer::class, 'to_dealer_id');
    }

    /**
     * Get the tenant that owns this transfer.
     */
    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }

    /**
     * Scope a query to only include pending transfers.
     */
    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    /**
     * Scope a query to only include completed transfers.
     */
    public function scopeCompleted($query)
    {
        return $query->where('status', 'completed');
    }
}
