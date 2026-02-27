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

tenants-
    id (bigint, pk)
    name (varchar 150)
    email (varchar 150)
    phone (varchar 20)
    address (varchar 255)
    subscription_plan_id (bigint, fk)
    subscription_start (date)
    subscription_end (date)
    status (enum: active,suspended,expired)
    created_at
    updated_at

subscription_plans-
    id (bigint, pk)
    name (varchar 100)
    max_dealers (int)
    max_vehicles (int)
    max_transactions (int)
    price (decimal 12,2)
    created_at
    updated_at

users-
    id (bigint, pk)
    name (varchar 100)
    email (varchar 150, unique)
    password (varchar 255)
    tenant_id (bigint, fk, nullable)
    role (enum: platform_admin,company_admin,dealer_admin)
    status (enum: active,inactive)
    created_at
    updated_at

dealers-
    id (bigint, pk)
    tenant_id (bigint, fk, nullable)
    name (varchar 150)
    email (varchar 150)
    phone (varchar 20)
    address (varchar 255)
    status (enum: active,inactive)
    created_at
    updated_at

vehicles-
    id (bigint, pk)
    tenant_id (bigint, fk)
    vehicle_code (varchar 50, unique)
    make (varchar 100)
    model (varchar 100)
    sub_model (varchar 100, nullable)
    vehicle_type (enum: Car,SUV,Van,Bus,Lorry,Truck,Pickup,Minivan,Coupe,Sedan,Hatchback,Wagon)
    year (int)
    color (varchar 50)
    country_of_origin (varchar 100)
    fuel_type (enum: Gasoline,Diesel,Electric,Hybrid,Plug-in Hybrid)
    mileage (int)
    transmission_type (enum: Automatic,Manual,CVT,Semi-Automatic)
    engine_size (varchar 50, nullable)
    vin (varchar 100, nullable)
    registration_type (enum: Registered,Unregistered)
    price (decimal 12,2)
    dealer_id (bigint, fk, nullable)
    status (enum: Available,Sold,Transferred,Reserved)
    description (text, nullable)
    created_at
    updated_at

vehicle_registrations-
    id (bigint, pk)
    vehicle_id (bigint, fk, unique)
    registration_number (varchar 100)
    number_plate (varchar 50)
    registration_date (date)
    number_of_previous_owners (int)

vehicle_imports-
    id (bigint, pk)
    vehicle_id (bigint, fk, unique)
    chassis_number (varchar 100)
    engine_number (varchar 100)
    import_year (int)
    auction_grade (varchar 10, nullable)

vehicle_images-
    id (bigint, pk)
    vehicle_id (bigint, fk)
    image_category (enum: frontView,rearView,leftSideView,rightSideView,interior,engine,dashboard,others)
    image_url (text)
    created_at

purchases-
    id (bigint, pk)
    tenant_id (bigint, fk)
    vehicle_id (bigint, fk)
    purchase_date (date)
    purchase_price (decimal 12,2)
    payment_method_id (bigint, fk)
    invoice_number (varchar 100)
    tax_amount (decimal 12,2)
    created_at
    updated_at

sellers-
    id (bigint, pk)
    tenant_id (bigint, fk)
    name (varchar 150)
    nic_or_reg (varchar 100)
    address (varchar 255)
    phone (varchar 20)
    email (varchar 150)
    seller_type (enum: individual,dealer,auction)
    created_at
    updated_at

purchase_sellers-
    purchase_id (bigint, fk)
    seller_id (bigint, fk)

transfers-
    id (bigint, pk)
    tenant_id (bigint, fk)
    vehicle_id (bigint, fk)
    from_dealer_id (bigint, fk, nullable)
    to_dealer_id (bigint, fk, nullable)
    transfer_date (date)
    transfer_price (decimal 12,2)
    transport_cost (decimal 12,2)
    status (enum: pending,completed)
    created_at
    updated_at

buyers-
    id (bigint, pk)
    tenant_id (bigint, fk)
    name (varchar 150)
    nic_or_reg (varchar 100)
    address (varchar 255)
    phone (varchar 20)
    email (varchar 150)
    created_at
    updated_at

sales-
    id (bigint, pk)
    tenant_id (bigint, fk)
    vehicle_id (bigint, fk)
    buyer_id (bigint, fk)
    sale_date (date)
    sale_price (decimal 12,2)
    discount (decimal 12,2)
    final_amount (decimal 12,2)
    payment_method_id (bigint, fk)
    invoice_number (varchar 100)
    commission (decimal 12,2)
    salesperson_name (varchar 150)
    created_at
    updated_at

payment_methods-
    id (bigint, pk)
    name (varchar 100)

models-
    id (bigint, pk)
    name (varchar 150)

colors-
    id (bigint, pk)
    name (varchar 100)

fuel_types-
    id (bigint, pk)
    name (varchar 100)

countries-
    id (bigint, pk)
    name (varchar 150)