# Vehicle API Requirements

## Overview
This document outlines the backend API endpoints that need to be implemented to support the vehicle management frontend that has been fully integrated with API calls.

## Base URL
All endpoints should be prefixed with: `/api/vehicles`

## Authentication
All endpoints require JWT authentication via Bearer token in the Authorization header.

---

## Endpoints

### 1. GET /api/vehicles
**Purpose**: Retrieve list of all vehicles

**Request**:
- Method: GET
- Headers: `Authorization: Bearer {token}`
- Query Parameters (optional):
  - `status`: Filter by vehicle status (Available, Sold, Transferred, Reserved)
  - `dealer`: Filter by dealer ID
  - `page`: Pagination page number
  - `limit`: Number of items per page

**Response** (200 OK):
```json
{
  "vehicles": [
    {
      "id": 1,
      "vehicleCode": "VEH-001",
      "make": "Toyota",
      "model": "Camry",
      "subModel": "XLE",
      "year": 2024,
      "color": "Silver",
      "countryOfOrigin": "Japan",
      "fuelType": "Gasoline",
      "mileage": 15000,
      "transmissionType": "Automatic",
      "registrationType": "Registered",
      "registeredDetails": {
        "registrationNumber": "CAR-2024-001",
        "numberPlate": "ABC 1234",
        "registrationDate": "2024-01-15",
        "numberOfPreviousOwners": 1
      },
      "price": 28500,
      "dealer": "Auto Galaxy Motors",
      "status": "Available",
      "description": "Well-maintained vehicle",
      "images": {
        "frontView": "url_to_image",
        "rearView": "url_to_image",
        "leftSideView": "url_to_image",
        "rightSideView": "url_to_image",
        "interior": "url_to_image",
        "engine": "url_to_image",
        "dashboard": "url_to_image",
        "others": []
      }
    }
  ],
  "total": 100,
  "page": 1,
  "limit": 50
}
```

**Error Responses**:
- 401 Unauthorized: Invalid/missing token
- 500 Internal Server Error

---

### 2. GET /api/vehicles/:id
**Purpose**: Retrieve single vehicle by ID

**Request**:
- Method: GET
- Headers: `Authorization: Bearer {token}`
- URL Parameters: `id` (vehicle ID)

**Response** (200 OK):
```json
{
  "vehicle": {
    "id": 1,
    "vehicleCode": "VEH-001",
    "make": "Toyota",
    "model": "Camry",
    // ... all vehicle fields as shown above
  }
}
```

**Error Responses**:
- 401 Unauthorized
- 404 Not Found: Vehicle doesn't exist
- 500 Internal Server Error

---

### 3. POST /api/vehicles
**Purpose**: Create new vehicle

**Request**:
- Method: POST
- Headers: 
  - `Authorization: Bearer {token}`
  - `Content-Type: application/json`
- Body:
```json
{
  "vehicleCode": "VEH-001",
  "make": "Toyota",
  "model": "Camry",
  "subModel": "XLE",
  "year": 2024,
  "color": "Silver",
  "countryOfOrigin": "Japan",
  "fuelType": "Gasoline",
  "mileage": 15000,
  "transmissionType": "Automatic",
  "registrationType": "Registered",
  "registeredDetails": {
    "registrationNumber": "CAR-2024-001",
    "numberPlate": "ABC 1234",
    "registrationDate": "2024-01-15",
    "numberOfPreviousOwners": 1
  },
  "price": 28500,
  "dealer": "Auto Galaxy Motors",
  "status": "Available",
  "description": "Well-maintained vehicle",
  "images": {
    "frontView": "base64_or_url",
    "rearView": "base64_or_url",
    "leftSideView": "base64_or_url",
    "rightSideView": "base64_or_url",
    "interior": "base64_or_url",
    "engine": "base64_or_url",
    "dashboard": "base64_or_url",
    "others": []
  }
}
```

**Note on Registration Types**:
- If `registrationType` is "Registered", include `registeredDetails`
- If `registrationType` is "Unregistered", include `unregisteredDetails`:
```json
{
  "unregisteredDetails": {
    "chassisNumber": "1FTFW1E85MFA12345",
    "engineNumber": "G567890123",
    "importYear": 2024,
    "auctionGrade": "4.5"
  }
}
```

