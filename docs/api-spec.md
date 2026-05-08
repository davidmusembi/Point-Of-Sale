# ultimatePOS API Specification (V1)

This document outlines the V1 API endpoints available for managing POS terminals, cash registers, and business reports.

## Authentication
All API requests must be authenticated using **Bearer Token**.

---

## Pagination Standards

Many endpoints return lists of data that are paginated.

### Request Parameters
| Parameter | Type | Default | Description |
| :--- | :--- | :--- | :--- |
| `page` | Integer | 1 | The page number to retrieve. |
| `perPage` | Integer | 20 | Number of items per page (Max: 100 recommended). |

### Response Structure (Paginated)
```json
{
    "ok": true,
    "data": [
        { "id": 1, "...": "..." },
        { "id": 2, "...": "..." }
    ],
    "meta": {
        "page": 1,
        "perPage": 20,
        "total": 150,
        "totalPages": 8
    },
    "error": null
}
```

#### Meta Object Definition
- `page`: The current page number (1-indexed).
- `perPage`: The number of items returned in this page.
- `total`: The total number of items available across all pages.
- `totalPages`: Total number of pages available based on `total` and `perPage`.

---

## Cash Register Management

### 1. Open Cash Register
Opens a new cash register session for a specific user at a specific location.

- **Endpoint:** `POST /api/v1/cash-register/open`
- **Authentication:** Required (Admin/Manager permissions recommended)
- **Content-Type:** `application/json`

#### Request Body
| Parameter | Type | Required | Description |
| :--- | :--- | :--- | :--- |
| `user_id` | Integer | Yes | The ID of the user for whom the register is being opened. |
| `location_id` | Integer | Yes | The Business Location ID. |
| `initial_amount` | Decimal | No | The opening cash balance (Default: 0). |

#### Success Response
- **Code:** 200 OK
- **Body:**
```json
{
    "ok": true,
    "data": {
        "register_id": 42,
        "user_id": 5,
        "location_id": 1,
        "status": "open",
        "opened_at": "2026-05-05 17:30:00"
    },
    "meta": null,
    "error": null
}
```

---

### 2. Check Register Status
Checks if a specific user has an open register.

- **Endpoint:** `GET /api/v1/cash-register/status/{user_id}`

#### Success Response
```json
{
    "ok": true,
    "data": {
        "is_open": true,
        "register_details": {
            "id": 42,
            "location_id": 1,
            "opened_at": "2026-05-05 17:30:00"
        }
    },
    "meta": null,
    "error": null
}
```

---

## Reports (V1)

All report endpoints support the following optional query parameters for filtering:
- `location_id`: Filter by business location ID.
- `start_date`: Filter by start date (YYYY-MM-DD).
- `end_date`: Filter by end date (YYYY-MM-DD).

### 1. Profit & Loss Report
Returns the profit and loss summary.
- **Endpoint:** `GET /api/v1/reports/profit-loss`
- **Success Response:**
```json
{
    "ok": true,
    "data": {
        "opening_stock": 1000.00,
        "closing_stock": 1200.00,
        "total_purchase": 5000.00,
        "total_sell": 8000.00,
        "total_expense": 500.00,
        "net_profit": 3700.00
    },
    "meta": null,
    "error": null
}
```

### 2. Stock Report
Returns current stock levels for products.
- **Endpoint:** `GET /api/v1/reports/stock`
- **Parameters:** `category_id`, `brand_id`, `unit_id`, `type`
- **Success Response:**
```json
{
    "ok": true,
    "data": [
        {
            "product_id": 1,
            "name": "Product Name",
            "variation": "Size: Large",
            "qty_available": 150.0,
            "unit": "Pc",
            "location_name": "Main Store"
        }
    ],
    "meta": {
        "page": 1,
        "perPage": 20,
        "total": 150,
        "totalPages": 8
    },
    "error": null
}
```

### 3. Sales Report (Revenue Analysis)
Returns revenue trends and breakdown.
- **Endpoint:** `GET /api/v1/reports/sales`
- **Parameters:** `groupBy` (day|week|month)
- **Success Response:**
```json
{
    "ok": true,
    "data": {
        "summary": {
            "totalRevenue": 15000.00,
            "totalTransactions": 120,
            "netRevenue": 14500.00
        },
        "series": [
            { "period": "2026-05-01", "revenue": 1200.00, "transactions": 10 }
        ],
        "byCategory": [
            { "categoryName": "Electronics", "revenue": 5000.00, "percent": 33.33 }
        ]
    },
    "meta": null,
    "error": null
}
```

