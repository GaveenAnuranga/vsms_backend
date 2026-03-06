<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class VehicleImport extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<string>
     */
    protected $fillable = [
        'vehicle_id',
        // Unregistered vehicle fields
        'chassis_number',
        'engine_number',
        // Import date
        'imported_date',
        // Exporter fields
        'exporter_name',
        'exporter_contact',
        // Registration notification fields
        'register_notification',
        'register_notification_date',
        'notification_dismissed',
        // Legacy fields (kept for backward compat)
        'import_year',
        'auction_grade',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'import_year' => 'integer',
        'imported_date' => 'date',
        'register_notification' => 'boolean',
        'register_notification_date' => 'date',
        'notification_dismissed' => 'boolean',
    ];

    /**
     * Get the vehicle that owns the import details.
     */
    public function vehicle(): BelongsTo
    {
        return $this->belongsTo(Vehicle::class);
    }

    /**
     * Indicates if the model should be timestamped.
     *
     * @var bool
     */
    public $timestamps = false;
}
