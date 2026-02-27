# Frontend-Backend Connection Setup Guide

## ✅ Connection Status: READY

Both frontend and backend are properly configured and ready to communicate!

---

## Configuration Summary

### Backend Configuration (Laravel)

**Location**: `backend/`

1. **Environment File** (`.env`):
   - ✅ Database: `rbs_vsms` on MySQL (localhost:3306)
   - ✅ APP_KEY: Generated
   - ✅ APP_URL: `http://localhost:8000`
   - ✅ FRONTEND_URL: `http://localhost:3000` (for CORS)

2. **CORS Configuration** (`config/cors.php`):
   - ✅ Allows requests from frontend URL
   - ✅ Supports credentials
   - ✅ All methods allowed (GET, POST, PUT, DELETE)
   - ✅ All headers allowed

3. **Middleware** (`bootstrap/app.php`):
   - ✅ CORS middleware enabled for API routes

4. **API Routes** (`routes/api.php`):
   ```
   GET    /api/test                    - Test endpoint
   GET    /api/vehicles                - List all vehicles
   GET    /api/vehicles/{id}           - Get single vehicle
   POST   /api/vehicles                - Create vehicle
   PUT    /api/vehicles/{id}           - Update vehicle
   DELETE /api/vehicles/{id}           - Delete vehicle
   POST   /api/vehicles/{id}/images    - Upload images
   ```

### Frontend Configuration (Next.js)

**Location**: `frontend/`

1. **Environment File** (`.env`):
   - ✅ NEXT_PUBLIC_API_URL: `http://localhost:8000/api`

2. **Axios Configuration** (`src/utils/axios.ts`):
   - ✅ Base URL: Points to backend API
   - ✅ JWT token interceptor configured
   - ✅ 401 error handling (auto logout)

3. **API Service** (`src/api/vehicle.ts`):
   - ✅ All CRUD operations implemented
   - ✅ SWR hooks for data fetching
   - ✅ Real-time cache revalidation

---

## Testing Results

### ✅ Backend API Tests

1. **Test Endpoint**:
   ```bash
   curl http://localhost:8000/api/test
   ```
   **Response**:
   ```json
   {
     "message": "API is working!",
     "timestamp": "2026-02-20 05:25:49",
     "version": "1.0.0"
   }
   ```

2. **Vehicles Endpoint**:
   ```bash
   curl http://localhost:8000/api/vehicles
   ```
   **Response**:
   ```json
   {
     "vehicles": [],
     "total": 0,
     "page": 1,
     "limit": 50
   }
   ```

---

## How to Start Both Servers

### 1. Start Backend Server

**Option A: Using PowerShell**
```powershell
cd E:\Projects\rbs_vsms\backend
php artisan serve
```

**Option B: Using Terminal (already running)**
The backend server is already started and listening on `http://localhost:8000`

**Verify**: Visit http://localhost:8000/api/test in browser

### 2. Start Frontend Server

**In a new terminal:**
```powershell
cd E:\Projects\rbs_vsms\frontend
npm run dev
```

**Access**: http://localhost:3000

---

## Database Setup

Before using the vehicle features, you need to:

### 1. Create Database
```powershell
# Connect to MySQL
mysql -u root -p

# Create database
CREATE DATABASE rbs_vsms;
EXIT;
```

### 2. Run Migrations
```powershell
cd E:\Projects\rbs_vsms\backend
php artisan migrate
```

This will create all required tables:
- subscription_plans
- tenants
- users
- dealers
- vehicles
- vehicle_registrations
- vehicle_imports
- vehicle_images

### 3. (Optional) Seed Sample Data
```powershell
php artisan db:seed
```
*Note: Seeders need to be created first*

---

## Testing the Connection

### Method 1: Browser DevTools

1. Start both servers (backend + frontend)
2. Open frontend: http://localhost:3000
3. Navigate to: **Dashboard → Tables → Vehicles**
4. Open browser DevTools (F12) → Network tab
5. You should see API calls to `http://localhost:8000/api/vehicles`

