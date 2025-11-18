<?php
/**
 * AI Website Editor Configuration - PRODUCTION
 * Server: shieldstack.dev
 *
 * IMPORTANT: Add your OpenAI API key below!
 */

return [
    // OpenAI-Compatible API Configuration
    // For shieldstack.dev production: using Claude via custom proxy
    'openai' => [
        'endpoint' => 'https://clove.shieldstack.dev/v1/chat/completions',  // Custom endpoint
        'api_key' => 'eric',  // API key for custom endpoint
        'model' => 'claude-sonnet-4-5-20250929',  // Claude model via proxy
        // For standard OpenAI use: 'gpt-4' or 'gpt-3.5-turbo'
    ],

    // Plan Configurations
    'plans' => [
        'basic' => [
            'tokens' => 10000,
            'price' => 29.99,
            'name' => 'AI Basic',
            'features' => [
                '10,000 AI Tokens/month',
                'Basic website modifications',
                'Automatic backups',
                'Email support'
            ]
        ],
        'pro' => [
            'tokens' => 50000,
            'price' => 79.99,
            'name' => 'AI Pro',
            'features' => [
                '50,000 AI Tokens/month',
                'Advanced website modifications',
                'Priority AI processing',
                'Automatic backups',
                'Priority support'
            ]
        ],
        'enterprise' => [
            'tokens' => -1,  // -1 = unlimited
            'price' => 199.99,
            'name' => 'AI Enterprise',
            'features' => [
                'Unlimited AI Tokens',
                'Full website control',
                'Real-time processing',
                'Automatic backups',
                '24/7 Premium support',
                'Dedicated account manager'
            ]
        ]
    ],

    // Safety Configuration
    'safety' => [
        'allowed_commands' => [
            'ls', 'cat', 'grep', 'find', 'head', 'tail', 'wc', 'pwd',
            'whoami', 'stat', 'file', 'du', 'df', 'test', 'echo',
            'cp', 'mkdir', 'touch'
        ],
        'blocked_commands' => [
            'rm', 'rmdir', 'dd', 'mkfs', 'fdisk', 'parted',
            'chmod 777', 'chown', 'kill', 'killall', 'pkill',
            'shutdown', 'reboot', 'init', 'systemctl',
            'service', 'iptables', 'ufw', 'firewall-cmd',
            'useradd', 'usermod', 'userdel', 'passwd',
            'su', 'sudo', 'visudo',
            'mysql', 'psql', 'mongo', 'redis-cli',
            'DROP', 'DELETE', 'TRUNCATE', 'ALTER TABLE',
            'wget', 'curl', 'nc', 'netcat', 'telnet',
            ':(){:|:&};:',
            'eval', 'exec', 'system'
        ],
        'max_file_size_mb' => 5,
        'backup_retention_days' => 30,
    ],

    // Rate Limiting (Production settings)
    'rate_limiting' => [
        'max_requests_per_minute' => 10,
        'max_requests_per_hour' => 100,
        'max_tokens_per_day' => 50000,
    ],

    // Features
    'features' => [
        'enable_backups' => true,
        'enable_command_execution' => true,
        'enable_file_deletion' => false,  // Recommended: false for production
        'require_confirmation_for_critical_files' => true,
    ],

    // Notification Settings
    'notifications' => [
        'notify_admin_on_errors' => true,
        'notify_customer_on_low_tokens' => true,
        'low_token_threshold_percent' => 10,
    ]
];
?>
