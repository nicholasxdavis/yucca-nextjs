<?php
/**
 * Reusable Authentication Handler
 * Include this file in any page that needs login/registration capability
 * Usage: require_once 'auth_handler.php'; or require_once '../../auth_handler.php';
 */

// Ensure config is loaded
if (!function_exists('db_connect')) {
    die('Configuration not loaded. Please require config.php before auth_handler.php');
}

// Check if user is already logged in
$is_logged_in = is_logged_in();

// --- Handle Logout ---
if (isset($_GET['logout']) || (isset($_POST['logout']) && $_POST['logout'] == 'true')) {
    // Clear remember me cookie
    if (isset($_COOKIE['remember_token'])) {
        setcookie('remember_token', '', time() - 3600, '/', '', true, true);
        
        if (is_logged_in()) {
            try {
                $conn = db_connect();
                $user_id = $_SESSION['user_id'];
                $stmt = $conn->prepare("UPDATE users SET remember_token = NULL WHERE id = ?");
                $stmt->bind_param("i", $user_id);
                $stmt->execute();
                $stmt->close();
                $conn->close();
            } catch (Exception $e) {
                error_log("Logout token clear error: " . $e->getMessage());
            }
        }
    }
    
    session_destroy();
    // Redirect to current page without logout parameter
    $redirect_url = strtok($_SERVER["REQUEST_URI"], '?');
    header('Location: ' . $redirect_url);
    exit;
}

// --- Handle Login and Registration Forms ---
if ($_SERVER['REQUEST_METHOD'] == 'POST' && !$is_logged_in) {
    $error = '';
    
    if (isset($_POST['action'])) {
        $action = $_POST['action'];
        $email = isset($_POST['email']) ? trim($_POST['email']) : '';
        $password = isset($_POST['password']) ? $_POST['password'] : '';

        if (empty($email) || empty($password)) {
            $error = "Email and password are required.";
        } elseif ($action == 'register' && isset($_POST['password_confirm'])) {
            // --- Registration Logic ---
            $password_confirm = isset($_POST['password_confirm']) ? $_POST['password_confirm'] : '';
            
            if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
                $error = "Invalid email address.";
            } elseif ($password !== $password_confirm) {
                $error = "Passwords do not match.";
            } elseif (strlen($password) < 8) {
                $error = "Password must be at least 8 characters long.";
            } else {
                try {
                    $conn = db_connect();
                    $hashed_password = password_hash($password, PASSWORD_DEFAULT);
                    $role = 'user';
                    $stmt = $conn->prepare("INSERT INTO users (email, password, role) VALUES (?, ?, ?)");
                    $stmt->bind_param("sss", $email, $hashed_password, $role);
                    
                    if ($stmt->execute()) {
                        // Regenerate session ID for security
                        session_regenerate_id(true);
                        
                        // Set session variables
                        $_SESSION['user_id'] = $stmt->insert_id;
                        $_SESSION['user_email'] = $email;
                        $_SESSION['user_role'] = $role;
                        $_SESSION['logged_in'] = true;
                        $_SESSION['login_time'] = time();
                        $_SESSION['last_activity'] = time();
                        
                        $stmt->close();
                        $conn->close();
                        
                        // Force session write before redirect
                        session_write_close();
                        
                        // Redirect to same page (remove any query params)
                        $redirect_url = strtok($_SERVER["REQUEST_URI"], '?');
                        header('Location: ' . $redirect_url);
                        exit;
                    } else {
                        if ($conn->errno == 1062) {
                            $error = "The email is already registered. Try logging in.";
                        } else {
                            $error = "Registration failed. Please try again.";
                        }
                    }
                    
                    if (isset($stmt)) $stmt->close();
                    if (isset($conn)) $conn->close();
                } catch (Exception $e) {
                    $error = "Registration error. Please try again later.";
                    error_log("Registration error: " . $e->getMessage());
                }
            }

        } elseif ($action == 'login') {
            // --- Login Logic ---
            try {
                $conn = db_connect();
                
                // Check if role column exists
                $result_check = $conn->query("SHOW COLUMNS FROM users LIKE 'role'");
                $has_role = $result_check->num_rows > 0;
                
                if ($has_role) {
                    $stmt = $conn->prepare("SELECT id, email, password, role FROM users WHERE email = ?");
                } else {
                    $stmt = $conn->prepare("SELECT id, email, password FROM users WHERE email = ?");
                }
                
                $stmt->bind_param("s", $email);
                $stmt->execute();
                $result = $stmt->get_result();

                if ($result->num_rows === 1) {
                    $user = $result->fetch_assoc();
                    if (password_verify($password, $user['password'])) {
                        // Regenerate session ID for security (prevent session fixation)
                        session_regenerate_id(true);
                        
                        // Set session variables
                        $_SESSION['user_id'] = $user['id'];
                        $_SESSION['user_email'] = $user['email'];
                        $_SESSION['user_role'] = isset($user['role']) ? $user['role'] : 'user';
                        $_SESSION['logged_in'] = true;
                        $_SESSION['login_time'] = time();
                        $_SESSION['last_activity'] = time();
                        
                        // Handle "Remember Me" - set cookie for 30 days
                        if (isset($_POST['remember_me']) && $_POST['remember_me'] == '1') {
                            $remember_token = bin2hex(random_bytes(32));
                            
                            $update_stmt = $conn->prepare("UPDATE users SET remember_token = ? WHERE id = ?");
                            $update_stmt->bind_param("si", $remember_token, $user['id']);
                            $update_stmt->execute();
                            $update_stmt->close();
                            
                            // Determine if HTTPS
                            $is_https = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') 
                                        || (!empty($_SERVER['HTTP_X_FORWARDED_PROTO']) && $_SERVER['HTTP_X_FORWARDED_PROTO'] === 'https');
                            
                            setcookie('remember_token', $remember_token, time() + (30 * 24 * 60 * 60), '/', '', $is_https, true);
                        }
                        
                        $stmt->close();
                        $conn->close();
                        
                        // Force session write before redirect
                        session_write_close();
                        
                        // Redirect based on role or to current page
                        if ($_SESSION['user_role'] === 'admin') {
                            // Get relative path to admin.php
                            $admin_path = str_repeat('../', substr_count($_SERVER['SCRIPT_NAME'], '/') - 1) . 'admin.php';
                            header('Location: ' . $admin_path);
                            exit;
                        } else {
                            // Redirect to same page (remove any query params)
                            $redirect_url = strtok($_SERVER["REQUEST_URI"], '?');
                            header('Location: ' . $redirect_url);
                            exit;
                        }
                    } else {
                        $error = "Invalid email or password.";
                    }
                } else {
                    $error = "Invalid email or password.";
                }
                
                if (isset($stmt)) $stmt->close();
                if (isset($conn)) $conn->close();
            } catch (Exception $e) {
                $error = "Login error. Please try again later.";
                error_log("Login error: " . $e->getMessage());
            }
        }
    }
}

// Set error variable for display
if (!isset($error)) {
    $error = '';
}

