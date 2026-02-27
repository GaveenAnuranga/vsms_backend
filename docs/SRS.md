# Vehicle Sales Management System - Software Requirements Specification

**Version:** 1.0  
**Date:** February 18, 2026

---

## 1. Overview

### 1.1 Purpose

This document describes the requirements for the Vehicle Sales Management System (VSMS), a web-based application designed for a car trading business operating under a multi-entity structure.

### 1.2 System Structure

The system consists of:
- **One Main Company (Head Office)**
- **Multiple Partner Dealers** (company-owned or independent)
- **A Public-facing Company Website**

### 1.3 Scope

The system will centralize:
- Vehicle inventory management
- Transaction tracking
- Public vehicle listing
- Multi-location operations
- Administrative functions

---

## 2. User Roles & Permissions

### 2.1 Super Admin (Main Company)

**Access Level:** Full system access

**Capabilities:**
- Add purchased vehicle entries
- Update vehicle status (sold/transferred)
- Add and manage partner dealers
- View all dealer inventories
- Access all system reports
- Manage website content
- Control vehicle publishing

### 2.2 Dealer Admin (Partner Dealer)

**Access Level:** Own inventory only

**Capabilities:**
- Manage their own inventory
- Add purchased vehicles
- Transfer vehicles (when permitted)
- Record sales transactions
- View dealer-specific reports

### 2.3 Website Visitor (Customer)

**Access Level:** Public website only

**Capabilities:**
- Browse vehicle catalog
- View detailed car information
- Submit inquiries
- Contact dealers

---

## 3. Core Modules

### 3.1 Car Purchase Module

When a vehicle is purchased, the following must be recorded:

#### A. Purchase Information
- Purchase date
- Purchased by (Main Company / Dealer Name)
- Purchase price
- Payment method
- Invoice number
- Tax details (if applicable)
- Document uploads (Invoice, Agreement, Import Documents)

#### B. Seller Information
- Seller name
- NIC / Company Registration No
- Address
- Contact number
- Email
- Seller type (Individual / Dealer / Auction)

#### C. Vehicle Details

**Basic Information:**
- Make & Model
- Sub Model
- Manufactured year
- Color
- Country of origin
- Fuel type
- Mileage

**For Registered Vehicles:**
- Registration number
- Number plate
- Registration date
- Number of previous owners

**For Unregistered Vehicles:**
- Chassis number
- Engine number
- Import year
- Auction grade (if applicable)

**Photo Gallery:**
- Front view
- Rear view
- Side views
- Interior
- Engine bay
- Dashboard
- Multiple image upload support

---

### 3.2 Car Transfer Module

**Transfer Routes:**
- Main Company → Partner Dealer
- Partner Dealer → Main Company
- Partner Dealer → Partner Dealer

**Transfer Details Required:**
- Transfer date
- From entity
- To entity
- Transfer price (if applicable)
- Transport cost
- Responsible person
- Transfer status (Pending / Completed)

**System Requirements:**
- Automatically update inventory location
- Maintain complete movement history
- Keep financial records of transfers

---

### 3.3 Car Sales Module

**Buyer Information:**
- Buyer name
- NIC / Business Registration No
- Address
- Contact number
- Email

**Sales Details:**
- Selling date
- Selling price
- Discount given
- Final amount
- Payment method
- Invoice number
- Commission (if applicable)
- Salesperson name

**Post-Sale Actions:**
- Car status automatically set to SOLD
- Removed from public listing
- Sales record permanently stored

---

### 3.4 Dashboard & Reports

#### Main Company Dashboard
- Total vehicles in system
- Vehicles by dealer breakdown
- Total sales revenue
- Transfer history
- Monthly sales charts
- Profit summaries

#### Dealer Dashboard
- Available stock count
- Sold vehicles count
- Monthly revenue
- Pending transfers
- Profit reports

#### Available Reports
- Purchase Report
- Sales Report
- Transfer Report
- Dealer-wise Stock Report
- Profit & Margin Analysis

---

### 3.5 Public Website Module

#### A. Home Page
- Company introduction
- Featured vehicles
- Contact details

#### B. Vehicle Listing Page

**Filters:**
- Make & Model
- Price range
- Manufacturing year
- Fuel type
- Registration status (Registered / Unregistered)

#### C. Vehicle Details Page

**Displays:**
- Full image gallery
- Complete specifications
- Price
- Contact number
- Inquiry form

---

## 4. Non-Functional Requirements

### 4.1 Performance Requirements
- Support multiple concurrent users
- Fast page loading times
- Efficient database queries
- Optimized image loading

### 4.2 Security Requirements
- Role-based access control (RBAC)
- Secure authentication system
- Data encryption for sensitive information
- Secure file uploads
- SQL injection prevention
- XSS protection

### 4.3 Data Integrity Requirements
- Unique identifiers for all vehicles (Registration or Chassis Number)
- Complete historical records
- Audit trails for all transactions
- Data validation on all inputs
- Referential integrity in database

### 4.4 Usability Requirements
- Intuitive user interface
- Responsive design (mobile-friendly)
- Clear error messages
- Help documentation
- Search functionality

---

## 5. Technical Architecture

### 5.1 Frontend
- **Technology:** Next.js with React
- **Features:** Server-side rendering, responsive design
- **Components:** Admin Panel + Public Website

### 5.2 Backend
- **Technology:** Laravel (REST API)
- **Database:** MySQL/PostgreSQL
- **Authentication:** Laravel Sanctum
- **File Storage:** Local/S3

### 5.3 API Structure
- RESTful API endpoints
- JSON response format
- Token-based authentication
- CORS configuration for frontend

---

## 6. Data Flow

### Vehicle Purchase Flow
1. User enters purchase details
2. System validates input
3. Vehicle record created in database
4. Documents uploaded to storage
5. Inventory updated
6. Confirmation sent

### Vehicle Transfer Flow
1. Transfer request initiated
2. Source and destination verified
3. Transfer record created
4. Vehicle location updated
5. Inventory adjusted for both entities
6. Transfer history logged

### Vehicle Sales Flow
1. Sales details entered
2. Buyer information recorded
3. Payment details captured
4. Vehicle status set to SOLD
5. Removed from public listing
6. Sales report generated

---

## 7. Database Schema (Conceptual)

### Key Tables
- `users` - System users
- `roles` - User roles
- `dealers` - Partner dealer information
- `vehicles` - Vehicle master data
- `purchases` - Purchase transactions
- `transfers` - Transfer records
- `sales` - Sales transactions
- `vehicle_images` - Vehicle photo gallery
- `inquiries` - Customer inquiries

---

## 8. Future Enhancements (Phase 2+)

- Mobile application
- SMS notifications
- Advanced analytics & AI insights
- Integration with payment gateways
- Vehicle comparison feature
- Customer reviews & ratings
- Financing calculator
- Vehicle history reports
- Multi-language support

---

## 9. Success Criteria

- All user roles can perform assigned functions
- Reports generate accurately
- Vehicle tracking is complete and auditable
- Public website displays accurate vehicle data
- System handles expected user load
- Data is secure and backed up
- User interface is intuitive

---

## 10. Constraints & Assumptions

### Constraints
- Must work in modern web browsers
- Must be mobile-responsive
- Must comply with data protection regulations

### Assumptions
- Users have stable internet connection
- Users have basic computer literacy
- Dealers will maintain accurate data entry

---

**Document Status:** Draft  
**Last Updated:** February 18, 2026  
**Prepared By:** Development Team
