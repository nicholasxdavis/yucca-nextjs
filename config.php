<?php
// File: config.php

// Application Configuration
define('APP_ENV', getenv('APP_ENV') ?: 'production');
define('APP_DEBUG', filter_var(getenv('APP_DEBUG'), FILTER_VALIDATE_BOOLEAN));
define('APP_NAME', getenv('APP_NAME') ?: 'Yucca Club');
define('APP_URL', getenv('APP_URL') ?: 'http://localhost');

// Database credentials - MariaDB/MySQL
// Coolify environment variables
define('DB_SERVER', getenv('DB_HOST') ?: 'localhost');
define('DB_PORT', getenv('DB_PORT') ?: 3306);
define('DB_USERNAME', getenv('DB_USERNAME') ?: 'root');
define('DB_PASSWORD', getenv('DB_PASSWORD') ?: '');
define('DB_NAME', getenv('DB_DATABASE') ?: 'yucca_club');

// Session Configuration
define('SESSION_LIFETIME', getenv('SESSION_LIFETIME') ?: 86400);
define('SESSION_DRIVER', getenv('SESSION_DRIVER') ?: 'file');

// GitHub configuration for image uploads
define('GITHUB_TOKEN', getenv('GITHUB_TOKEN') ?: '');
define('GITHUB_OWNER', getenv('GITHUB_OWNER') ?: 'nicholasxdavis');
define('GITHUB_REPO', getenv('GITHUB_REPO') ?: 'yucca-club');
define('GITHUB_FOLDER', getenv('GITHUB_FOLDER') ?: 'saved-imgs');

// Admin Configuration
define('ADMIN_EMAIL', getenv('ADMIN_EMAIL') ?: '');

// Ticketmaster API
define('TICKETMASTER_API_KEY', getenv('TICKETMASTER_API_KEY') ?: '');

// Logging
define('LOG_LEVEL', getenv('LOG_LEVEL') ?: 'error');

// Error Reporting - Disable in production
if (APP_ENV === 'production' && !APP_DEBUG) {
    error_reporting(0);
    ini_set('display_errors', '0');
    ini_set('display_startup_errors', '0');
} else {
    error_reporting(E_ALL);
    ini_set('display_errors', '1');
    ini_set('display_startup_errors', '1');
}

// Configure session settings for 24-hour persistence
ini_set('session.cookie_httponly', '1');
ini_set('session.gc_maxlifetime', SESSION_LIFETIME); // 24 hours server-side
ini_set('session.cookie_lifetime', SESSION_LIFETIME); // 24 hours cookie lifetime

// Determine if we're on HTTPS (works in dev and production)
$is_https = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') 
            || (!empty($_SERVER['HTTP_X_FORWARDED_PROTO']) && $_SERVER['HTTP_X_FORWARDED_PROTO'] === 'https')
            || (!empty($_SERVER['HTTP_X_FORWARDED_SSL']) && $_SERVER['HTTP_X_FORWARDED_SSL'] === 'on')
            || (isset($_SERVER['SERVER_PORT']) && $_SERVER['SERVER_PORT'] == 443);

// Set secure cookie only on HTTPS
if ($is_https) {
    ini_set('session.cookie_secure', '1');
}
ini_set('session.cookie_samesite', 'Lax'); // Changed from Strict to Lax for better navigation

// Start session with 24-hour lifetime
if (session_status() === PHP_SESSION_NONE) {
    session_set_cookie_params([
        'lifetime' => SESSION_LIFETIME,
        'path' => '/',
        'domain' => '',
        'secure' => $is_https,
        'httponly' => true,
        'samesite' => 'Lax'
    ]);
    session_start();
    
    // Regenerate session ID less frequently for better persistence (every 4 hours)
    if (!isset($_SESSION['last_regeneration'])) {
        $_SESSION['last_regeneration'] = time();
    } elseif (time() - $_SESSION['last_regeneration'] > 14400) { // 4 hours instead of 30 minutes
        session_regenerate_id(true);
        $_SESSION['last_regeneration'] = time();
    }
    
    // Update last activity time on each request and extend session if needed
    if (isset($_SESSION['user_id'])) {
        $_SESSION['last_activity'] = time();
        
        // Extend session cookie lifetime if user is active
        if (isset($_COOKIE[session_name()])) {
            setcookie(session_name(), session_id(), time() + SESSION_LIFETIME, '/', '', $is_https, true);
        }
    }
}