**Response** (201 Created):
```json
{
  "message": "Vehicle created successfully",
  "vehicle": {
    "id": 123,
    "vehicleCode": "VEH-001",
    // ... all vehicle fields
  }
}
```

**Error Responses**:
- 400 Bad Request: Validation errors
- 401 Unauthorized
- 409 Conflict: Vehicle code already exists
- 500 Internal Server Error

---

### 4. PUT /api/vehicles/:id
**Purpose**: Update existing vehicle

**Request**:
- Method: PUT
- Headers: 
  - `Authorization: Bearer {token}`
  - `Content-Type: application/json`
- URL Parameters: `id` (vehicle ID)
- Body: Same as POST, all fields optional

**Response** (200 OK):
```json
{
  "message": "Vehicle updated successfully",
  "vehicle": {
    "id": 1,
    // ... updated vehicle fields
  }
}
```

**Error Responses**:
- 400 Bad Request: Validation errors
- 401 Unauthorized
- 404 Not Found
- 500 Internal Server Error

---

### 5. DELETE /api/vehicles/:id
**Purpose**: Delete vehicle

**Request**:
- Method: DELETE
- Headers: `Authorization: Bearer {token}`
- URL Parameters: `id` (vehicle ID)

**Response** (200 OK):
```json
{
  "message": "Vehicle deleted successfully"
}
```

**Error Responses**:
- 401 Unauthorized
- 404 Not Found
- 500 Internal Server Error

---

### 6. POST /api/vehicles/:id/images
**Purpose**: Upload additional images for a vehicle

**Request**:
- Method: POST
- Headers: 
  - `Authorization: Bearer {token}`
  - `Content-Type: multipart/form-data`
- URL Parameters: `id` (vehicle ID)
- Body: FormData with files
  - `images[0]`: File
  - `images[1]`: File
  - etc.

**Response** (200 OK):
```json
{
  "message": "Images uploaded successfully",
  "images": ["url1", "url2", "url3"]
}
```

**Error Responses**:
- 400 Bad Request: Invalid file type/size
- 401 Unauthorized
- 404 Not Found: Vehicle doesn't exist
- 413 Payload Too Large
- 500 Internal Server Error

---

## Data Types Reference

### Vehicle Status
- `Available`
- `Sold`
- `Transferred`
- `Reserved`

### Registration Type
- `Registered`
- `Unregistered`

### Transmission Type
- `Automatic`
- `Manual`
- `CVT`
- `Semi-Automatic`

### Fuel Type
- `Gasoline`
- `Diesel`
- `Electric`
- `Hybrid`
- `Plug-in Hybrid`

---

## Image Handling

### Storage Requirements
- Images should be stored in a secure file storage system (e.g., AWS S3, local storage)
- Return URLs to images, not base64 data
- Support formats: JPG, PNG, WEBP
- Maximum file size: 5MB per image
- Image categories:
  - frontView
  - rearView
  - leftSideView
  - rightSideView
  - interior
  - engine
  - dashboard
  - others (array of additional images)

### Image Upload Process
1. Frontend sends base64 or file upload
2. Backend validates file type and size
3. Backend stores file with unique name
4. Backend returns URL to stored image
5. URL is saved in database with vehicle record

---

## Database Schema Recommendations

### vehicles table
```sql
CREATE TABLE vehicles (
  id BIGINT PRIMARY KEY AUTO_INCREMENT,
  vehicle_code VARCHAR(50) UNIQUE NOT NULL,
  make VARCHAR(100) NOT NULL,
  model VARCHAR(100) NOT NULL,
  sub_model VARCHAR(100),
  year INT NOT NULL,
  color VARCHAR(50) NOT NULL,
  country_of_origin VARCHAR(100) NOT NULL,
  fuel_type ENUM('Gasoline', 'Diesel', 'Electric', 'Hybrid', 'Plug-in Hybrid') NOT NULL,
  mileage INT NOT NULL,
  transmission_type ENUM('Automatic', 'Manual', 'CVT', 'Semi-Automatic') NOT NULL,
  registration_type ENUM('Registered', 'Unregistered') NOT NULL,
  price DECIMAL(10, 2) NOT NULL,
  dealer VARCHAR(255),
  status ENUM('Available', 'Sold', 'Transferred', 'Reserved') DEFAULT 'Available',
  description TEXT,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);
```

