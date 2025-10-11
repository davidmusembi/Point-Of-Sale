# AQUA FRESCO POS SYSTEM - Complete User Guide & Manual

## Table of Contents
1. [System Overview](#system-overview)
2. [System Architecture](#system-architecture)
3. [Installation Guide](#installation-guide)
4. [Project Structure](#project-structure)
5. [Getting Started](#getting-started)
6. [Module-by-Module Guide](#module-by-module-guide)
7. [How to Use Each Feature](#how-to-use-each-feature)
8. [File Purpose & Usage](#file-purpose--usage)
9. [Troubleshooting](#troubleshooting)
10. [Best Practices](#best-practices)

---

## 1. System Overview

### What is Aqua Fresco POS?
**Aqua Fresco POS** is a comprehensive business management system specifically designed for water production and distribution companies. It's tailored for businesses like Aqua Fresco in Langata, Nairobi, Kenya, that produce bottled water and manage distribution to schools, offices, and retail outlets.

### Key Features
- **Accounting Module**: Complete double-entry bookkeeping with vouchers, ledgers, and journal entries
- **Inventory Management**: Track water bottles, raw materials, and packaging supplies
- **Sales Management**: Create invoices, track payments, manage customers
- **Manufacturing Module**: Bill of Materials (BOM), production orders, and manufacturing journals
- **Party Management**: Manage customers and suppliers
- **Reporting**: Financial reports, inventory reports, and analytics
- **User Management**: Role-based access control (Admin, Manager, User)
- **Dashboard**: Real-time business metrics and insights

### Business Use Case
The system handles the complete workflow of a water bottling company:
1. **Purchase** raw materials (bottles, caps, labels, chemicals)
2. **Manufacture** finished products (500ml, 1L, 5L, 20L water bottles)
3. **Sell** to customers (schools, offices, retail stores, households)
4. **Track** inventory, finances, and production
5. **Generate** reports for business insights

---

## 2. System Architecture

### Technology Stack

#### Frontend
- **Framework**: React 18.3.1
- **Routing**: React Router DOM v6
- **Styling**: Tailwind CSS 3.4.7
- **Build Tool**: Vite 5.3.5
- **HTTP Client**: Axios 1.7.2
- **Charts**: Recharts 2.12.7
- **Animations**: Framer Motion 11.3.24
- **Notifications**: React Hot Toast 2.4.1
- **PDF Generation**: jsPDF 2.5.1
- **Excel Export**: XLSX 0.18.5

#### Backend
- **Language**: PHP (Server-side scripting)
- **Database**: MySQL 8.0+ (PDO)
- **Authentication**: JWT (JSON Web Tokens)
- **API Architecture**: RESTful API
- **Security**: CORS protection, password hashing (bcrypt)

#### Database
- **Database Name**: `aqua_fresco_pos`
- **Type**: MySQL/MariaDB
- **Character Set**: utf8mb4 (supports all languages)
- **Tables**: 25+ tables covering all business operations

### Application Flow

```
┌─────────────────────────────────────────────────────────────┐
│                        USER BROWSER                          │
│                   (http://localhost:5173)                    │
└────────────────────────┬────────────────────────────────────┘
                         │
                         │ React Frontend (Vite Dev Server)
                         │
                    ┌────▼─────┐
                    │   Login  │
                    │  /login  │
                    └────┬─────┘
                         │
                         │ POST /auth/login.php
                         │
┌────────────────────────▼────────────────────────────────────┐
│                    BACKEND API                               │
│              (http://localhost/water/backend/api)            │
│                                                              │
│  ┌──────────────┐  ┌──────────────┐  ┌─────────────┐      │
│  │   Auth API   │  │  Sales API   │  │ Reports API │      │
│  │  JWT Tokens  │  │  Invoices    │  │  Analytics  │      │
│  └──────┬───────┘  └──────┬───────┘  └──────┬──────┘      │
│         │                  │                  │              │
│         └──────────────────┼──────────────────┘              │
│                            │                                 │
│                    ┌───────▼────────┐                       │
│                    │  Database.php  │                       │
│                    │   PDO Layer    │                       │
│                    └───────┬────────┘                       │
└────────────────────────────┼────────────────────────────────┘
                             │
                    ┌────────▼────────┐
                    │   MySQL Server  │
                    │ aqua_fresco_pos │
                    │                 │
                    │  - users        │
                    │  - ledgers      │
                    │  - stock_items  │
                    │  - invoices     │
                    │  - vouchers     │
                    │  - etc...       │
                    └─────────────────┘
```

---

## 3. Installation Guide

### Prerequisites
Before you start, ensure you have:
- **XAMPP** or **WAMP** or **LAMP** (Apache + MySQL + PHP)
- **PHP 8.0+**
- **MySQL 8.0+** or MariaDB 10.5+
- **Node.js 18+** and npm
- **Git** (optional, for version control)
- **Modern Web Browser** (Chrome, Firefox, Edge)

### Step-by-Step Installation

#### Step 1: Install Backend Server (XAMPP)
1. Download and install XAMPP from https://www.apachefriends.org/
2. Start **Apache** and **MySQL** from XAMPP Control Panel
3. Verify:
   - Apache running on: http://localhost
   - MySQL running on: localhost:3306

#### Step 2: Setup Database
1. Open phpMyAdmin: http://localhost/phpmyadmin
2. Click on **Import** tab
3. Click **Choose File** and select: `E:\water\database\schema.sql`
4. Click **Go** to import
5. Verify database `aqua_fresco_pos` is created with all tables

**OR use MySQL command line:**
```bash
mysql -u root -p < E:\water\database\schema.sql
```

#### Step 3: Configure Backend
1. Copy the entire `E:\water` folder to:
   - **Windows XAMPP**: `C:\xampp\htdocs\water`
   - **Mac MAMP**: `/Applications/MAMP/htdocs/water`
   - **Linux LAMP**: `/var/www/html/water`

2. Edit database configuration if needed:
   - File: `E:\water\backend\config\database.php`
   - Change host, username, password if different

```php
private $host = "localhost";
private $db_name = "aqua_fresco_pos";
private $username = "root";
private $password = ""; // Change if you have a password
```

3. Test backend API:
   - Visit: http://localhost/water/backend/api/auth/login.php
   - You should see a JSON error (method not allowed) - this is correct!

#### Step 4: Install Frontend Dependencies
1. Open Command Prompt or Terminal
2. Navigate to frontend folder:
```bash
cd E:\water\frontend
```

3. Install Node.js dependencies:
```bash
npm install
```

4. Wait for installation to complete (may take 2-5 minutes)

#### Step 5: Configure Frontend API Connection
1. Verify API base URL in: `E:\water\frontend\src\services\api.js`
```javascript
const API_BASE_URL = 'http://localhost/water/backend/api';
```

2. If your XAMPP htdocs path is different, update this URL accordingly

#### Step 6: Start Frontend Development Server
```bash
cd E:\water\frontend
npm run dev
```

You should see:
```
  VITE v5.3.5  ready in 1234 ms

  ➜  Local:   http://localhost:5173/
  ➜  Network: use --host to expose
  ➜  press h + enter to show help
```

#### Step 7: Access the Application
1. Open browser and go to: **http://localhost:5173**
2. You should see the Login page

#### Step 8: First Login
Use the sample admin credentials:
- **Username**: `safia`
- **Password**: (You need to set this first - see below)

### Setting Initial Password

The sample data has placeholder passwords. You need to create a real password:

1. Open phpMyAdmin: http://localhost/phpmyadmin
2. Select database: `aqua_fresco_pos`
3. Click on `users` table
4. Click **Edit** for user `safia`
5. In `password_hash` field, use this PHP snippet to generate hash:

```php
<?php
echo password_hash('admin123', PASSWORD_BCRYPT);
?>
```

Or run this SQL query:
```sql
UPDATE users
SET password_hash = '$2y$10$YourGeneratedHashHere'
WHERE username = 'safia';
```

**Recommended**: Create a quick PHP file to generate hash:
1. Create file: `C:\xampp\htdocs\generate_password.php`
```php
<?php
echo password_hash('admin123', PASSWORD_BCRYPT);
?>
```
2. Visit: http://localhost/generate_password.php
3. Copy the generated hash
4. Update database with that hash

---

## 4. Project Structure

### Root Directory Structure
```
E:\water\
│
├── backend/                # PHP Backend API
│   ├── api/               # API Endpoints
│   ├── config/            # Configuration files
│   └── utils/             # Utility classes
│
├── database/              # Database schema
│   └── schema.sql         # Complete database structure + sample data
│
└── frontend/              # React Frontend
    ├── dist/              # Production build (generated)
    ├── node_modules/      # Dependencies (generated)
    ├── public/            # Static assets
    ├── src/               # Source code
    │   ├── components/    # React components
    │   ├── contexts/      # React contexts (Auth, etc.)
    │   ├── hooks/         # Custom React hooks
    │   ├── services/      # API services
    │   ├── utils/         # Helper functions
    │   ├── App.jsx        # Main app component
    │   ├── main.jsx       # App entry point
    │   └── index.css      # Global styles
    ├── index.html         # HTML template
    ├── package.json       # Dependencies
    ├── vite.config.js     # Vite configuration
    └── tailwind.config.js # Tailwind CSS config
```

### Backend Structure (Detailed)
```
backend/
│
├── api/
│   ├── auth/              # Authentication & Users
│   │   ├── login.php           # POST: User login
│   │   ├── register.php        # POST: User registration
│   │   ├── verify.php          # GET: Verify JWT token
│   │   ├── profile.php         # GET/PUT: User profile
│   │   ├── change_password.php # POST: Change password
│   │   └── users.php           # CRUD: User management
│   │
│   ├── accounting/        # Accounting module
│   │   ├── groups.php          # Account groups
│   │   ├── ledgers.php         # Ledger accounts
│   │   └── vouchers.php        # Journal vouchers
│   │
│   ├── inventory/         # Inventory management
│   │   ├── stock_items.php     # Stock items CRUD
│   │   ├── stock_groups.php    # Stock categories
│   │   ├── units.php           # Units of measurement
│   │   └── warehouses.php      # Warehouse management
│   │
│   ├── parties/           # Customers & Suppliers
│   │   └── parties.php         # Party management
│   │
│   ├── sales/             # Sales module
│   │   └── invoices.php        # Sales invoices
│   │
│   ├── manufacturing/     # Manufacturing module
│   │   ├── bom.php             # Bill of Materials
│   │   ├── production.php      # Production orders
│   │   └── journal.php         # Manufacturing journals
│   │
│   ├── reports/           # Reporting module
│   │   ├── financial.php       # Financial reports
│   │   ├── inventory.php       # Inventory reports
│   │   └── sales_purchase.php  # Sales/Purchase reports
│   │
│   └── dashboard/         # Dashboard
│       └── stats.php           # Statistics API
│
├── config/
│   ├── database.php       # Database connection class
│   └── cors.php           # CORS security headers
│
└── utils/
    ├── Auth.php           # JWT authentication utilities
    ├── Response.php       # API response formatting
    └── Validator.php      # Input validation helpers
```

### Frontend Structure (Detailed)
```
frontend/src/
│
├── components/
│   ├── accounting/        # Accounting UI
│   │   ├── LedgersList.jsx      # View all ledgers
│   │   ├── VoucherForm.jsx      # Create/Edit voucher
│   │   └── VouchersList.jsx     # View all vouchers
│   │
│   ├── admin/             # Admin panel
│   │   └── UserManagement.jsx   # Manage users
│   │
│   ├── auth/              # Authentication
│   │   ├── Login.jsx            # Login page
│   │   └── Register.jsx         # Registration page
│   │
│   ├── common/            # Shared components
│   │   └── Layout.jsx           # App layout wrapper
│   │
│   ├── dashboard/         # Dashboard
│   │   └── Dashboard.jsx        # Main dashboard
│   │
│   ├── inventory/         # Inventory UI
│   │   └── StockItemsList.jsx   # Stock items list
│   │
│   ├── manufacturing/     # Manufacturing UI
│   │   ├── BOMList.jsx          # BOM list
│   │   ├── BOMForm.jsx          # BOM form
│   │   ├── ProductionOrdersList.jsx
│   │   ├── ManufacturingJournalForm.jsx
│   │   └── ManufacturingJournalsList.jsx
│   │
│   ├── profile/           # User profile
│   │   └── EditProfile.jsx      # Edit profile
│   │
│   ├── reports/           # Reports UI
│   │   ├── ReportsHub.jsx       # Reports home
│   │   └── FinancialReports.jsx # Financial reports
│   │
│   └── sales/             # Sales UI
│       ├── SalesInvoicesList.jsx
│       ├── SalesInvoiceForm.jsx
│       └── InvoicePrintView.jsx # Print invoice
│
├── contexts/
│   └── AuthContext.jsx    # Authentication context
│
├── hooks/
│   └── useAuth.js         # Authentication hook
│
├── services/
│   └── api.js             # All API calls centralized
│
├── utils/
│   └── helpers.js         # Helper functions
│
├── App.jsx                # Main app with routes
├── main.jsx               # React app initialization
└── index.css              # Global CSS + Tailwind
```

---

## 5. Getting Started

### First-Time Setup Checklist

#### ✅ Backend Setup
- [ ] XAMPP/WAMP installed and running
- [ ] Apache server running (http://localhost)
- [ ] MySQL server running (localhost:3306)
- [ ] Database `aqua_fresco_pos` created
- [ ] All tables imported from schema.sql
- [ ] Sample data loaded successfully
- [ ] Backend API accessible at http://localhost/water/backend/api

#### ✅ Frontend Setup
- [ ] Node.js installed (v18 or higher)
- [ ] Frontend dependencies installed (`npm install`)
- [ ] API base URL configured correctly
- [ ] Dev server running on http://localhost:5173
- [ ] Login page loads without errors

#### ✅ Initial Login
- [ ] Admin password set in database
- [ ] Can login with admin credentials
- [ ] Dashboard loads successfully
- [ ] All menu items accessible

### Quick Start Workflow

#### For Administrators:
1. **Login** as admin (`safia` / your password)
2. **Check Dashboard** - view current stats
3. **Review Users** - go to Admin → User Management
4. **Check Inventory** - go to Inventory → Stock Items
5. **Review Customers** - check existing parties
6. **Explore Reports** - visit Reports Hub

#### For Daily Operations:
1. **Login** with your credentials
2. **Create Sales Invoice** - Sales → New Invoice
3. **Record Payments** - Accounting → Vouchers
4. **Check Stock Levels** - Inventory → Stock Items
5. **Generate Reports** - Reports → Select report type

---

## 6. Module-by-Module Guide

### 6.1 Authentication & User Management

#### Login Process
- **URL**: `/login`
- **Users**: admin, manager, user roles
- **Features**: JWT token-based authentication
- **Session**: 24-hour token validity

#### User Roles
1. **Admin**: Full system access
   - Manage users
   - View all reports
   - Delete records
   - Configure system

2. **Manager**: Business operations
   - Create invoices
   - Manage inventory
   - View reports
   - Cannot delete users

3. **User**: Basic operations
   - View dashboard
   - Create sales invoices
   - View own records
   - Limited reports access

#### User Management (Admin Only)
- **Location**: Admin → User Management
- **Features**:
  - Create new users
  - Edit user details
  - Reset passwords
  - Deactivate users
  - Assign roles

### 6.2 Dashboard Module

#### Dashboard Components
1. **Sales Statistics**
   - Today's sales
   - Weekly revenue
   - Monthly trends
   - Top customers

2. **Inventory Alerts**
   - Low stock items
   - Out of stock
   - Expiry warnings
   - Reorder suggestions

3. **Financial Summary**
   - Cash balance
   - Bank balance
   - Accounts receivable
   - Accounts payable

4. **Quick Actions**
   - New sales invoice
   - Record payment
   - View reports
   - Stock adjustment

#### How Dashboard Works
- **Real-time data** from database
- **Auto-refresh** every 30 seconds
- **Charts and graphs** using Recharts
- **Click-through** to detailed views

### 6.3 Accounting Module

The accounting module follows **double-entry bookkeeping** principles.

#### Account Structure
```
Account Groups
└── Ledgers (Individual Accounts)
    └── Voucher Entries (Transactions)
```

#### Account Groups (Chart of Accounts)
```
Assets
├── Current Assets
│   ├── Cash & Bank
│   ├── Inventory
│   └── Accounts Receivable
└── Fixed Assets

Liabilities
└── Current Liabilities
    └── Accounts Payable

Income
├── Sales
└── Other Income

Expenses
├── Operating Expenses
│   ├── Utilities
│   ├── Salaries & Wages
│   ├── Transport & Delivery
│   └── Rent
└── Cost of Sales

Equity
└── Capital
```

#### Ledgers
- **Purpose**: Individual accounts under groups
- **Examples**:
  - Cash in Hand (under Cash & Bank)
  - KCB Bank Account (under Cash & Bank)
  - Sales - 500ml Bottles (under Sales)
  - Salaries Expense (under Operating Expenses)

#### Voucher Types
1. **Receipt**: Money received
   - Customer payment
   - Cash received

2. **Payment**: Money paid out
   - Supplier payment
   - Expense payment

3. **Contra**: Internal transfer
   - Cash to bank
   - Bank to cash

4. **Journal**: Other entries
   - Adjustments
   - Corrections

5. **Sales**: Auto-created from sales invoice
6. **Purchase**: Auto-created from purchase invoice

#### Creating a Voucher
**Example: Record Rent Payment**

1. Go to: Accounting → Vouchers → New Voucher
2. Select Type: **Payment**
3. Enter Date: Today's date
4. Add Entries:
   ```
   Debit:  Rent Expense      KES 50,000.00
   Credit: Bank Account      KES 50,000.00
   ```
5. Add Narration: "January 2025 rent payment"
6. Click **Save**

#### Accounting Equation
```
Assets = Liabilities + Equity
```

Every transaction must balance:
```
Total Debits = Total Credits
```

### 6.4 Inventory Management

#### Stock Item Structure
```
Stock Groups (Categories)
└── Stock Items
    ├── Basic Info (name, code, description)
    ├── Pricing (selling price, purchase price)
    ├── Stock Levels (current stock, reorder level)
    ├── Unit of Measurement
    └── Batch Tracking (if enabled)
```

#### Item Types
1. **Raw Material**: Production inputs
   - Empty bottles
   - Caps and labels
   - Purification chemicals

2. **Finished Good**: Ready to sell
   - 500ml water bottles
   - 1L water bottles
   - 5L water bottles
   - 20L water refills

3. **Consumable**: Operational supplies
   - Office supplies
   - Cleaning materials

4. **Service**: Non-physical items
   - Delivery service
   - Installation service

#### Creating a Stock Item
**Example: Add new product**

1. Go to: Inventory → Stock Items → New Item
2. Fill details:
   - **Name**: Aqua Fresco Water 10 Litre
   - **Code**: AF-10L
   - **Group**: Finished Goods
   - **Type**: Finished Good
   - **Unit**: Bottles
   - **Selling Price**: KES 300.00
   - **Purchase Price**: KES 150.00
   - **Reorder Level**: 100
3. Click **Save**

#### Warehouses
- **Purpose**: Track stock by location
- **Examples**:
  - Main Refill Station (production)
  - Delivery Depot Karen (distribution)

#### Stock Movements
- **Type: In** - Increase stock
  - Purchase delivery
  - Production completion
  - Stock transfer received

- **Type: Out** - Decrease stock
  - Sales delivery
  - Material consumption
  - Stock transfer sent

- **Type: Adjustment** - Corrections
  - Physical count differences
  - Damaged goods
  - Expiry write-off

### 6.5 Party Management (Customers & Suppliers)

#### Party Types
1. **Customer**: Buyers of products
2. **Supplier**: Vendors of materials
3. **Both**: Can be customer and supplier

#### Customer Categories
Based on sample data:
- **Schools**: Large bulk orders, 30-day credit
- **Offices**: Regular orders, 30-45 day credit
- **Mini Marts**: Retail, 7-14 day credit
- **Walk-in**: Cash customers, no credit

#### Party Information
- **Basic**: Name, contact person, phone, email
- **Address**: Full address, city, state, pincode
- **Tax**: GSTIN (GST number), PAN
- **Financial**: Opening balance, credit limit, credit days
- **Ledger**: Linked to accounting ledger

#### Creating a Customer
**Example: Add new school customer**

1. Go to: Sales → Customers → New Customer (or similar path)
2. Fill details:
   - **Name**: Nairobi Academy
   - **Type**: Customer
   - **Contact**: Mr. John Doe
   - **Phone**: 0722123456
   - **Email**: admin@nairobiacademy.ac.ke
   - **Credit Limit**: KES 100,000
   - **Credit Days**: 30
3. Click **Save**

### 6.6 Sales Module

#### Sales Invoice Flow
```
Create Invoice → Add Items → Calculate Total → Save Invoice
                                                     ↓
                                             Generate Voucher
                                                     ↓
                                            Update Stock (Out)
                                                     ↓
                                          Update Party Balance
```

#### Creating a Sales Invoice
**Step-by-Step Example: School Water Order**

1. **Navigate**: Sales → Invoices → New Invoice

2. **Select Customer**: Langata Primary School

3. **Invoice Details**:
   - Date: Today
   - Warehouse: Main Refill Station
   - Reference: (Optional) PO-2025-001

4. **Add Items**:
   ```
   Item: Aqua Fresco Water 20 Litre Refill
   Quantity: 100
   Rate: KES 150.00
   Discount: KES 0.00
   Total: KES 15,000.00
   ```

5. **Review Totals**:
   - Subtotal: KES 15,000.00
   - Discount: KES 0.00
   - Tax: KES 0.00
   - **Grand Total: KES 15,000.00**

6. **Payment Terms**:
   - Due Date: 30 days from invoice date
   - Payment Status: Unpaid (or Partial/Paid)

7. **Click Save**

8. **Result**:
   - Invoice created with number: INV-2025-XXXX
   - Stock reduced by 100 units
   - Customer balance increased by KES 15,000.00
   - Accounting voucher auto-generated

9. **Print**: Click Print to generate PDF

#### Payment Status
- **Unpaid**: No payment received
- **Partial**: Some amount paid
- **Paid**: Full amount received

#### Recording Payment
After invoice is created:
1. Go to: Accounting → Vouchers → New Receipt
2. Select Type: **Receipt**
3. Add Entry:
   ```
   Debit:  Cash/Bank         KES 15,000.00
   Credit: Customer Ledger   KES 15,000.00
   ```
4. Reference: Invoice number
5. Save

### 6.7 Manufacturing Module

The manufacturing module handles production of finished goods from raw materials.

#### Bill of Materials (BOM)
**Definition**: Recipe for producing a finished product

**Example BOM: 500ml Water Bottle**
```
Output: 1 unit of Aqua Fresco Water 500ml

Inputs (Components):
- Purified Water: 0.5 liters
- Empty Bottle 500ml: 1 piece
- Bottle Cap: 1 piece
- Label: 1 piece
```

#### Creating a BOM
1. Go to: Manufacturing → BOM → New BOM
2. Enter:
   - **Name**: Production: 500ml Water Bottle
   - **Finished Good**: Aqua Fresco Water 500ml
   - **Output Quantity**: 1
3. Add Components:
   - Each raw material with quantity
4. Save

#### Production Order
**Definition**: Plan to produce specific quantity

**Workflow**:
```
Create Production Order → Status: Planned
                              ↓
                    Start Production
                              ↓
           Status: In Progress
                              ↓
        Create Manufacturing Journal
                              ↓
           Status: Completed
```

#### Creating Production Order
1. Go to: Manufacturing → Production Orders → New Order
2. Select:
   - **BOM**: Production: 500ml Water Bottle
   - **Planned Quantity**: 1000 units
   - **Warehouse**: Main Refill Station
   - **Start Date**: Today
3. Save (Status: Planned)

#### Manufacturing Journal
**Purpose**: Record actual production

**Process**:
1. Go to: Manufacturing → Journals → New Journal
2. Select:
   - **Production Order**: Select order
   - **Date**: Today
   - **Quantity Produced**: 950 (actual output)
3. System auto-fills:
   - **Input Items**: Materials consumed (based on BOM)
   - **Output Item**: Finished good produced
   - **Scrap**: 50 units (if any wastage)
4. Save

**Result**:
- Raw materials stock decreased
- Finished goods stock increased
- Production order updated
- Cost calculated
- Accounting entries created

### 6.8 Reports Module

#### Financial Reports
1. **Trial Balance**
   - All ledgers with debit/credit balances
   - Verify accounting accuracy
   - Date range filter

2. **Profit & Loss Statement**
   - Income vs Expenses
   - Net profit/loss
   - Period comparison

3. **Balance Sheet**
   - Assets, Liabilities, Equity
   - Financial position snapshot
   - As of specific date

4. **Cash Flow Statement**
   - Cash inflows and outflows
   - Operating, investing, financing activities

5. **Day Book**
   - Daily transactions journal
   - All vouchers for selected date

#### Inventory Reports
1. **Stock Summary**
   - All items with current stock
   - Stock value
   - By warehouse

2. **Stock Movements**
   - All in/out transactions
   - Date range filter
   - By item or warehouse

3. **Reorder List**
   - Items below reorder level
   - Suggested purchase quantities

4. **Expiry Alerts**
   - Items nearing expiry
   - Critical alerts

#### Generating Reports
1. Go to: Reports → Select Report Type
2. Choose Filters:
   - Date range
   - Item/Customer/etc (if applicable)
3. Click **Generate**
4. View on screen
5. **Export**:
   - PDF: Click Export PDF
   - Excel: Click Export Excel
   - Print: Click Print

---

## 7. How to Use Each Feature

### Daily Operations Workflow

#### Morning Routine
1. **Login** to system
2. **Check Dashboard**:
   - Review sales from yesterday
   - Check low stock alerts
   - View pending payments
3. **Process Orders**:
   - Create sales invoices for today's orders
   - Print delivery notes
4. **Record Collections**:
   - Enter payments received from customers

#### Sales Transaction
**Complete Flow: Customer Walk-in Purchase**

1. **Customer arrives** with order
2. **Check stock**: Inventory → Stock Items
3. **Create Invoice**:
   - Sales → New Invoice
   - Select customer (or Walk-in)
   - Add items and quantities
   - Review total
   - Save invoice
4. **Print Invoice**: Click Print button
5. **Collect Payment**:
   - If cash payment, record receipt voucher
   - Accounting → New Receipt
   - Debit: Cash, Credit: Sales
6. **Deliver Products**:
   - Pick items from warehouse
   - Hand over with printed invoice

#### Production Process
**Weekly Production Planning**

1. **Check Stock Levels**:
   - Inventory → Stock Items
   - Note items below reorder level

2. **Verify Raw Materials**:
   - Check bottles, caps, labels availability
   - If low, create purchase order

3. **Create Production Order**:
   - Manufacturing → Production Orders
   - Select BOM (e.g., 500ml bottles)
   - Enter quantity needed (e.g., 2000 units)
   - Set start date
   - Save

4. **During Production**:
   - Update order status to "In Progress"

5. **After Production**:
   - Count finished goods produced
   - Create Manufacturing Journal
   - Enter actual quantity
   - System deducts raw materials
   - System adds finished goods

#### Payment Collection
**When Customer Pays Outstanding Invoice**

1. **Check Amount Due**:
   - Sales → Invoices
   - Filter by customer
   - Note outstanding invoices

2. **Record Receipt**:
   - Accounting → Vouchers → New Receipt
   - Date: Today
   - Entries:
     ```
     Debit: Cash in Hand (or Bank)  KES 18,000
     Credit: Customer Ledger         KES 18,000
     ```
   - Narration: "Payment for Invoice INV-2025-0001"
   - Save

3. **Update Invoice**:
   - Go back to invoice
   - Update payment status to "Paid"
   - Enter paid amount

#### Month-End Procedures
1. **Reconcile Bank Accounts**:
   - Compare bank statement with ledger
   - Create adjustment vouchers if needed

2. **Physical Stock Count**:
   - Count all items in warehouse
   - Create adjustment vouchers for differences

3. **Generate Reports**:
   - Trial Balance (verify accuracy)
   - Profit & Loss Statement
   - Balance Sheet
   - Stock Summary

4. **Review Financials**:
   - Analyze profit margins
   - Check expense trends
   - Review debtor aging
   - Plan for next month

---

## 8. File Purpose & Usage

### Backend Files Explained

#### `/backend/config/database.php`
**Purpose**: Establishes connection to MySQL database
**When Used**: Every API request
**How It Works**:
```php
$database = new Database();
$db = $database->getConnection();
// Returns PDO connection object
```
**Modify**: Only change if database credentials change

#### `/backend/config/cors.php`
**Purpose**: Security - prevents unauthorized access
**When Used**: Included in every API endpoint
**How It Works**:
- Checks request origin
- Only allows localhost during development
- Blocks requests from unknown sources
**Modify**: Add production domain when deploying

#### `/backend/utils/Auth.php`
**Purpose**: JWT token generation and verification
**Key Methods**:
- `generateToken()`: Create JWT after login
- `verifyToken()`: Validate JWT on each request
- `checkRole()`: Verify user has required permissions
- `logAudit()`: Record user actions

#### `/backend/utils/Response.php`
**Purpose**: Standardize API responses
**Usage**:
```php
Response::success($data, $message);
Response::error($message, $statusCode);
Response::unauthorized($message);
Response::forbidden($message);
```

#### `/backend/utils/Validator.php`
**Purpose**: Input validation and sanitization
**Methods**:
- `required()`: Check required fields
- `sanitize()`: Clean user input
- `email()`: Validate email format
- `phone()`: Validate phone numbers

#### `/backend/api/*/` Files
**Pattern**: Each file handles CRUD operations
```php
// Example: /backend/api/sales/invoices.php

switch($_SERVER['REQUEST_METHOD']) {
    case 'GET':    // Read (list or single)
    case 'POST':   // Create
    case 'PUT':    // Update
    case 'DELETE': // Delete/Cancel
}
```

### Frontend Files Explained

#### `/frontend/src/main.jsx`
**Purpose**: Application entry point
**What It Does**:
- Initializes React
- Wraps app with providers (Router, Auth, Toast)
- Mounts app to DOM

#### `/frontend/src/App.jsx`
**Purpose**: Main routing configuration
**What It Does**:
- Defines all routes
- Implements protected routes (login required)
- Handles authentication redirects

#### `/frontend/src/services/api.js`
**Purpose**: Centralized API client
**What It Does**:
- Configures axios base URL
- Adds authentication token to requests
- Handles errors (401, 403, etc)
- Exports API methods for all modules

**Usage in Components**:
```javascript
import { salesAPI } from '../services/api';

// Get invoices
const invoices = await salesAPI.getInvoices();

// Create invoice
await salesAPI.createInvoice(invoiceData);
```

#### `/frontend/src/contexts/AuthContext.jsx`
**Purpose**: Global authentication state
**Provides**:
- `user`: Current user data
- `token`: JWT token
- `login()`: Login function
- `logout()`: Logout function
- `isAuthenticated`: Boolean flag

**Usage in Components**:
```javascript
import { useAuth } from '../contexts/AuthContext';

function MyComponent() {
  const { user, logout } = useAuth();

  return <div>Welcome {user.full_name}</div>;
}
```

#### Component Structure
**Pattern**: Each module has list and form components

**Example: Sales Module**
```
SalesInvoicesList.jsx    // View all invoices (table)
SalesInvoiceForm.jsx     // Create/Edit invoice (form)
InvoicePrintView.jsx     // Print-friendly invoice
```

**Typical List Component**:
- Fetch data on mount
- Display in table
- Search and filter
- Pagination
- Actions (view, edit, delete)

**Typical Form Component**:
- Load existing data (if edit mode)
- Form validation
- Submit to API
- Handle success/error
- Redirect after save

### Configuration Files

#### `/frontend/package.json`
**Purpose**: Define dependencies and scripts
**Key Sections**:
```json
{
  "scripts": {
    "dev": "vite",           // Start dev server
    "build": "vite build",   // Build for production
    "preview": "vite preview" // Preview production build
  },
  "dependencies": {
    // Runtime dependencies
  },
  "devDependencies": {
    // Development tools
  }
}
```

#### `/frontend/vite.config.js`
**Purpose**: Vite build tool configuration
**Default**: Usually no changes needed
**Customize**: Change port, add plugins, proxy API

#### `/frontend/tailwind.config.js`
**Purpose**: Tailwind CSS configuration
**Customize**:
- Theme colors
- Font families
- Custom utilities

#### `/frontend/postcss.config.js`
**Purpose**: CSS post-processing
**Required for**: Tailwind CSS to work

---

## 9. Troubleshooting

### Common Issues & Solutions

#### Issue: Frontend won't start
**Error**: `npm run dev` fails
**Solutions**:
1. Delete `node_modules` and reinstall:
   ```bash
   rm -rf node_modules
   npm install
   ```
2. Clear npm cache:
   ```bash
   npm cache clean --force
   npm install
   ```
3. Check Node.js version:
   ```bash
   node --version  # Should be 18+
   ```

#### Issue: Cannot connect to backend API
**Error**: "Network Error" or CORS error
**Solutions**:
1. **Check Apache is running** in XAMPP
2. **Verify backend URL**:
   - Should be: `http://localhost/water/backend/api`
   - Check `frontend/src/services/api.js`
3. **Check CORS configuration**:
   - File: `backend/config/cors.php`
   - Ensure frontend port (5173) is in allowed origins
4. **Test API directly**:
   ```bash
   curl http://localhost/water/backend/api/auth/login.php
   ```

#### Issue: Database connection failed
**Error**: "Database connection failed"
**Solutions**:
1. **Check MySQL is running** in XAMPP
2. **Verify credentials**:
   - File: `backend/config/database.php`
   - Default: username=root, password=blank
3. **Test MySQL connection**:
   ```bash
   mysql -u root -p
   ```
4. **Check database exists**:
   ```sql
   SHOW DATABASES;
   USE aqua_fresco_pos;
   SHOW TABLES;
   ```

#### Issue: Login fails with correct credentials
**Error**: "Invalid credentials"
**Solutions**:
1. **Check password hash**:
   ```sql
   SELECT username, password_hash FROM users WHERE username='safia';
   ```
2. **Regenerate password hash**:
   - Use online bcrypt generator
   - Or create PHP file:
     ```php
     <?php echo password_hash('your_password', PASSWORD_BCRYPT); ?>
     ```
3. **Update database**:
   ```sql
   UPDATE users
   SET password_hash = 'your_generated_hash'
   WHERE username='safia';
   ```

#### Issue: "Token expired" after login
**Error**: Logged out immediately
**Solutions**:
1. **Check system clock** - ensure date/time is correct
2. **Verify token generation**:
   - File: `backend/utils/Auth.php`
   - Token expires in 86400 seconds (24 hours)
3. **Clear browser storage**:
   - Open DevTools → Application → Local Storage
   - Delete old token
   - Login again

#### Issue: Blank page after login
**Error**: White screen, no errors
**Solutions**:
1. **Check browser console** (F12)
2. **Verify API responses**:
   - Network tab should show successful requests
3. **Check routing**:
   - File: `frontend/src/App.jsx`
   - Ensure routes are configured
4. **Try clearing cache**:
   ```bash
   npm run build
   npm run dev
   ```

#### Issue: Data not saving
**Error**: Form submits but data doesn't save
**Solutions**:
1. **Check browser console** for errors
2. **Check Network tab** - is API call successful?
3. **Test API with curl**:
   ```bash
   curl -X POST http://localhost/water/backend/api/sales/invoices.php \
     -H "Content-Type: application/json" \
     -H "Authorization: Bearer YOUR_TOKEN" \
     -d '{"invoice_date":"2025-01-15", ...}'
   ```
4. **Check PHP errors**:
   - File: `C:\xampp\apache\logs\error.log`

#### Issue: Reports not generating
**Error**: Report shows "No data"
**Solutions**:
1. **Check date range** - ensure data exists for selected period
2. **Verify database has data**:
   ```sql
   SELECT COUNT(*) FROM sales_invoices;
   SELECT COUNT(*) FROM vouchers;
   ```
3. **Check API response**:
   - Open Network tab
   - Check response data

#### Issue: PDF export not working
**Error**: PDF download fails
**Solutions**:
1. **Check jsPDF loaded**:
   ```bash
   npm list jspdf
   ```
2. **Reinstall if needed**:
   ```bash
   npm install jspdf jspdf-autotable
   ```
3. **Check browser allows downloads**

#### Issue: Slow performance
**Symptoms**: Pages load slowly
**Solutions**:
1. **Optimize queries** - add indexes
2. **Limit results** - add pagination
3. **Clear browser cache**
4. **Check MySQL slow query log**
5. **Add indexes to database**:
   ```sql
   CREATE INDEX idx_date ON sales_invoices(invoice_date);
   CREATE INDEX idx_party ON sales_invoices(party_id);
   ```

### Debugging Tips

#### Enable PHP Error Display
Edit `php.ini`:
```ini
display_errors = On
error_reporting = E_ALL
```
Restart Apache.

#### Check Backend Logs
Windows: `C:\xampp\apache\logs\error.log`
Linux: `/var/log/apache2/error.log`

#### Check Frontend Console
Press F12 → Console tab

#### Test API with Postman
1. Download Postman
2. Import API endpoints
3. Test each endpoint individually

#### Database Query Debugging
Add to PHP code:
```php
$stmt = $db->prepare($query);
error_log("SQL: " . $query);
error_log("Params: " . json_encode($params));
$stmt->execute($params);
```

---

## 10. Best Practices

### Security Best Practices

#### 1. Change Default Passwords
**Don't use**:
- Default admin password
- Database root password (blank)

**Do**:
```sql
UPDATE users SET password_hash = '...' WHERE username='safia';
ALTER USER 'root'@'localhost' IDENTIFIED BY 'strong_password';
```

#### 2. Use Strong Passwords
- Minimum 12 characters
- Mix of letters, numbers, symbols
- Not dictionary words

#### 3. Backup Database Regularly
**Daily backup**:
```bash
mysqldump -u root -p aqua_fresco_pos > backup_$(date +%Y%m%d).sql
```

**Restore backup**:
```bash
mysql -u root -p aqua_fresco_pos < backup_20250115.sql
```

#### 4. Restrict File Permissions
Linux/Mac:
```bash
chmod 644 backend/config/database.php
chmod 755 backend/api/
```

#### 5. Keep Software Updated
- Update PHP regularly
- Update MySQL/MariaDB
- Update Node.js packages:
  ```bash
  npm update
  npm audit fix
  ```

### Operational Best Practices

#### 1. Daily Backups
- Backup database every day
- Store backups off-site
- Test restores monthly

#### 2. Regular Data Verification
- Weekly: Reconcile bank accounts
- Monthly: Physical stock count
- Monthly: Review trial balance

#### 3. User Training
- Train all users before giving access
- Document company-specific procedures
- Regular refresher training

#### 4. Data Entry Standards
- Use consistent naming
- Enter complete information
- Don't skip mandatory fields
- Double-check amounts

#### 5. Audit Trail
- System logs all actions
- Review audit logs weekly
- Investigate anomalies

### Development Best Practices

#### 1. Version Control
Use Git:
```bash
cd E:\water
git init
git add .
git commit -m "Initial commit"
```

#### 2. Separate Environments
- **Development**: localhost
- **Staging**: test server
- **Production**: live server

#### 3. Code Documentation
Add comments:
```php
/**
 * Create sales invoice
 * @param array $data Invoice data
 * @return array Response with invoice_id
 */
```

#### 4. Error Handling
Always handle errors:
```javascript
try {
  await salesAPI.createInvoice(data);
  toast.success('Invoice created');
} catch (error) {
  toast.error(error.message || 'Failed to create invoice');
}
```

#### 5. Testing
Test before deploying:
- Test all modules
- Test different user roles
- Test on different browsers
- Test error scenarios

### Performance Best Practices

#### 1. Database Optimization
- Add indexes on frequently queried columns
- Use LIMIT for large result sets
- Optimize slow queries

#### 2. Frontend Optimization
- Minimize API calls
- Cache static data
- Lazy load components
- Compress images

#### 3. Caching
```php
// Example: Cache stock items for 5 minutes
$cacheKey = 'stock_items_' . date('YmdHi');
if (!$cached = apcu_fetch($cacheKey)) {
    $cached = fetchStockItems();
    apcu_store($cacheKey, $cached, 300);
}
```

---

## Appendix

### A. Sample Data Overview

The system comes with sample data for Aqua Fresco water company:

#### Users (8 staff members)
- 1 Admin (Safia)
- 2 Managers (Fardowsa, Boniface)
- 5 Users (attendants, drivers, logistics)

#### Stock Items (13 items)
- 4 Finished products (500ml, 1L, 5L, 20L water)
- 3 Raw materials (water, chemicals, filters)
- 4 Packaging (bottles, caps, labels)

#### Parties (15 total)
- 10 Customers (schools, offices, retail)
- 5 Suppliers (bottles, chemicals, utilities)

#### Transactions
- 5 Sample sales invoices
- 3 Sample purchase invoices
- 4 Sample BOMs (production recipes)
- 4 Sample vouchers (accounting entries)

### B. Database Schema Quick Reference

#### Key Tables

**Users & Authentication**
- `users`: System users with roles

**Accounting**
- `account_groups`: Chart of accounts
- `ledgers`: Individual accounts
- `vouchers`: Transaction headers
- `voucher_entries`: Transaction details

**Inventory**
- `stock_groups`: Item categories
- `stock_items`: Products and materials
- `units`: Measurement units
- `warehouses`: Storage locations
- `stock_batches`: Batch tracking
- `stock_movements`: In/out transactions

**Parties**
- `parties`: Customers and suppliers

**Sales**
- `sales_invoices`: Sales invoice headers
- `sales_invoice_items`: Invoice line items

**Purchase**
- `purchase_invoices`: Purchase invoice headers
- `purchase_invoice_items`: Purchase line items

**Manufacturing**
- `bill_of_materials`: Product recipes
- `bom_components`: Recipe ingredients
- `production_orders`: Production plans
- `manufacturing_journals`: Production records
- `manufacturing_journal_items`: Production details

**Audit**
- `audit_logs`: User activity tracking

### C. API Endpoint Reference

#### Authentication
```
POST   /api/auth/login.php              Login
POST   /api/auth/register.php           Register
GET    /api/auth/verify.php             Verify token
GET    /api/auth/profile.php            Get profile
PUT    /api/auth/profile.php            Update profile
POST   /api/auth/change_password.php    Change password
GET    /api/auth/users.php              List users (admin)
POST   /api/auth/users.php              Create user (admin)
PUT    /api/auth/users.php              Update user (admin)
DELETE /api/auth/users.php?id=X         Delete user (admin)
```

#### Dashboard
```
GET    /api/dashboard/stats.php         Get dashboard stats
```

#### Accounting
```
GET    /api/accounting/groups.php       List account groups
GET    /api/accounting/ledgers.php      List ledgers
GET    /api/accounting/ledgers.php?id=X Get ledger
POST   /api/accounting/ledgers.php      Create ledger
PUT    /api/accounting/ledgers.php      Update ledger
DELETE /api/accounting/ledgers.php?id=X Delete ledger
GET    /api/accounting/vouchers.php     List vouchers
GET    /api/accounting/vouchers.php?id=X Get voucher
POST   /api/accounting/vouchers.php     Create voucher
PUT    /api/accounting/vouchers.php     Update voucher
DELETE /api/accounting/vouchers.php?id=X Cancel voucher
```

#### Inventory
```
GET    /api/inventory/stock_items.php   List stock items
GET    /api/inventory/stock_items.php?id=X Get stock item
POST   /api/inventory/stock_items.php   Create stock item
PUT    /api/inventory/stock_items.php   Update stock item
DELETE /api/inventory/stock_items.php?id=X Delete stock item
GET    /api/inventory/stock_groups.php  List stock groups
GET    /api/inventory/units.php         List units
GET    /api/inventory/warehouses.php    List warehouses
```

#### Parties
```
GET    /api/parties/parties.php         List parties
GET    /api/parties/parties.php?id=X    Get party
POST   /api/parties/parties.php         Create party
PUT    /api/parties/parties.php         Update party
DELETE /api/parties/parties.php?id=X    Delete party
```

#### Sales
```
GET    /api/sales/invoices.php          List invoices
GET    /api/sales/invoices.php?id=X     Get invoice
POST   /api/sales/invoices.php          Create invoice
PUT    /api/sales/invoices.php          Update invoice
DELETE /api/sales/invoices.php?id=X     Cancel invoice
```

#### Manufacturing
```
GET    /api/manufacturing/bom.php       List BOMs
GET    /api/manufacturing/bom.php?id=X  Get BOM
POST   /api/manufacturing/bom.php       Create BOM
PUT    /api/manufacturing/bom.php       Update BOM
DELETE /api/manufacturing/bom.php?id=X  Delete BOM
GET    /api/manufacturing/production.php List orders
GET    /api/manufacturing/journal.php   List journals
POST   /api/manufacturing/journal.php   Create journal
```

#### Reports
```
GET    /api/reports/financial.php?type=trial_balance
GET    /api/reports/financial.php?type=profit_loss
GET    /api/reports/financial.php?type=balance_sheet
GET    /api/reports/inventory.php?type=stock_summary
GET    /api/reports/inventory.php?type=stock_movements
```

### D. Keyboard Shortcuts

**General**
- `Ctrl + S`: Save form (most forms)
- `Esc`: Close modal/dialog
- `Tab`: Navigate form fields
- `Enter`: Submit form (when in input)

**Browser**
- `F5`: Refresh page
- `F12`: Open developer tools
- `Ctrl + Shift + I`: Open developer tools
- `Ctrl + Shift + C`: Inspect element

### E. Support & Resources

#### Official Documentation
- React: https://react.dev
- Vite: https://vitejs.dev
- Tailwind CSS: https://tailwindcss.com
- PHP: https://www.php.net
- MySQL: https://dev.mysql.com/doc/

#### Community Help
- Stack Overflow: https://stackoverflow.com
- React community: https://react.dev/community
- PHP forums: https://www.php.net/support.php

#### Learning Resources
- React tutorial: https://react.dev/learn
- PHP tutorial: https://www.w3schools.com/php/
- SQL tutorial: https://www.w3schools.com/sql/
- REST API design: https://restfulapi.net

### F. Glossary

**API**: Application Programming Interface - how frontend communicates with backend

**BOM**: Bill of Materials - recipe for manufacturing

**CORS**: Cross-Origin Resource Sharing - security feature

**CRUD**: Create, Read, Update, Delete - basic operations

**JWT**: JSON Web Token - authentication method

**Ledger**: Individual account in accounting

**PDO**: PHP Data Objects - database access layer

**REST**: Representational State Transfer - API architecture

**SPA**: Single Page Application - React app type

**Voucher**: Accounting transaction/journal entry

---

## Quick Reference Card

### Most Common Tasks

#### Login
URL: http://localhost:5173/login
User: `safia` | Password: (your set password)

#### Create Sales Invoice
1. Sales → New Invoice
2. Select customer
3. Add items
4. Save → Print

#### Record Payment
1. Accounting → Vouchers → New Receipt
2. Debit: Cash/Bank
3. Credit: Customer
4. Save

#### Check Stock
1. Inventory → Stock Items
2. View current stock column

#### Generate Report
1. Reports → Select type
2. Choose filters
3. Generate
4. Export PDF/Excel

### Emergency Contacts
- System Admin: (your admin)
- Database Admin: (your DBA)
- Technical Support: (your support)

---

**Document Version**: 1.0
**Last Updated**: 2025-01-15
**System Version**: Aqua Fresco POS v1.0.0

---

*End of User Guide*
