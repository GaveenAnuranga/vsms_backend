# Authentication System Setup

## Overview
The RBS VSMS (Vehicle Sales Management System) authentication has been configured to support **login-only access**. User registration is disabled, and all users must be created by administrators.

---

## Backend (Laravel) Configuration

### 1. Database Structure

**Users Table** (`users` table):
- `id` - Primary key
- `name` - User full name
- `email` - Unique email (login identifier)
- `password` - Hashed password
- `tenant_id` - Foreign key to tenants (nullable)
- `role` - Enum: `platform_admin`, `company_admin`, `dealer_admin`
- `status` - Enum: `active`, `inactive` (default: `active`)
- `timestamps` - Created/Updated timestamps

### 2. User Roles

Three user levels are defined in the system:

1. **Platform Admin** (`platform_admin`) - Super admin with full system access
2. **Company Admin** (`company_admin`) - Manages company-level operations
3. **Dealer Admin** (`dealer_admin`) - Manages dealer-level operations

### 3. Default Admin Users

When running database migrations and seeders, three admin accounts are created:

#### Super Admin (Platform Admin)
- **Email**: `sadmin@gmail.com`
- **Password**: `Sadnim@1234`
- **Role**: `platform_admin`
- **Access**: Full system access (all dashboards and tables)

#### Company Admin (for future use)
- **Email**: `companyadmin@example.com`
- **Password**: `CompanyAdmin@1234`
- **Role**: `company_admin`

#### Dealer Admin (for future use)
- **Email**: `dealeradmin@example.com`
- **Password**: `DealerAdmin@1234`
- **Role**: `dealer_admin`

### 4. API Endpoints

**Base URL**: `http://localhost:8000/api`

#### Authentication Endpoints:

1. **Login** - `POST /account/login`
   ```json
   Request:
   {
     "email": "sadmin@gmail.com",
     "password": "Sadnim@1234"
   }
   
   Response:
   {
     "success": true,
     "message": "Login successful",
     "user": {
       "id": 1,
       "name": "Super Admin",
       "email": "sadmin@gmail.com",
       "role": "platform_admin",
       "status": "active",
       "tenant_id": null
     },
     "serviceToken": "base64_encoded_token"
   }
   ```

2. **Register** - `POST /account/register` - **DISABLED**
   ```json
   Response:
   {
     "success": false,
     "message": "Registration is disabled. Users must be created by administrators."
   }
   ```

3. **Logout** - `POST /account/logout`
   ```json
   Response:
   {
     "success": true,
     "message": "Logged out successfully"
   }
   ```

4. **Get Current User** - `GET /account/me`
   ```json
   Response:
   {
     "success": true,
     "user": {
       "id": 1,
       "name": "Super Admin",
       "email": "sadmin@gmail.com",
       "role": "platform_admin",
       "status": "active",
       "tenant_id": null
     }
   }
   ```

### 5. Security Features

- Passwords are hashed using Laravel's `Hash` facade (bcrypt)
- User status is checked on login - inactive users cannot log in
- Session-based authentication using Laravel's Auth facade
- Simple token generation for API access

---

## Frontend (Next.js) Configuration

### 1. Authentication Provider

The frontend uses **NextAuth.js** for authentication management.

**Configuration File**: `frontend/src/utils/authOptions.ts`

Key features:
- Single credentials provider (login only)
- JWT-based session management
- Session timeout: 24 hours (86400 seconds)
- Custom callbacks for role and status handling

### 2. Login Flow

1. User visits landing page at `http://localhost:3000/`
2. User clicks "Login to Dashboard" button (in hero section or header)
3. Redirected to `/login`
4. User enters email and password
5. Credentials are sent to backend API
6. On success, user is redirected to `/admin-dashboard`
7. Session token is stored and used for subsequent API calls

### 3. Protected Routes

The application uses `AuthGuard` component to protect dashboard routes:
- **Location**: `frontend/src/utils/route-guard/AuthGuard.tsx`
- Automatically redirects unauthenticated users to `/login`
- Checks session status on route navigation