### registered_vehicle_details table
```sql
CREATE TABLE registered_vehicle_details (
  id BIGINT PRIMARY KEY AUTO_INCREMENT,
  vehicle_id BIGINT UNIQUE NOT NULL,
  registration_number VARCHAR(100) NOT NULL,
  number_plate VARCHAR(50) NOT NULL,
  registration_date DATE NOT NULL,
  number_of_previous_owners INT DEFAULT 0,
  FOREIGN KEY (vehicle_id) REFERENCES vehicles(id) ON DELETE CASCADE
);
```

### unregistered_vehicle_details table
```sql
CREATE TABLE unregistered_vehicle_details (
  id BIGINT PRIMARY KEY AUTO_INCREMENT,
  vehicle_id BIGINT UNIQUE NOT NULL,
  chassis_number VARCHAR(100) NOT NULL,
  engine_number VARCHAR(100) NOT NULL,
  import_year INT NOT NULL,
  auction_grade VARCHAR(10),
  FOREIGN KEY (vehicle_id) REFERENCES vehicles(id) ON DELETE CASCADE
);
```

### vehicle_images table
```sql
CREATE TABLE vehicle_images (
  id BIGINT PRIMARY KEY AUTO_INCREMENT,
  vehicle_id BIGINT NOT NULL,
  image_category ENUM('frontView', 'rearView', 'leftSideView', 'rightSideView', 'interior', 'engine', 'dashboard', 'others') NOT NULL,
  image_url TEXT NOT NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (vehicle_id) REFERENCES vehicles(id) ON DELETE CASCADE
);
```

---

## Integration Status

✅ **Frontend Completed**:
- Vehicle API service created (`frontend/src/api/vehicle.ts`)
- Vehicle list page integrated with `useGetVehicles()` hook
- Vehicle details page integrated with `useGetVehicle(id)` hook
- Vehicle registration form integrated with `createVehicle()` function
- Dashboard pie chart integrated with real-time vehicle count by status
- Loading and error states implemented
- Success/error notifications with snackbar

❌ **Backend Required**:
- All 6 endpoints listed above need implementation
- Database tables need creation
- Image upload and storage system
- JWT authentication middleware
- Input validation and sanitization
- Error handling middleware

---

## Testing Recommendations

### Unit Tests
- Test each endpoint with valid data
- Test validation errors
- Test authentication failures
- Test database constraints

### Integration Tests
- Test complete vehicle creation flow with images
- Test vehicle update with status changes
- Test filtering and pagination
- Test orphaned record cleanup on delete

### Load Tests
- Test concurrent vehicle creation
- Test image upload performance
- Test listing performance with large datasets

---

## Security Considerations

1. **Authentication**: All endpoints must verify JWT token
2. **Authorization**: Verify user has permission to perform action
3. **Input Validation**: Sanitize all inputs to prevent SQL injection
4. **File Upload**: Validate file types, sizes, and scan for malware
5. **Rate Limiting**: Implement rate limiting on image upload endpoints
6. **CORS**: Configure CORS for frontend domain only
7. **SQL Injection**: Use parameterized queries
8. **XSS Prevention**: Sanitize text fields in responses

---

## Next Steps

1. Create Laravel migrations for vehicle tables
2. Create Vehicle model with relationships
3. Create VehicleController with all CRUD methods
4. Implement image upload service
5. Add validation rules (FormRequest classes)
6. Create API routes in `routes/api.php`
7. Test endpoints with Postman/Insomnia
8. Update `.env` with `NEXT_APP_API_URL` pointing to backend
9. Test frontend integration
10. Deploy and monitor

---

## Environment Variables

### Backend (.env)
```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=rbs_vsms
DB_USERNAME=root
DB_PASSWORD=

# File Storage
FILESYSTEM_DISK=local
# or for production:
# FILESYSTEM_DISK=s3
# AWS_ACCESS_KEY_ID=
# AWS_SECRET_ACCESS_KEY=
# AWS_DEFAULT_REGION=
# AWS_BUCKET=

# CORS
SANCTUM_STATEFUL_DOMAINS=localhost:3000
SESSION_DOMAIN=localhost
```

### Frontend (.env.local)
```env
NEXT_APP_API_URL=http://localhost:8000
```

---

**Document Version**: 1.0  
**Last Updated**: 2024  
**Author**: AI Assistant  
**Status**: Ready for Implementation
