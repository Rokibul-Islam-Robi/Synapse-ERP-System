# Synapse-ERP

<p align="center">
  <img src="mini_inventory_management_system/assets/img/logo.svg" alt="Synapse-ERP Logo" width="280">
</p>

<p align="center">
  <strong>Next-Gen Corporate Inventory &amp; Supply Intelligence</strong><br>
  <em>Precision Stock Control for Modern Enterprises</em>
</p>

<p align="center">
  <img src="https://img.shields.io/badge/PHP-8.2%2B-777BB4?style=for-the-badge&logo=php&logoColor=white" alt="PHP 8.2+">
  <img src="https://img.shields.io/badge/MySQL-8.0%2B-4479A1?style=for-the-badge&logo=mysql&logoColor=white" alt="MySQL 8.0+">
  <img src="https://img.shields.io/badge/Docker-Ready-2496ED?style=for-the-badge&logo=docker&logoColor=white" alt="Docker Ready">
  <img src="https://img.shields.io/badge/RBAC-Admin%20%7C%20Manager%20%7C%20Staff-10b981?style=for-the-badge" alt="RBAC">
  <img src="https://img.shields.io/badge/Tests-100%25%20Passing-success?style=for-the-badge" alt="Tests Passing">
  <img src="https://img.shields.io/badge/License-MIT-blue?style=for-the-badge" alt="MIT License">
</p>

---

## 📌 Executive Overview

**Synapse-ERP** is an enterprise-grade inventory governance and supply intelligence platform engineered for mid-to-large-scale commercial operations, distribution centers, and multi-departmental enterprises. 

Equipped with a **split-panel authentication portal**, **multi-tier RBAC authorization**, **real-time valuation algorithms**, **automated reorder deficit engine**, **in-app live notification drawer**, and **Docker containerization**, Synapse-ERP ensures complete operational visibility and transaction audit compliance.

---

## 🌟 Key Capabilities & Architecture

### 1. 🛡️ Split-Panel Authentication & RBAC Engine
* **Split-Panel UI**: Dark navy hero brand panel + clean corporate card with embedded input icons and password view toggle.
* **1-Click Demo Role Switcher**: Instant switching between `Admin`, `Manager`, and `Staff` roles with preloaded credentials.
* **Role-Based Access Control (RBAC)**:
  * **Admin**: System-wide authority, user account creation, permission governance, and audit trails.
  * **Manager**: Master catalogs, stock adjustments, supplier/client linkages, valuation reports, and restock alerts.
  * **Staff**: Operational receiving (Stock In) and order dispatch (Stock Out) with zero modification rights over historical logs.

### 2. 📈 Inventory Valuation & Stock Movement
* **Live Financial Asset Tracking**: Real-time asset valuation based on buying prices:
  $$\text{Asset Valuation} = \sum (\text{Current Stock}_i \times \text{Buying Price}_i)$$
* **Stock Consistency Safeguards**: Strict mathematical balance formula:
  $$\text{Current Stock} = \text{Opening Stock} + \sum(\text{Stock In}) - \sum(\text{Stock Out})$$
* **Negative Stock Prevention**: Database and backend validation prevents dispatching units beyond current physical balance.

### 3. 🚨 Real-Time Reorder & Deficit Engine
* **Threshold Monitors**: Automatic identification of SKUs where $\text{Current Stock} \le \text{Alert Quantity}$.
* **Deficit Calculation**: Calculates precise restock deficit quantities:
  $$\text{Deficit} = \max(0, \text{Alert Quantity} \times 2 - \text{Current Stock})$$
* **1-Click Restock Pipeline**: Direct navigation from alerts to purchase requisition with prefilled SKU parameters.

### 4. 🔔 Interactive Notification Center & Server-Side Pagination
* **Live Topbar Notification Drawer**: Real-time notifications for critical reorder alerts and high-volume dispatches.
* **Pagination & Multi-Filtering**: Server-side pagination with category dropdowns, SKU search, and CSV/Print export.

### 5. 🐳 Full Docker & Multi-Container Stack
* Complete `Dockerfile` and `docker-compose.yml` defining an isolated multi-container stack (PHP 8.2 Apache + MySQL 8.0 with automated health checks + phpMyAdmin).

---

## 🔑 Demo Role Credentials

| Role | Username | Password | Default Permissions |
| :--- | :--- | :--- | :--- |
| 🛡️ **Enterprise Admin** | `admin` | `password` | Complete access, User Management, Activity Logs |
| 📊 **Inventory Manager** | `manager` | `password` | Products, Stock In/Out, Partners, All Reports |
| 📦 **Warehouse Staff** | `staff` | `password` | Stock In (Receive), Stock Out (Dispatch), Low Stock View |

---

## 🚀 Quick Start & Installation

### Option A: Localhost with XAMPP (Instant)

1. **Clone or Copy Repository**:
   Place the project folder into your XAMPP web root:
   ```bash
   C:\xampp\htdocs\synapse_erp
   ```
2. **Start Apache & MySQL**:
   Open **XAMPP Control Panel** and start **Apache** and **MySQL**.
