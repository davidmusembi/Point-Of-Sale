# 🌊 Aqua Fresco POS - Complete User Guide

## 📖 Step-by-Step Guide from First Login to Full Operation

---

## 🎯 Table of Contents

1. [First Time Login](#first-time-login)
2. [Initial System Setup](#initial-system-setup)
3. [Company Configuration](#company-configuration)
4. [User Management](#user-management)
5. [Chart of Accounts Setup](#chart-of-accounts-setup)
6. [Inventory Setup](#inventory-setup)
7. [Manufacturing & BOM Setup](#manufacturing--bom-setup)
8. [Making Your First Sale](#making-your-first-sale)
9. [Recording Purchases](#recording-purchases)
10. [Manufacturing Process](#manufacturing-process)
11. [Accounting & Vouchers](#accounting--vouchers)
12. [Generating Reports](#generating-reports)
13. [Daily Operations Workflow](#daily-operations-workflow)
14. [Keyboard Shortcuts](#keyboard-shortcuts)

---

## 🚀 FIRST TIME LOGIN

### Step 1: Access the System

1. **Open your browser** and go to: `http://localhost:3000`
2. You'll see the **Aqua Fresco POS Login** page

### Step 2: Login with Default Credentials

```
Username: admin
Password: qwerty
```

3. Click **"Sign in"** button
4. You'll see a **success toast notification**: "Welcome back, admin!"
5. The **Dashboard** will load automatically

### Step 3: First Look at Dashboard

You'll see:
- **4 Stat Cards**: Sales, Purchases, Low Stock, Pending Orders
- **2 Charts**: Sales Trend (line chart) and Inventory Distribution (pie chart)
- **Production Status**: Recent manufacturing activities
- **Quick Actions**: Buttons for common tasks
- **Bell Icon** (top right): Notifications
- **Profile Icon** (top right): Your account menu

---

## ⚙️ INITIAL SYSTEM SETUP

### Step 1: Change Your Password (IMPORTANT!)

1. Click **Profile Icon** (top right corner)
2. Select **"Profile"**
3. Scroll down to **"Change Password"** section
4. Click **"Change Password"** button
5. Enter:
   - Current Password: `admin123`
   - New Password: `YourNewSecurePassword`
   - Confirm Password: `YourNewSecurePassword`
6. Click **"Update Password"**
7. ✅ Success toast: "Password changed successfully"

### Step 2: Update Your Profile

1. Still on Profile page, click **"Edit Profile"**
2. Update:
   - Username: Keep as `admin` or change to your name
   - Email: `your.email@aquafresco.com`
3. Click **"Save Changes"**
4. ✅ Success toast: "Profile updated successfully"

---


## 👥 USER MANAGEMENT

### Step 1: Access User Management (Admin Only)

1. Click **Profile Icon** (top right)
2. Select **"User Management"**
3. You'll see a table of all users

### Step 2: Add Your First Manager

1. Click **"Add User"** button (top right)
2. Fill in the modal form:

```
Username: john_manager
Email: john@aquafresco.com
Password: Manager@123
Role: Manager
Active: ✓ (checked)
```

3. Click **"Create User"**
4. ✅ Success: "User created successfully"
5. User appears in the table with a **blue "Manager" badge**

### Step 3: Add Your First Regular User

1. Click **"Add User"** again
2. Fill in:

```
Username: sarah_sales
Email: sarah@aquafresco.com
Password: Sales@123
Role: User
Active: ✓
```

3. Click **"Create User"**
4. ✅ Success toast appears
5. User appears with a **green "User" badge**

### Step 4: Test Different User Roles

**Logout and test:**
1. Click Profile Icon → **"Logout"**
2. Login as: `john_manager` / `Manager@123`
3. Notice: **No "User Management"** in menu (manager can't manage users)
4. Logout and login back as `admin`

---

## 📊 CHART OF ACCOUNTS SETUP

The system comes with **default account groups**, but let's understand them:

### Step 1: View Chart of Accounts

1. Click **Sidebar** → **Accounting** → **Chart of Accounts**
2. You'll see a **hierarchical tree** of account groups:

```
📁 Assets
  📁 Current Assets
    💰 Cash Accounts
    🏦 Bank Accounts
    📋 Accounts Receivable
    📦 Inventory Accounts
  📁 Fixed Assets

📁 Liabilities
  📁 Current Liabilities
    💳 Accounts Payable
    📄 GST Payable
  📁 Long Term Liabilities

📁 Income
  📁 Sales Income
    💵 Water Sales
    💵 Other Sales

📁 Expenses
  📁 Direct Expenses
    🔧 Raw Material Expenses
    🏭 Manufacturing Expenses
    📦 Packaging Expenses
  📁 Indirect Expenses
    ⚡ Utilities
    👤 Salaries
    🏢 Administrative Expenses

📁 Equity
```

### Step 2: Create a New Account Group (Optional)

1. Click **"Add Group"** button
2. Fill in:

```
Group Name: Delivery Vehicles
Parent Group: Fixed Assets
Group Type: Asset
```

3. Click **"Save"**
4. ✅ New group appears under Fixed Assets

---

## 📦 INVENTORY SETUP

This is crucial for a water company! Let's set up your complete inventory.

### Step 1: Access Stock Items

1. Sidebar → **Inventory** → **Stock Items**
2. You'll see an empty table or existing items

### Step 2: Add Raw Materials

#### Add Item #1: Purified Water (Bulk)

1. Click **"Add Stock Item"** button
2. Fill in the modal:

```
Item Name: Purified Water (Bulk)
Description: Purified drinking water in bulk storage
Category/Group: Raw Materials
Unit of Measure: Liters
Opening Stock: 10000
Reorder Level: 2000
HSN Code: 22011010
Rate/Price: 0.50
Current Stock: 10000
Warehouse: Main Warehouse
```

3. Click **"Save Item"**
4. ✅ Success: "Stock item created successfully"

#### Add Item #2: 1L PET Bottles

Click **"Add Stock Item"** again:

```
Item Name: 1L PET Bottles
Description: Empty 1 liter PET bottles
Category/Group: Packaging Materials
Unit of Measure: Pieces
Opening Stock: 5000
Reorder Level: 1000
HSN Code: 39233010
Rate/Price: 5.00
Current Stock: 5000
Warehouse: Main Warehouse
```

Click **"Save Item"**

#### Add Item #3: 500ml PET Bottles

```
Item Name: 500ml PET Bottles
Description: Empty 500ml PET bottles
Category/Group: Packaging Materials
Unit of Measure: Pieces
Opening Stock: 8000
Reorder Level: 1500
Rate/Price: 3.00
Current Stock: 8000
Warehouse: Main Warehouse
```

#### Add Item #4: Bottle Caps

```
Item Name: Bottle Caps
Description: Screw-on bottle caps
Category/Group: Packaging Materials
Unit of Measure: Pieces
Opening Stock: 15000
Reorder Level: 3000
HSN Code: 39235090
Rate/Price: 0.50
Current Stock: 15000
Warehouse: Main Warehouse
```

#### Add Item #5: Product Labels/Stickers

```
Item Name: Product Labels (1L)
Description: Aqua Fresco branded labels for 1L bottles
Category/Group: Packaging Materials
Unit of Measure: Pieces
Opening Stock: 5000
Reorder Level: 1000
HSN Code: 48211010
Rate/Price: 1.00
Current Stock: 5000
Warehouse: Main Warehouse
```

### Step 3: Add Finished Goods

#### Add Item #6: Bottled Water 1L (Finished Product)

```
Item Name: Aqua Fresco 1L Bottled Water
Description: 1 liter bottled drinking water - finished product
Category/Group: Finished Goods
Unit of Measure: Pieces
Opening Stock: 0
Reorder Level: 500
HSN Code: 22011010
Rate/Price: 25.00 (selling price)
Current Stock: 0
Warehouse: Main Warehouse
```

#### Add Item #7: Bottled Water 500ml (Finished Product)

```
Item Name: Aqua Fresco 500ml Bottled Water
Description: 500ml bottled drinking water - finished product
Category/Group: Finished Goods
Unit of Measure: Pieces
Opening Stock: 0
Reorder Level: 800
HSN Code: 22011010
Rate/Price: 15.00 (selling price)
Current Stock: 0
Warehouse: Main Warehouse
```

### Step 4: View Your Inventory

Your stock items list now shows:
- ✅ 7 items added
- Raw materials, packaging, and finished goods
- Current stock levels
- Reorder levels set

**Note:** Dashboard will now show low stock alerts if any item is below reorder level!

---

## 🏭 MANUFACTURING & BOM SETUP

This is where the magic happens! We'll create Bill of Materials for your products.

### Step 1: Access BOM Management

1. Sidebar → **Manufacturing** → **BOM Management**
2. Click **"Create New BOM"**

### Step 2: Create BOM for 1L Bottled Water

Fill in the BOM form:

```
Product Item: Aqua Fresco 1L Bottled Water
BOM Name: BOM - 1L Bottled Water
Quantity to Produce: 1 (means this BOM is for producing 1 unit)
```

**Add Components:**

| Component | Quantity | Unit | Rate | Cost |
|-----------|----------|------|------|------|
| Purified Water (Bulk) | 1.05 | Liters | 0.50 | 0.53 |
| 1L PET Bottles | 1 | Pieces | 5.00 | 5.00 |
| Bottle Caps | 1 | Pieces | 0.50 | 0.50 |
| Product Labels (1L) | 1 | Pieces | 1.00 | 1.00 |

```
Total Component Cost: 7.03 per unit
Manufacturing Overhead: 1.00 (labor, electricity, etc.)
Total BOM Cost: 8.03 per unit
```

Click **"Save BOM"**
✅ Success: "BOM created successfully"

### Step 3: Create BOM for 500ml Bottled Water

Click **"Create New BOM"** again:

```
Product Item: Aqua Fresco 500ml Bottled Water
BOM Name: BOM - 500ml Bottled Water
Quantity to Produce: 1
```

**Add Components:**

| Component | Quantity | Unit | Rate | Cost |
|-----------|----------|------|------|------|
| Purified Water (Bulk) | 0.52 | Liters | 0.50 | 0.26 |
| 500ml PET Bottles | 1 | Pieces | 3.00 | 3.00 |
| Bottle Caps | 1 | Pieces | 0.50 | 0.50 |
| Product Labels (500ml) | 1 | Pieces | 1.00 | 1.00 |

```
Total Component Cost: 4.76 per unit
Manufacturing Overhead: 0.60
Total BOM Cost: 5.36 per unit
```

Click **"Save BOM"**

**Now you have:**
- ✅ BOM for 1L water (Cost: KSh 8.03/unit)
- ✅ BOM for 500ml water (Cost: KSh 5.36/unit)

---

## 🛒 MAKING YOUR FIRST SALE

Let's make a sale using the POS system!

### Method 1: Using POS Mode (Fastest)

#### Step 1: Access POS

1. Press **F8** key (keyboard shortcut!) OR
2. Sidebar → **Sales** → **POS Mode** OR
3. Dashboard → Click **"New Sale"** quick action

#### Step 2: Create Invoice in POS

The POS screen opens with a clean interface:

```
1. Select/Search Customer:
   - Type: "Walk-in Customer" or select existing customer

2. Add Items to Cart:

   Item 1:
   - Product: Aqua Fresco 1L Bottled Water
   - Quantity: 20
   - Price: 25.00 (auto-filled)
   - Tax: 16% GST (auto-calculated)
   - Line Total: 580.00

   Item 2:
   - Product: Aqua Fresco 500ml Bottled Water
   - Quantity: 50
   - Price: 15.00
   - Tax: 16% GST
   - Line Total: 870.00

3. Review Totals:
   Subtotal: 1,250.00
   Tax (16%): 200.00
   Discount: 0 (or enter discount if any)
   TOTAL: 1,450.00

4. Payment:
   - Payment Method: Cash
   - Amount Received: 1,500.00
   - Change: 50.00
```

#### Step 3: Complete Sale

1. Click **"Complete Sale"** button
2. ✅ Success toast: "Sale completed successfully"
3. Invoice number generated: **INV-001**
4. **Print receipt** button appears
5. Stock automatically deducted:
   - 1L Water: 0 → (-20)
   - 500ml Water: 0 → (-50)

**Note:** If stock is 0, you need to manufacture first! (See Manufacturing Process section)

### Method 2: Using Sales Invoice (Detailed)

#### Step 1: Access Sales Invoice

1. Sidebar → **Sales** → **Sales Invoice**
2. Click **"Create New Invoice"**

#### Step 2: Fill Invoice Details

```
Invoice Date: 2025-10-10 (auto-filled)
Due Date: 2025-10-25 (15 days payment term)
Customer: Select or create new customer
  - Name: ABC Supermarket
  - Email: abc@supermarket.com
  - Phone: +254 712 555 666
  - Address: Shop No. 45, City Mall, Nairobi

Reference Number: PO-12345 (customer's PO number)
```

#### Step 3: Add Line Items

```
Line 1:
- Item: Aqua Fresco 1L Bottled Water
- Description: 1 Liter bottled drinking water
- HSN Code: 22011010 (auto-filled)
- Quantity: 100
- Unit Price: 25.00
- Discount: 0%
- Tax Rate: 16%
- Amount: 2,900.00 (including tax)

Line 2:
- Item: Aqua Fresco 500ml Bottled Water
- Description: 500ml bottled drinking water
- HSN Code: 22011010
- Quantity: 200
- Unit Price: 15.00
- Discount: 5% (bulk discount)
- Tax Rate: 16%
- Amount: 3,306.00

Totals:
- Subtotal: 5,362.07
- Tax Amount: 738.00
- Discount: 150.00
- GRAND TOTAL: 6,206.00
```

#### Step 4: Save & Export

1. Click **"Save Invoice"**
2. ✅ Success: "Invoice INV-002 created successfully"
3. Click **"Export to PDF"** button
4. PDF downloads: `invoice-INV-002.pdf`
5. Open PDF to see professional invoice with:
   - Company header
   - Invoice details
   - Line items table
   - Totals
   - Terms & conditions

---

## 🛍️ RECORDING PURCHASES

Let's buy raw materials from suppliers.

### Step 1: Access Purchase Orders

1. Press **F9** key (keyboard shortcut!) OR
2. Sidebar → **Purchase** → **Purchase Orders**

### Step 2: Create Purchase Order

Click **"Create Purchase Order"**

```
Supplier Details:
- Supplier Name: Nairobi Water Suppliers Ltd
- Email: orders@nairobiwater.co.ke
- Phone: +254 711 222 333
- Address: Industrial Area, Nairobi

PO Date: 2025-10-10
Expected Delivery: 2025-10-15
Payment Terms: 30 days credit
```

### Step 3: Add Items to PO

```
Item 1:
- Product: Purified Water (Bulk)
- Quantity: 5000 Liters
- Rate: 0.50
- Tax: 16%
- Amount: 2,900.00

Item 2:
- Product: 1L PET Bottles
- Quantity: 3000 Pieces
- Rate: 5.00
- Tax: 16%
- Amount: 17,400.00

Item 3:
- Product: Bottle Caps
- Quantity: 3000 Pieces
- Rate: 0.50
- Tax: 16%
- Amount: 1,740.00

Total PO Value: 22,040.00
```

### Step 4: Save Purchase Order

1. Click **"Create Purchase Order"**
2. ✅ Success: "Purchase order PO-001 created successfully"
3. Status: **Pending**

### Step 5: Receive Goods (When Delivered)

When supplier delivers:

1. Open PO-001
2. Click **"Mark as Received"**
3. Stock automatically increases:
   - Purified Water: 10,000 → 15,000 Liters
   - 1L PET Bottles: 5,000 → 8,000 Pieces
   - Bottle Caps: 15,000 → 18,000 Pieces
4. Status changes to: **Received**

### Step 6: Record Payment to Supplier

1. Sidebar → **Accounting** → **Vouchers**
2. Press **F5** (Payment voucher shortcut)
3. Fill in:

```
Voucher Type: Payment
Date: 2025-10-10
Payment To: Nairobi Water Suppliers Ltd
Amount: 22,040.00
Payment Mode: Bank Transfer
Reference: PO-001
Bank Account: Main Bank Account
Narration: Payment for PO-001 - Raw materials purchase
```

4. Click **"Save Voucher"**
5. ✅ Accounts updated automatically

---

## 🏭 MANUFACTURING PROCESS

Now let's produce bottled water using your BOM!

### Step 1: Create Work Order

1. Sidebar → **Manufacturing** → **Work Orders**
2. Click **"Create Work Order"**

```
Work Order Details:
- Product: Aqua Fresco 1L Bottled Water
- BOM: BOM - 1L Bottled Water (auto-selected)
- Quantity to Produce: 1000 units
- Start Date: 2025-10-10
- Expected End Date: 2025-10-11
- Priority: High
```

#### View Material Requirements (Auto-calculated from BOM):

```
Materials Needed for 1000 units:
- Purified Water (Bulk): 1,050 Liters (1.05 × 1000)
- 1L PET Bottles: 1,000 Pieces
- Bottle Caps: 1,000 Pieces
- Product Labels (1L): 1,000 Pieces

Current Stock Check:
✅ Purified Water: 15,000 L (sufficient)
✅ 1L PET Bottles: 8,000 pcs (sufficient)
✅ Bottle Caps: 18,000 pcs (sufficient)
✅ Product Labels: 5,000 pcs (sufficient)

Estimated Cost: 8,030.00 (8.03 × 1000)
```

3. Click **"Create Work Order"**
4. ✅ Work Order WO-001 created
5. Status: **Planned**

### Step 2: Start Production

1. Open WO-001
2. Click **"Start Production"** button
3. Status changes to: **In Progress**
4. Production tracking begins

### Step 3: Record Production in Manufacturing Journal

1. Sidebar → **Manufacturing** → **Manufacturing Journal**
2. Click **"New Production Entry"**

```
Production Entry Details:
- Work Order: WO-001
- Product: Aqua Fresco 1L Bottled Water
- Production Date: 2025-10-10
- Quantity Produced: 1000 units
- Batch Number: BATCH-20251010-001
```

#### Materials Consumed (Auto-filled from BOM):

```
Input (Raw Materials):
□ Purified Water: 1,050 L × 0.50 = 525.00
□ 1L PET Bottles: 1,000 pcs × 5.00 = 5,000.00
□ Bottle Caps: 1,000 pcs × 0.50 = 500.00
□ Product Labels: 1,000 pcs × 1.00 = 1,000.00
Total Material Cost: 7,025.00

Labor & Overhead:
□ Manufacturing Overhead: 1,000.00
Total Production Cost: 8,025.00
```

#### Output (Finished Goods):

```
□ Aqua Fresco 1L Bottled Water: 1,000 units
□ Unit Cost: 8.03 (auto-calculated)
□ Total Value: 8,030.00

Scrap/Wastage:
□ Broken Bottles: 5 units
□ Damaged Labels: 3 units
```

### Step 4: Save Manufacturing Journal

1. Click **"Save Production Entry"**
2. ✅ Success: "Production recorded successfully"

**Automatic Stock Movements:**

```
DEDUCTIONS (Raw Materials):
- Purified Water: 15,000 → 13,950 Liters (-1,050)
- 1L PET Bottles: 8,000 → 7,000 Pieces (-1,000)
- Bottle Caps: 18,000 → 17,000 Pieces (-1,000)
- Product Labels: 5,000 → 4,000 Pieces (-1,000)

ADDITIONS (Finished Goods):
- Aqua Fresco 1L Bottled Water: 0 → 1,000 units (+1,000)
```

**Automatic Accounting Entries:**

```
Dr. Finished Goods Inventory: 8,030.00
   Cr. Raw Materials Inventory: 7,025.00
   Cr. Manufacturing Overhead: 1,005.00
```

### Step 5: Complete Work Order

1. Go back to WO-001
2. Click **"Mark as Completed"**
3. Status: **Completed**
4. Actual production: 1,000 units
5. Progress: 100%

**Now you have 1,000 units of 1L water ready to sell!**

---

## 💼 ACCOUNTING & VOUCHERS

Understanding the double-entry system and vouchers.

### Voucher Types & Keyboard Shortcuts:

| Key | Voucher Type | Purpose |
|-----|--------------|---------|
| F4 | Contra | Bank/Cash transfers |
| F5 | Payment | Pay suppliers/expenses |
| F6 | Receipt | Receive from customers |
| F7 | Journal | Adjustments, corrections |
| F8 | Sales | Record sales (or use Sales module) |
| F9 | Purchase | Record purchases (or use Purchase module) |

### Example 1: Contra Entry (Bank to Cash Transfer)

**Scenario:** Transfer KSh 50,000 from bank to petty cash

1. Press **F4** key
2. Voucher form opens:

```
Voucher Type: Contra
Date: 2025-10-10
Reference: TRANS-001
Narration: Transfer from bank to petty cash for daily expenses

Debit:
- Account: Petty Cash
- Amount: 50,000.00

Credit:
- Account: Main Bank Account
- Amount: 50,000.00

Total Debit: 50,000.00
Total Credit: 50,000.00
✓ Balanced
```

3. Click **"Save Voucher"**
4. ✅ Contra voucher CONTRA-001 saved

### Example 2: Payment Entry (Pay Electricity Bill)

**Scenario:** Pay monthly electricity bill

1. Press **F5** key
2. Fill in:

```
Voucher Type: Payment
Date: 2025-10-10
Payment To: Kenya Power & Lighting Co.
Mode: Bank Transfer
Reference: KPLC-202510
Narration: Electricity bill for September 2025

Debit:
- Account: Utilities Expense
- Amount: 45,000.00

Credit:
- Account: Main Bank Account
- Amount: 45,000.00
```

3. Click **"Save Payment"**

### Example 3: Receipt Entry (Receive from Customer)

**Scenario:** Customer pays for invoice INV-002

1. Press **F6** key
2. Fill in:

```
Voucher Type: Receipt
Date: 2025-10-11
Received From: ABC Supermarket
Mode: Cheque
Reference: CHQ-445566
Narration: Payment for Invoice INV-002

Debit:
- Account: Main Bank Account
- Amount: 6,206.00

Credit:
- Account: Accounts Receivable - ABC Supermarket
- Amount: 6,206.00
```

3. Click **"Save Receipt"**
4. ✅ Invoice INV-002 marked as paid

### Example 4: Journal Entry (GST Adjustment)

**Scenario:** Adjust GST calculation error

1. Press **F7** key
2. Fill in:

```
Voucher Type: Journal
Date: 2025-10-10
Reference: ADJ-001
Narration: GST reversal for export sales (zero-rated)

Debit:
- Account: GST Payable
- Amount: 500.00

Credit:
- Account: Sales Income
- Amount: 500.00
```

3. Click **"Save Journal"**

---

## 📊 GENERATING REPORTS

### Financial Reports

#### Step 1: Access Reports

1. Sidebar → **Accounting** → **Reports**
2. You'll see report types:
   - Trial Balance
   - Balance Sheet
   - Profit & Loss Statement
   - Cash Flow Statement
   - Day Book

#### Step 2: Generate Trial Balance

```
Select Report: Trial Balance
From Date: 2025-01-01
To Date: 2025-10-10
```

Click **"Generate Report"**

**Sample Trial Balance Output:**

```
AQUA FRESCO WATER COMPANY
TRIAL BALANCE
As on October 10, 2025

Account Name                    Debit       Credit
----------------------------------------------------
ASSETS:
Main Bank Account              125,000.00
Petty Cash                      50,000.00
Accounts Receivable            156,000.00
Stock - Raw Materials          215,000.00
Stock - Finished Goods          40,150.00

LIABILITIES:
Accounts Payable                            89,000.00
GST Payable                                 45,200.00

INCOME:
Water Sales                                285,000.00
Other Sales                                 12,000.00

EXPENSES:
Raw Material Expenses           67,000.00
Manufacturing Expenses          28,000.00
Utilities                       45,000.00
Salaries                       120,000.00
----------------------------------------------------
TOTAL:                         846,150.00  431,200.00
```

#### Step 3: Export to PDF

1. Click **"Export to PDF"** button
2. Professional PDF downloads
3. Share with accountant or management

#### Step 4: Export to Excel

1. Click **"Export to Excel"** button
2. `.xlsx` file downloads
3. Open in Excel for further analysis

### Inventory Reports

#### Step 1: Stock Summary Report

1. Sidebar → **Inventory** → **Reports** (or use Reports module)
2. Select: **Stock Summary**
3. Click **"Generate"**

**Output:**

```
Item Name                       Current Stock  Reorder Level  Status
------------------------------------------------------------------------
Purified Water (Bulk)           13,950 L       2,000 L        ✓ OK
1L PET Bottles                  7,000 pcs      1,000 pcs      ✓ OK
500ml PET Bottles               8,000 pcs      1,500 pcs      ✓ OK
Bottle Caps                     17,000 pcs     3,000 pcs      ✓ OK
Product Labels (1L)             4,000 pcs      1,000 pcs      ✓ OK
Aqua Fresco 1L Water            1,000 units    500 units      ✓ OK
Aqua Fresco 500ml Water         0 units        800 units      ⚠ LOW
```

#### Step 2: Stock Movement Report

Shows all stock in/out transactions:

```
Date       Item                Type        Qty      Balance
------------------------------------------------------------
10-Oct     1L PET Bottles      Purchase    +3,000   8,000
10-Oct     Purified Water      Purchase    +5,000   15,000
10-Oct     1L PET Bottles      Production  -1,000   7,000
10-Oct     Purified Water      Production  -1,050   13,950
10-Oct     1L Bottled Water    Production  +1,000   1,000
10-Oct     1L Bottled Water    Sales       -20      980
```

### Sales Reports

#### Sales Register (Last 30 Days)

```
Invoice No   Date       Customer          Items  Amount     Status
-------------------------------------------------------------------
INV-001      10-Oct     Walk-in Customer  2      1,450.00   Paid
INV-002      10-Oct     ABC Supermarket   2      6,206.00   Paid
INV-003      11-Oct     City Mall         5      12,500.00  Pending
```

Click **"Export to Excel"** for detailed analysis.

### Manufacturing Reports

#### BOM Cost Analysis

```
Product                    Materials  Overhead  Total Cost  Selling Price  Margin
-----------------------------------------------------------------------------------
1L Bottled Water          7.03       1.00      8.03        25.00          68%
500ml Bottled Water       4.76       0.60      5.36        15.00          64%
```

#### Production Summary

```
Month: October 2025

Product              Quantity Produced  Material Cost  Total Cost
-------------------------------------------------------------------
1L Bottled Water     1,000 units        7,025.00       8,030.00
500ml Bottled Water  0 units            0.00           0.00

Total Production Value: 8,030.00
```

---

## 📅 DAILY OPERATIONS WORKFLOW

### Morning Routine (30 minutes)

**8:00 AM - Login & Check Dashboard**

1. Login to system
2. Review dashboard:
   - Yesterday's sales total
   - Low stock alerts (if any)
   - Pending orders count
3. Check notifications (bell icon)
4. Review production status

**8:15 AM - Check Pending Orders**

1. Go to Sales → Quotations/Orders
2. Review pending quotations
3. Convert approved quotations to invoices
4. Send invoices to customers

**8:30 AM - Production Planning**

1. Go to Manufacturing → Work Orders
2. Review in-progress production
3. Create new work orders if needed
4. Check material availability for planned production

### Mid-Day Operations (Ongoing)

**Process Sales (Throughout the day)**

Option A: Walk-in customers (POS)
1. Press **F8** (or click POS icon)
2. Add items to cart
3. Select payment method
4. Complete sale
5. Print receipt

Option B: Corporate orders (Full Invoice)
1. Create Sales Invoice
2. Add multiple line items
3. Set payment terms
4. Save and send PDF to customer

**Record Production**

When production batch completes:
1. Manufacturing → Manufacturing Journal
2. Create new entry
3. Link to work order
4. Save (stock auto-updates)

**Handle Customer Payments**

When customer pays:
1. Press **F6** (Receipt)
2. Enter customer and amount
3. Link to invoice
4. Save

### End of Day (30 minutes)

**5:00 PM - Sales Closing**

1. Go to Reports → Sales Register
2. Select today's date
3. Review all sales
4. Export to Excel
5. Match with cash in hand

**5:15 PM - Stock Verification**

1. Check low stock alerts on dashboard
2. Create purchase orders for low stock items
3. Review reorder levels

**5:30 PM - Accounting Reconciliation**

1. Review Day Book (all vouchers for today)
2. Verify all entries
3. Check Trial Balance
4. Note any discrepancies for next day

**5:45 PM - Backup**

1. Go to Settings
2. Click **"Backup Now"** (manual backup)
3. Or ensure auto-backup is scheduled

---

## ⌨️ KEYBOARD SHORTCUTS

### Global Shortcuts

| Key | Action | Takes you to |
|-----|--------|--------------|
| F4 | Contra Voucher | Accounting → Vouchers (Contra) |
| F5 | Payment Voucher | Accounting → Vouchers (Payment) |
| F6 | Receipt Voucher | Accounting → Vouchers (Receipt) |
| F7 | Journal Voucher | Accounting → Vouchers (Journal) |
| F8 | Sales/POS | Sales → Invoices |
| F9 | Purchase Order | Purchase → Orders |

### Usage Tips:

**Example 1:** Quick Cash Sale
```
1. Press F8
2. POS opens
3. Add items
4. Click "Complete Sale"
5. Done in 30 seconds!
```

**Example 2:** Record Supplier Payment
```
1. Press F5
2. Payment voucher opens
3. Select supplier
4. Enter amount
5. Save
6. Done in 1 minute!
```

**Example 3:** Transfer Cash to Bank
```
1. Press F4
2. Contra voucher opens
3. Debit: Bank Account
4. Credit: Cash
5. Save
```

---

## 🎓 ADVANCED FEATURES

### Multi-Currency Transactions

**Step 1: Enable Multi-Currency**
1. Settings → Company
2. Check "Enable Multi-Currency"
3. Select default currency: KES

**Step 2: Create USD Sale**
```
Invoice Currency: USD
Item Price: $10.00
Quantity: 100
Exchange Rate: 129.50 KES/USD (auto-fetched)
Amount in KES: 129,500.00
```

### E-Way Bill Generation

**For interstate/high-value sales:**

1. Create invoice with value > 50,000
2. Fill transport details:
   - Vehicle Number: KBZ 123A
   - Transporter: Fast Delivery Ltd
   - Distance: 450 km
   - LR Number: LR-12345
3. Click **"Generate e-Way Bill"**
4. PDF downloads with:
   - e-Way Bill Number
   - Transport details
   - Item details with HSN codes
   - Tax details

### Barcode Integration (Future Feature)

**When implemented:**
1. Each product gets barcode in stock master
2. In POS, scan barcode instead of searching
3. Faster checkout process

---

## 🆘 COMMON SCENARIOS & SOLUTIONS

### Scenario 1: Customer Returns Product

**Steps:**
1. Create Credit Note:
   - Go to Sales → Credit Notes
   - Select original invoice
   - Enter returned quantity
   - Save
2. Stock increases automatically
3. Refund customer (use F5 - Payment voucher)

### Scenario 2: Stock Adjustment (Damaged Goods)

**Steps:**
1. Go to Inventory → Stock Journal
2. Create adjustment entry:
   ```
   Item: 1L Bottled Water
   Adjustment Type: Negative
   Quantity: 10 (damaged units)
   Reason: Damaged during loading
   ```
3. Save
4. Stock reduces by 10

### Scenario 3: Production Scrap/Wastage

**Steps:**
1. In Manufacturing Journal entry:
2. Add scrap section:
   ```
   Broken Bottles: 5 units
   Leaked Water: 10 liters
   Scrap Value: 0
   ```
3. Cost is absorbed in production overhead
4. Save entry

### Scenario 4: Advance Payment from Customer

**Steps:**
1. Press F6 (Receipt)
2. Create receipt:
   ```
   From: Customer Name
   Amount: 10,000.00
   Against: Advance (no invoice)
   ```
3. When invoice is created:
   - Link invoice to advance payment
   - Adjust balance due

### Scenario 5: Low Stock Alert

**When dashboard shows low stock:**
1. Click the alert notification
2. It takes you to Stock Items
3. Note items below reorder level
4. Create Purchase Order for those items
5. Send PO to supplier

---

## 📱 MOBILE ACCESS

The system is responsive! Access from:

**Mobile Phone:**
- Sidebar collapses to hamburger menu
- Tables scroll horizontally
- Touch-friendly buttons
- POS optimized for tablet

**Tablet:**
- Perfect for POS operations
- Warehouse stock checking
- Production floor updates

---

## 🎯 QUICK REFERENCE CARD

### Daily Tasks Checklist

**Morning:**
- [ ] Check dashboard for alerts
- [ ] Review low stock items
- [ ] Check pending orders
- [ ] Plan production

**During Day:**
- [ ] Process sales (F8)
- [ ] Record receipts (F6)
- [ ] Update production status
- [ ] Handle customer queries

**Evening:**
- [ ] Generate sales report
- [ ] Reconcile cash
- [ ] Check Trial Balance
- [ ] Backup data

### Important Reminders

✅ **Always double-check:**
- Customer details in invoices
- Quantities in production
- Payment amounts
- Stock levels before promising delivery

✅ **Regular Tasks:**
- Weekly: Generate financial reports
- Monthly: Stock audit
- Monthly: Supplier payment reconciliation
- Quarterly: BOM cost review

✅ **Security:**
- Change passwords regularly
- Logout when leaving desk
- Don't share admin credentials
- Review audit logs monthly

---

## 🎉 CONGRATULATIONS!

You now know how to:
✅ Setup the entire system from scratch
✅ Manage users and permissions
✅ Configure company settings
✅ Set up complete inventory
✅ Create Bill of Materials
✅ Record manufacturing production
✅ Make sales (POS and invoicing)
✅ Record purchases
✅ Handle accounting vouchers
✅ Generate comprehensive reports
✅ Export to PDF and Excel
✅ Use keyboard shortcuts for speed

---

## 📞 NEED HELP?

**For Technical Issues:**
- Check TROUBLESHOOTING section in README.md
- Review error messages in browser console
- Check XAMPP error logs

**For Operational Queries:**
- Refer to specific sections in this guide
- Check VERIFICATION_CHECKLIST.md for testing steps
- Review TESTING_REPORT.md for feature status

---

**Happy Managing Your Water Business! 💧🚀**

*Aqua Fresco POS - Making water business management simple and efficient!*

---

**Document Version:** 1.0
**Last Updated:** October 10, 2025
**System Version:** 2.0.0 Enhanced
