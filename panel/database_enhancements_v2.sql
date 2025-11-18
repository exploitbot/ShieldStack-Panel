-- ShieldStack Panel Database Enhancements V2
USE shieldstack_panel;

-- 1. Create ticket_departments table
CREATE TABLE IF NOT EXISTS ticket_departments (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(255) NOT NULL,
    description TEXT,
    email VARCHAR(255),
    auto_response TEXT,
    is_default TINYINT(1) DEFAULT 0,
    status VARCHAR(50) DEFAULT 'active',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_status (status),
    INDEX idx_default (is_default)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 2. Insert default ticket departments
INSERT IGNORE INTO ticket_departments (id, name, description, email, auto_response, is_default) VALUES
(1, 'General Support', 'General inquiries and support requests', 'support@shieldstack.dev', 'Thank you for contacting support. We have received your ticket and will respond within 24 hours.', 1),
(2, 'Technical Support', 'Technical issues with services and hosting', 'tech@shieldstack.dev', 'Your technical support request has been received. Our team will investigate and respond shortly.', 0),
(3, 'Billing & Payments', 'Billing questions, invoices, and payment issues', 'billing@shieldstack.dev', 'Your billing inquiry has been received. Our billing team will review and respond within 24 hours.', 0),
(4, 'Sales', 'Pre-sales questions and new service inquiries', 'sales@shieldstack.dev', 'Thank you for your interest! A sales representative will contact you shortly.', 0),
(5, 'Security', 'Security concerns and incident reports', 'security@shieldstack.dev', 'Your security report has been received. This is being treated with high priority.', 0);

SELECT "Departments created" as Status;
