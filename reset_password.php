<?php
// Reset Password Page
require_once 'config.php';

$error = '';
$success = '';
$token = isset($_GET['token']) ? $_GET['token'] : '';
$token_valid = false;

// Check if token is provided and valid
if (!empty($token)) {
    try {
        $conn = db_connect();
        $stmt = $conn->prepare("SELECT pr.*, u.email FROM password_resets pr JOIN users u ON pr.user_id = u.id WHERE pr.token = ? AND pr.used = FALSE AND pr.expires_at > NOW()");
        $stmt->bind_param("s", $token);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows === 1) {
            $token_valid = true;
            $reset_data = $result->fetch_assoc();
        } else {
            $error = "Invalid or expired reset token. Please request a new password reset link.";
        }
        
        $stmt->close();
        $conn->close();
    } catch (Exception $e) {
        $error = "Database error. Please try again later.";
        error_log("Password reset error: " . $e->getMessage());
    }
} else {
    $error = "No reset token found. Please request a new password reset link.";
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] == 'POST' && $token_valid && isset($_POST['token'])) {
    $new_password = $_POST['new_password'] ?? '';
    $confirm_password = $_POST['confirm_password'] ?? '';
    $submitted_token = $_POST['token'] ?? '';
    
    if (empty($new_password) || empty($confirm_password)) {
        $error = "Please fill in all fields.";
    } elseif (strlen($new_password) < 8) {
        $error = "Password must be at least 8 characters long.";
    } elseif ($new_password !== $confirm_password) {
        $error = "Passwords do not match.";
    } elseif ($submitted_token !== $token) {
        $error = "Invalid token.";
    } else {
        try {
            $conn = db_connect();
            
            // Get user ID from token again for security
            $stmt = $conn->prepare("SELECT user_id FROM password_resets WHERE token = ? AND used = FALSE AND expires_at > NOW()");
            $stmt->bind_param("s", $submitted_token);
            $stmt->execute();
            $result = $stmt->get_result();
            
            if ($result->num_rows === 1) {
                $reset = $result->fetch_assoc();
                $user_id = $reset['user_id'];
                
                // Update password
                $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);
                $update_stmt = $conn->prepare("UPDATE users SET password = ? WHERE id = ?");
                $update_stmt->bind_param("si", $hashed_password, $user_id);
                
                if ($update_stmt->execute()) {
                    // Mark token as used
                    $mark_used = $conn->prepare("UPDATE password_resets SET used = TRUE WHERE token = ?");
                    $mark_used->bind_param("s", $submitted_token);
                    $mark_used->execute();
                    $mark_used->close();
                    
                    $success = "Password successfully reset! You can now log in with your new password.";
                    $token_valid = false; // Prevent further submissions
                } else {
                    $error = "Failed to update password. Please try again.";
                }
                
                $update_stmt->close();
            } else {
                $error = "Invalid or expired token.";
            }
            
            $stmt->close();
            $conn->close();
        } catch (Exception $e) {
            $error = "An error occurred. Please try again later.";
            error_log("Password update error: " . $e->getMessage());
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Yucca Club | Reset Password</title>
    <link rel="icon" type="image/png" href="ui/img/favicon.png">
    <meta name="description" content="Reset your password for your Yucca Club account.">
    <meta name="robots" content="noindex, nofollow">

    <!-- Fonts & Icons -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Lato:wght@400;700&family=Lora:wght@500;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    
    <!-- Styles -->
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="ui/css/styles.css">
</head>
<body class="form-page-body">
    <header class="site-header">
        <div class="container header-content">
            <a href="index.php" class="site-logo" aria-label="Yucca Club Homepage">
                <img class="logo-light" src="ui/img/logo.png" alt="Yucca Club Logo Light" style="width:180px; height:auto;">
                <img class="logo-dark" src="ui/img/logo_dark.png" alt="Yucca Club Logo Dark" style="width:180px; height:auto;">
            </a>
            <nav class="primary-nav" aria-label="Main Navigation">
                <ul>
                    <li><a href="nav/stories/index.php">Stories</a></li>
                    <li><a href="nav/guides/index.php">Guides</a></li>
                    <li><a href="nav/events/index.php">Events</a></li>
                    <li><a href="https://yucca.printify.me/" target="_blank" rel="noopener noreferrer">Shop</a></li>
                    <li><a href="nav/membership/index.php">Membership</a></li>
                </ul>
            </nav>
            <div class="header-actions">
                <button id="theme-toggle" aria-label="Toggle dark mode" class="desktop-only">
                    <i class="fas fa-moon" aria-hidden="true"></i>
                    <i class="fas fa-sun" aria-hidden="true"></i>
                </button>
                
                <!-- Mobile Menu -->
                <div class="mobile-menu">
                    <button id="mobile-menu-trigger" aria-label="Menu">
                        <i class="fas fa-ellipsis-h" aria-hidden="true"></i>
                    </button>
                    <div id="mobile-menu-dropdown" class="mobile-dropdown">
                        <button id="mobile-theme-toggle">
                            <i class="fas fa-moon"></i>
                            <span>Theme</span>
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </header>

    <main>
        <div class="content-container">
            <div class="form-card">
                <?php if ($success): ?>
                    <h2>Password Changed!</h2>
                    <p style="color: var(--cactus-green); font-weight: bold; margin-bottom: 1rem;"><?= htmlspecialchars($success) ?></p>
                    <p>You can now <a href="index.php" style="color: var(--yucca-yellow); font-weight: bold;">log in</a> with your new password.</p>
                <?php elseif ($error): ?>
                    <h2>Password Reset</h2>
                    <p style="color: #A81919; font-weight: bold; margin-bottom: 1rem;"><?= htmlspecialchars($error) ?></p>
                    <p><a href="index.php" style="color: var(--yucca-yellow);">Return to homepage</a> to request a new reset link.</p>
                <?php else: ?>
                    <h2>Set a New Password</h2>
                    <p>Enter and confirm your new password below.</p>
                    <form method="POST" action="reset_password.php?token=<?= htmlspecialchars($token) ?>">
                        <input type="hidden" name="token" value="<?= htmlspecialchars($token) ?>">
                        
                        <label for="new-password" class="visually-hidden">New Password</label>
                        <input type="password" id="new-password" name="new_password" class="form-input" placeholder="New Password" required autocomplete="new-password" minlength="8">
                        
                        <label for="confirm-password" class="visually-hidden">Confirm New Password</label>
                        <input type="password" id="confirm-password" name="confirm_password" class="form-input" placeholder="Confirm Password" required autocomplete="new-password" minlength="8">
                        
                        <button type="submit" class="cta-button">Reset Password</button>
                    </form>
                <?php endif; ?>
            </div>
        </div>
    </main>

    <footer class="site-footer">
        <div class="container">
            <div class="footer-content site-footer-main">
                <p>&copy; <?= date('Y') ?> Yucca Club. All Rights Reserved.</p>
                <nav class="footer-nav" aria-label="Footer Navigation">
                    <ul>
                        <li><a href="#" id="contact-trigger">Contact</a></li>
                        <li><a href="privacy_policy.php">Privacy Policy</a></li>
                    </ul>
                </nav>
            </div>
            <p class="sustainability-statement">
                Crafted with love in Las Cruces, New Mexico
            </p>
        </div>
    </footer>

    <script src="ui/js/main.js"></script>
</body>
</html>


