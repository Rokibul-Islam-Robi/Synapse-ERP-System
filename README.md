# Synapse-ERP System

<p align="center">
  <img src="assets/img/favicon.svg" alt="Synapse-ERP Logo" width="120">
</p>

<p align="center">
  <strong>Next-Generation Corporate Inventory &amp; Supply Intelligence</strong><br>
  <em>Precision Multi-Tier Stock Control, Real-Time Valuation &amp; Audit Governance</em>
</p>

<p align="center">
  <a href="https://synapse-erp-system.vercel.app"><img src="https://img.shields.io/badge/Live%20Demo-Vercel%20Production-00f59b?style=for-the-badge&logo=vercel&logoColor=black" alt="Live Demo on Vercel"></a>
  <img src="https://img.shields.io/badge/PHP-8.2%2B-777BB4?style=for-the-badge&logo=php&logoColor=white" alt="PHP 8.2+">
  <img src="https://img.shields.io/badge/Database-TiDB%20%7C%20MySQL%208.0-4479A1?style=for-the-badge&logo=mysql&logoColor=white" alt="MySQL 8.0+ / TiDB Cloud">
  <img src="https://img.shields.io/badge/Theme-White%20%26%20Neon%20Green-10b981?style=for-the-badge" alt="Neon Green Theme">
  <img src="https://img.shields.io/badge/Docker-Ready-2496ED?style=for-the-badge&logo=docker&logoColor=white" alt="Docker Ready">
  <img src="https://img.shields.io/badge/License-MIT-blue?style=for-the-badge" alt="MIT License">
</p>

---

## 🌐 Live Production Deployment

