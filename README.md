Vehicle Sales Management System (VSMS)
Backend Documentation
Tech Stack:
- Laravel API Backend
- MySQL Database (rbs_vsms)
- Multi-Tenant SaaS Architecture
System Overview (SRS Summary)
This system is a SaaS-based Vehicle Sales Management System operated under a software
company. Multiple companies (tenants) operate within the system, each managing their own
dealers, vehicles, sales, transfers, and public listings. The platform owner monitors non-sensitive
aggregated data only.
Architecture
Platform Level: Software Company (Platform Admin)
Tenant Level: Registered Main Companies
Sub Level: Partner Dealers
Public Level: Website Visitors
Backend Principles
- API-only Laravel backend
- Multi-tenant data isolation
- Role-based access control
- Subscription enforcement
- Normalized database (3NF)
- Each file <= 150 lines
- Services & repositories for logic separation
Database Structure (db.md)
DATABASE: rbs_vsms
TABLES:
tenants
subscription_plans
users
dealers
vehicles
vehicle_registrations
vehicle_imports
vehicle_images
purchases
sellers
purchase_sellers
transfers
buyers
sales
payment_methods
models
colors
fuel_types
countries
To-Do List (todo.md)
- Multi-tenant authentication
- Subscription enforcement middleware
- Platform dashboard stats API
- Tenant dashboard API
- Dealer dashboard API
- Vehicle management
- Purchase management
- Transfer management
- Sales management
- Reports
- Public website APIs
API Endpoints (endpoint.md)
Authentication
POST /api/login
Request: { email, password }
Response: { token, user }
Vehicles
GET /api/vehicles
POST /api/vehicles
Sales
POST /api/sales
Platform Dashboard
GET /api/platform/dashboard
Returns aggregated non-sensitive statistics
Conclusion
This document defines a clean, scalable, and professional backend foundation for the VSMS SaaS
platform. It ensures security, maintainability, and easy frontend integration using Next.js.