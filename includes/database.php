<?php
// Database configuration and initialization
require_once __DIR__ . '/config.php';

class Database {
    private static $instance = null;
    private $db;

    private function __construct() {
        try {
            $dsn = "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=" . DB_CHARSET;
            $this->db = new PDO($dsn, DB_USER, DB_PASS, [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES => false
            ]);
            $this->initDatabase();
        } catch (PDOException $e) {
            die('Database connection failed: ' . $e->getMessage());
        }
    }

    public static function getInstance() {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    public function getConnection() {
        return $this->db;
    }

    private function initDatabase() {
        // Create tables if they don't exist (MySQL syntax)
        $this->db->exec("
            CREATE TABLE IF NOT EXISTS customers (
                id INT AUTO_INCREMENT PRIMARY KEY,
                email VARCHAR(255) UNIQUE NOT NULL,
                password VARCHAR(255) NOT NULL,
                full_name VARCHAR(255) NOT NULL,
                company VARCHAR(255),
                phone VARCHAR(50),
                address TEXT,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                status VARCHAR(50) DEFAULT 'active',
                is_admin TINYINT(1) DEFAULT 0,
                INDEX idx_email (email),
                INDEX idx_status (status)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
        ");

        $this->db->exec("
            CREATE TABLE IF NOT EXISTS plans (
                id INT AUTO_INCREMENT PRIMARY KEY,
                name VARCHAR(255) NOT NULL,
                type VARCHAR(100) NOT NULL,
                description TEXT,
                price DECIMAL(10,2) NOT NULL,
                billing_cycle VARCHAR(50) NOT NULL,
                features TEXT,
                status VARCHAR(50) DEFAULT 'active',
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                INDEX idx_type (type),
                INDEX idx_status (status)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
        ");

        $this->db->exec("
            CREATE TABLE IF NOT EXISTS services (
                id INT AUTO_INCREMENT PRIMARY KEY,
                customer_id INT NOT NULL,
                plan_id INT NOT NULL,
                domain VARCHAR(255),
                status VARCHAR(50) DEFAULT 'pending',
                start_date TIMESTAMP NULL,
                renewal_date TIMESTAMP NULL,
                notes TEXT,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                FOREIGN KEY (customer_id) REFERENCES customers(id) ON DELETE CASCADE,
                FOREIGN KEY (plan_id) REFERENCES plans(id) ON DELETE RESTRICT,
                INDEX idx_customer (customer_id),
                INDEX idx_status (status)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
        ");

        $this->db->exec("
            CREATE TABLE IF NOT EXISTS tickets (
                id INT AUTO_INCREMENT PRIMARY KEY,
                customer_id INT NOT NULL,
                subject VARCHAR(255) NOT NULL,
                department VARCHAR(100) NOT NULL,
                priority VARCHAR(50) DEFAULT 'medium',
                status VARCHAR(50) DEFAULT 'open',
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                FOREIGN KEY (customer_id) REFERENCES customers(id) ON DELETE CASCADE,
                INDEX idx_customer (customer_id),
                INDEX idx_status (status),
                INDEX idx_priority (priority)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
        ");

        $this->db->exec("
            CREATE TABLE IF NOT EXISTS ticket_replies (
                id INT AUTO_INCREMENT PRIMARY KEY,
                ticket_id INT NOT NULL,
                customer_id INT,
                admin_id INT,
                message TEXT NOT NULL,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                FOREIGN KEY (ticket_id) REFERENCES tickets(id) ON DELETE CASCADE,
                FOREIGN KEY (customer_id) REFERENCES customers(id) ON DELETE SET NULL,
                FOREIGN KEY (admin_id) REFERENCES customers(id) ON DELETE SET NULL,
                INDEX idx_ticket (ticket_id)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
        ");

        $this->db->exec("
            CREATE TABLE IF NOT EXISTS invoices (
                id INT AUTO_INCREMENT PRIMARY KEY,
                customer_id INT NOT NULL,
                service_id INT,
                invoice_number VARCHAR(100) UNIQUE NOT NULL,
                amount DECIMAL(10,2) NOT NULL,
                status VARCHAR(50) DEFAULT 'unpaid',
                due_date TIMESTAMP NOT NULL,
                paid_date TIMESTAMP NULL,
                description TEXT,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                FOREIGN KEY (customer_id) REFERENCES customers(id) ON DELETE CASCADE,
                FOREIGN KEY (service_id) REFERENCES services(id) ON DELETE SET NULL,
                INDEX idx_customer (customer_id),
                INDEX idx_status (status),
                INDEX idx_invoice_number (invoice_number)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
        ");

        // Create default admin account if not exists
        $stmt = $this->db->prepare("SELECT COUNT(*) as count FROM customers WHERE is_admin = 1");
        $stmt->execute();
        $result = $stmt->fetch();

        if ($result['count'] == 0) {
            // Default admin: eric@shieldstack.dev / jinho2310
            $hashedPassword = password_hash('jinho2310', PASSWORD_BCRYPT);
            $stmt = $this->db->prepare("
                INSERT INTO customers (email, password, full_name, is_admin, status)
                VALUES (?, ?, ?, 1, 'active')
            ");
            $stmt->execute(['eric@shieldstack.dev', $hashedPassword, 'Administrator']);
        }

        // Insert sample plans if table is empty
        $stmt = $this->db->prepare("SELECT COUNT(*) as count FROM plans");
        $stmt->execute();
        $result = $stmt->fetch();

        if ($result['count'] == 0) {
            $this->insertSamplePlans();
        }
    }

    private function insertSamplePlans() {
        $plans = [
            [
                'name' => 'Starter VPS',
                'type' => 'hosting',
                'description' => '1 vCPU, 1GB RAM, 25GB SSD, 1TB Bandwidth',
                'price' => 9.99,
                'billing_cycle' => 'monthly',
                'features' => json_encode(['1 vCPU', '1GB RAM', '25GB SSD Storage', '1TB Bandwidth', 'DDoS Protection', '99.9% Uptime SLA'])
            ],
            [
                'name' => 'Business VPS',
                'type' => 'hosting',
                'description' => '2 vCPU, 4GB RAM, 80GB SSD, 3TB Bandwidth',
                'price' => 24.99,
                'billing_cycle' => 'monthly',
                'features' => json_encode(['2 vCPU', '4GB RAM', '80GB SSD Storage', '3TB Bandwidth', 'DDoS Protection', '99.9% Uptime SLA', 'Daily Backups'])
            ],
            [
                'name' => 'Enterprise VPS',
                'type' => 'hosting',
                'description' => '4 vCPU, 8GB RAM, 160GB SSD, 5TB Bandwidth',
                'price' => 49.99,
                'billing_cycle' => 'monthly',
                'features' => json_encode(['4 vCPU', '8GB RAM', '160GB SSD Storage', '5TB Bandwidth', 'DDoS Protection', '99.99% Uptime SLA', 'Daily Backups', 'Priority Support'])
            ],
            [
                'name' => 'Shared Hosting',
                'type' => 'hosting',
                'description' => '10GB Storage, Unlimited Bandwidth, 5 Websites',
                'price' => 5.99,
                'billing_cycle' => 'monthly',
                'features' => json_encode(['10GB Storage', 'Unlimited Bandwidth', '5 Websites', 'Free SSL', 'cPanel Access', 'Email Accounts'])
            ],
            [
                'name' => 'Domain Registration',
                'type' => 'domain',
                'description' => 'Register your domain with free privacy protection',
                'price' => 12.99,
                'billing_cycle' => 'yearly',
                'features' => json_encode(['Free WHOIS Privacy', 'DNS Management', 'Easy Transfer', 'Auto-Renewal'])
            ],
            [
                'name' => 'SSL Certificate',
                'type' => 'security',
                'description' => 'Premium SSL certificate with warranty',
                'price' => 29.99,
                'billing_cycle' => 'yearly',
                'features' => json_encode(['256-bit Encryption', '$10,000 Warranty', 'Trust Seal', 'Unlimited Servers'])
            ]
        ];

        $stmt = $this->db->prepare("
            INSERT INTO plans (name, type, description, price, billing_cycle, features)
            VALUES (?, ?, ?, ?, ?, ?)
        ");

        foreach ($plans as $plan) {
            $stmt->execute([
                $plan['name'],
                $plan['type'],
                $plan['description'],
                $plan['price'],
                $plan['billing_cycle'],
                $plan['features']
            ]);
        }
    }
}
?>