### 4. Purchase & Sell Report
- **Endpoint:** `GET /api/v1/reports/purchase-sell`
- **Success Response:**
```json
{
    "ok": true,
    "data": {
        "purchase": { "total_purchase_inc_tax": 5000.00 },
        "sell": { "total_sell_inc_tax": 8000.00 },
        "difference": { "total_purchase_inc_tax": -3000.00 }
    },
    "meta": null,
    "error": null
}
```

### 5. Tax Report
- **Endpoint:** `GET /api/v1/reports/tax`
- **Success Response:**
```json
{
    "ok": true,
    "data": {
        "input_tax": { "total_tax": 200.00 },
        "output_tax": { "total_tax": 450.00 },
        "tax_diff": 250.00
    },
    "meta": null,
    "error": null
}
```

### 6. Expense Report
- **Endpoint:** `GET /api/v1/reports/expense`
- **Success Response:**
```json
{
    "ok": true,
    "data": [
        { "category": "Rent", "total_expense": 1200.00 }
    ],
    "meta": null,
    "error": null
}
```

### 7. Stock Value Report
- **Endpoint:** `GET /api/v1/reports/stock-value`
- **Success Response:**
```json
{
    "ok": true,
    "data": {
        "closing_stock_by_pp": 15000.00,
        "closing_stock_by_sp": 22000.00,
        "potential_profit": 7000.00,
        "profit_margin": 31.81
    },
    "meta": null,
    "error": null
}
```

### 8. Customer & Supplier Report
Returns a paginated list of contacts with their total purchase, invoice, and payment amounts.
- **Endpoint:** `GET /api/v1/reports/customer-supplier`
- **Parameters:** `customer_group_id`, `contact_id`, `contact_type`
- **Success Response:** (Paginated)
```json
{
    "ok": true,
    "data": [
        {
            "id": 1,
            "name": "Acme Corp",
            "supplier_business_name": "Acme Wholesale",
            "contact_type": "supplier",
            "total_purchase": 5000.00,
            "purchase_paid": 2000.00,
            "total_invoice": 0.00,
            "invoice_received": 0.00
        }
    ],
    "meta": {
        "page": 1,
        "perPage": 20,
        "total": 50,
        "totalPages": 3
    },
    "error": null
}
```

### 9. Sales Representative Report
Returns an aggregated summary of sales, expenses, and commissions for a specific user.
- **Endpoint:** `GET /api/v1/reports/sales-representative`
- **Parameters:** `created_by` (User ID)
- **Success Response:**
```json
{
    "ok": true,
    "data": {
        "total_expense": 500.00,
        "total_sell_exc_tax": 10000.00,
        "total_sell_return_exc_tax": 500.00,
        "total_sell": 9500.00,
        "total_commission": 475.00
    },
    "meta": null,
    "error": null
}
```

### 10. Balance Sheet
Returns the core accounting overview including dues, account balances, and stock value.
- **Endpoint:** `GET /api/v1/reports/balance-sheet`
- **Success Response:**
```json
{
    "ok": true,
    "data": {
        "supplier_due": 3000.00,
        "customer_due": 1500.00,
        "account_balances": {
            "Main Account": 15000.00,
            "Petty Cash": 500.00
        },
        "closing_stock": 25000.00,
        "capital_account_details": null
    },
    "meta": null,
    "error": null
}
```

### 11. Activity Log
Returns a paginated list of system activities and audit logs.
- **Endpoint:** `GET /api/v1/reports/activity-log`
- **Parameters:** `user_id`, `subject_type` (contact, user, sell, purchase, etc.)
- **Success Response:** (Paginated)
```json
{
    "ok": true,
    "data": [
        {
            "id": 101,
            "log_name": "default",
            "description": "created",
            "subject_id": 42,
            "subject_type": "App\\Transaction",
            "causer_id": 5,
            "created_by": "John Doe",
            "created_at": "2026-05-07T10:00:00.000000Z"
        }
    ],
    "meta": {
        "page": 1,
        "perPage": 20,
        "total": 1200,
        "totalPages": 60
    },
    "error": null
}
```

### 12. Other Available Report Endpoints
The following endpoints share the standard paginated JSON structure and accept standard date/location filters:

*   **Customer Group Report:** `GET /api/v1/reports/customer-group`
*   **Service Staff Report:** `GET /api/v1/reports/service-staff`
*   **Purchase Payment Report:** `GET /api/v1/reports/purchase-payment`
*   **Sell Payment Report:** `GET /api/v1/reports/sell-payment`
*   **Trial Balance:** `GET /api/v1/reports/trial-balance` (Returns summary object)
*   **Payment Account Report:** `GET /api/v1/reports/payment-account`
*   **Table Report:** `GET /api/v1/reports/table`
*   **GST Sales Report:** `GET /api/v1/reports/gst-sales`
*   **GST Purchase Report:** `GET /api/v1/reports/gst-purchase`
