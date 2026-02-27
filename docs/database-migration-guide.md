# Database Migration Guide for Vehicle Management System

## Overview
The database schema has been updated to properly support the vehicle management API requirements. This document outlines the changes made and how to apply them.

---

## Changes Made

### 1. Updated `db.md` Documentation
The database schema documentation has been updated to reflect the correct vehicle table structure and related tables.

### 2. Updated Migrations

#### **vehicles table** (`2024_01_01_000013_create_vehicles_table.php`)
**Before**: Had incorrect fields (name, email, phone, address - which are dealer fields)

**After**: Complete vehicle information structure
- `vehicle_code` (varchar 50, unique) - Unique identifier for each vehicle
- `make` (varchar 100) - Vehicle manufacturer (Toyota, Honda, etc.)
- `model` (varchar 100) - Vehicle model (Camry, Accord, etc.)
- `sub_model` (varchar 100, nullable) - Sub-model variant (XLE, Sport, etc.)
- `year` (int) - Manufacturing year
- `color` (varchar 50) - Vehicle color
- `country_of_origin` (varchar 100) - Country where vehicle was manufactured
- `fuel_type` (enum) - Gasoline, Diesel, Electric, Hybrid, Plug-in Hybrid
- `mileage` (int) - Current odometer reading
- `transmission_type` (enum) - Automatic, Manual, CVT, Semi-Automatic
- `engine_size` (varchar 50, nullable) - Engine displacement (e.g., "2.0L", "3.5L V6")
- `vin` (varchar 100, nullable) - Vehicle Identification Number
- `registration_type` (enum) - Registered, Unregistered
- `price` (decimal 12,2) - Vehicle price
- `dealer_id` (bigint, fk, nullable) - Reference to assigned dealer
- `status` (enum) - Available, Sold, Transferred, Reserved
- `description` (text, nullable) - Additional notes about the vehicle

#### **vehicle_registrations table** (`2024_01_01_000020_create_vehicle_registrations_table.php`)
**Changes**:
- Made `vehicle_id` unique (one registration per vehicle)
- Renamed `plate_number` to `number_plate` for consistency with frontend
- Renamed `previous_owners` to `number_of_previous_owners` for clarity
- Changed `registration_number` length from 50 to 100 characters
- Added `onDelete('cascade')` to foreign key

#### **vehicle_imports table** (`2024_01_01_000021_create_vehicle_imports_table.php`)
**Changes**:
- Made `vehicle_id` unique (one import record per vehicle)
- Removed `unique` constraint from `chassis_number` (moved to vehicle_id)
- Changed `import_year` from `year` type to `integer` for better compatibility
- Made `auction_grade` nullable
- Added `onDelete('cascade')` to foreign key

#### **vehicle_images table** (`2024_01_01_000022_create_vehicle_images_table.php`)
**Changes**:
- Added `image_category` enum field with values:
  - `frontView` - Front view of vehicle
  - `rearView` - Rear view of vehicle
  - `leftSideView` - Left side view
  - `rightSideView` - Right side view
  - `interior` - Interior view
  - `engine` - Engine bay view
  - `dashboard` - Dashboard view
  - `others` - Additional miscellaneous images
- Renamed `image_path` to `image_url` for consistency
- Changed `image_url` to `text` type to support long URLs
- Added `onDelete('cascade')` to foreign key

---

## Database Schema Diagram

```
tenants
  ├── vehicles (1:many)
  │     ├── vehicle_registrations (1:1)
  │     ├── vehicle_imports (1:1)
  │     ├── vehicle_images (1:many)
  │     ├── purchases (1:many)
  │     ├── sales (1:many)
  │     └── transfers (1:many)
  ├── dealers (1:many)
  └── ...

dealers
  └── vehicles (1:many via dealer_id)
```

---

## Migration Instructions

### If Database Doesn't Exist Yet (Fresh Install)

1. **Configure Database Connection**
   ```bash
   cd backend
   cp .env.example .env
   ```

2. **Update `.env` file**
   ```env
   DB_CONNECTION=mysql
   DB_HOST=127.0.0.1
   DB_PORT=3306
   DB_DATABASE=rbs_vsms
   DB_USERNAME=root
   DB_PASSWORD=your_password
   ```

3. **Create Database**
   ```sql
   CREATE DATABASE rbs_vsms CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
   ```

4. **Run Migrations**
   ```bash
   php artisan migrate
   ```

### If Database Already Exists (Need to Update)

**⚠️ WARNING**: This will delete all existing vehicle data!

**Option 1: Fresh Migration (Development Only)**
```bash
php artisan migrate:fresh
```

**Option 2: Rollback and Re-migrate (Development Only)**
```bash
# Rollback vehicle-related migrations
php artisan migrate:rollback --step=7

# Re-run migrations
php artisan migrate
```

**Option 3: Manual Migration for Production (Recommended)**

If you have production data, you'll need to:

