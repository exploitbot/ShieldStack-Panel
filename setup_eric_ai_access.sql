--
-- ShieldStack Panel - Set up AI Enterprise Access for eric@shieldstack.dev
-- Run this after importing ai_editor_migration.sql
--

-- Grant Enterprise AI Plan to eric@shieldstack.dev (unlimited tokens)
INSERT INTO ai_service_plans (customer_id, plan_type, token_limit, tokens_used, status, activated_at)
SELECT id, 'enterprise', -1, 0, 'active', NOW()
FROM customers
WHERE email = 'eric@shieldstack.dev'
ON DUPLICATE KEY UPDATE
    plan_type = 'enterprise',
    token_limit = -1,
    status = 'active',
    activated_at = NOW();

-- Note: SSH credentials need to be added manually via Admin Panel
-- Navigate to: Admin Panel > AI Website Editor > SSH Credentials
-- Add:
--   - SSH Host: your-server-host (e.g., shieldstack.dev)
--   - SSH Port: 22
--   - SSH Username: your-ssh-username
--   - SSH Password: your-ssh-password
--   - Web Root Path: /var/www/html (or your actual web root)
--   - Website URL: https://shieldstack.dev
--   - Website Type: php, wordpress, laravel, etc.

-- Verify the plan was created
SELECT c.email, asp.plan_type, asp.token_limit, asp.status
FROM customers c
JOIN ai_service_plans asp ON c.id = asp.customer_id
WHERE c.email = 'eric@shieldstack.dev';
