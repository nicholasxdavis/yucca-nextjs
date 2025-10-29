<?php
/**
 * Health Check Endpoint
 * Used by monitoring services and load balancers to check application health
 */

header('Content-Type: application/json');
require_once 'config.php';

$health = [
    'status' => 'healthy',
    'timestamp' => time(),
    'checks' => []
];

// Check database connection
try {
    $conn = db_connect();
    
    // Test a simple query
    $result = $conn->query("SELECT 1");
    
    if ($result) {
        $health['checks']['database'] = 'ok';
    } else {
        $health['checks']['database'] = 'error';
        $health['status'] = 'unhealthy';
    }
    
    $conn->close();
} catch (Exception $e) {
    $health['checks']['database'] = 'error';
    $health['status'] = 'unhealthy';
    error_log("Health check database error: " . $e->getMessage());
}

// Check if session is working
if (session_status() === PHP_SESSION_ACTIVE) {
    $health['checks']['sessions'] = 'ok';
} else {
    $health['checks']['sessions'] = 'warning';
}

// Check if critical environment variables are set
$required_env = ['DB_HOST', 'DB_USERNAME', 'DB_DATABASE'];
$env_status = true;

foreach ($required_env as $var) {
    if (empty(getenv($var))) {
        $env_status = false;
        break;
    }
}

$health['checks']['environment'] = $env_status ? 'ok' : 'warning';

// Check if critical directories are writable (if needed)
// Uncomment if your app needs to write files
// $health['checks']['filesystem'] = is_writable(__DIR__) ? 'ok' : 'warning';

// Set appropriate HTTP status code
http_response_code($health['status'] === 'healthy' ? 200 : 503);

// Return JSON response
echo json_encode($health, JSON_PRETTY_PRINT);
exit;


