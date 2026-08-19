<?php
// Automatic Migration, Bootstrap & Seeding Script for Synapse-ERP

function run_migrations($pdo) {
    static $migrated = false;
    if ($migrated) return;

    try {
        // 1. Create users table if not exists
        $pdo->exec("CREATE TABLE IF NOT EXISTS users (
            id INT AUTO_INCREMENT PRIMARY KEY,
            name VARCHAR(100) NOT NULL,
            username VARCHAR(50) NOT NULL UNIQUE,
            email VARCHAR(100) NULL,
            phone VARCHAR(20) NULL,
            password VARCHAR(255) NOT NULL,
            role ENUM('admin','manager','staff') DEFAULT 'admin',
            status TINYINT(1) DEFAULT 1,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        ) ENGINE=InnoDB;");

        // Ensure role column enum includes 'manager' and 'staff'
        try {
            $pdo->exec("ALTER TABLE users MODIFY COLUMN role ENUM('admin','manager','staff') DEFAULT 'admin'");
        } catch (Exception $e) {}

        // Seed or update all 3 demo role accounts: admin, manager, staff (Password: password)
        $passwordHash = password_hash('password', PASSWORD_BCRYPT);

        $demoUsers = [
            ['name' => 'Enterprise Admin', 'username' => 'admin', 'email' => 'admin@company.com', 'phone' => '+880 1700-000001', 'role' => 'admin'],
            ['name' => 'Inventory Manager', 'username' => 'manager', 'email' => 'manager@company.com', 'phone' => '+880 1700-000002', 'role' => 'manager'],
            ['name' => 'Warehouse Staff', 'username' => 'staff', 'email' => 'staff@company.com', 'phone' => '+880 1700-000003', 'role' => 'staff']
        ];

        $stmtCheck = $pdo->prepare("SELECT id FROM users WHERE username = ?");
        $stmtInsert = $pdo->prepare("INSERT INTO users (name, username, email, phone, password, role, status) VALUES (?, ?, ?, ?, ?, ?, 1)");
        $stmtUpdate = $pdo->prepare("UPDATE users SET password = ?, role = ?, status = 1 WHERE username = ?");

        foreach ($demoUsers as $u) {
            $stmtCheck->execute([$u['username']]);
            $existing = $stmtCheck->fetch();
            if (!$existing) {
                $stmtInsert->execute([$u['name'], $u['username'], $u['email'], $u['phone'], $passwordHash, $u['role']]);
            } else {
                $stmtUpdate->execute([$passwordHash, $u['role'], $u['username']]);
            }
        }

        // 2. Create categories table if not exists
        $pdo->exec("CREATE TABLE IF NOT EXISTS categories (
            id INT AUTO_INCREMENT PRIMARY KEY,
            category_name VARCHAR(100) NOT NULL UNIQUE,
            description TEXT NULL,
            status TINYINT(1) DEFAULT 1,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        ) ENGINE=InnoDB;");

        // 3. Create suppliers table if not exists
        $pdo->exec("CREATE TABLE IF NOT EXISTS suppliers (
            id INT AUTO_INCREMENT PRIMARY KEY,
            name VARCHAR(100) NOT NULL,
            company_name VARCHAR(120) NULL,
            email VARCHAR(100) NULL,
            phone VARCHAR(30) NULL,
            address TEXT NULL,
            status TINYINT(1) DEFAULT 1,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        ) ENGINE=InnoDB;");

        // 4. Create customers table if not exists
        $pdo->exec("CREATE TABLE IF NOT EXISTS customers (
            id INT AUTO_INCREMENT PRIMARY KEY,
            name VARCHAR(100) NOT NULL,
            customer_type ENUM('individual', 'corporate', 'internal_dept') DEFAULT 'corporate',
            email VARCHAR(100) NULL,
            phone VARCHAR(30) NULL,
            address TEXT NULL,
            status TINYINT(1) DEFAULT 1,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        ) ENGINE=InnoDB;");

        // 5. Create products table if not exists
        $pdo->exec("CREATE TABLE IF NOT EXISTS products (
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
        ) ENGINE=InnoDB;");

        // 6. Create stock_transactions table if not exists
        $pdo->exec("CREATE TABLE IF NOT EXISTS stock_transactions (
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
        ) ENGINE=InnoDB;");

        // 7. Create activity_logs table if not exists
        $pdo->exec("CREATE TABLE IF NOT EXISTS activity_logs (
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
        ) ENGINE=InnoDB;");

        // Check & apply missing columns if upgrading older database
        $userCols = $pdo->query("SHOW COLUMNS FROM users")->fetchAll(PDO::FETCH_COLUMN);
        if (!in_array('email', $userCols)) {
            $pdo->exec("ALTER TABLE users ADD COLUMN email VARCHAR(100) NULL AFTER username");
        }
        if (!in_array('phone', $userCols)) {
            $pdo->exec("ALTER TABLE users ADD COLUMN phone VARCHAR(20) NULL AFTER email");
        }
        if (!in_array('status', $userCols)) {
            $pdo->exec("ALTER TABLE users ADD COLUMN status TINYINT(1) DEFAULT 1 AFTER role");
        }

        $prodCols = $pdo->query("SHOW COLUMNS FROM products")->fetchAll(PDO::FETCH_COLUMN);
        if (!in_array('buying_price', $prodCols)) {
            $pdo->exec("ALTER TABLE products ADD COLUMN buying_price DECIMAL(10,2) NOT NULL DEFAULT 0.00 AFTER opening_stock");
        }
        if (!in_array('selling_price', $prodCols)) {
            $pdo->exec("ALTER TABLE products ADD COLUMN selling_price DECIMAL(10,2) NOT NULL DEFAULT 0.00 AFTER buying_price");
        }
        if (!in_array('alert_quantity', $prodCols)) {
            $pdo->exec("ALTER TABLE products ADD COLUMN alert_quantity INT NOT NULL DEFAULT 5 AFTER selling_price");
        }
        if (!in_array('description', $prodCols)) {
            $pdo->exec("ALTER TABLE products ADD COLUMN description TEXT NULL AFTER alert_quantity");
        }

        $transCols = $pdo->query("SHOW COLUMNS FROM stock_transactions")->fetchAll(PDO::FETCH_COLUMN);
        if (!in_array('supplier_id', $transCols)) {
            $pdo->exec("ALTER TABLE stock_transactions ADD COLUMN supplier_id INT NULL AFTER transaction_type");
            $pdo->exec("ALTER TABLE stock_transactions ADD CONSTRAINT fk_stock_supplier FOREIGN KEY (supplier_id) REFERENCES suppliers(id) ON UPDATE CASCADE ON DELETE SET NULL");
        }
        if (!in_array('customer_id', $transCols)) {
            $pdo->exec("ALTER TABLE stock_transactions ADD COLUMN customer_id INT NULL AFTER supplier_id");
            $pdo->exec("ALTER TABLE stock_transactions ADD CONSTRAINT fk_stock_customer FOREIGN KEY (customer_id) REFERENCES customers(id) ON UPDATE CASCADE ON DELETE SET NULL");
        }
        if (!in_array('reference_no', $transCols)) {
            $pdo->exec("ALTER TABLE stock_transactions ADD COLUMN reference_no VARCHAR(100) NULL AFTER customer_id");
        }
        if (!in_array('unit_price', $transCols)) {
            $pdo->exec("ALTER TABLE stock_transactions ADD COLUMN unit_price DECIMAL(10,2) NOT NULL DEFAULT 0.00 AFTER quantity");
        }
        if (!in_array('total_price', $transCols)) {
            $pdo->exec("ALTER TABLE stock_transactions ADD COLUMN total_price DECIMAL(12,2) NOT NULL DEFAULT 0.00 AFTER unit_price");
        }

        $migrated = true;
    } catch (Exception $e) {
        error_log("Migration notice: " . $e->getMessage());
    }
}
