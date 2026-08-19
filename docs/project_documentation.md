# Synapse-ERP - Enterprise Inventory Management System Documentation (Corporate Edition)
> **Tagline:** Next-Gen Corporate Inventory & Supply Intelligence | Precision Stock Control for Modern Enterprises
> **Author / Maintainer:** [Rokibul Islam Robi](https://github.com/Rokibul-Islam-Robi)

## 1. System Architecture & Overview

This system is an enterprise-grade Inventory & Supply Chain Management application built using **Core PHP (PDO)**, **MySQL (InnoDB)**, **Modern CSS3 (Responsive UI/UX)**, **JavaScript**, and **Chart.js**.

---

## 2. Database Schema Design

### `users`
Stores system operators, administrators, and staff.
- `id` (INT PK AI)
- `name` (VARCHAR 100)
- `username` (VARCHAR 50 UNIQUE)
- `email` (VARCHAR 100)
- `phone` (VARCHAR 20)
- `password` (VARCHAR 255 - Hashed)
- `role` (ENUM 'admin', 'manager', 'staff')
- `status` (TINYINT 1)
- `created_at` (TIMESTAMP)

### `categories`
Stores product category groupings.
- `id` (INT PK AI)
- `category_name` (VARCHAR 100 UNIQUE)
- `description` (TEXT)
- `status` (TINYINT 1)
- `created_at` (TIMESTAMP)

### `products`
Master catalog items.
- `id` (INT PK AI)
- `category_id` (INT FK -> `categories.id`)
- `product_name` (VARCHAR 150)
- `sku` (VARCHAR 80 UNIQUE)
- `unit` (VARCHAR 30, e.g. pcs, box, kg)
- `opening_stock` (INT)
- `buying_price` (DECIMAL 10,2)
- `selling_price` (DECIMAL 10,2)
- `alert_quantity` (INT)
- `description` (TEXT)
- `status` (TINYINT 1)
- `created_at` (TIMESTAMP)

### `suppliers`
Vendor and procurement partners.
- `id` (INT PK AI)
- `name` (VARCHAR 100)
- `company_name` (VARCHAR 120)
- `email` (VARCHAR 100)
- `phone` (VARCHAR 30)
- `address` (TEXT)
- `status` (TINYINT 1)
- `created_at` (TIMESTAMP)

### `customers`
Clients and internal departments.
- `id` (INT PK AI)
- `name` (VARCHAR 100)
- `customer_type` (ENUM 'individual', 'corporate', 'internal_dept')
- `email` (VARCHAR 100)
- `phone` (VARCHAR 30)
- `address` (TEXT)
- `status` (TINYINT 1)
- `created_at` (TIMESTAMP)

### `stock_transactions`
Inward and outward stock movement records.
- `id` (INT PK AI)
- `product_id` (INT FK -> `products.id`)
- `transaction_type` (ENUM 'IN', 'OUT')
- `supplier_id` (INT NULL FK -> `suppliers.id`)
- `customer_id` (INT NULL FK -> `customers.id`)
- `reference_no` (VARCHAR 100)
- `quantity` (INT)
- `unit_price` (DECIMAL 10,2)
- `total_price` (DECIMAL 12,2)
- `transaction_date` (DATE)
- `remarks` (VARCHAR 255)
- `created_by` (INT FK -> `users.id`)
- `created_at` (TIMESTAMP)

### `activity_logs`
System audit trail.
- `id` (INT PK AI)
- `user_id` (INT FK -> `users.id`)
- `action` (VARCHAR 100)
- `details` (TEXT)
- `ip_address` (VARCHAR 50)
- `created_at` (TIMESTAMP)

---

## 3. Directory Layout

```text
mini_inventory_management_system/
├── assets/
│   ├── css/style.css             # Enterprise corporate design system
│   └── js/app.js                 # Search, CSV exporter, modal & drawer handlers
├── auth/
│   ├── login.php                 # Corporate login screen
│   └── logout.php                # Session clearing
├── categories/
│   ├── index.php                 # Categories list with product counts
│   ├── create.php                # Create category
│   ├── edit.php                  # Edit category
│   └── delete.php                # Safe deletion
├── products/
│   ├── index.php                 # Catalog list with health indicators
│   ├── create.php                # Add SKU, pricing & threshold
│   ├── edit.php                  # Edit product
│   └── delete.php                # Safe delete
├── suppliers/
│   ├── index.php                 # Vendor directory
│   ├── create.php                # Add vendor
│   ├── edit.php                  # Edit vendor
│   └── delete.php                # Safe delete
├── customers/
│   ├── index.php                 # Clients & departments directory
│   ├── create.php                # Add client/department
│   ├── edit.php                  # Edit client/department
│   └── delete.php                # Safe delete
├── stock/
│   ├── stock_in.php              # Inward receiving voucher
│   └── stock_out.php             # Outward dispatch voucher
├── reports/
│   ├── current_stock.php         # Warehouse stock valuation report
│   ├── date_wise.php             # Date-wise audit history report
│   └── low_stock.php             # Reorder replenishment sheet
├── users/
│   ├── index.php                 # Operator management (Admin only)
│   ├── create.php                # Add operator
│   ├── edit.php                  # Edit operator & password
│   └── delete.php                # Safe delete operator
├── config/
│   ├── database.php              # PDO configuration with auto-migration hook
│   └── helpers.php               # Flash messaging, RBAC, formatting
├── database/
│   ├── inventory_db.sql          # Complete SQL schema
│   └── migrate.php               # Safe schema auto-migration script
├── docs/
│   └── project_documentation.md  # Detailed technical documentation
└── includes/
    ├── header.php                # Header with meta & Chart.js
    ├── sidebar.php               # Sidebar navigation & topbar
    └── footer.php                # Layout footer & script inclusion
```
