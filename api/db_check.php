<?php
/**
 * Database Check Script
 * Checks if required tables exist
 */

require_once 'config.php';

header('Content-Type: application/json');

try {
    $conn = db_connect();
    
    // Check if stories table exists
    $result = $conn->query("SHOW TABLES LIKE 'stories'");
    $stories_exists = $result->num_rows > 0;
    
    // Check if guides table exists
    $result = $conn->query("SHOW TABLES LIKE 'guides'");
    $guides_exists = $result->num_rows > 0;
    
    // Check if events table exists
    $result = $conn->query("SHOW TABLES LIKE 'events'");
    $events_exists = $result->num_rows > 0;
    
    // Check if users table exists
    $result = $conn->query("SHOW TABLES LIKE 'users'");
    $users_exists = $result->num_rows > 0;
    
    // Check if user_posts table exists
    $result = $conn->query("SHOW TABLES LIKE 'user_posts'");
    $user_posts_exists = $result->num_rows > 0;
    
    $conn->close();
    
    echo json_encode([
        'success' => true,
        'tables' => [
            'stories' => $stories_exists,
            'guides' => $guides_exists,
            'events' => $events_exists,
            'users' => $users_exists,
            'user_posts' => $user_posts_exists
        ]
    ]);
    
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'error' => $e->getMessage()
    ]);
}
?>
