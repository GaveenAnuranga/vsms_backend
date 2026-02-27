<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * @property int $id
 * @property int|null $tenant_id
 * @property string $vehicle_code
 * @property string $stock_number
 * @property string $make
 * @property string $model
 * @property string|null $sub_model
 * @property string $vehicle_type
 * @property int $year
 * @property string $color
 * @property string $country_of_origin
 * @property string $fuel_type
 * @property int $mileage
 * @property string $transmission_type
 * @property float|null $engine_size
 * @property string|null $vin
 * @property string $registration_type
 * @property float $price
 * @property int $dealer_id
 * @property string $status
 * @property string|null $description
 */
class Vehicle extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<string>
     */
    protected $fillable = [
        'tenant_id',
        'vehicle_code',
        'stock_number',
        'make',
        'model',
        'sub_model',
        'vehicle_type',
        'year',
        'color',
        'country_of_origin',
        'fuel_type',
        'mileage',
        'transmission_type',
        'engine_size',
        'vin',
        'registration_type',
        'price',
        'dealer_id',
        'status',
        'description',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'year' => 'integer',
        'mileage' => 'integer',
        'dealer_id' => 'integer',
        'price' => 'decimal:2',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * Get the registration details for the vehicle.
     */
    public function registration(): HasOne
    {
        return $this->hasOne(VehicleRegistration::class);
    }

    /**
     * Get the import details for the vehicle.
     */
    public function import(): HasOne
    {
        return $this->hasOne(VehicleImport::class);
    }

    /**
     * Get the images for the vehicle.
     */
    public function images(): HasMany
    {
        return $this->hasMany(VehicleImage::class);
    }

    /**
     * Get the dealer that owns the vehicle.
     */
    public function dealer(): BelongsTo
    {
        return $this->belongsTo(Dealer::class);
    }

    /**
     * Get the tenant that owns the vehicle.
     */
    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }

    /**
     * Scope a query to only include available vehicles.
     */
    public function scopeAvailable($query)
    {
        return $query->where('status', 'Available');
    }

    /**
     * Scope a query to filter by status.
     */
    public function scopeByStatus($query, $status)
    {
        return $query->where('status', $status);
    }

    /**
     * Scope a query to filter by dealer.
     */
    public function scopeByDealer($query, $dealerId)
    {
        return $query->where('dealer_id', $dealerId);
    }

    /**
     * Scope a query to filter by registration type.
     */
    public function scopeByRegistrationType($query, $type)
    {
        return $query->where('registration_type', $type);
    }
}
