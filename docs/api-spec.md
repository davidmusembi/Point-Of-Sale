# ultimatePOS API Specification (V1)

This document outlines the V1 API endpoints available for managing POS terminals and cash registers.

## Authentication
All API requests must be authenticated using **Bearer Token**.

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

#### Example Request
```json
{
    "user_id": 5,
    "location_id": 1,
    "initial_amount": 100.00
}
```

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

#### Error Responses
- **400 Bad Request:** User already has an open register (`REGISTER_ALREADY_OPEN`).
- **403 Forbidden:** Unauthorized to open register for this user (`UNAUTHORIZED`).
- **422 Unprocessable Entity:** Validation failed (`VALIDATION_FAILED`).
- **Body:**
```json
{
    "ok": false,
    "data": null,
    "meta": null,
    "error": {
        "code": "VALIDATION_FAILED",
        "message": "The given data was invalid.",
        "fields": {
            "user_id": ["The user id field is required."]
        }
    }
}
```

---

### 2. Check Register Status
Checks if a specific user has an open register.

- **Endpoint:** `GET /api/v1/cash-register/status/{user_id}`
- **Authentication:** Required

#### Success Response
- **Code:** 200 OK
- **Body:**
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

### 3. Close Cash Register
Closes an active cash register session.

- **Endpoint:** `POST /api/v1/cash-register/close`
- **Content-Type:** `application/json`

*(Note: Endpoint implementation pending, but follows the same V1 response structure)*

---

## Reports (V1)

All report endpoints support the following optional query parameters for filtering:
- `location_id`: Filter by business location.
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
- **Success Response:** `Standard Paginated Response`

### 3. Sales Report
Returns a detailed list of sales.
- **Endpoint:** `GET /api/v1/reports/sales`
- **Success Response:** `Standard Paginated Response`

*(More report endpoints documented in TASK_TRACKER_REPORTS.md)*
