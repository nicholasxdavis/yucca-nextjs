<?php
/**
 * Database Initialization Script
 * Creates all necessary tables for Yucca Club
 * Run this once to set up the database structure
 * 
 * SECURITY: This file is protected by .htaccess and should only be run via CLI
 * To run: php init.php
 */

// Prevent direct web access
if (php_sapi_name() !== 'cli' && !defined('INIT_ALLOWED')) {
    http_response_code(403);
    die('Access Denied. This script can only be run from command line.');
}

require_once 'config.php';

echo "═══════════════════════════════════════════════════════\n";
echo "  Yucca Club - Database Initialization\n";
echo "  Environment: " . APP_ENV . "\n";
echo "═══════════════════════════════════════════════════════\n\n";

if (APP_ENV === 'production') {
    echo "⚠️  WARNING: Running in PRODUCTION environment!\n";
    echo "This will create/modify database tables.\n";
    echo "Type 'yes' to continue: ";
    
    $handle = fopen ("php://stdin","r");
    $line = trim(fgets($handle));
    
    if($line !== 'yes'){
        echo "Aborted.\n";
        exit;
    }
    echo "\n";
}

// Connect to database (without selecting a specific database first to create it if needed)
$conn = new mysqli(DB_SERVER, DB_USERNAME, DB_PASSWORD);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Create database if it doesn't exist
$sql = "CREATE DATABASE IF NOT EXISTS `" . DB_NAME . "` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;";
if ($conn->query($sql) === TRUE) {
    echo "Database '" . DB_NAME . "' created or already exists.\n";
} else {
    echo "Error creating database: " . $conn->error . "\n";
}

// Select the database
$conn->select_db(DB_NAME);
$conn->set_charset('utf8mb4');

