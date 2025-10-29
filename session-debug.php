<?php
// Session Debug Page - For testing session persistence
require_once 'config.php';

// Only allow this in development/debug mode
if (APP_ENV === 'production' && !APP_DEBUG) {
    http_response_code(404);
    die('Not Found');
}

$is_logged_in = is_logged_in();
$user_email = $is_logged_in ? htmlspecialchars($_SESSION['user_email']) : '';

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Session Debug - Yucca Club</title>
    <link rel="icon" type="image/png" href="ui/img/favicon.png">
    <style>
        body {
            font-family: Arial, sans-serif;
            max-width: 800px;
            margin: 2rem auto;
            padding: 2rem;
            background: #f5f5f5;
        }
        .debug-container {
            background: white;
            padding: 2rem;
            border-radius: 8px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.1);
        }
        .status {
            padding: 1rem;
            border-radius: 6px;
            margin-bottom: 1rem;
        }
        .status.logged-in {
            background: #d4edda;
            border: 1px solid #c3e6cb;
            color: #155724;
        }
        .status.logged-out {
            background: #f8d7da;
            border: 1px solid #f5c6cb;
            color: #721c24;
        }
        .info-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 1rem;
            margin: 1rem 0;
        }
        .info-item {
            background: #f8f9fa;
            padding: 1rem;
            border-radius: 6px;
            border: 1px solid #dee2e6;
        }
        .info-label {
            font-weight: bold;
            color: #495057;
            margin-bottom: 0.5rem;
        }
        .info-value {
            color: #6c757d;
            word-break: break-all;
        }
        .actions {
            margin-top: 2rem;
            display: flex;
            gap: 1rem;
        }
        .btn {
            padding: 0.75rem 1.5rem;
            border: none;
            border-radius: 6px;
            cursor: pointer;
            text-decoration: none;
            display: inline-block;
            font-weight: 600;
        }
        .btn-primary {
            background: #007bff;
            color: white;
        }
        .btn-danger {
            background: #dc3545;
            color: white;
        }
        .btn-secondary {
            background: #6c757d;
            color: white;
        }
    </style>
</head>
<body>
    <div class="debug-container">
        <h1>🔍 Session Debug Information</h1>
        
        <div class="status <?= $is_logged_in ? 'logged-in' : 'logged-out' ?>">
            <strong><?= $is_logged_in ? '✅ Logged In' : '❌ Not Logged In' ?></strong>
            <?php if ($is_logged_in): ?>
                <br>User: <?= $user_email ?>
            <?php endif; ?>
        </div>

        <div class="info-grid">
            <div class="info-item">
                <div class="info-label">Session ID</div>
                <div class="info-value"><?= session_id() ?></div>
            </div>
            
            <div class="info-item">
                <div class="info-label">Session Name</div>
                <div class="info-value"><?= session_name() ?></div>
            </div>
            
            <div class="info-item">
                <div class="info-label">Session Lifetime</div>
                <div class="info-value"><?= SESSION_LIFETIME ?> seconds (<?= SESSION_LIFETIME / 3600 ?> hours)</div>
            </div>
            
            <div class="info-item">
                <div class="info-label">Current Time</div>
                <div class="info-value"><?= date('Y-m-d H:i:s') ?></div>
            </div>
            
            <?php if ($is_logged_in): ?>
            <div class="info-item">
                <div class="info-label">Login Time</div>
                <div class="info-value"><?= isset($_SESSION['login_time']) ? date('Y-m-d H:i:s', $_SESSION['login_time']) : 'Not set' ?></div>
            </div>
            
            <div class="info-item">
                <div class="info-label">Last Activity</div>
                <div class="info-value"><?= isset($_SESSION['last_activity']) ? date('Y-m-d H:i:s', $_SESSION['last_activity']) : 'Not set' ?></div>
            </div>
            
            <div class="info-item">
                <div class="info-label">Time Since Login</div>
                <div class="info-value"><?= isset($_SESSION['login_time']) ? gmdate('H:i:s', time() - $_SESSION['login_time']) : 'Unknown' ?></div>
            </div>
            
            <div class="info-item">
                <div class="info-label">Time Since Activity</div>
                <div class="info-value"><?= isset($_SESSION['last_activity']) ? gmdate('H:i:s', time() - $_SESSION['last_activity']) : 'Unknown' ?></div>
            </div>
            
            <div class="info-item">
                <div class="info-label">User Role</div>
                <div class="info-value"><?= $_SESSION['user_role'] ?? 'Not set' ?></div>
            </div>
            
            <div class="info-item">
                <div class="info-label">Remember Token</div>
                <div class="info-value"><?= isset($_COOKIE['remember_token']) ? 'Set (30 days)' : 'Not set' ?></div>
            </div>
            <?php endif; ?>
        </div>

        <div class="actions">
            <?php if ($is_logged_in): ?>
                <a href="?logout=true" class="btn btn-danger">Logout</a>
            <?php else: ?>
                <a href="index.php" class="btn btn-primary">Go to Login</a>
            <?php endif; ?>
            <a href="index.php" class="btn btn-secondary">Back to Home</a>
            <button onclick="location.reload()" class="btn btn-secondary">Refresh Page</button>
        </div>

        <div style="margin-top: 2rem; padding: 1rem; background: #e9ecef; border-radius: 6px;">
            <h3>Session Variables:</h3>
            <pre style="background: white; padding: 1rem; border-radius: 4px; overflow-x: auto;"><?= htmlspecialchars(print_r($_SESSION, true)) ?></pre>
        </div>

        <div style="margin-top: 1rem; padding: 1rem; background: #e9ecef; border-radius: 6px;">
            <h3>Cookies:</h3>
            <pre style="background: white; padding: 1rem; border-radius: 4px; overflow-x: auto;"><?= htmlspecialchars(print_r($_COOKIE, true)) ?></pre>
        </div>
    </div>
</body>
</html>
