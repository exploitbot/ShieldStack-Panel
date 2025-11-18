-- ShieldStack Panel Database Enhancements
-- Date: 2025-10-28

USE shieldstack_panel;

-- 1. Create ticket_departments table
CREATE TABLE IF NOT EXISTS ticket_departments (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(255) NOT NULL,
    description TEXT,
    email VARCHAR(255),
    auto_response TEXT,
    is_default TINYINT(1) DEFAULT 0,
    status VARCHAR(50) DEFAULT "active",
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_status (status),
    INDEX idx_default (is_default)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 2. Add department_id column to tickets table (if not exists)
ALTER TABLE tickets 
ADD COLUMN IF NOT EXISTS department_id INT NULL AFTER customer_id,
ADD CONSTRAINT fk_ticket_department FOREIGN KEY (department_id) REFERENCES ticket_departments(id) ON DELETE SET NULL;

-- 3. Add hidden column to plans table (if not exists)
ALTER TABLE plans 
ADD COLUMN IF NOT EXISTS hidden TINYINT(1) DEFAULT 0 AFTER status,
ADD INDEX idx_hidden (hidden);

-- 4. Update tickets table to support new status values
ALTER TABLE tickets MODIFY status VARCHAR(50) DEFAULT "open";

-- 5. Add last_reply_by and last_reply_at columns to tickets
ALTER TABLE tickets
ADD COLUMN IF NOT EXISTS last_reply_by VARCHAR(50) NULL AFTER status,
ADD COLUMN IF NOT EXISTS last_reply_at TIMESTAMP NULL AFTER last_reply_by;

-- 6. Insert default ticket departments
INSERT INTO ticket_departments (name, description, email, auto_response, is_default) VALUES
("General Support", "General inquiries and support requests", "support@shieldstack.dev", "Thank you for contacting support. We have received your ticket and will respond within 24 hours.", 1),
("Technical Support", "Technical issues with services and hosting", "tech@shieldstack.dev", "Your technical support request has been received. Our team will investigate and respond shortly.", 0),
("Billing & Payments", "Billing questions, invoices, and payment issues", "billing@shieldstack.dev", "Your billing inquiry has been received. Our billing team will review and respond within 24 hours.", 0),
("Sales", "Pre-sales questions and new service inquiries", "sales@shieldstack.dev", "Thank you for your interest! A sales representative will contact you shortly.", 0),
("Security", "Security concerns and incident reports", "security@shieldstack.dev", "Your security report has been received. This is being treated with high priority.", 0)
ON DUPLICATE KEY UPDATE name=name;

-- 7. Update existing tickets to use default department
UPDATE tickets 
SET department_id = (SELECT id FROM ticket_departments WHERE is_default = 1 LIMIT 1)
WHERE department_id IS NULL;

-- 8. Add remember_token column to customers if not exists
ALTER TABLE customers
ADD COLUMN IF NOT EXISTS remember_token VARCHAR(100) NULL AFTER password,
ADD INDEX idx_remember_token (remember_token);

-- Verification queries
SELECT "=== TICKET DEPARTMENTS ===" as Info;
SELECT * FROM ticket_departments;

SELECT "=== PLANS WITH HIDDEN COLUMN ===" as Info;
SELECT id, name, type, hidden, status FROM plans LIMIT 5;

SELECT "=== TICKETS TABLE STRUCTURE ===" as Info;
DESCRIBE tickets;

SELECT "Database enhancements completed successfully!" as Status;