Access the live cloud deployment of Synapse-ERP instantly:
👉 **[https://synapse-erp-system.vercel.app](https://synapse-erp-system.vercel.app)**

---

## 📌 Executive Overview

**Synapse-ERP** is an enterprise-grade inventory governance, supply chain management, and financial stock valuation platform designed for commercial operations, warehouses, distribution networks, and corporate enterprises.

Engineered with a **White &amp; Neon-Green organic design system**, **stateless signed cookie authentication**, **sub-second database connection pooling**, and **multi-tier RBAC authorization**, Synapse-ERP guarantees zero latency, complete audit compliance, and seamless cloud serverless scalability.

---

## 🌟 Key Capabilities & Architecture

### 1. 🛡️ White Canvas &amp; Neon-Green Modern Landing / Auth Portal
* **Modern Organic Composition**: Crisp pure white canvas (`#ffffff`) featuring top-left organic neon green ribbon, top navigation bar, and fluid botanical illustration with smooth 3D floating keyframe animations.
* **1-Click Demo Role Chips**: Instant pre-filled credentials for `Admin`, `Manager`, and `Staff` roles.
* **Stateless Signed Cookie Authentication (`HMAC-SHA256`)**: Bulletproof session persistence designed specifically for serverless lambda environments (Vercel, AWS Lambda, Cloudflare) with zero `ERR_TOO_MANY_REDIRECTS`.
* **Multi-Tier Role-Based Access Control (RBAC)**:
  * 👨‍💼 **Admin**: Full authority, user account creation, system configuration, activity logging, and permission governance.
  * 📋 **Manager**: Product &amp; category catalogs, stock transactions, vendor &amp; client directories, valuation reports, and restock alerts.
  * 📦 **Staff**: Operational receiving (Stock In) and order dispatch (Stock Out) with strict view-only permissions for sensitive logs.

### 2. 📈 Inventory Valuation &amp; Stock Math Balances
* **Live Financial Asset Tracking**: Real-time inventory asset valuation based on buying rates:
  $$\text{Total Asset Value} = \sum (\text{Current Stock}_i \times \text{Buying Price}_i)$$
* **Mathematical Consistency Formula**:
  $$\text{Current Stock} = \text{Opening Stock} + \sum(\text{Stock In}) - \sum(\text{Stock Out})$$
* **Negative Stock Prevention**: Database-level constraints and backend logic strictly prohibit dispatching units beyond available physical balance.

### 3. 🚨 Real-Time Reorder &amp; Deficit Alert Engine
* **Threshold Monitors**: Real-time identification of SKUs where $\text{Current Stock} \le \text{Alert Quantity}$.
* **Automated Deficit Calculation**:
  $$\text{Deficit} = \max(0, \text{Alert Quantity} \times 2 - \text{Current Stock})$$
* **1-Click Restock Pipeline**: Direct navigation from alerts to purchase requisition with prefilled product SKU parameters.

### 4. 📊 Interactive Analytics &amp; Notification Center
* **Chart.js Visualization**: Real-time 6-month stock flow bar charts (Inflow vs. Outflow) and category distribution doughnut charts.
* **Live Topbar Notification Drawer**: Instant badge alerts for low-stock warnings and high-volume operations.
* **Universal CSV Export &amp; Live Search**: Instant client-side and server-side filtering with one-click CSV report downloads.

---

## 🔑 Demo Role Credentials

| Role | Username / Email | Password | Access Scope |
| :--- | :--- | :--- | :--- |
| 🛡️ **Enterprise Admin** | `admin` / `admin@company.com` | `password` | Complete access, User Management, Activity Logs |
| 📊 **Inventory Manager** | `manager` / `manager@company.com` | `password` | Products, Stock In/Out, Partners, All Reports |
| 📦 **Warehouse Staff** | `staff` / `staff@company.com` | `password` | Stock In (Receive), Stock Out (Dispatch), Low Stock Alerts |

---

## 🚀 Deployment & Installation Guide

### Option 1: Deploy to Vercel + TiDB Cloud (Production Serverless)

1. **Set Up Cloud Database**:
   - Create a free MySQL database on [TiDB Cloud](https://tidbcloud.com/) or [Aiven](https://aiven.io/).
   - Copy connection parameters (Host, Port `4000`, User, Password, Database `test`).
2. **Deploy on Vercel**:
   - Import your GitHub repository into [Vercel](https://vercel.com).
   - In Vercel Project Settings > **Environment Variables**, add:
     - `DB_HOST`: `gateway01.ap-southeast-1.prod.aws.tidbcloud.com`
     - `DB_PORT`: `4000`
     - `DB_USER`: `your_tidb_username`
     - `DB_PASS`: `your_tidb_password`
     - `DB_NAME`: `test`
3. Click **Deploy**. Vercel will automatically build the serverless functions via `api/index.php`.

---

### Option 2: Localhost with XAMPP (Instant)

1. Place the project folder into your XAMPP web root:
   ```bash
   C:\xampp\htdocs\Synapse-ERP
   ```
2. Start **Apache** and **MySQL** in XAMPP Control Panel.
3. Open your browser and navigate to:
   ```text
   http://localhost/Synapse-ERP/
   ```
   > **Note:** The auto-migration engine will automatically create the database tables and seed demo users on initial launch.

---

### Option 3: Multi-Container Docker Stack

1. Ensure [Docker Desktop](https://www.docker.com/) is installed and running.
2. In the project root, run:
   ```bash
   docker-compose up -d
   ```
3. Access services:
   * **Synapse-ERP Application:** `http://localhost:8080`
   * **phpMyAdmin Database Console:** `http://localhost:8081` (User: `root`, Password: `secret`)

---

## 🧪 Automated Testing Suite

Synapse-ERP includes an automated CLI integration test suite verifying database integrity, credential authentication, RBAC authorization matrix, and inventory mathematical formulas:

```bash
php tests/run_tests.php
```

---

## 📂 Project Structure

```text
Synapse-ERP/
├── api/
│   └── index.php                # Vercel Serverless Front-Controller Router
├── assets/
│   ├── css/
│   │   └── style.css            # Modern White, Neon Green & Obsidian Design System
│   ├── img/
│   │   ├── favicon.svg          # Isometric Neon Green Geometric Synapse Favicon
│   │   └── logo.svg             # Vector Enterprise Logo
│   └── js/
│       └── app.js               # Notification Drawer & Live Table Search
├── auth/
│   ├── login.php                # White & Neon Green Landing with 1-Click Role Switcher
│   └── logout.php               # Secure Session & Cookie Teardown
├── categories/                  # Product Taxonomy Classification CRUD
├── config/
│   ├── cacert.pem               # Bundled Mozilla CA Certificate for Cloud SSL/TLS
│   ├── database.php             # Persistent PDO Connector & Lazy Auto-Migration Engine
│   ├── helpers.php              # Signed Auth Cookie, Dynamic base_url & RBAC Helpers
│   └── pagination.php           # Server-Side Pagination Utility
├── customers/                   # Corporate Clients & Internal Departments Directory
├── database/
│   ├── inventory_db.sql         # Enterprise SQL Schema & Seeds
│   └── migrate.php              # Auto-Bootstrap & Schema Migration Engine
├── includes/
│   ├── header.php               # HTML Head, Inlined CSS Fallbacks & Chart.js
│   ├── sidebar.php              # Dark Obsidian Sidebar with Neon Active States
│   └── footer.php               # JavaScript Utilities & Document Closure
├── products/                    # Products Master Catalog with Filters & Pagination
├── reports/
│   ├── current_stock.php        # Real-Time Stock Valuation & Asset Balances
│   ├── date_wise.php            # Transaction Ledger & Audit Trails
│   └── low_stock.php            # Reorder Deficit Monitor & 1-Click Restock
├── stock/
│   ├── stock_in.php             # Supplier Inward & Procurement Receiving
│   └── stock_out.php            # Sales Dispatch & Departmental Outward
├── suppliers/                   # Supplier & Vendor Directory CRUD
├── tests/
│   └── run_tests.php            # Automated CLI Integration Test Suite
├── users/                       # Administrator User Management & RBAC Governance
├── dashboard.php                # Corporate Executive Dashboard & Analytics
├── vercel.json                  # Vercel Serverless Function & Rewrite Configuration
├── Dockerfile                   # PHP 8.2 Apache Container Definition
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

