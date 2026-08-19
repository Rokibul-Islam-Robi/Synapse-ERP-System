-- Enterprise Mini Inventory Management System Database Schema
-- Version 2.0 (Corporate Edition)

CREATE DATABASE IF NOT EXISTS mini_inventory_db
CHARACTER SET utf8mb4
COLLATE utf8mb4_unicode_ci;

USE mini_inventory_db;

-- 1. Users Table
CREATE TABLE IF NOT EXISTS users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    username VARCHAR(50) NOT NULL UNIQUE,
    email VARCHAR(100) NULL,
    phone VARCHAR(20) NULL,
    password VARCHAR(255) NOT NULL,
    role ENUM('admin','manager','staff') DEFAULT 'admin',
    status TINYINT(1) DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- 2. Categories Table
CREATE TABLE IF NOT EXISTS categories (
    id INT AUTO_INCREMENT PRIMARY KEY,
    category_name VARCHAR(100) NOT NULL UNIQUE,
    description TEXT NULL,
    status TINYINT(1) DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- 3. Suppliers (Vendors) Table
CREATE TABLE IF NOT EXISTS suppliers (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    company_name VARCHAR(120) NULL,
    email VARCHAR(100) NULL,
    phone VARCHAR(30) NULL,
    address TEXT NULL,
    status TINYINT(1) DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- 4. Customers / Recipients Table
CREATE TABLE IF NOT EXISTS customers (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    customer_type ENUM('individual', 'corporate', 'internal_dept') DEFAULT 'corporate',
    email VARCHAR(100) NULL,
    phone VARCHAR(30) NULL,
    address TEXT NULL,
    status TINYINT(1) DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- 5. Products Table
CREATE TABLE IF NOT EXISTS products (
    id INT AUTO_INCREMENT PRIMARY KEY,
    category_id INT NOT NULL,
    product_name VARCHAR(150) NOT NULL,
    sku VARCHAR(80) NOT NULL UNIQUE,
    unit VARCHAR(30) NOT NULL DEFAULT 'pcs',
    opening_stock INT NOT NULL DEFAULT 0,
    buying_price DECIMAL(10,2) NOT NULL DEFAULT 0.00,
    selling_price DECIMAL(10,2) NOT NULL DEFAULT 0.00,
    alert_quantity INT NOT NULL DEFAULT 5,
    description TEXT NULL,
    status TINYINT(1) DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_products_category
        FOREIGN KEY (category_id) REFERENCES categories(id)
        ON UPDATE CASCADE
        ON DELETE RESTRICT
) ENGINE=InnoDB;

-- 6. Stock Transactions Table
CREATE TABLE IF NOT EXISTS stock_transactions (
    id INT AUTO_INCREMENT PRIMARY KEY,
    product_id INT NOT NULL,
    transaction_type ENUM('IN','OUT') NOT NULL,
    supplier_id INT NULL,
    customer_id INT NULL,
    reference_no VARCHAR(100) NULL,
    quantity INT NOT NULL,
    unit_price DECIMAL(10,2) NOT NULL DEFAULT 0.00,
    total_price DECIMAL(12,2) NOT NULL DEFAULT 0.00,
    transaction_date DATE NOT NULL,
    remarks VARCHAR(255) NULL,
    created_by INT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_stock_product
        FOREIGN KEY (product_id) REFERENCES products(id)
        ON UPDATE CASCADE
        ON DELETE RESTRICT,
    CONSTRAINT fk_stock_supplier
        FOREIGN KEY (supplier_id) REFERENCES suppliers(id)
        ON UPDATE CASCADE
        ON DELETE SET NULL,
    CONSTRAINT fk_stock_customer
        FOREIGN KEY (customer_id) REFERENCES customers(id)
        ON UPDATE CASCADE
        ON DELETE SET NULL,
    CONSTRAINT fk_stock_user
        FOREIGN KEY (created_by) REFERENCES users(id)
        ON UPDATE CASCADE
        ON DELETE SET NULL,
    CONSTRAINT chk_quantity_positive CHECK (quantity > 0)
) ENGINE=InnoDB;

-- 7. Activity Logs Table
CREATE TABLE IF NOT EXISTS activity_logs (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NULL,
    action VARCHAR(100) NOT NULL,
    details TEXT NULL,
    ip_address VARCHAR(50) NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_logs_user
        FOREIGN KEY (user_id) REFERENCES users(id)
        ON UPDATE CASCADE
        ON DELETE SET NULL
) ENGINE=InnoDB;

-- Default Admin User (username: admin, password: password)
INSERT INTO users (name, username, email, phone, password, role, status)
VALUES (
    'Enterprise Admin',
    'admin',
    'admin@company.com',
    '+880 1700-000000',
    '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2uheWG/igi.',
    'admin',
    1
)
ON DUPLICATE KEY UPDATE name = VALUES(name);

-- Sample Categories (Optional Seed)
INSERT INTO categories (id, category_name, description, status)
VALUES 
(1, 'Electronics & IT', 'Laptops, Monitors, Keyboards, IT Accessories', 1),
(2, 'Office Supplies', 'Paper, Files, Pens, Stationery', 1),
(3, 'Furniture & Fixtures', 'Chairs, Desks, Storage Cabinets', 1)
ON DUPLICATE KEY UPDATE category_name = VALUES(category_name);

-- Sample Suppliers (Optional Seed)
INSERT INTO suppliers (id, name, company_name, email, phone, address, status)
VALUES
(1, 'Tanvir Ahmed', 'Apex Tech Solutions Ltd.', 'contact@apextech.com', '+880 1812-345678', 'Gulshan-2, Dhaka', 1),
(2, 'Nazmul Huda', 'Star Stationeries & Paper', 'sales@starstationery.com', '+880 1912-987654', 'Motijheel, Dhaka', 1)
ON DUPLICATE KEY UPDATE name = VALUES(name);

-- Sample Customers (Optional Seed)
INSERT INTO customers (id, name, customer_type, email, phone, address, status)
VALUES
(1, 'Accounts & Finance Dept', 'internal_dept', 'accounts@company.com', '+880 1711-111111', 'Floor 4, Head Office', 1),
(2, 'Creative Design Agency', 'corporate', 'procurement@creativedesign.com', '+880 1611-222222', 'Banani, Dhaka', 1)
ON DUPLICATE KEY UPDATE name = VALUES(name);