// Create users table with proper indexes for session management
$sql = "CREATE TABLE IF NOT EXISTS `users` (
    `id` INT(11) NOT NULL AUTO_INCREMENT,
    `email` VARCHAR(255) NOT NULL UNIQUE,
    `password` VARCHAR(255) NOT NULL,
    `role` ENUM('user', 'editor', 'admin') DEFAULT 'user',
    `remember_token` VARCHAR(255) DEFAULT NULL,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    UNIQUE KEY `unique_email` (`email`),
    INDEX `idx_email` (`email`),
    INDEX `idx_role` (`role`),
    INDEX `idx_remember_token` (`remember_token`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='User accounts with authentication';";

if ($conn->query($sql) === TRUE) {
    echo "Table 'users' created or already exists.\n";
} else {
    echo "Error creating table: " . $conn->error . "\n";
}

// Create stories table
$sql = "CREATE TABLE IF NOT EXISTS `stories` (
    `id` INT(11) NOT NULL AUTO_INCREMENT,
    `title` VARCHAR(255) NOT NULL,
    `slug` VARCHAR(255) NOT NULL UNIQUE,
    `excerpt` TEXT,
    `content` LONGTEXT,
    `featured_image` VARCHAR(255),
    `category` VARCHAR(100),
    `status` ENUM('draft', 'published', 'archived') DEFAULT 'draft',
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    INDEX `idx_slug` (`slug`),
    INDEX `idx_status` (`status`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;";

if ($conn->query($sql) === TRUE) {
    echo "Table 'stories' created or already exists.\n";
} else {
    echo "Error creating table: " . $conn->error . "\n";
}

// Create guides table
$sql = "CREATE TABLE IF NOT EXISTS `guides` (
    `id` INT(11) NOT NULL AUTO_INCREMENT,
    `title` VARCHAR(255) NOT NULL,
    `slug` VARCHAR(255) NOT NULL UNIQUE,
    `excerpt` TEXT,
    `content` LONGTEXT,
    `featured_image` VARCHAR(255),
    `category` VARCHAR(100),
    `status` ENUM('draft', 'published', 'archived') DEFAULT 'draft',
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    INDEX `idx_slug` (`slug`),
    INDEX `idx_status` (`status`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;";

if ($conn->query($sql) === TRUE) {
    echo "Table 'guides' created or already exists.\n";
} else {
    echo "Error creating table: " . $conn->error . "\n";
}

// Create events table
$sql = "CREATE TABLE IF NOT EXISTS `events` (
    `id` INT(11) NOT NULL AUTO_INCREMENT,
    `title` VARCHAR(255) NOT NULL,
    `slug` VARCHAR(255) NOT NULL UNIQUE,
    `excerpt` TEXT,
    `content` LONGTEXT,
    `featured_image` VARCHAR(255),
    `event_date` DATETIME,
    `location` VARCHAR(255),
    `status` ENUM('upcoming', 'active', 'past', 'cancelled') DEFAULT 'upcoming',
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    INDEX `idx_slug` (`slug`),
    INDEX `idx_event_date` (`event_date`),
    INDEX `idx_status` (`status`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;";

if ($conn->query($sql) === TRUE) {
    echo "Table 'events' created or already exists.\n";
} else {
    echo "Error creating table: " . $conn->error . "\n";
}

// Create user_posts table for community posts
$sql = "CREATE TABLE IF NOT EXISTS `user_posts` (
    `id` INT(11) NOT NULL AUTO_INCREMENT,
    `user_id` INT(11) NOT NULL,
    `title` VARCHAR(255) NOT NULL,
    `slug` VARCHAR(255) NOT NULL,
    `content` LONGTEXT,
    `category` VARCHAR(100),
    `featured_image` VARCHAR(255),
    `status` ENUM('pending', 'approved', 'rejected', 'published') DEFAULT 'pending',
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE CASCADE,
    INDEX `idx_user_id` (`user_id`),
    INDEX `idx_status` (`status`),
    INDEX `idx_slug` (`slug`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;";

if ($conn->query($sql) === TRUE) {
    echo "Table 'user_posts' created or already exists.\n";
} else {
    echo "Error creating table: " . $conn->error . "\n";
}

// Create post_usage table to track user post limits (5 posts per month)
$sql = "CREATE TABLE IF NOT EXISTS `post_usage` (
    `id` INT(11) NOT NULL AUTO_INCREMENT,
    `user_id` INT(11) NOT NULL,
    `month` VARCHAR(7) NOT NULL COMMENT 'Format: YYYY-MM',
    `post_count` INT(11) DEFAULT 0,
    `reset_date` TIMESTAMP,
    PRIMARY KEY (`id`),
    FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE CASCADE,
    UNIQUE KEY `user_month` (`user_id`, `month`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;";

if ($conn->query($sql) === TRUE) {
    echo "Table 'post_usage' created or already exists.\n";
} else {
    echo "Error creating table: " . $conn->error . "\n";
}

// Create password_resets table
$sql = "CREATE TABLE IF NOT EXISTS `password_resets` (
    `id` INT(11) NOT NULL AUTO_INCREMENT,
    `user_id` INT(11) NOT NULL,
    `token` VARCHAR(255) NOT NULL UNIQUE,
    `expires_at` TIMESTAMP NOT NULL,
    `used` BOOLEAN DEFAULT FALSE,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE CASCADE,
    INDEX `idx_token` (`token`),
    INDEX `idx_expires_at` (`expires_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;";

if ($conn->query($sql) === TRUE) {
    echo "Table 'password_resets' created or already exists.\n";
} else {
    echo "Error creating table: " . $conn->error . "\n";
}

// Create contacts table
$sql = "CREATE TABLE IF NOT EXISTS `contacts` (
    `id` INT(11) NOT NULL AUTO_INCREMENT,
    `name` VARCHAR(255) NOT NULL,
    `email` VARCHAR(255) NOT NULL,
    `message` TEXT NOT NULL,
    `status` ENUM('new', 'read', 'replied', 'archived') DEFAULT 'new',
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    INDEX `idx_status` (`status`),
    INDEX `idx_created_at` (`created_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;";

if ($conn->query($sql) === TRUE) {
    echo "Table 'contacts' created or already exists.\n";
} else {
    echo "Error creating table: " . $conn->error . "\n";
}

// Check if role column exists in users table, add if missing
$result = $conn->query("SHOW COLUMNS FROM users LIKE 'role'");
if ($result->num_rows === 0) {
    echo "Adding 'role' column to users table...\n";
    $sql = "ALTER TABLE users ADD COLUMN role ENUM('user', 'editor', 'admin') DEFAULT 'user' AFTER password";
    if ($conn->query($sql)) {
        echo "✓ Role column added.\n";
        
        // Add index
        $sql_index = "ALTER TABLE users ADD INDEX idx_role (role)";
        if ($conn->query($sql_index)) {
            echo "✓ Role index added.\n";
        }
    } else {
        echo "Error adding role column: " . $conn->error . "\n";
    }
} else {
    echo "✓ Role column already exists.\n";
}

// Check if remember_token column exists in users table, add if missing
$result = $conn->query("SHOW COLUMNS FROM users LIKE 'remember_token'");
if ($result->num_rows === 0) {
    echo "Adding 'remember_token' column to users table...\n";
    $sql = "ALTER TABLE users ADD COLUMN remember_token VARCHAR(255) DEFAULT NULL";
    if ($conn->query($sql)) {
        echo "✓ Remember token column added.\n";
    } else {
        echo "Error adding remember_token column: " . $conn->error . "\n";
    }
} else {
    echo "✓ Remember token column already exists.\n";
}

// Check if admin user exists
if (ADMIN_EMAIL) {
    $sql = "SELECT id, role FROM users WHERE email = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", ADMIN_EMAIL);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows === 0) {
        echo "\n⚠️  Admin email configured (" . ADMIN_EMAIL . ") but user not found.\n";
        echo "   Please register this email on the website to create admin account.\n";
    } else {
        $user = $result->fetch_assoc();
        if (empty($user['role']) || $user['role'] !== 'admin') {
            // Update user role to admin
            $admin_role = 'admin';
            $update_sql = "UPDATE users SET role = ? WHERE email = ?";
            $update_stmt = $conn->prepare($update_sql);
            $update_stmt->bind_param("ss", $admin_role, ADMIN_EMAIL);
            if ($update_stmt->execute()) {
                echo "\n✓ Set " . ADMIN_EMAIL . " as admin role.\n";
            }
            $update_stmt->close();
        } else {
            echo "\n✓ Admin user (" . ADMIN_EMAIL . ") found with role: " . $user['role'] . "\n";
        }
    }
    $stmt->close();
} else {
    echo "\n⚠️  No ADMIN_EMAIL configured in environment variables.\n";
}

// Verify users table structure (especially for session management)
echo "\n--- Verifying Users Table Structure ---\n";
$result = $conn->query("DESCRIBE users");
if ($result) {
    $columns = [];
    while ($row = $result->fetch_assoc()) {
        $columns[] = $row['Field'];
    }
    
    // Check for required columns
    $required_columns = ['id', 'email', 'password', 'role', 'remember_token', 'created_at', 'updated_at'];
    $missing_columns = array_diff($required_columns, $columns);
    
    if (empty($missing_columns)) {
        echo "✓ All required columns exist in users table\n";
    } else {
        echo "⚠️  Missing columns in users table: " . implode(', ', $missing_columns) . "\n";
        echo "   Run 'php migrate_add_role_column.php' if needed\n";
    }
    
    // Verify indexes
    $indexes_result = $conn->query("SHOW INDEX FROM users");
    if ($indexes_result) {
        echo "✓ Indexes verified\n";
    }
} else {
    echo "⚠️  Could not verify users table structure\n";
}

echo "\n--- Session Configuration Check ---\n";
echo "Session Lifetime: " . SESSION_LIFETIME . " seconds (" . (SESSION_LIFETIME / 3600) . " hours)\n";
echo "Session Save Path: " . (ini_get('session.save_path') ?: 'default') . "\n";
echo "Session Cookie Params:\n";
echo "  - Lifetime: " . SESSION_LIFETIME . " seconds\n";
echo "  - Path: /\n";
echo "  - HttpOnly: Yes\n";
echo "  - SameSite: Lax\n";
echo "✓ Sessions configured for 24-hour persistence\n";

$conn->close();

echo "\n═══════════════════════════════════════════════════════\n";
echo "  ✅ Database initialization complete!\n";
echo "═══════════════════════════════════════════════════════\n\n";
echo "Next steps:\n";
echo "1. Register your admin account at your domain/index.php\n";
echo "2. Verify database connection via /health.php endpoint\n";
echo "3. Test login on one page, navigate to another (should stay logged in)\n";
echo "4. Check browser cookies - should see PHPSESSID with 24-hour expiry\n";
echo "5. Check application logs for any errors\n\n";

