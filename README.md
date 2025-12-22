# Point of Sale (POS) System

## Introduction
This is a **Point of Sale (POS) System** built using Laravel, a PHP framework, designed to manage sales, inventory, and customers efficiently. The system provides a user-friendly interface for processing transactions, tracking stock levels, and generating sales reports.

## Features
- **User Management** (Admins, Cashiers, Managers)
- **Product & Inventory Management**
- **Sales & Invoicing**
- **Customer Management**
- **Reports & Analytics**
- **Discounts & Promotions**
- **Multi-Payment Methods**
- **Role-Based Access Control**
- **Receipt Printing**
- **Tax Management**

## Technologies Used
- **Framework:** Laravel 10+
- **Frontend:** Blade, Tailwind CSS, Vue.js 
- **Database:** MySQL/PostgreSQL
- **Authentication:** Laravel Breeze / Laravel Jetstream
- **Payment Gateway:** Stripe, PesaPal 
- **Server:** Apache / Nginx

## Installation Guide
### Prerequisites
Ensure you have the following installed on your system:
- PHP 8.1+
- Composer
- MySQL / PostgreSQL
- Node.js & NPM (for frontend dependencies)
- Laravel CLI

### Steps to Install
1. **Clone the Repository:**
   ```sh
   git clone https://github.com/davidmusembi/pos-system.git
   cd pos-system
   ```



2. **Environment Setup:**
   ```sh
   cp .env.example .env
   php artisan key:generate
   ```
   - Configure your `.env` file with the correct database credentials.

3. **Run Migrations & Seed Data:**
   ```sh
   php artisan migrate --seed
   ```

4. **Start the Application:**
   ```sh
   php artisan serve
   ```

5. **Access the POS System:**
   Open your browser and navigate to:
   ```
   http://127.0.0.1:8000
   ```

## Usage
- Admin can add products, manage users, and view reports.
- Cashiers can process transactions, generate invoices, and print receipts.
- Inventory is updated automatically after each sale.



## Contribution
Contributions are welcome! To contribute:
1. Fork the repository
2. Create a new branch (`feature/your-feature`)
3. Commit your changes (`git commit -m 'Add new feature'`)
4. Push to your branch (`git push origin feature/your-feature`)
5. Open a Pull Request

## License
This project is open-source and available under the [MIT License](LICENSE).