1. **Backup existing data**
   ```bash
   mysqldump -u root -p rbs_vsms > backup_$(date +%Y%m%d_%H%M%S).sql
   ```

2. **Create a data migration script** (manual SQL):
   ```sql
   -- Backup existing vehicle data if any
   CREATE TABLE vehicles_backup AS SELECT * FROM vehicles;
   
   -- Drop and recreate vehicles table
   DROP TABLE IF EXISTS vehicle_images;
   DROP TABLE IF EXISTS vehicle_imports;
   DROP TABLE IF EXISTS vehicle_registrations;
   DROP TABLE IF EXISTS vehicles;
   
   -- Now run: php artisan migrate
   ```

3. **Run the migration**
   ```bash
   php artisan migrate
   ```

---

## Verification

After running migrations, verify the tables:

```bash
php artisan tinker
```

Then in Tinker:
```php
// Check if tables exist
Schema::hasTable('vehicles'); // should return true
Schema::hasTable('vehicle_registrations'); // should return true
Schema::hasTable('vehicle_imports'); // should return true
Schema::hasTable('vehicle_images'); // should return true

// Check columns
Schema::hasColumn('vehicles', 'vehicle_code'); // should return true
Schema::hasColumn('vehicles', 'make'); // should return true
Schema::hasColumn('vehicles', 'model'); // should return true
Schema::hasColumn('vehicle_images', 'image_category'); // should return true
```

Or use SQL:
```sql
DESCRIBE vehicles;
DESCRIBE vehicle_registrations;
DESCRIBE vehicle_imports;
DESCRIBE vehicle_images;
```

---

## Sample Data

To test the API with sample data, you can create a seeder:

```bash
php artisan make:seeder VehicleSeeder
```

Example seeder content:
```php
<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class VehicleSeeder extends Seeder
{
    public function run()
    {
        $vehicleId = DB::table('vehicles')->insertGetId([
            'tenant_id' => 1,
            'vehicle_code' => 'VEH-' . time(),
            'make' => 'Toyota',
            'model' => 'Camry',
            'sub_model' => 'XLE',
            'year' => 2024,
            'color' => 'Silver',
            'country_of_origin' => 'Japan',
            'fuel_type' => 'Gasoline',
            'mileage' => 15000,
            'transmission_type' => 'Automatic',
            'registration_type' => 'Registered',
            'price' => 28500.00,
            'status' => 'Available',
            'description' => 'Well-maintained vehicle with full service history',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        DB::table('vehicle_registrations')->insert([
            'vehicle_id' => $vehicleId,
            'registration_number' => 'CAR-2024-001',
            'number_plate' => 'ABC 1234',
            'registration_date' => '2024-01-15',
            'number_of_previous_owners' => 1,
        ]);

        DB::table('vehicle_images')->insert([
            'vehicle_id' => $vehicleId,
            'image_category' => 'frontView',
            'image_url' => '/storage/vehicles/front-' . $vehicleId . '.jpg',
            'created_at' => now(),
        ]);
    }
}
```

Run the seeder:
```bash
php artisan db:seed --class=VehicleSeeder
```

---

## Common Issues & Solutions

### Issue 1: Foreign Key Constraint Fails
**Error**: `SQLSTATE[23000]: Integrity constraint violation`

**Solution**: Ensure migrations run in correct order. Tenants and dealers must exist before vehicles.

### Issue 2: Enum Values Not Matching
**Error**: `SQLSTATE[HY000]: General error: 1265 Data truncated`

**Solution**: Ensure you're using exact enum values (case-sensitive):
- Status: `Available`, `Sold`, `Transferred`, `Reserved` (NOT `active`, `inactive`)
- Fuel Type: `Gasoline`, `Diesel`, `Electric`, `Hybrid`, `Plug-in Hybrid`

### Issue 3: Unique Constraint on vehicle_code
**Error**: `SQLSTATE[23000]: Integrity constraint violation: 1062 Duplicate entry`

**Solution**: Ensure vehicle_code is unique. Use timestamp-based generation:
```php
$vehicleCode = 'VEH-' . time() . '-' . rand(100, 999);
```

---

## Next Steps

1. ✅ Database migrations updated
2. ✅ Documentation updated
3. ⏳ Create Vehicle Model
4. ⏳ Create VehicleController
5. ⏳ Add validation rules
6. ⏳ Implement API routes
7. ⏳ Test with Postman
8. ⏳ Frontend integration test

---

## Related Files

- **Migration Files**: `backend/database/migrations/2024_01_01_0000*_create_*_table.php`
- **Database Schema**: `backend/docs/db.md`
- **API Requirements**: `backend/docs/vehicle-api-requirements.md`
- **Frontend Integration**: `backend/docs/frontend-integration-summary.md`

---

**Status**: ✅ Ready for Migration  
**Last Updated**: February 20, 2026  
**Version**: 1.0