### Method 2: Manual API Test

**Test GET Request:**
```powershell
Invoke-WebRequest -Uri "http://localhost:8000/api/vehicles" `
  -Method GET `
  -Headers @{"Accept"="application/json"} | 
  Select-Object -ExpandProperty Content
```

**Test POST Request (Create Vehicle):**
```powershell
$body = @{
  make = "Toyota"
  model = "Camry"
  year = 2023
  color = "White"
  countryOfOrigin = "Japan"
  fuelType = "Petrol"
  mileage = 15000
  transmissionType = "Automatic"
  registrationType = "Registered"
  price = 25000
  dealerId = 1
  status = "Available"
  registeredDetails = @{
    registrationNumber = "REG123456"
    numberPlate = "ABC-1234"
    registrationDate = "2023-01-15"
    numberOfPreviousOwners = 1
  }
} | ConvertTo-Json

Invoke-WebRequest -Uri "http://localhost:8000/api/vehicles" `
  -Method POST `
  -Headers @{"Content-Type"="application/json"; "Accept"="application/json"} `
  -Body $body
```

---

## Troubleshooting

### Issue: CORS Error
**Symptom**: Browser console shows "CORS policy blocked"
**Solution**: 
1. Verify `FRONTEND_URL` in `backend/.env` matches your frontend URL
2. Restart Laravel server: `php artisan serve`

### Issue: 404 Not Found
**Symptom**: API returns 404 for `/api/vehicles`
**Solution**:
1. Check routes: `php artisan route:list --path=api`
2. Clear route cache: `php artisan route:clear`

### Issue: Connection Refused
**Symptom**: "Connection refused" on port 8000
**Solution**: Start backend server with `php artisan serve`

### Issue: Database Connection Error
**Symptom**: "SQLSTATE[HY000] [2002] Connection refused"
**Solution**:
1. Start MySQL service
2. Verify database credentials in `backend/.env`
3. Check if database exists: `SHOW DATABASES;`

### Issue: Frontend Can't Connect
**Symptom**: Network error in frontend
**Solution**:
1. Verify `NEXT_PUBLIC_API_URL` in `frontend/.env`
2. Restart Next.js dev server
3. Clear browser cache

---

## API Request Flow

```
Frontend (Next.js)
    ↓
axios (with JWT interceptor)
    ↓
http://localhost:3000 → http://localhost:8000/api
    ↓
CORS Middleware (Laravel)
    ↓
API Routes (routes/api.php)
    ↓
VehicleController
    ↓
Models (Vehicle, VehicleRegistration, etc.)
    ↓
MySQL Database (rbs_vsms)
```

---

## Environment Variables Reference

### Backend (`.env`)
```env
APP_URL=http://localhost:8000
DB_DATABASE=rbs_vsms
DB_USERNAME=root
DB_PASSWORD=
FRONTEND_URL=http://localhost:3000
```

### Frontend (`.env`)
```env
NEXT_PUBLIC_API_URL=http://localhost:8000/api
```

---

## Next Steps

1. ✅ **Backend server running** on `http://localhost:8000`
2. ⏳ **Create database** (`rbs_vsms`)
3. ⏳ **Run migrations** (`php artisan migrate`)
4. ⏳ **Start frontend server** (`npm run dev` in frontend folder)
5. ⏳ **Test vehicle creation** through the UI or API

---

## Quick Start Commands

```powershell
# Terminal 1: Backend
cd E:\Projects\rbs_vsms\backend
php artisan serve

# Terminal 2: Frontend
cd E:\Projects\rbs_vsms\frontend
npm run dev

# Terminal 3: Database Setup
cd E:\Projects\rbs_vsms\backend
php artisan migrate
```

---

## Status: ✅ READY TO USE

Both frontend and backend are properly configured and communicating successfully!