3. **Launch in Browser**:
   Navigate to:
   ```text
   http://localhost/synapse_erp/
   ```
   > **Note:** The built-in database connector automatically creates the database (`synapse_erp_db`) and bootstraps all tables on first launch.

---

### Option B: Docker Container Stack (1-Command Launch)

1. Ensure [Docker Desktop](https://www.docker.com/) is installed and running.
2. Open terminal in the project root and run:
   ```bash
   docker-compose up -d
   ```
3. Access services:
   * **Synapse-ERP Portal:** `http://localhost:8080`
   * **phpMyAdmin Console:** `http://localhost:8081` (User: `root`, Password: `secret`)

---

## 🧪 Automated Testing Suite

Synapse-ERP includes an automated CLI integration test suite verifying database integrity, credential authentication, RBAC authorization matrix, and inventory mathematical formulas:

```bash
php tests/run_tests.php
```

**Test Execution Output:**
```text
========================================================
  SYNAPSE-ERP AUTOMATED TEST SUITE
========================================================

▶ Running Suite 1: Database Connectivity & Tables...
  [PASS] Database PDO Connection is Active
  [PASS] Table 'users' exists in database
  [PASS] Table 'categories' exists in database
  [PASS] Table 'suppliers' exists in database
  [PASS] Table 'customers' exists in database
  [PASS] Table 'products' exists in database
  [PASS] Table 'stock_transactions' exists in database
  [PASS] Table 'activity_logs' exists in database

▶ Running Suite 2: Demo Role Credentials (Admin, Manager, Staff)...
  [PASS] User account 'admin' exists (Role: admin)
  [PASS] User 'admin' password 'password' verifies successfully
  [PASS] User account 'manager' exists (Role: manager)
  [PASS] User 'manager' password 'password' verifies successfully
  [PASS] User account 'staff' exists (Role: staff)
  [PASS] User 'staff' password 'password' verifies successfully

▶ Running Suite 3: RBAC Authorization Matrix...
  [PASS] Admin role: is_admin() is TRUE & can_manage_users() is TRUE
  [PASS] Manager role: is_manager() is TRUE & can_manage_users() is FALSE
  [PASS] Staff role: is_staff() is TRUE & can_delete() is FALSE

▶ Running Suite 4: Inventory Mathematical Logic...
  [PASS] Stock Equation (50 Opening + 20 IN - 15 OUT = 55)

========================================================
  TEST EXECUTION SUMMARY: 35/35 PASSED (100% PASS)
========================================================
```

---

## 📂 Project Structure

```text
synapse_erp/
├── assets/
│   ├── css/
│   │   └── style.css            # Corporate Slate & Blue Design System
│   ├── img/
│   │   ├── logo.svg             # Isometric Vector Brand Logo
│   │   └── favicon.svg          # High-Res SVG Favicon
│   └── js/
│       └── app.js               # Notification Drawer & Live Table Filters
├── auth/
│   ├── login.php                # Split-Panel Auth with 1-Click Role Switcher
│   └── logout.php               # Secure Session Teardown
├── categories/                  # Category Classification CRUD
├── config/
│   ├── database.php             # Smart Multi-DB Auto-Create Connector
│   ├── helpers.php              # Dynamic base_url, RBAC & Flash Notifications
│   └── pagination.php           # Server-Side Pagination Helper
├── customers/                   # Corporate Clients & Departments CRUD
├── database/
│   ├── inventory_db.sql         # Enterprise SQL Schema & Demo Seed
│   └── migrate.php              # Auto-Bootstrap & Schema Migration Engine
├── includes/
│   ├── header.php               # HTML Head, Favicon & Chart.js
│   ├── sidebar.php              # Role-Adapted Navigation & Notification Drawer
│   └── footer.php               # Scripts & Document Closure
├── products/                    # Products Master with Pagination & Filters
├── reports/
│   ├── current_stock.php        # Real-Time Valuation & Quantity Balances
│   ├── date_wise.php            # Transaction Ledger & Audit Trail
│   └── low_stock.php            # Reorder Deficit Monitor & 1-Click Restock
├── stock/
│   ├── stock_in.php             # Procurement Receiving & Supplier Inward
│   └── stock_out.php            # Sales Dispatch & Departmental Outward
├── suppliers/                   # Vendor & Supplier Directory CRUD
├── tests/
│   └── run_tests.php            # Automated Integration Test Suite
├── users/                       # Admin User Management & RBAC Controls
├── dashboard.php                # Corporate Executive Dashboard & Analytics
├── Dockerfile                   # PHP 8.2 Apache Container
├── docker-compose.yml           # Multi-Container Compose Configuration
├── LICENSE                      # MIT License
└── README.md                    # System Documentation
```

---

## 👤 Author & Credits

* **Developer:** [Rokibul Islam Robi](https://github.com/Rokibul-Islam-Robi)
* **GitHub Repository:** [`https://github.com/Rokibul-Islam-Robi/Synapse-ERP-System`](https://github.com/Rokibul-Islam-Robi/Synapse-ERP-System)

---

## 📄 License

This project is licensed under the **MIT License** - see the [LICENSE](LICENSE) file for details.