// Function to establish database connection
function db_connect() {
    // Always create a new connection - don't reuse static connections
    $conn = null;
    $error = '';
    $attempts = [
        DB_SERVER,
        DB_SERVER . '.internal',
        explode('.', DB_SERVER)[0]
    ];
    
    foreach ($attempts as $hostname) {
        $conn = @new mysqli($hostname, DB_USERNAME, DB_PASSWORD, DB_NAME, DB_PORT);
        if (!$conn->connect_error) {
            $conn->set_charset('utf8mb4');
            return $conn;
        }
        $error = $conn->connect_error;
    }
    
    // If all attempts failed, log error
    error_log("Database connection failed. Last error: $error");
    
    // Show appropriate error message based on environment
    if (APP_ENV === 'production' && !APP_DEBUG) {
        die("<h1>Service Unavailable</h1><p>We're experiencing technical difficulties. Please try again later.</p>");
    } else {
        die("<h1>Database Connection Failed</h1>
            <p>Unable to connect to database server.</p>
            <p><strong>Attempted hostnames:</strong></p>
            <ul>" . 
            implode('', array_map(function($h) { return "<li>" . htmlspecialchars($h) . "</li>"; }, $attempts)) . 
            "</ul>
            <p><strong>Last error:</strong> " . htmlspecialchars($error) . "</p>");
    }
}

// Function to check if user is logged in
function is_logged_in() {
    // Check if user is logged in via session
    if (isset($_SESSION['user_id'])) {
        // Check if session is still valid (within 24 hours)
        if (isset($_SESSION['last_activity']) && (time() - $_SESSION['last_activity']) < SESSION_LIFETIME) {
            return true;
        } else {
            // Session expired, clear it
            unset($_SESSION['user_id'], $_SESSION['user_email'], $_SESSION['user_role'], $_SESSION['logged_in'], $_SESSION['login_time'], $_SESSION['last_activity']);
        }
    }
    
    // Check for remember token if session expired
    if (isset($_COOKIE['remember_token']) && !empty($_COOKIE['remember_token'])) {
        try {
            $conn = db_connect();
            $stmt = $conn->prepare("SELECT id, email, role FROM users WHERE remember_token = ?");
            $stmt->bind_param("s", $_COOKIE['remember_token']);
            $stmt->execute();
            $result = $stmt->get_result();
            
            if ($result->num_rows === 1) {
                $user = $result->fetch_assoc();
                
                // Regenerate session ID for security
                session_regenerate_id(true);
                
                // Restore session
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['user_email'] = $user['email'];
                $_SESSION['user_role'] = $user['role'] ?? 'user';
                $_SESSION['logged_in'] = true;
                $_SESSION['login_time'] = time();
                $_SESSION['last_activity'] = time();
                
                $stmt->close();
                $conn->close();
                
                return true;
            }
            
            $stmt->close();
            $conn->close();
        } catch (Exception $e) {
            error_log("Remember token check error: " . $e->getMessage());
        }
    }
    
    return false;
}

// Function to check if user is admin
function is_admin() {
    // Check if user has admin role in database OR is the configured admin email
    if (isset($_SESSION['user_role']) && $_SESSION['user_role'] === 'admin') {
        return true;
    }
    // Also check for configured admin email (backup)
    if (ADMIN_EMAIL && isset($_SESSION['user_email']) && $_SESSION['user_email'] === ADMIN_EMAIL) {
        return true;
    }
    return false;
}

// Function to check if user is editor
function is_editor() {
    if (!isset($_SESSION['user_id'])) return false;
    return isset($_SESSION['user_role']) && in_array($_SESSION['user_role'], ['editor', 'admin']);
}

// Function to check if user has specific role
function has_role($role) {
    if (!isset($_SESSION['user_id'])) return false;
    if ($role === 'admin') {
        return is_admin();
    }
    if ($role === 'editor') return is_editor();
    return isset($_SESSION['user_role']) && $_SESSION['user_role'] === $role;
}

// Function to check if user is pro member
function is_pro() {
    if (!isset($_SESSION['user_id'])) return false;
    return isset($_SESSION['user_role']) && $_SESSION['user_role'] === 'pro';
}

// Function to require authentication
function require_auth() {
    if (!is_logged_in()) {
        header('Location: ../index.php');
        exit;
    }
}

// Function to require admin
function require_admin() {
    if (!is_admin()) {
        header('Location: ../index.php');
        exit;
    }
}

// Function to require editor or admin
function require_editor() {
    if (!is_editor() && !is_admin()) {
        header('Location: ../index.php');
        exit;
    }
}

// Global variable for storing errors
$error = '';
?>