### 4. User Session

Session data includes:
```typescript
{
  user: {
    id: string,
    name: string,
    email: string,
    role: 'platform_admin' | 'company_admin' | 'dealer_admin',
    status: 'active' | 'inactive'
  },
  token: {
    accessToken: string
  }
}
```

### 5. UI Changes

#### Landing Page Updates:
- **Hero Section**: Added "Login to Dashboard" button
- **Header**: Simplified to show only "Login" button
- **Mobile Menu**: Simplified to show only "Login" option

#### Login Page Updates:
- Removed "Don't have an account?" link
- Empty default credentials (no pre-filled values)
- Removed password max length validation

#### Register Page:
- Redirects to `/login` automatically (registration disabled)

---

## Running the Application

### 1. Backend Setup

```bash
cd backend

# Install dependencies
composer install

# Run migrations and seed super admin
php artisan migrate:fresh --seed

# Start Laravel server
php artisan serve
```

Backend will run at: `http://localhost:8000`

### 2. Frontend Setup

```bash
cd frontend

# Install dependencies
npm install

# Start Next.js development server
npm run dev
```

Frontend will run at: `http://localhost:3000`

---

## Testing Authentication

### Test Super Admin Login:

1. Navigate to `http://localhost:3000/`
2. Click "Login to Dashboard" button
3. Enter credentials:
   - **Email**: `sadmin@gmail.com`
   - **Password**: `Sadnim@1234`
4. Click "Login"
5. You should be redirected to `/admin-dashboard`
6. Super admin has access to:
   - All dashboard views
   - All tables (Vehicles, Dealers, Sellers, Buyers, etc.)
   - All management features

### Test Failed Login:

1. Try logging in with incorrect credentials
2. Error message should appear: "Invalid email or password"

### Test Inactive User:

1. Update a user's status to `inactive` in the database
2. Try logging in with that user
3. Error message: "Your account is inactive. Please contact administrator."

---

## File Changes Summary

### Backend Files Modified:
1. `backend/database/seeders/UserSeeder.php` - Added three admin users
2. `backend/app/Http/Controllers/Api/AuthController.php` - 
   - Updated login to return role and status
   - Disabled registration endpoint
   - Added active status check

### Frontend Files Modified:
1. `frontend/src/utils/authOptions.ts` - 
   - Removed registration provider
   - Added role and status to session
   - Updated error handling
2. `frontend/src/views/authentication/Login.tsx` - Removed sign-up link
3. `frontend/src/sections/auth/auth-forms/AuthLogin.tsx` - 
   - Empty default credentials
   - Removed password max length validation
4. `frontend/src/sections/landing/HeroSection.tsx` - Added login button
5. `frontend/src/layout/SimpleLayout/Header.tsx` - Simplified to show only login
6. `frontend/src/app/(auth)/register/page.tsx` - Redirects to login

---

## Security Recommendations

1. **Change Default Passwords**: Update super admin password after first login
2. **Environment Variables**: Keep `.env` files secure (never commit to version control)
3. **HTTPS**: Use HTTPS in production
4. **JWT Secret**: Use strong, random JWT secrets in production
5. **Rate Limiting**: Implement login rate limiting to prevent brute force attacks
6. **Password Policy**: Enforce strong password requirements

---

## Future Enhancements

1. **Role-Based Access Control (RBAC)**: Implement middleware to restrict routes based on user role
2. **User Management UI**: Create admin interface for creating/managing users
3. **Password Reset**: Implement forgot password functionality
4. **2FA**: Add two-factor authentication for enhanced security
5. **Activity Logs**: Track user login/logout and actions
6. **Token Expiration**: Implement token refresh mechanism
7. **Session Management**: Add ability to view/revoke active sessions

---

## Support

For issues or questions regarding authentication:
- Check Laravel logs: `backend/storage/logs/laravel.log`
- Check browser console for frontend errors
- Verify backend API is running: `http://localhost:8000/api/test`
- Verify environment variables are correctly set in `.env` files
