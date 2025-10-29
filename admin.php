<?php
// Admin Panel - User & Editor Management + Analytics
require_once 'config.php';

// Check if user is admin
if (!is_admin()) {
    header('Location: index.php');
    exit;
}

// Try to connect to database with error handling
try {
    $conn = db_connect();
} catch (Exception $e) {
    error_log("Admin panel - Database connection failed: " . $e->getMessage());
    die("<h1>Database Connection Failed</h1><p>Unable to connect to database. Please check your configuration.</p>");
}

// Check if role column exists
try {
    $result_check = $conn->query("SHOW COLUMNS FROM users LIKE 'role'");
if (isset($result_check) && $result_check->num_rows === 0) {
    echo "<div style='padding: 2rem; max-width: 800px; margin: 0 auto;'>";
    echo "<h1>Database Migration Required</h1>";
    echo "<p>The 'role' column is missing from the users table.</p>";
    echo "<p><a href='migrate_add_role_column.php' style='display: inline-block; padding: 1rem 2rem; background: #a8aa19; color: white; text-decoration: none; border-radius: 6px; font-weight: bold;'>Run Migration Now</a></p>";
    echo "<p>Or run: <code>php init.php</code> to recreate all tables.</p>";
    echo "</div>";
    exit;
}
} catch (Exception $e) {
    error_log("Admin panel - Role check failed: " . $e->getMessage());
}

// Get statistics
$stats = [
    'total_users' => 0,
    'total_editors' => 0,
    'total_stories' => 0,
    'total_guides' => 0,
    'total_events' => 0,
    'active_members' => 0,
    'new_contacts' => 0
];

// Get statistics with error handling
try {
    $result = $conn->query("SELECT COUNT(*) as count FROM users");
    if ($result) $stats['total_users'] = $result->fetch_assoc()['count'];
} catch (Exception $e) {
    $stats['total_users'] = 0;
}

try {
    $result = $conn->query("SELECT COUNT(*) as count FROM users WHERE role IN ('editor', 'admin')");
    if ($result) $stats['total_editors'] = $result->fetch_assoc()['count'];
} catch (Exception $e) {
    $stats['total_editors'] = 0;
}

try {
    $result = $conn->query("SELECT COUNT(*) as count FROM stories");
    if ($result) $stats['total_stories'] = $result->fetch_assoc()['count'];
} catch (Exception $e) {
    $stats['total_stories'] = 0;
}

try {
    $result = $conn->query("SELECT COUNT(*) as count FROM guides");
    if ($result) $stats['total_guides'] = $result->fetch_assoc()['count'];
} catch (Exception $e) {
    $stats['total_guides'] = 0;
}

try {
    $result = $conn->query("SELECT COUNT(*) as count FROM events");
    if ($result) $stats['total_events'] = $result->fetch_assoc()['count'];
} catch (Exception $e) {
    $stats['total_events'] = 0;
}

try {
    $result = $conn->query("SELECT COUNT(*) as count FROM members WHERE is_active = 1");
    if ($result) $stats['active_members'] = $result->fetch_assoc()['count'];
} catch (Exception $e) {
    $stats['active_members'] = 0;
}

try {
    $result = $conn->query("SELECT COUNT(*) as count FROM contacts WHERE status = 'new'");
    if ($result) $stats['new_contacts'] = $result->fetch_assoc()['count'];
} catch (Exception $e) {
    $stats['new_contacts'] = 0;
}

// Get recent activity with error handling
$recent_stories = [];
try {
    $result = $conn->query("SELECT title, created_at, status FROM stories ORDER BY created_at DESC LIMIT 5");
    if ($result) {
        while ($row = $result->fetch_assoc()) {
            $recent_stories[] = $row;
        }
    }
} catch (Exception $e) {
    $recent_stories = [];
}

$recent_guides = [];
try {
    $result = $conn->query("SELECT title, created_at, status FROM guides ORDER BY created_at DESC LIMIT 5");
    if ($result) {
        while ($row = $result->fetch_assoc()) {
            $recent_guides[] = $row;
        }
    }
} catch (Exception $e) {
    $recent_guides = [];
}

// Get all users with their roles
$users = [];
try {
    $result = $conn->query("SELECT id, email, role, created_at FROM users ORDER BY created_at DESC");
    if ($result) {
        while ($row = $result->fetch_assoc()) {
            $users[] = $row;
        }
    }
} catch (Exception $e) {
    $users = [];
}

try {
    $conn->close();
} catch (Exception $e) {
    // Connection already closed or error
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard | Yucca Club</title>
    <link rel="icon" type="image/png" href="ui/img/favicon.png">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', sans-serif;
            background: #F5F1E9;
            color: #1a1a1a;
            line-height: 1.6;
        }

        .dashboard-container {
            display: grid;
            grid-template-columns: 260px 1fr;
            min-height: 100vh;
        }

        /* Sidebar */
        .sidebar {
            background: #1a1a1a;
            color: white;
            padding: 2rem 0;
            position: sticky;
            top: 0;
            height: 100vh;
            overflow-y: auto;
        }

        .sidebar-header {
            padding: 0 1.5rem 2rem;
            border-bottom: 1px solid rgba(255,255,255,0.1);
            margin-bottom: 2rem;
        }

        .sidebar-header h1 {
            font-size: 1.5rem;
            font-weight: 700;
            color: #a8aa19;
        }

        .sidebar-header p {
            font-size: 0.875rem;
            opacity: 0.7;
            margin-top: 0.25rem;
        }

        .nav-section {
            padding: 0 1rem;
            margin-bottom: 2rem;
        }

        .nav-section-title {
            font-size: 0.75rem;
            text-transform: uppercase;
            letter-spacing: 1px;
            opacity: 0.5;
            padding: 0 0.75rem;
            margin-bottom: 0.75rem;
        }

        .nav-item {
            display: flex;
            align-items: center;
            gap: 0.75rem;
            padding: 0.75rem;
            margin: 0.25rem 0;
            border-radius: 8px;
            cursor: pointer;
            transition: all 0.2s;
            font-size: 0.9375rem;
            color: rgba(255,255,255,0.8);
        }

        .nav-item:hover {
            background: rgba(255,255,255,0.1);
            color: white;
        }

        .nav-item.active {
            background: #a8aa19;
            color: white;
        }

        .nav-item i {
            width: 20px;
            text-align: center;
        }

        /* Main Content */
        .main-content {
            padding: 2rem;
        }

        .dashboard-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 2rem;
        }

        .dashboard-header h2 {
            font-size: 1.875rem;
            font-weight: 700;
            color: #1a1a1a;
        }

        .dashboard-header p {
            color: #63666A;
            margin-top: 0.25rem;
        }

        /* Stats Grid */
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 1.5rem;
            margin-bottom: 2rem;
        }

        .stat-card {
            background: white;
            padding: 1.5rem;
            border-radius: 12px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.04);
            border: 1px solid rgba(0,0,0,0.06);
        }

        .stat-card-header {
            display: flex;
            justify-content: space-between;
            align-items: start;
            margin-bottom: 1rem;
        }

        .stat-card-title {
            font-size: 0.875rem;
            color: #63666A;
            font-weight: 500;
        }

        .stat-card-icon {
            font-size: 1.5rem;
            opacity: 0.7;
        }

        .stat-card-value {
            font-size: 2rem;
            font-weight: 700;
            color: #1a1a1a;
        }

        .stat-card-value.new {
            color: #A81919;
        }

        /* Content Area */
        .content-area {
            background: white;
            padding: 2rem;
            border-radius: 12px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.04);
            border: 1px solid rgba(0,0,0,0.06);
            min-height: 400px;
        }

        .content-section {
            display: none;
        }

        .content-section.active {
            display: block;
        }

        /* Table */
        .data-table {
            width: 100%;
            border-collapse: collapse;
        }

        .data-table thead {
            background: #F5F1E9;
            border-bottom: 2px solid #ede9df;
        }

        .data-table th {
            padding: 1rem;
            text-align: left;
            font-size: 0.875rem;
            font-weight: 600;
            color: #63666A;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .data-table td {
            padding: 1rem;
            border-bottom: 1px solid #ede9df;
        }

        .data-table tbody tr:hover {
            background: #F5F1E9;
        }

        /* Badge */
        .badge {
            display: inline-block;
            padding: 0.25rem 0.75rem;
            border-radius: 6px;
            font-size: 0.75rem;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .badge-user { background: #ede9df; color: #63666A; }
        .badge-editor { background: #E3F2FD; color: #1976D2; }
        .badge-admin { background: #a8aa19; color: white; }
        .badge-published { background: #E8F5E9; color: #388E3C; }
        .badge-draft { background: #FFF3E0; color: #F57C00; }
        .badge-new { background: #FFEBEE; color: #C62828; }

        /* Buttons */
        .btn {
            padding: 0.625rem 1.25rem;
            border-radius: 8px;
            border: none;
            font-size: 0.9375rem;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.2s;
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            text-decoration: none;
        }

        .btn-primary {
            background: #a8aa19;
            color: white;
        }

        .btn-primary:hover {
            background: #94941c;
        }

        .btn-secondary {
            background: #ede9df;
            color: #63666A;
        }

        .btn-secondary:hover {
            background: #d6d1c4;
        }

        .btn-danger {
            background: #A81919;
            color: white;
        }

        .btn-danger:hover {
            background: #8d1313;
        }

        .btn-sm {
            padding: 0.5rem 1rem;
            font-size: 0.875rem;
        }

        /* Actions Bar */
        .actions-bar {
            display: flex;
            gap: 1rem;
            margin-bottom: 1.5rem;
            flex-wrap: wrap;
        }

        /* Modal */
        .modal {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0,0,0,0.5);
            z-index: 1000;
            align-items: center;
            justify-content: center;
        }

        .modal.active {
            display: flex;
        }

        .modal-content {
            background: white;
            padding: 2rem;
            border-radius: 12px;
            max-width: 600px;
            width: 90%;
            max-height: 90vh;
            overflow-y: auto;
            position: relative;
        }

        .modal-close {
            position: absolute;
            top: 1rem;
            right: 1rem;
            background: none;
            border: none;
            font-size: 1.5rem;
            cursor: pointer;
            color: #63666A;
        }

        .modal-close:hover {
            color: #1a1a1a;
        }

        .form-group {
            margin-bottom: 1.5rem;
        }

        .form-group label {
            display: block;
            margin-bottom: 0.5rem;
            font-weight: 500;
            color: #1a1a1a;
        }

        .form-group input,
        .form-group select,
        .form-group textarea {
            width: 100%;
            padding: 0.75rem;
            border: 1px solid #ede9df;
            border-radius: 8px;
            font-size: 0.9375rem;
            transition: border-color 0.2s;
        }

        .form-group input:focus,
        .form-group select:focus,
        .form-group textarea:focus {
            outline: none;
            border-color: #a8aa19;
        }

        /* Rich Builder */
        #rich-builder {
            background: #F5F1E9;
            border: 2px solid #ede9df;
            border-radius: 8px;
            padding: 1rem;
            margin-top: 1rem;
        }

        #blocks-container {
            display: grid;
            gap: 0.75rem;
            margin-top: 1rem;
        }

        .block-item {
            background: white;
            padding: 1rem;
            border-radius: 8px;
            border: 1px solid #ede9df;
            position: relative;
        }

        .block-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 0.5rem;
        }

        .block-actions {
            display: flex;
            gap: 0.5rem;
        }

        .block-actions button {
            padding: 0.375rem 0.75rem;
            font-size: 0.75rem;
        }

        /* Empty State */
        .empty-state {
            text-align: center;
            padding: 4rem 2rem;
            color: #63666A;
        }

        .empty-state i {
            font-size: 4rem;
            opacity: 0.3;
            margin-bottom: 1rem;
        }

        /* Responsive */
        @media (max-width: 1024px) {
            .dashboard-container {
                grid-template-columns: 1fr;
            }

            .sidebar {
                display: none;
            }
        }
        
        /* Enhanced Rich Builder Styles */
        .rich-builder-toolbar {
            background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
            border: 1px solid #dee2e6;
            border-radius: 12px;
            padding: 1.5rem;
            margin-bottom: 1.5rem;
            box-shadow: 0 2px 8px rgba(0,0,0,0.1);
        }
        
        .toolbar-section {
            margin-bottom: 1rem;
        }
        
        .toolbar-section:last-child {
            margin-bottom: 0;
        }
        
        .toolbar-buttons {
            display: flex;
            flex-wrap: wrap;
            gap: 0.5rem;
            margin-top: 0.5rem;
        }
        
        .blocks-container {
            min-height: 200px;
            border: 2px dashed #dee2e6;
            border-radius: 12px;
            padding: 1rem;
            background: #fafbfc;
            transition: all 0.3s ease;
        }
        
        .blocks-container:empty::before {
            content: "Click the buttons above to add content blocks. Drag blocks to reorder them.";
            display: block;
            text-align: center;
            color: #6c757d;
            font-style: italic;
            padding: 2rem;
        }
        
        .blocks-container.has-blocks {
            border-style: solid;
            border-color: #28a745;
            background: #f8fff9;
        }
        
        .block-item {
            border: 1px solid #dee2e6;
            border-radius: 12px;
            padding: 1.25rem;
            margin-bottom: 1rem;
            background: white;
            box-shadow: 0 2px 4px rgba(0,0,0,0.05);
            transition: all 0.3s ease;
            position: relative;
        }
        
        .block-item:hover {
            box-shadow: 0 4px 12px rgba(0,0,0,0.1);
            transform: translateY(-1px);
        }
        
        .block-item.dragging {
            opacity: 0.5;
            transform: rotate(2deg);
        }
        
        .block-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 0.75rem;
            padding-bottom: 0.75rem;
            border-bottom: 1px solid #e9ecef;
        }
        
        .block-header strong {
            color: var(--lobo-gray);
            font-size: 0.9rem;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        
        .block-actions {
            display: flex;
            gap: 0.25rem;
        }
        
        .block-actions .btn {
            padding: 0.25rem 0.5rem;
            font-size: 0.8rem;
        }
        
        .block-content {
            position: relative;
        }
        
        .block-content input,
        .block-content textarea {
            border: 1px solid #ced4da;
            border-radius: 6px;
            transition: border-color 0.3s ease;
        }
        
        .block-content input:focus,
        .block-content textarea:focus {
            border-color: var(--yucca-yellow);
            box-shadow: 0 0 0 2px rgba(184, 186, 32, 0.25);
            outline: none;
        }
        
        .quick-actions {
            display: flex;
            gap: 0.5rem;
            flex-wrap: wrap;
        }
        
        .btn-outline {
            background: transparent;
            border: 1px solid #ced4da;
            color: #495057;
        }
        
        .btn-outline:hover {
            background: #f8f9fa;
            border-color: var(--yucca-yellow);
            color: var(--lobo-gray);
        }
        
        /* Block Type Specific Styles */
        .block-item[data-type="heading"] {
            border-left: 4px solid #007bff;
        }
        
        .block-item[data-type="subheading"] {
            border-left: 4px solid #6f42c1;
        }
        
        .block-item[data-type="paragraph"] {
            border-left: 4px solid #28a745;
        }
        
        .block-item[data-type="image"] {
            border-left: 4px solid #fd7e14;
        }
        
        .block-item[data-type="gallery"] {
            border-left: 4px solid #e83e8c;
        }
        
        .block-item[data-type="blockquote"] {
            border-left: 4px solid #6c757d;
        }
        
        .block-item[data-type="list"] {
            border-left: 4px solid #20c997;
        }
        
        
        .block-item[data-type="video"] {
            border-left: 4px solid #17a2b8;
        }
        
        .block-item[data-type="divider"] {
            border-left: 4px solid #6c757d;
        }
    </style>
</head>
<body>
    <div class="dashboard-container">
        <!-- Sidebar -->
        <div class="sidebar">
            <div class="sidebar-header">
                <h1><img src="ui/img/favicon.png" alt="Yucca Club" style="width: 24px; height: 24px; margin-right: 8px; vertical-align: middle;"> Yucca Club</h1>
                <p>Admin Dashboard</p>
            </div>

            <nav class="nav-section">
                <div class="nav-section-title">Main</div>
                <div class="nav-item active" onclick="showSection('dashboard')">
                    <i class="fas fa-home"></i>
                    <span>Dashboard</span>
                </div>
            </nav>

            <nav class="nav-section">
                <div class="nav-section-title">Content</div>
                <div class="nav-item" onclick="showSection('stories')">
                    <i class="fas fa-book"></i>
                    <span>Stories</span>
                </div>
                <div class="nav-item" onclick="showSection('guides')">
                    <i class="fas fa-map"></i>
                    <span>Guides</span>
                </div>
                <div class="nav-item" onclick="showSection('for-review')">
                    <i class="fas fa-clipboard-check"></i>
                    <span>For Review</span>
                    <?php 
                    $pending_count = 0;
                    try {
                        $conn = db_connect();
                        $result = $conn->query("SELECT COUNT(*) as count FROM user_posts WHERE status = 'pending'");
                        if ($result) $pending_count = $result->fetch_assoc()['count'];
                        $conn->close();
                    } catch (Exception $e) {
                        $pending_count = 0;
                    }
                    ?>
                    <?php if ($pending_count > 0): ?>
                        <span class="badge badge-new" style="margin-left: auto;"><?= $pending_count ?></span>
                    <?php endif; ?>
                </div>
                <div class="nav-item" onclick="showSection('messages')">
                    <i class="fas fa-envelope"></i>
                    <span>Messages</span>
                    <?php if ($stats['new_contacts'] > 0): ?>
                        <span class="badge badge-new" style="margin-left: auto;"><?= $stats['new_contacts'] ?></span>
                    <?php endif; ?>
                </div>
            </nav>

            <nav class="nav-section">
                <div class="nav-section-title">Users</div>
                <div class="nav-item" onclick="showSection('users')">
                    <i class="fas fa-users"></i>
                    <span>All Users</span>
                </div>
                <div class="nav-item" onclick="showSection('create-user')">
                    <i class="fas fa-user-plus"></i>
                    <span>Create User</span>
                </div>
            </nav>

            <nav class="nav-section">
                <div class="nav-section-title">Tools</div>
                <div class="nav-item" onclick="testAPIs()">
                    <i class="fas fa-flask"></i>
                    <span>Test APIs</span>
                </div>
                <div class="nav-item" onclick="showSection('maintenance')">
                    <i class="fas fa-tools"></i>
                    <span>Maintenance</span>
                </div>
            </nav>

            <div style="padding: 0 1.5rem; margin-top: auto; padding-top: 2rem; border-top: 1px solid rgba(255,255,255,0.1);">
                <a href="index.php" style="display: flex; align-items: center; gap: 0.75rem; color: rgba(255,255,255,0.8); text-decoration: none; padding: 0.75rem; border-radius: 8px; transition: all 0.2s;">
                    <i class="fas fa-arrow-left"></i>
                    <span>Back to Site</span>
                </a>
            </div>
        </div>

        <!-- Main Content -->
        <div class="main-content">
            <!-- Dashboard Section -->
            <div id="dashboard-section" class="content-section active">
                <div class="dashboard-header">
                    <div>
                        <h2>Dashboard</h2>
                        <p>Overview of your content and activity</p>
                    </div>
                </div>

                <!-- Stats Grid -->
                <div class="stats-grid">
                    <div class="stat-card">
                        <div class="stat-card-header">
                            <div class="stat-card-title">Total Stories</div>
                            <div class="stat-card-icon"><i class="fas fa-book"></i></div>
                        </div>
                        <div class="stat-card-value"><?= $stats['total_stories'] ?></div>
                    </div>

                    <div class="stat-card">
                        <div class="stat-card-header">
                            <div class="stat-card-title">Total Guides</div>
                            <div class="stat-card-icon"><i class="fas fa-map"></i></div>
                        </div>
                        <div class="stat-card-value"><?= $stats['total_guides'] ?></div>
                    </div>

                    <div class="stat-card">
                        <div class="stat-card-header">
                            <div class="stat-card-title">Events</div>
                            <div class="stat-card-icon"><i class="fas fa-calendar"></i></div>
                        </div>
                        <div class="stat-card-value"><?= $stats['total_events'] ?></div>
                    </div>

                    <div class="stat-card">
                        <div class="stat-card-header">
                            <div class="stat-card-title">Total Users</div>
                            <div class="stat-card-icon"><i class="fas fa-users"></i></div>
                        </div>
                        <div class="stat-card-value"><?= $stats['total_users'] ?></div>
                    </div>

                    <div class="stat-card">
                        <div class="stat-card-header">
                            <div class="stat-card-title">Staff</div>
                            <div class="stat-card-icon"><i class="fas fa-user-tie"></i></div>
                        </div>
                        <div class="stat-card-value"><?= $stats['total_editors'] ?></div>
                    </div>

                    <div class="stat-card">
                        <div class="stat-card-header">
                            <div class="stat-card-title">New Messages</div>
                            <div class="stat-card-icon"><i class="fas fa-envelope"></i></div>
                        </div>
                        <div class="stat-card-value new"><?= $stats['new_contacts'] ?></div>
                    </div>
                </div>

                <!-- Recent Activity -->
                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1.5rem; margin-top: 2rem;">
                    <div class="content-area">
                        <h3 style="font-size: 1.25rem; font-weight: 600; margin-bottom: 1rem;">Recent Stories</h3>
                        <div style="max-height: 400px; overflow-y: auto;">
                            <?php if (count($recent_stories) > 0): ?>
                                <?php foreach ($recent_stories as $story): ?>
                                <div style="padding: 1rem; border-bottom: 1px solid #ede9df;">
                                    <div style="font-weight: 600; margin-bottom: 0.5rem;"><?= htmlspecialchars($story['title']) ?></div>
                                    <div style="font-size: 0.875rem; color: #63666A;">
                                        <span class="badge badge-<?= $story['status'] ?>"><?= ucfirst($story['status']) ?></span>
                                        <span style="margin-left: 1rem;"><?= date('M j, Y', strtotime($story['created_at'])) ?></span>
                                    </div>
                                </div>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <div class="empty-state">
                                    <i class="fas fa-book"></i>
                                    <p>No stories yet</p>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>

                    <div class="content-area">
                        <h3 style="font-size: 1.25rem; font-weight: 600; margin-bottom: 1rem;">Recent Guides</h3>
                        <div style="max-height: 400px; overflow-y: auto;">
                            <?php if (count($recent_guides) > 0): ?>
                                <?php foreach ($recent_guides as $guide): ?>
                                <div style="padding: 1rem; border-bottom: 1px solid #ede9df;">
                                    <div style="font-weight: 600; margin-bottom: 0.5rem;"><?= htmlspecialchars($guide['title']) ?></div>
                                    <div style="font-size: 0.875rem; color: #63666A;">
                                        <span class="badge badge-<?= $guide['status'] ?>"><?= ucfirst($guide['status']) ?></span>
                                        <span style="margin-left: 1rem;"><?= date('M j, Y', strtotime($guide['created_at'])) ?></span>
                                    </div>
                                </div>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <div class="empty-state">
                                    <i class="fas fa-map"></i>
                                    <p>No guides yet</p>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Stories Section -->
            <div id="stories-section" class="content-section">
                <div class="dashboard-header">
                    <div>
                        <h2><i class="fas fa-book"></i> Stories</h2>
                        <p>Manage all story content</p>
                    </div>
                </div>

                <div class="content-area">
                    <div class="actions-bar">
                        <button class="btn btn-primary" onclick="openEditor('stories', null)">
                            <i class="fas fa-plus"></i>
                            Create New Story
                        </button>
                    </div>
                    <div id="stories-list"></div>
                </div>
            </div>

            <!-- Guides Section -->
            <div id="guides-section" class="content-section">
                <div class="dashboard-header">
                    <div>
                        <h2><i class="fas fa-map"></i> Guides</h2>
                        <p>Manage all guide content</p>
                    </div>
                </div>

                <div class="content-area">
                    <div class="actions-bar">
                        <button class="btn btn-primary" onclick="openEditor('guides', null)">
                            <i class="fas fa-plus"></i>
                            Create New Guide
                        </button>
                    </div>
                    <div id="guides-list"></div>
                </div>
            </div>

            <!-- For Review Section -->
            <div id="for-review-section" class="content-section">
                <div class="dashboard-header">
                    <div>
                        <h2><i class="fas fa-clipboard-check"></i> Posts For Review</h2>
                        <p>Review and approve community post submissions</p>
                    </div>
                </div>

                <div class="content-area">
                    <div id="review-posts-list"></div>
                </div>
            </div>

            <!-- Messages Section -->
            <div id="messages-section" class="content-section">
                <div class="dashboard-header">
                    <div>
                        <h2><i class="fas fa-envelope"></i> Messages</h2>
                        <p>View and manage contact submissions</p>
                    </div>
                </div>

                <div class="content-area">
                    <div id="contacts-list"></div>
                </div>
            </div>

            <!-- Users Section -->
            <div id="users-section" class="content-section">
                <div class="dashboard-header">
                    <div>
                        <h2><i class="fas fa-users"></i> All Users</h2>
                        <p>Manage user accounts and permissions</p>
                    </div>
                </div>

                <div class="content-area">
                    <table class="data-table">
                        <thead>
                            <tr>
                                <th>Email</th>
                                <th>Role</th>
                                <th>Created</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($users as $user): ?>
                            <tr>
                                <td><?= htmlspecialchars($user['email']) ?></td>
                                <td>
                                    <span class="badge badge-<?= $user['role'] ?>">
                                        <?= ucfirst($user['role']) ?>
                                    </span>
                                </td>
                                <td><?= date('M j, Y', strtotime($user['created_at'])) ?></td>
                                <td>
                                    <?php if ($user['email'] !== $_SESSION['user_email']): ?>
                                        <select onchange="updateUserRole(<?= $user['id'] ?>, this.value)" style="padding: 0.5rem; border: 1px solid #ede9df; border-radius: 8px; font-size: 0.875rem;">
                                            <option value="user" <?= $user['role'] === 'user' ? 'selected' : '' ?>>User</option>
                                            <option value="editor" <?= $user['role'] === 'editor' ? 'selected' : '' ?>>Editor</option>
                                        </select>
                                    <?php else: ?>
                                        <span style="opacity: 0.5; font-size: 0.875rem;">Can't change own role</span>
                                    <?php endif; ?>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Create User Section -->
            <div id="create-user-section" class="content-section">
                <div class="dashboard-header">
                    <div>
                        <h2><i class="fas fa-user-plus"></i> Create User</h2>
                        <p>Add new editor or admin accounts</p>
                    </div>
                </div>

                <div class="content-area">
                    <div class="actions-bar">
                        <button class="btn btn-primary" onclick="openCreateEditorModal()">
                            <i class="fas fa-user-tie"></i>
                            Create Editor
                        </button>
                        <button class="btn btn-secondary" onclick="openCreateAdminModal()">
                            <i class="fas fa-user-shield"></i>
                            Create Admin
                        </button>
                    </div>
                </div>
            </div>

            <!-- Testing Section -->
            <div id="testing-section" class="content-section">
                <div class="dashboard-header">
                    <div>
                        <h2><i class="fas fa-flask"></i> Testing</h2>
                        <p>Test API connections and functionality</p>
                    </div>
                </div>

                <div class="content-area">
                    <div class="actions-bar">
                        <button class="btn btn-primary" onclick="testGitHub()">
                            <i class="fab fa-github"></i>
                            Test GitHub
                        </button>
                        <button class="btn btn-secondary" onclick="testDatabase()">
                            <i class="fas fa-database"></i>
                            Test Database
                        </button>
                        <button class="btn btn-secondary" onclick="testAPIs()">
                            <i class="fas fa-plug"></i>
                            Test APIs
                        </button>
                    </div>
                    <div id="test-results" style="margin-top: 2rem;"></div>
                </div>
            </div>

            <!-- Maintenance Section -->
            <div id="maintenance-section" class="content-section">
                <div class="dashboard-header">
                    <div>
                        <h2><i class="fas fa-tools"></i> Maintenance Mode</h2>
                        <p>Control site accessibility</p>
                    </div>
                </div>

                <div class="content-area">
                    <div id="maintenance-status"></div>
                    <div class="actions-bar" style="margin-top: 2rem;">
                        <button class="btn btn-danger" onclick="toggleMaintenance('enable')">
                            <i class="fas fa-lock"></i>
                            Enable Maintenance
                        </button>
                        <button class="btn btn-primary" onclick="toggleMaintenance('disable')">
                            <i class="fas fa-unlock"></i>
                            Disable Maintenance
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Editor Modal -->
    <div id="editor-modal" class="modal">
        <div class="modal-content">
            <button class="modal-close" onclick="closeEditor()">&times;</button>
            <h2 id="editor-title">Create Content</h2>
            <form id="editor-form">
                <input type="hidden" id="content-type" name="content_type">
                <input type="hidden" id="content-id" name="content_id">

                <div class="form-group">
                    <label>Title</label>
                    <input type="text" id="content-title" name="title" required>
                </div>

                <div class="form-group">
                    <label>Slug</label>
                    <input type="text" id="content-slug" name="slug" required>
                </div>

                <div class="form-group">
                    <label>Category</label>
                    <input type="text" id="content-category" name="category">
                </div>

                <div class="form-group">
                    <label>Featured Image URL</label>
                    <input type="url" id="content-image" name="featured_image" placeholder="https://...">
                </div>

                <div class="form-group">
                    <label>Excerpt</label>
                    <textarea id="content-excerpt" name="excerpt" rows="3"></textarea>
                </div>

                <div class="form-group">
                    <label>Content</label>
                    <div style="display: flex; gap: 1rem; align-items: center; margin-bottom: 0.5rem;">
                        <button type="button" class="btn btn-secondary btn-sm" onclick="toggleBuilder()">
                            <i class="fas fa-magic"></i> Toggle Rich Builder
                        </button>
                        <span id="builder-status" style="font-size: 0.9rem; opacity: 0.8; font-weight: 600;">
                            Rich builder: off
                        </span>
                        <div style="margin-left: auto; display: flex; gap: 0.5rem;">
                            <button type="button" class="btn btn-outline btn-sm" onclick="loadTemplate()" id="template-btn">
                                <i class="fas fa-file-import"></i> Load Template
                            </button>
                            <button type="button" class="btn btn-outline btn-sm" onclick="previewContent()" id="preview-btn" style="display: none;">
                                <i class="fas fa-eye"></i> Preview
                            </button>
                            <button type="button" class="btn btn-outline btn-sm" onclick="exportContent()" id="export-btn" style="display: none;">
                                <i class="fas fa-download"></i> Export
                            </button>
                    </div>
                    </div>
                    <textarea id="content-body" name="content" rows="10" placeholder="Start typing your content or enable the rich builder for a visual editing experience..."></textarea>

                    <div id="rich-builder" style="display:none;">
                        <!-- Enhanced Toolbar -->
                        <div class="rich-builder-toolbar">
                            <div class="toolbar-section">
                                <h4 style="margin: 0; font-size: 0.9rem; color: var(--lobo-gray);">Text Blocks</h4>
                                <div class="toolbar-buttons">
                                    <button type="button" class="btn btn-outline btn-sm" onclick="addBlock('heading')" title="Add Heading">
                                        <i class="fas fa-heading"></i> Heading
                                    </button>
                                    <button type="button" class="btn btn-outline btn-sm" onclick="addBlock('subheading')" title="Add Subheading">
                                        <i class="fas fa-heading"></i> Subheading
                                    </button>
                                    <button type="button" class="btn btn-outline btn-sm" onclick="addBlock('paragraph')" title="Add Paragraph">
                                        <i class="fas fa-paragraph"></i> Paragraph
                                    </button>
                                    <button type="button" class="btn btn-outline btn-sm" onclick="addBlock('blockquote')" title="Add Quote">
                                        <i class="fas fa-quote-left"></i> Quote
                                    </button>
                        </div>
                            </div>
                            
                            <div class="toolbar-section">
                                <h4 style="margin: 0; font-size: 0.9rem; color: var(--lobo-gray);">Media & Lists</h4>
                                <div class="toolbar-buttons">
                                    <button type="button" class="btn btn-outline btn-sm" onclick="addBlock('image')" title="Add Image">
                                        <i class="fas fa-image"></i> Image
                                    </button>
                                    <button type="button" class="btn btn-outline btn-sm" onclick="addBlock('gallery')" title="Add Gallery">
                                        <i class="fas fa-images"></i> Gallery
                                    </button>
                                    <button type="button" class="btn btn-outline btn-sm" onclick="addBlock('list')" title="Add List">
                                        <i class="fas fa-list"></i> List
                                    </button>
                                    <button type="button" class="btn btn-outline btn-sm" onclick="addBlock('numbered-list')" title="Add Numbered List">
                                        <i class="fas fa-list-ol"></i> Numbered
                                    </button>
                                </div>
                            </div>
                            
                            <div class="toolbar-section">
                                <h4 style="margin: 0; font-size: 0.9rem; color: var(--lobo-gray);">Layout & Special</h4>
                                <div class="toolbar-buttons">
                                    <button type="button" class="btn btn-outline btn-sm" onclick="addBlock('divider')" title="Add Divider">
                                        <i class="fas fa-minus"></i> Divider
                                    </button>
                                    <button type="button" class="btn btn-outline btn-sm" onclick="addBlock('video')" title="Add Video">
                                        <i class="fas fa-video"></i> Video
                                    </button>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Blocks Container -->
                        <div id="blocks-container" class="blocks-container"></div>
                        
                        <!-- Quick Actions -->
                        <div class="quick-actions" style="margin-top: 1rem; padding-top: 1rem; border-top: 1px solid #e5e5e5;">
                            <button type="button" class="btn btn-secondary btn-sm" onclick="addBlock('paragraph')">
                                <i class="fas fa-plus"></i> Add Paragraph
                            </button>
                            <button type="button" class="btn btn-secondary btn-sm" onclick="duplicateLastBlock()">
                                <i class="fas fa-copy"></i> Duplicate Last
                            </button>
                            <button type="button" class="btn btn-secondary btn-sm" onclick="clearAllBlocks()">
                                <i class="fas fa-trash"></i> Clear All
                            </button>
                        </div>
                    </div>
                </div>

                <div class="form-group">
                    <label>Status</label>
                    <select id="content-status" name="status">
                        <option value="draft">Draft</option>
                        <option value="published">Published</option>
                        <option value="archived">Archived</option>
                    </select>
                </div>

                <!-- Validation Status -->
                <div id="validation-status"></div>

                <div style="display: flex; gap: 1rem; margin-top: 2rem;">
                    <button type="submit" class="btn btn-primary">Save</button>
                    <button type="button" class="btn btn-secondary" onclick="closeEditor()">Cancel</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Create Editor Modal -->
    <div id="create-editor-modal" class="modal">
        <div class="modal-content">
            <button class="modal-close" onclick="closeCreateEditorModal()">&times;</button>
            <h2>Create Editor Account</h2>
            <form id="create-editor-form">
                <div class="form-group">
                    <label>Email</label>
                    <input type="email" name="email" required>
                </div>
                <div class="form-group">
                    <label>Password</label>
                    <input type="password" name="password" required minlength="6">
                </div>
                <div style="display: flex; gap: 1rem; margin-top: 2rem;">
                    <button type="submit" class="btn btn-primary">Create Account</button>
                    <button type="button" class="btn btn-secondary" onclick="closeCreateEditorModal()">Cancel</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Create Admin Modal -->
    <div id="create-admin-modal" class="modal">
        <div class="modal-content">
            <button class="modal-close" onclick="closeCreateAdminModal()">&times;</button>
            <h2>Create Admin Account</h2>
            <form id="create-admin-form">
                <div class="form-group">
                    <label>Email</label>
                    <input type="email" name="email" required>
                </div>
                <div class="form-group">
                    <label>Password</label>
                    <input type="password" name="password" required minlength="6">
                </div>
                <div style="display: flex; gap: 1rem; margin-top: 2rem;">
                    <button type="submit" class="btn btn-primary">Create Account</button>
                    <button type="button" class="btn btn-secondary" onclick="closeCreateAdminModal()">Cancel</button>
                </div>
            </form>
        </div>
    </div>

    <script>
        // Navigation
        function showSection(sectionName) {
            // Hide all sections
            document.querySelectorAll('.content-section').forEach(section => {
                section.classList.remove('active');
            });
            
            // Remove active from all nav items
            document.querySelectorAll('.nav-item').forEach(item => {
                item.classList.remove('active');
            });

            // Show selected section
            const section = document.getElementById(sectionName + '-section');
            if (section) {
                section.classList.add('active');
            }

            // Add active to clicked nav item
            event.currentTarget.classList.add('active');

            // Load section-specific content
            if (sectionName === 'stories') loadStories();
            if (sectionName === 'guides') loadGuides();
            if (sectionName === 'for-review') loadReviewPosts();
            if (sectionName === 'messages') loadContacts();
        }

        // Load functions (keeping all existing functionality)
        
        async function loadStories() {
            try {
                const response = await fetch('api/content_api.php?type=stories&action=list', {
                    credentials: 'same-origin'
                });
                const data = await response.json();
                
                const container = document.getElementById('stories-list');
                let html = '<table class="data-table"><thead><tr><th>Title</th><th>Category</th><th>Status</th><th>Updated</th><th>Actions</th></tr></thead><tbody>';
                
                if (data.data && data.data.length > 0) {
                    data.data.forEach(item => {
                        html += `<tr>
                            <td>${item.title}</td>
                            <td>${item.category || '-'}</td>
                            <td><span class="badge badge-${item.status}">${item.status}</span></td>
                            <td>${new Date(item.updated_at).toLocaleDateString()}</td>
                            <td>
                                <button class="btn btn-secondary btn-sm" onclick="openEditor('stories', ${item.id})">Edit</button>
                                <button class="btn btn-danger btn-sm" onclick="deleteContent('stories', ${item.id})">Delete</button>
                            </td>
                        </tr>`;
                    });
                } else {
                    html += '<tr><td colspan="5" style="text-align: center; padding: 2rem;">No stories yet.</td></tr>';
                }
                
                html += '</tbody></table>';
                container.innerHTML = html;
            } catch (error) {
                console.error('Error loading stories:', error);
            }
        }

        async function loadGuides() {
            try {
                const response = await fetch('api/content_api.php?type=guides&action=list', {
                    credentials: 'same-origin'
                });
                const data = await response.json();
                
                const container = document.getElementById('guides-list');
                let html = '<table class="data-table"><thead><tr><th>Title</th><th>Category</th><th>Status</th><th>Updated</th><th>Actions</th></tr></thead><tbody>';
                
                if (data.data && data.data.length > 0) {
                    data.data.forEach(item => {
                        html += `<tr>
                            <td>${item.title}</td>
                            <td>${item.category || '-'}</td>
                            <td><span class="badge badge-${item.status}">${item.status}</span></td>
                            <td>${new Date(item.updated_at).toLocaleDateString()}</td>
                            <td>
                                <button class="btn btn-secondary btn-sm" onclick="openEditor('guides', ${item.id})">Edit</button>
                                <button class="btn btn-danger btn-sm" onclick="deleteContent('guides', ${item.id})">Delete</button>
                            </td>
                        </tr>`;
                    });
                } else {
                    html += '<tr><td colspan="5" style="text-align: center; padding: 2rem;">No guides yet.</td></tr>';
                }
                
                html += '</tbody></table>';
                container.innerHTML = html;
            } catch (error) {
                console.error('Error loading guides:', error);
            }
        }

        async function loadReviewPosts() {
            try {
                const response = await fetch('api/user_posts_api.php?action=list', {
                    credentials: 'same-origin'
                });
                const data = await response.json();
                
                const container = document.getElementById('review-posts-list');
                let html = '<table class="data-table"><thead><tr><th>Title</th><th>Author</th><th>Category</th><th>Status</th><th>Submitted</th><th>Actions</th></tr></thead><tbody>';
                
                if (data.data && data.data.length > 0) {
                    data.data.forEach(item => {
                        const content = JSON.parse(item.content || '{}');
                        const excerpt = content.intro ? content.intro.substring(0, 100) + '...' : '';
                        
                        html += `<tr>
                            <td>
                                <div style="font-weight: 600; margin-bottom: 0.25rem;">${item.title}</div>
                                <div style="font-size: 0.875rem; color: #666; max-width: 300px;">${excerpt}</div>
                            </td>
                            <td>${item.user_email}</td>
                            <td>${item.category || '-'}</td>
                            <td><span class="badge badge-${item.status}">${item.status}</span></td>
                            <td>${new Date(item.created_at).toLocaleDateString()}</td>
                            <td>
                                <div style="display: flex; gap: 0.5rem; flex-wrap: wrap;">
                                    <button class="btn btn-primary btn-sm" onclick="approvePost(${item.id})">Approve</button>
                                    <button class="btn btn-danger btn-sm" onclick="rejectPost(${item.id})">Reject</button>
                                    <button class="btn btn-secondary btn-sm" onclick="viewPost(${item.id})">View</button>
                                </div>
                            </td>
                        </tr>`;
                    });
                } else {
                    html += '<tr><td colspan="6" style="text-align: center; padding: 2rem;">No posts pending review.</td></tr>';
                }
                
                html += '</tbody></table>';
                container.innerHTML = html;
            } catch (error) {
                console.error('Error loading review posts:', error);
            }
        }

        async function loadContacts() {
            try {
                const response = await fetch('api/contacts_api.php', {
                    credentials: 'same-origin'
                });
                const data = await response.json();
                
                const container = document.getElementById('contacts-list');
                let html = '<table class="data-table"><thead><tr><th>Name</th><th>Email</th><th>Message</th><th>Status</th><th>Date</th><th>Actions</th></tr></thead><tbody>';
                
                if (data.data && data.data.length > 0) {
                    data.data.forEach(item => {
                        html += `<tr>
                            <td>${item.name}</td>
                            <td>${item.email}</td>
                            <td style="max-width: 300px; white-space: nowrap; overflow: hidden; text-overflow: ellipsis;">${item.message}</td>
                            <td><span class="badge badge-${item.status}">${item.status}</span></td>
                            <td>${new Date(item.created_at).toLocaleDateString()}</td>
                            <td>
                                <select onchange="updateContactStatus(${item.id}, this.value)" style="padding: 0.5rem; border: 1px solid #ede9df; border-radius: 8px;">
                                    <option value="new" ${item.status === 'new' ? 'selected' : ''}>New</option>
                                    <option value="read" ${item.status === 'read' ? 'selected' : ''}>Read</option>
                                    <option value="replied" ${item.status === 'replied' ? 'selected' : ''}>Replied</option>
                                    <option value="archived" ${item.status === 'archived' ? 'selected' : ''}>Archived</option>
                                </select>
                            </td>
                        </tr>`;
                    });
                } else {
                    html += '<tr><td colspan="6" style="text-align: center; padding: 2rem;">No messages yet.</td></tr>';
                }
                
                html += '</tbody></table>';
                container.innerHTML = html;
            } catch (error) {
                console.error('Error loading contacts:', error);
            }
        }

        async function openEditor(type, id) {
            try {
                document.getElementById('content-type').value = type;
                document.getElementById('editor-modal').classList.add('active');
                
                if (id) {
                    document.getElementById('editor-title').textContent = 'Edit ' + type;
                    const response = await fetch(`api/content_api.php?type=${type}&action=get&id=${id}`, {
                        credentials: 'same-origin'
                    });
                    const data = await response.json();
                    
                    if (data.success) {
                        document.getElementById('content-id').value = data.data.id;
                        document.getElementById('content-title').value = data.data.title || '';
                        document.getElementById('content-slug').value = data.data.slug || '';
                        document.getElementById('content-category').value = data.data.category || '';
                        document.getElementById('content-image').value = data.data.featured_image || '';
                        document.getElementById('content-excerpt').value = data.data.excerpt || '';
                        document.getElementById('content-body').value = data.data.content || '';
                        document.getElementById('content-status').value = data.data.status || 'draft';
                    } else {
                        alert('Error loading content: ' + data.error);
                    }
                } else {
                    document.getElementById('editor-title').textContent = 'New ' + type;
                    document.getElementById('editor-form').reset();
                    document.getElementById('content-id').value = '';
                    builderEnabled = false;
                    blocks = [];
                    document.getElementById('rich-builder').style.display = 'none';
                    document.getElementById('builder-status').textContent = 'Rich builder: off';
                }
                
                // Setup slug generation
                setupSlugGeneration();
                
                // Setup real-time validation
                setupRealTimeValidation();
                
                // Show initial validation
                setTimeout(showValidationResults, 100);
            } catch (error) {
                alert('Error opening editor: ' + error.message);
            }
        }

        function closeEditor() {
            document.getElementById('editor-modal').classList.remove('active');
        }

        async function deleteContent(type, id) {
            if (!confirm(`Are you sure you want to delete this ${type}?`)) return;
            
            try {
                const formData = new FormData();
                formData.append('action', 'delete');
                formData.append('id', id);
                
                const response = await fetch(`api/content_api.php?type=${type}&action=delete`, {
                    method: 'POST',
                    body: formData,
                    credentials: 'same-origin'
                });
                
                const data = await response.json();
                if (data.success) {
                    alert('Successfully deleted!');
                    loadStories();
                    loadGuides();
                } else {
                    alert('Error: ' + (data.error || 'Failed to delete'));
                }
            } catch (error) {
                alert('Error: ' + error.message);
            }
        }

        async function updateUserRole(userId, newRole) {
            try {
                const formData = new FormData();
                formData.append('user_id', userId);
                formData.append('role', newRole);
                
                await fetch('api/user_api.php', {
                    method: 'POST',
                    body: formData,
                    credentials: 'same-origin'
                });
                
                alert('User role updated!');
                location.reload();
            } catch (error) {
                alert('Error updating role: ' + error.message);
            }
        }

        async function updateContactStatus(id, status) {
            const formData = new FormData();
            formData.append('id', id);
            formData.append('status', status);
            
            await fetch('api/contacts_api.php', {
                method: 'POST',
                body: formData,
                credentials: 'same-origin'
            });
        }

        async function approvePost(postId) {
            if (!confirm('Are you sure you want to approve this post? It will be published to the community.')) return;
            
            try {
                const formData = new FormData();
                formData.append('id', postId);
                formData.append('status', 'published');
                
                const response = await fetch('api/user_posts_api.php?action=update_status', {
                method: 'POST',
                body: formData
            });
                
                const data = await response.json();
                if (data.success) {
                    alert('Post approved and published!');
                    loadReviewPosts();
                } else {
                    alert('Error: ' + (data.error || 'Failed to approve post'));
                }
            } catch (error) {
                alert('Error: ' + error.message);
            }
        }

        async function rejectPost(postId) {
            if (!confirm('Are you sure you want to reject this post? The author will be notified.')) return;
            
            try {
                const formData = new FormData();
                formData.append('id', postId);
                formData.append('status', 'rejected');
                
                const response = await fetch('api/user_posts_api.php?action=update_status', {
                    method: 'POST',
                    body: formData,
                    credentials: 'same-origin'
                });
                
                const data = await response.json();
                if (data.success) {
                    alert('Post rejected.');
                    loadReviewPosts();
                } else {
                    alert('Error: ' + (data.error || 'Failed to reject post'));
                }
            } catch (error) {
                alert('Error: ' + error.message);
            }
        }

        async function viewPost(postId) {
            try {
                const response = await fetch(`api/user_posts_api.php?action=get&id=${postId}`, {
                    credentials: 'same-origin'
                });
                const data = await response.json();
                
                if (data.success) {
                    const post = data.data;
                    const content = JSON.parse(post.content || '{}');
                    
                    let contentHtml = '';
                    if (content.intro) {
                        contentHtml += `<h3>Introduction</h3><p>${content.intro}</p>`;
                    }
                    
                    if (content.sections && content.sections.length > 0) {
                        content.sections.forEach(section => {
                            switch(section.type) {
                                case 'paragraph':
                                    contentHtml += `<p>${section.data.text}</p>`;
                                    break;
                                case 'heading':
                                    contentHtml += `<h4>${section.data.text}</h4>`;
                                    break;
                                case 'image':
                                    if (section.data.url) {
                                        contentHtml += `<img src="${section.data.url}" alt="${section.data.alt || ''}" style="max-width: 100%; height: auto; margin: 1rem 0;">`;
                                    }
                                    break;
                                case 'list':
                                    const items = section.data.text.split('\n').filter(item => item.trim());
                                    contentHtml += '<ul>';
                                    items.forEach(item => {
                                        contentHtml += `<li>${item}</li>`;
                                    });
                                    contentHtml += '</ul>';
                                    break;
                            }
                        });
                    }
                    
                    const modal = document.createElement('div');
                    modal.className = 'modal active';
                    modal.innerHTML = `
                        <div class="modal-content" style="max-width: 800px;">
                            <button class="modal-close" onclick="this.closest('.modal').remove()">&times;</button>
                            <h2>${post.title}</h2>
                            <div style="margin-bottom: 1rem;">
                                <strong>Author:</strong> ${post.user_email}<br>
                                <strong>Category:</strong> ${post.category || 'None'}<br>
                                <strong>Status:</strong> <span class="badge badge-${post.status}">${post.status}</span><br>
                                <strong>Submitted:</strong> ${new Date(post.created_at).toLocaleString()}
                            </div>
                            ${post.featured_image ? `<img src="${post.featured_image}" alt="${post.title}" style="width: 100%; height: 300px; object-fit: cover; border-radius: 8px; margin-bottom: 1rem;">` : ''}
                            <div style="line-height: 1.6;">
                                ${contentHtml}
                            </div>
                        </div>
                    `;
                    
                    document.body.appendChild(modal);
                } else {
                    alert('Error loading post: ' + data.error);
                }
            } catch (error) {
                alert('Error: ' + error.message);
            }
        }

        // Form submissions
        document.getElementById('editor-form').onsubmit = async (e) => {
            e.preventDefault();
            
            // Validate content before submission
            const validation = validateContent();
            if (validation.errors.length > 0) {
                showNotification('Please fix the following errors before saving:\n\n' + validation.errors.join('\n'), 'error', 8000);
                return;
            }
            
            try {
                const formData = new FormData(e.target);
                const type = document.getElementById('content-type').value;
                const action = document.getElementById('content-id').value ? 'update' : 'create';
                
                formData.append('action', action);
                if (document.getElementById('content-id').value) {
                    formData.append('id', document.getElementById('content-id').value);
                }
                
                const response = await fetch(`api/content_api.php?type=${type}&action=${action}`, {
                    method: 'POST',
                    body: formData,
                    credentials: 'same-origin'
                });
                
                const data = await response.json();
                if (data.success) {
                    showNotification(`${action === 'create' ? 'Created' : 'Updated'} successfully!`, 'success');
                    closeEditor();
                    loadStories();
                    loadGuides();
                } else {
                    if (data.error && data.error.includes('Slug already exists')) {
                        showNotification('This slug is already in use. Please choose a different slug.', 'error', 5000);
                        document.getElementById('content-slug').focus();
                    } else {
                        showNotification('Error: ' + (data.error || 'Failed to save'), 'error');
                    }
                }
            } catch (error) {
                showNotification('Error: ' + error.message, 'error');
            }
        };

        function openCreateEditorModal() {
            document.getElementById('create-editor-modal').classList.add('active');
        }

        function closeCreateEditorModal() {
            document.getElementById('create-editor-modal').classList.remove('active');
        }

        function openCreateAdminModal() {
            document.getElementById('create-admin-modal').classList.add('active');
        }

        function closeCreateAdminModal() {
            document.getElementById('create-admin-modal').classList.remove('active');
        }

        // Create Editor Form
        document.getElementById('create-editor-form').onsubmit = async (e) => {
            e.preventDefault();
            
            const formData = new FormData(e.target);
            formData.append('role', 'editor');
            
            const response = await fetch('api/user_api.php', {
                method: 'POST',
                body: formData,
                credentials: 'same-origin'
            });
            
            const data = await response.json();
            if (data.success) {
                alert('Editor account created!');
                closeCreateEditorModal();
                location.reload();
            } else {
                alert('Error: ' + data.error);
            }
        };

        // Create Admin Form
        document.getElementById('create-admin-form').onsubmit = async (e) => {
            e.preventDefault();
            
            const formData = new FormData(e.target);
            formData.append('role', 'admin');
            
            const response = await fetch('api/user_api.php', {
                method: 'POST',
                body: formData,
                credentials: 'same-origin'
            });
            
            const data = await response.json();
            if (data.success) {
                alert('Admin account created!');
                closeCreateAdminModal();
                location.reload();
            } else {
                alert('Error: ' + data.error);
            }
        };

        // Testing functions
        async function testGitHub() {
            const resultsDiv = document.getElementById('test-results');
            resultsDiv.innerHTML = '<p>Testing GitHub connection...</p>';
            
            try {
                // const response = await fetch('api/test_github.php'); // removed test endpoint
                const data = await response.json();
                
                let html = '<div style="background: #F5F1E9; padding: 1rem; border-radius: 8px;">';
                html += '<h3 style="margin-bottom: 1rem;">GitHub Test Results</h3>';
                
                if (data.success) {
                    html += '<p style="color: green; margin: 0.5rem 0;">✓ GitHub connection successful</p>';
                } else {
                    html += '<p style="color: red; margin: 0.5rem 0;">✗ GitHub connection failed</p>';
                }
                
                html += '</div>';
                resultsDiv.innerHTML = html;
            } catch (error) {
                resultsDiv.innerHTML = '<p style="color: red;">Error: ' + error.message + '</p>';
            }
        }

        async function testDatabase() {
            // window.open('test_connection.php', '_blank'); // removed test page
        }

        async function testAPIs() {
            const resultsDiv = document.getElementById('test-results');
            resultsDiv.innerHTML = '<p>Testing APIs...</p>';
            
            try {
                const tests = [
                    { name: 'Content API', url: 'api/content_api.php?type=stories&action=list', credentials: true },
                    { name: 'Contacts API', url: 'api/contacts_api.php', credentials: true },
                    { name: 'User Posts API', url: 'api/user_posts_api.php?action=list', credentials: true }
                ];
                
                let html = '<div style="background: #F5F1E9; padding: 1rem; border-radius: 8px;">';
                html += '<h3 style="margin-bottom: 1rem;">API Test Results</h3>';
                
                for (const test of tests) {
                    try {
                        const options = { method: 'GET' };
                        if (test.credentials) {
                            options.credentials = 'same-origin';
                        }
                        const response = await fetch(test.url, options);
                        const data = await response.json();
                        
                        html += `<div style="margin-bottom: 0.5rem; padding: 0.5rem; background: ${response.ok ? '#d4edda' : '#f8d7da'}; border-radius: 4px;">`;
                        html += `<strong>${test.name}:</strong> ${response.ok ? '✅ OK' : '❌ Error'} (${response.status})`;
                        if (!response.ok && data.error) {
                            html += ` - ${data.error}`;
                        }
                        html += '</div>';
                    } catch (error) {
                        html += `<div style="margin-bottom: 0.5rem; padding: 0.5rem; background: #f8d7da; border-radius: 4px;">`;
                        html += `<strong>${test.name}:</strong> ❌ Network Error - ${error.message}`;
                        html += '</div>';
                    }
                }
                
                html += '</div>';
                resultsDiv.innerHTML = html;
            } catch (error) {
                resultsDiv.innerHTML = '<p style="color: red;">Error: ' + error.message + '</p>';
            }
        }

        async function toggleMaintenance(action) {
            try {
                const response = await fetch('api/maintenance.php', {
                    method: 'POST',
                    body: JSON.stringify({ action }),
                    headers: { 'Content-Type': 'application/json' },
                    credentials: 'same-origin'
                });
                
                const data = await response.json();
                if (data.success) {
                    alert('Maintenance mode ' + action + 'd successfully!');
                    loadMaintenanceStatus();
                } else {
                    alert('Error: ' + data.error);
                }
            } catch (error) {
                alert('Error: ' + error.message);
            }
        }

        async function loadMaintenanceStatus() {
            try {
                const response = await fetch('api/maintenance.php', {
                    credentials: 'same-origin'
                });
                const data = await response.json();
                
                const statusDiv = document.getElementById('maintenance-status');
                const isActive = data.success ? data.enabled : false;
                
                statusDiv.innerHTML = `
                    <div style="padding: 1rem; background: ${isActive ? '#ffebee' : '#e8f5e9'}; border-radius: 8px; border: 1px solid ${isActive ? '#c62828' : '#388e3c'};">
                        <p style="font-weight: 600; margin-bottom: 0.5rem;">Status: <span style="color: ${isActive ? '#c62828' : '#388e3c'};">${isActive ? 'ACTIVE' : 'INACTIVE'}</span></p>
                        <p style="font-size: 0.875rem; opacity: 0.8;">${isActive ? 'The site is currently in maintenance mode.' : 'The site is publicly accessible.'}</p>
                        ${!data.success ? `<p style="color: red; font-size: 0.875rem;"><strong>Error:</strong> ${data.error}</p>` : ''}
                    </div>
                `;
            } catch (error) {
                console.error('Error loading maintenance status:', error);
                const statusDiv = document.getElementById('maintenance-status');
                statusDiv.innerHTML = `
                    <div style="padding: 1rem; background: #ffebee; border-radius: 8px; border: 1px solid #c62828;">
                        <p style="font-weight: 600; margin-bottom: 0.5rem; color: #c62828;">Error Loading Status</p>
                        <p style="font-size: 0.875rem; opacity: 0.8;">Failed to load maintenance status</p>
                    </div>
                `;
            }
        }

        // Generate UUID function for cross-browser compatibility
        function generateUUID() {
            return 'xxxxxxxx-xxxx-4xxx-yxxx-xxxxxxxxxxxx'.replace(/[xy]/g, function(c) {
                const r = Math.random() * 16 | 0;
                const v = c == 'x' ? r : (r & 0x3 | 0x8);
                return v.toString(16);
            });
        }
        
        // Generate slug from title
        function generateSlugFromTitle(title) {
            return title
                .toLowerCase()
                .replace(/[^a-z0-9\s-]/g, '') // Remove special characters
                .replace(/\s+/g, '-') // Replace spaces with hyphens
                .replace(/-+/g, '-') // Replace multiple hyphens with single
                .replace(/^-|-$/g, ''); // Remove leading/trailing hyphens
        }
        
        // Drag and drop functionality for blocks
        function setupDragAndDrop() {
            const container = document.getElementById('blocks-container');
            if (!container) return;
            
            // Make container sortable
            container.addEventListener('dragover', (e) => {
                e.preventDefault();
                e.dataTransfer.dropEffect = 'move';
            });
            
            container.addEventListener('drop', (e) => {
                e.preventDefault();
                const draggedId = e.dataTransfer.getData('text/plain');
                const dropTarget = e.target.closest('.block-item');
                
                if (dropTarget && draggedId) {
                    const draggedIndex = blocks.findIndex(block => block.id === draggedId);
                    const dropIndex = blocks.findIndex(block => block.id === dropTarget.dataset.blockId);
                    
                    if (draggedIndex !== -1 && dropIndex !== -1 && draggedIndex !== dropIndex) {
                        // Move block in array
                        const draggedBlock = blocks.splice(draggedIndex, 1)[0];
                        blocks.splice(dropIndex, 0, draggedBlock);
                        
                        // Re-render blocks
                        renderBlocks();
                    }
                }
            });
        }
        
        // Enhanced block actions
        function addBlockActions(blockElement, blockId) {
            const actionsDiv = document.createElement('div');
            actionsDiv.className = 'block-actions';
            actionsDiv.style.cssText = `
                position: absolute;
                top: 0.5rem;
                right: 0.5rem;
                display: flex;
                gap: 0.25rem;
                opacity: 0;
                transition: opacity 0.2s;
            `;
            
            // Duplicate button
            const duplicateBtn = document.createElement('button');
            duplicateBtn.innerHTML = '<i class="fas fa-copy"></i>';
            duplicateBtn.title = 'Duplicate Block';
            duplicateBtn.className = 'btn btn-sm btn-outline';
            duplicateBtn.style.cssText = 'padding: 0.25rem; width: 2rem; height: 2rem;';
            duplicateBtn.onclick = () => duplicateBlock(blockId);
            
            // Delete button
            const deleteBtn = document.createElement('button');
            deleteBtn.innerHTML = '<i class="fas fa-trash"></i>';
            deleteBtn.title = 'Delete Block';
            deleteBtn.className = 'btn btn-sm btn-outline';
            deleteBtn.style.cssText = 'padding: 0.25rem; width: 2rem; height: 2rem; color: #dc3545;';
            deleteBtn.onclick = () => deleteBlock(blockId);
            
            // Drag handle
            const dragHandle = document.createElement('div');
            dragHandle.innerHTML = '<i class="fas fa-grip-vertical"></i>';
            dragHandle.title = 'Drag to Reorder';
            dragHandle.style.cssText = `
                padding: 0.25rem;
                width: 2rem;
                height: 2rem;
                display: flex;
                align-items: center;
                justify-content: center;
                cursor: move;
                color: #6c757d;
            `;
            dragHandle.draggable = true;
            dragHandle.addEventListener('dragstart', (e) => {
                e.dataTransfer.setData('text/plain', blockId);
                e.dataTransfer.effectAllowed = 'move';
            });
            
            actionsDiv.appendChild(duplicateBtn);
            actionsDiv.appendChild(deleteBtn);
            actionsDiv.appendChild(dragHandle);
            
            blockElement.appendChild(actionsDiv);
            
            // Show actions on hover
            blockElement.addEventListener('mouseenter', () => {
                actionsDiv.style.opacity = '1';
            });
            
            blockElement.addEventListener('mouseleave', () => {
                actionsDiv.style.opacity = '0';
            });
        }
        
        // Duplicate block function
        function duplicateBlock(blockId) {
            const blockIndex = blocks.findIndex(block => block.id === blockId);
            if (blockIndex !== -1) {
                const originalBlock = blocks[blockIndex];
                const duplicatedBlock = {
                    ...originalBlock,
                    id: generateUUID()
                };
                
                blocks.splice(blockIndex + 1, 0, duplicatedBlock);
                renderBlocks();
            }
        }
        
        // Delete block function
        function deleteBlock(blockId) {
            if (confirm('Are you sure you want to delete this block?')) {
                blocks = blocks.filter(block => block.id !== blockId);
                renderBlocks();
            }
        }
        
        // Move block function
        function moveBlock(blockId, direction) {
            const index = blocks.findIndex(block => block.id === blockId);
            if (index === -1) return;
            
            if (direction === 'up' && index > 0) {
                [blocks[index], blocks[index - 1]] = [blocks[index - 1], blocks[index]];
                renderBlocks();
            } else if (direction === 'down' && index < blocks.length - 1) {
                [blocks[index], blocks[index + 1]] = [blocks[index + 1], blocks[index]];
                renderBlocks();
            }
        }
        
        // Remove block function (alias for deleteBlock)
        function removeBlock(blockId) {
            deleteBlock(blockId);
        }
        
        // Content validation system
        function validateContent() {
            const errors = [];
            const warnings = [];
            
            // Validate title
            const title = document.getElementById('content-title').value.trim();
            if (!title) {
                errors.push('Title is required');
            } else if (title.length < 3) {
                warnings.push('Title is very short (less than 3 characters)');
            } else if (title.length > 255) {
                errors.push('Title is too long (maximum 255 characters)');
            }
            
            // Validate slug
            const slug = document.getElementById('content-slug').value.trim();
            if (!slug) {
                errors.push('Slug is required');
            } else if (!/^[a-z0-9-]+$/.test(slug)) {
                errors.push('Slug can only contain lowercase letters, numbers, and hyphens');
            } else if (slug.length > 255) {
                errors.push('Slug is too long (maximum 255 characters)');
            }
            
            // Validate excerpt
            const excerpt = document.getElementById('content-excerpt').value.trim();
            if (excerpt && excerpt.length > 500) {
                warnings.push('Excerpt is quite long (over 500 characters)');
            }
            
            // Validate content blocks
            if (builderEnabled && blocks.length === 0) {
                warnings.push('No content blocks added yet');
            }
            
            // Check for empty blocks
            blocks.forEach((block, index) => {
                if (block.type === 'heading' || block.type === 'subheading') {
                    if (!block.data.text || block.data.text.trim() === '') {
                        warnings.push(`Block ${index + 1} (${block.type}) is empty`);
                    }
                } else if (block.type === 'paragraph') {
                    if (!block.data.text || block.data.text.trim() === '') {
                        warnings.push(`Block ${index + 1} (paragraph) is empty`);
                    }
                } else if (block.type === 'image') {
                    if (!block.data.url || block.data.url.trim() === '') {
                        warnings.push(`Block ${index + 1} (image) has no URL`);
                    }
                }
            });
            
            return { errors, warnings };
        }
        
        // Show validation results
        function showValidationResults() {
            const validation = validateContent();
            const statusDiv = document.getElementById('validation-status');
            
            if (!statusDiv) return;
            
            let html = '<div style="margin-top: 1rem; padding: 1rem; border-radius: 8px;">';
            
            if (validation.errors.length === 0 && validation.warnings.length === 0) {
                html += '<div style="background: #d4edda; color: #155724; padding: 0.5rem; border-radius: 4px;">';
                html += '<i class="fas fa-check-circle"></i> Content validation passed';
                html += '</div>';
            } else {
                if (validation.errors.length > 0) {
                    html += '<div style="background: #f8d7da; color: #721c24; padding: 0.5rem; border-radius: 4px; margin-bottom: 0.5rem;">';
                    html += '<strong><i class="fas fa-exclamation-triangle"></i> Errors:</strong><ul style="margin: 0.5rem 0 0 1rem;">';
                    validation.errors.forEach(error => {
                        html += `<li>${error}</li>`;
                    });
                    html += '</ul></div>';
                }
                
                if (validation.warnings.length > 0) {
                    html += '<div style="background: #fff3cd; color: #856404; padding: 0.5rem; border-radius: 4px;">';
                    html += '<strong><i class="fas fa-info-circle"></i> Warnings:</strong><ul style="margin: 0.5rem 0 0 1rem;">';
                    validation.warnings.forEach(warning => {
                        html += `<li>${warning}</li>`;
                    });
                    html += '</ul></div>';
                }
            }
            
            html += '</div>';
            statusDiv.innerHTML = html;
        }
        
        // Real-time validation
        function setupRealTimeValidation() {
            const titleField = document.getElementById('content-title');
            const slugField = document.getElementById('content-slug');
            const excerptField = document.getElementById('content-excerpt');
            
            if (titleField) {
                titleField.addEventListener('input', showValidationResults);
            }
            if (slugField) {
                slugField.addEventListener('input', showValidationResults);
            }
            if (excerptField) {
                excerptField.addEventListener('input', showValidationResults);
            }
        }
        
        // Enhanced loading states
        function showLoading(elementId, message = 'Loading...') {
            const element = document.getElementById(elementId);
            if (element) {
                element.innerHTML = `
                    <div style="display: flex; align-items: center; justify-content: center; padding: 2rem;">
                        <div style="display: flex; flex-direction: column; align-items: center; gap: 1rem;">
                            <div style="width: 2rem; height: 2rem; border: 3px solid #f3f3f3; border-top: 3px solid #007bff; border-radius: 50%; animation: spin 1s linear infinite;"></div>
                            <p style="margin: 0; color: #6c757d;">${message}</p>
                        </div>
                    </div>
                    <style>
                        @keyframes spin {
                            0% { transform: rotate(0deg); }
                            100% { transform: rotate(360deg); }
                        }
                    </style>
                `;
            }
        }
        
        // Enhanced success/error notifications
        function showNotification(message, type = 'success', duration = 3000) {
            const notification = document.createElement('div');
            notification.style.cssText = `
                position: fixed;
                top: 20px;
                right: 20px;
                padding: 1rem 1.5rem;
                border-radius: 8px;
                color: white;
                font-weight: 500;
                z-index: 10000;
                box-shadow: 0 4px 12px rgba(0,0,0,0.15);
                transform: translateX(100%);
                transition: transform 0.3s ease;
            `;
            
            if (type === 'success') {
                notification.style.background = 'linear-gradient(135deg, #28a745, #20c997)';
            } else if (type === 'error') {
                notification.style.background = 'linear-gradient(135deg, #dc3545, #e74c3c)';
            } else if (type === 'warning') {
                notification.style.background = 'linear-gradient(135deg, #ffc107, #fd7e14)';
            } else {
                notification.style.background = 'linear-gradient(135deg, #007bff, #6f42c1)';
            }
            
            notification.innerHTML = `
                <div style="display: flex; align-items: center; gap: 0.5rem;">
                    <i class="fas ${type === 'success' ? 'fa-check-circle' : type === 'error' ? 'fa-exclamation-circle' : type === 'warning' ? 'fa-exclamation-triangle' : 'fa-info-circle'}"></i>
                    <span>${message}</span>
                </div>
            `;
            
            document.body.appendChild(notification);
            
            // Animate in
            setTimeout(() => {
                notification.style.transform = 'translateX(0)';
            }, 100);
            
            // Auto remove
            setTimeout(() => {
                notification.style.transform = 'translateX(100%)';
                setTimeout(() => {
                    if (notification.parentNode) {
                        notification.parentNode.removeChild(notification);
                    }
                }, 300);
            }, duration);
        }
        
        // Enhanced button states
        function setButtonLoading(button, loading = true) {
            if (loading) {
                button.dataset.originalText = button.innerHTML;
                button.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Loading...';
                button.disabled = true;
            } else {
                button.innerHTML = button.dataset.originalText || button.innerHTML;
                button.disabled = false;
            }
        }

        function toggleBuilder() {
            builderEnabled = !builderEnabled;
            const builder = document.getElementById('rich-builder');
            const status = document.getElementById('builder-status');
            const previewBtn = document.getElementById('preview-btn');
            const exportBtn = document.getElementById('export-btn');
            
            builder.style.display = builderEnabled ? 'block' : 'none';
            status.textContent = `Rich builder: ${builderEnabled ? 'on' : 'off'}`;
            previewBtn.style.display = builderEnabled ? 'inline-flex' : 'none';
            exportBtn.style.display = builderEnabled ? 'inline-flex' : 'none';
            
            if (builderEnabled && blocks.length === 0) {
                try {
                    const raw = document.getElementById('content-body').value.trim();
                    if (raw) {
                    const parsed = JSON.parse(raw);
                    if (Array.isArray(parsed)) {
                        blocks = parsed;
                        renderBlocks();
                        } else {
                            // Convert plain text to blocks
                            convertTextToBlocks(raw);
                        }
                    }
                } catch (e) {
                    // Convert plain text to blocks
                    const raw = document.getElementById('content-body').value.trim();
                    if (raw) {
                        convertTextToBlocks(raw);
                    }
                }
            }
            
            // Update container class
            const container = document.getElementById('blocks-container');
            if (builderEnabled) {
                container.classList.toggle('has-blocks', blocks.length > 0);
            }
        }
        
        function convertTextToBlocks(text) {
            const lines = text.split('\n').filter(line => line.trim());
            blocks = [];
            
            lines.forEach(line => {
                const trimmed = line.trim();
                if (trimmed.startsWith('# ')) {
                    blocks.push({
                        id: generateUUID(),
                        type: 'heading',
                        data: { text: trimmed.substring(2), level: 1 }
                    });
                } else if (trimmed.startsWith('## ')) {
                    blocks.push({
                        id: generateUUID(),
                        type: 'subheading',
                        data: { text: trimmed.substring(3), level: 2 }
                    });
                } else if (trimmed.startsWith('> ')) {
                    blocks.push({
                        id: generateUUID(),
                        type: 'blockquote',
                        data: { text: trimmed.substring(2) }
                    });
                } else if (trimmed.startsWith('- ') || trimmed.startsWith('* ')) {
                    blocks.push({
                        id: generateUUID(),
                        type: 'list',
                        data: { items: [trimmed.substring(2)] }
                    });
                } else if (trimmed.length > 0) {
                    blocks.push({
                        id: generateUUID(),
                        type: 'paragraph',
                        data: { text: trimmed }
                    });
                }
            });
            
            renderBlocks();
        }

        function addBlock(type) {
            const newBlock = {
                id: generateUUID(),
                type,
                data: {}
            };
            
            // Initialize block data based on type
            switch(type) {
                case 'heading':
                    newBlock.data = { text: 'A Heading for a New Section', level: 1 };
                    break;
                case 'subheading':
                    newBlock.data = { text: 'A Sub-heading for Finer Details', level: 2 };
                    break;
                case 'paragraph':
                    newBlock.data = { text: 'Write your paragraph here...' };
                    break;
                case 'blockquote':
                    newBlock.data = { text: '"Quote goes here."' };
                    break;
                case 'list':
                    newBlock.data = { items: ['First item', 'Second item'] };
                    break;
                case 'numbered-list':
                    newBlock.data = { items: ['First numbered item', 'Second numbered item'] };
                    break;
                case 'image':
                    newBlock.data = { url: '', alt: 'Descriptive alt text', caption: '' };
                    break;
                case 'gallery':
                    newBlock.data = { images: [{ url: '', alt: '', caption: '' }] };
                    break;
                case 'video':
                    newBlock.data = { url: '', title: 'Video Title', description: '' };
                    break;
                case 'divider':
                    newBlock.data = { style: 'line' };
                    break;
                default:
                    newBlock.data = { text: 'New content block' };
            }
            
            blocks.push(newBlock);
            renderBlocks();
        }

        function removeBlock(id) {
            blocks = blocks.filter(b => b.id !== id);
            renderBlocks();
        }

        function duplicateLastBlock() {
            if (blocks.length === 0) return;
            const lastBlock = blocks[blocks.length - 1];
            const duplicatedBlock = {
                id: generateUUID(),
                type: lastBlock.type,
                data: JSON.parse(JSON.stringify(lastBlock.data)) // Deep copy
            };
            blocks.push(duplicatedBlock);
            renderBlocks();
        }
        
        function clearAllBlocks() {
            if (confirm('Are you sure you want to clear all blocks? This cannot be undone.')) {
                blocks = [];
                renderBlocks();
            }
        }
        
        function previewContent() {
            const previewWindow = window.open('', '_blank', 'width=800,height=600');
            const html = generatePreviewHTML();
            previewWindow.document.write(html);
            previewWindow.document.close();
        }
        
        function exportContent() {
            const content = {
                blocks: blocks,
                html: generatePreviewHTML(),
                json: JSON.stringify(blocks, null, 2)
            };
            
            const blob = new Blob([JSON.stringify(content, null, 2)], { type: 'application/json' });
            const url = URL.createObjectURL(blob);
            const a = document.createElement('a');
            a.href = url;
            a.download = `content-export-${Date.now()}.json`;
            a.click();
            URL.revokeObjectURL(url);
        }
        
        function loadTemplate() {
            if (!confirm('This will replace all current content with the Yucca Club welcome template. Continue?')) {
                return;
            }
            
            // Enable rich builder if not already enabled
            if (!builderEnabled) {
                builderEnabled = true;
                const builder = document.getElementById('rich-builder');
                const status = document.getElementById('builder-status');
                const previewBtn = document.getElementById('preview-btn');
                const exportBtn = document.getElementById('export-btn');
                
                builder.style.display = 'block';
                status.textContent = 'Rich builder: on';
                previewBtn.style.display = 'inline-flex';
                exportBtn.style.display = 'inline-flex';
            }
            
            // Clear existing blocks
            blocks = [];
            
            // Load the comprehensive Yucca Club welcome template
            const templateBlocks = [
                // Main heading
                {
                    id: generateUUID(),
                    type: 'heading',
                    data: { text: 'Welcome to Yucca Club', level: 1 }
                },
                
                // Description paragraph
                {
                    id: generateUUID(),
                    type: 'paragraph',
                    data: { text: 'Yucca Club was built to tell the stories that live here. From Las Cruces to El Paso, Alamogordo, Cloudcroft, Silver City, Ruidoso, Juárez, Tucson, Phoenix, Hatch, Deming, Mesilla, and everywhere in between.\n\nThis is a space for real people, local stories, and honest perspectives.' }
                },
                
                // What You'll Find section
                {
                    id: generateUUID(),
                    type: 'heading',
                    data: { text: "What You'll Find", level: 2 }
                },
                
                {
                    id: generateUUID(),
                    type: 'subheading',
                    data: { text: 'Local Guides', level: 3 }
                },
                
                {
                    id: generateUUID(),
                    type: 'list',
                    data: { 
                        items: [
                            'The best food spots, from hole-in-the-wall gems to family recipes passed down through generations',
                            'Hiking trails locals actually use, from the Organ Mountains to White Sands',
                            'Hangouts where the community gathers — coffee shops, breweries, and gathering places',
                            'Hidden gems tourists miss but locals treasure'
                        ]
                    }
                },
                
                {
                    id: generateUUID(),
                    type: 'subheading',
                    data: { text: 'Community Features', level: 3 }
                },
                
                {
                    id: generateUUID(),
                    type: 'list',
                    data: { 
                        items: [
                            'Artists who capture the Southwest\'s unique beauty and culture',
                            'Small businesses that define our communities',
                            'People doing things their own way, creating authentic experiences',
                            'Entrepreneurs building something meaningful in the desert'
                        ]
                    }
                },
                
                {
                    id: generateUUID(),
                    type: 'subheading',
                    data: { text: 'Local Happenings', level: 3 }
                },
                
                {
                    id: generateUUID(),
                    type: 'list',
                    data: { 
                        items: [
                            'Community events that bring people together',
                            'Cultural celebrations that honor our diverse heritage',
                            'Seasonal traditions that mark time in the Southwest',
                            'Local festivals, markets, and gatherings worth attending'
                        ]
                    }
                },
                
                {
                    id: generateUUID(),
                    type: 'subheading',
                    data: { text: 'Southwest Culture', level: 3 }
                },
                
                {
                    id: generateUUID(),
                    type: 'list',
                    data: { 
                        items: [
                            'What makes our culture different from anywhere else',
                            'Traditions that connect us to the land and each other',
                            'Stories of resilience, creativity, and community spirit',
                            'The unique blend of cultures that defines the Borderland region'
                        ]
                    }
                },
                
                // Image
                {
                    id: generateUUID(),
                    type: 'image',
                    data: { 
                        url: 'https://nicholasxdavis.github.io/BN-db1/img/southern.png',
                        alt: 'Southern New Mexico landscape',
                        caption: 'The beautiful landscapes of Southern New Mexico'
                    }
                },
                
                // Why We're Doing This section
                {
                    id: generateUUID(),
                    type: 'heading',
                    data: { text: "Why We're Doing This", level: 2 }
                },
                
                {
                    id: generateUUID(),
                    type: 'paragraph',
                    data: { text: 'Too often, this region gets overlooked. People pass through without seeing the history, the flavor, and the work that goes into keeping it alive. We\'re here to change that.\n\nYucca Club is about connection — showing what\'s growing, what\'s worth visiting, and who\'s making things happen across the desert.' }
                },
                
                // Our Mission section
                {
                    id: generateUUID(),
                    type: 'heading',
                    data: { text: 'Our Mission', level: 2 }
                },
                
                {
                    id: generateUUID(),
                    type: 'list',
                    data: { 
                        items: [
                            'Amplify Local Voices: Give a platform to the stories that matter to our communities',
                            'Preserve Culture: Document traditions, recipes, and ways of life before they\'re lost',
                            'Build Community: Connect people across the Southwest who share common values',
                            'Support Local: Highlight businesses, artists, and organizations that make our region special'
                        ]
                    }
                },
                
                // Our Values section
                {
                    id: generateUUID(),
                    type: 'heading',
                    data: { text: 'Our Values', level: 2 }
                },
                
                {
                    id: generateUUID(),
                    type: 'list',
                    data: { 
                        items: [
                            'Authenticity: Real stories from real people, no fluff or fake experiences',
                            'Respect: Honoring the land, cultures, and people who call this place home',
                            'Community: Building bridges between cities, towns, and cultures',
                            'Quality: Curating content that\'s worth your time and attention'
                        ]
                    }
                },
                
                // The Southwest We Cover section
                {
                    id: generateUUID(),
                    type: 'heading',
                    data: { text: 'The Southwest We Cover', level: 2 }
                },
                
                {
                    id: generateUUID(),
                    type: 'image',
                    data: { 
                        url: 'https://nicholasxdavis.github.io/BN-db1/img/southwest.png',
                        alt: 'Southwest region map',
                        caption: 'The Southwest region we cover'
                    }
                },
                
                {
                    id: generateUUID(),
                    type: 'subheading',
                    data: { text: 'New Mexico', level: 3 }
                },
                
                {
                    id: generateUUID(),
                    type: 'list',
                    data: { 
                        items: [
                            'Las Cruces: The heart of our operation, where desert meets mountains',
                            'Alamogordo: Gateway to White Sands and space history',
                            'Cloudcroft: Cool mountain escape in the Sacramento Mountains',
                            'Silver City: Historic mining town with a vibrant arts scene',
                            'Ruidoso: Mountain town with skiing, hiking, and horse racing',
                            'Hatch: Chile capital of the world',
                            'Deming: Crossroads of the Southwest',
                            'Mesilla: Historic plaza town with deep cultural roots'
                        ]
                    }
                },
                
                {
                    id: generateUUID(),
                    type: 'subheading',
                    data: { text: 'Texas', level: 3 }
                },
                
                {
                    id: generateUUID(),
                    type: 'list',
                    data: { 
                        items: [
                            'El Paso: Border city with rich Mexican-American culture',
                            'West Texas: Vast landscapes and small-town charm'
                        ]
                    }
                },
                
                {
                    id: generateUUID(),
                    type: 'subheading',
                    data: { text: 'Mexico', level: 3 }
                },
                
                {
                    id: generateUUID(),
                    type: 'list',
                    data: { 
                        items: [
                            'Juárez: Sister city to El Paso, full of life and cultural exchange'
                        ]
                    }
                },
                
                {
                    id: generateUUID(),
                    type: 'subheading',
                    data: { text: 'Arizona', level: 3 }
                },
                
                {
                    id: generateUUID(),
                    type: 'list',
                    data: { 
                        items: [
                            'Tucson: Desert city with unique character',
                            'Phoenix: Urban Southwest with desert soul'
                        ]
                    }
                },
                
                // What Makes Us Different section
                {
                    id: generateUUID(),
                    type: 'heading',
                    data: { text: 'What Makes Us Different', level: 2 }
                },
                
                {
                    id: generateUUID(),
                    type: 'subheading',
                    data: { text: 'Hyperlocal Focus', level: 3 }
                },
                
                {
                    id: generateUUID(),
                    type: 'paragraph',
                    data: { text: 'We\'re not trying to be everything to everyone. We\'re focused on the Southwest — the Borderland region where New Mexico meets Texas and Mexico.' }
                },
                
                {
                    id: generateUUID(),
                    type: 'subheading',
                    data: { text: 'Community-Driven', level: 3 }
                },
                
                {
                    id: generateUUID(),
                    type: 'paragraph',
                    data: { text: 'Our content comes from locals, for locals. Real people, real businesses, real experiences.' }
                },
                
                {
                    id: generateUUID(),
                    type: 'subheading',
                    data: { text: 'Modern Platform', level: 3 }
                },
                
                {
                    id: generateUUID(),
                    type: 'paragraph',
                    data: { text: 'A clean, mobile-first design that looks great on your phone or desktop.' }
                },
                
                {
                    id: generateUUID(),
                    type: 'subheading',
                    data: { text: 'Quality Curation', level: 3 }
                },
                
                {
                    id: generateUUID(),
                    type: 'paragraph',
                    data: { text: 'We don\'t just post everything — we curate content that\'s actually worth your time.' }
                },
                
                // Blockquote
                {
                    id: generateUUID(),
                    type: 'blockquote',
                    data: { text: 'Launching January 1, 2026 — We\'re just getting started. New stories, local guides, and community features will start rolling out soon.' }
                },
                
                // What's Coming section
                {
                    id: generateUUID(),
                    type: 'heading',
                    data: { text: 'What\'s Coming', level: 2 }
                },
                
                {
                    id: generateUUID(),
                    type: 'list',
                    data: { 
                        items: [
                            'Weekly Story Features: Deep dives into local culture, people, and places',
                            'Interactive Maps: Discover hidden gems through curated location guides',
                            'Community Posts: A platform for locals to share their own stories',
                            'Event Calendar: Stay in the loop with what\'s happening near you',
                            'Local Business Directory: Find the best spots recommended by locals',
                            'Seasonal Guides: What to do, eat, and see throughout the year'
                        ]
                    }
                },
                
                // How to Get Involved section
                {
                    id: generateUUID(),
                    type: 'heading',
                    data: { text: 'How to Get Involved', level: 2 }
                },
                
                {
                    id: generateUUID(),
                    type: 'subheading',
                    data: { text: 'Stay Updated', level: 3 }
                },
                
                {
                    id: generateUUID(),
                    type: 'paragraph',
                    data: { text: 'Sign up for our newsletter to get the latest stories and guides delivered to your inbox.' }
                },
                
                {
                    id: generateUUID(),
                    type: 'subheading',
                    data: { text: 'Share Your Story', level: 3 }
                },
                
                {
                    id: generateUUID(),
                    type: 'paragraph',
                    data: { text: 'Have a story to tell or a favorite spot to recommend? We want to hear from you.' }
                },
                
                {
                    id: generateUUID(),
                    type: 'subheading',
                    data: { text: 'Support Local', level: 3 }
                },
                
                {
                    id: generateUUID(),
                    type: 'paragraph',
                    data: { text: 'Use our guides to discover and support local businesses, artists, and organizations.' }
                },
                
                {
                    id: generateUUID(),
                    type: 'subheading',
                    data: { text: 'Partner With Us', level: 3 }
                },
                
                {
                    id: generateUUID(),
                    type: 'paragraph',
                    data: { text: 'If you\'re a local business, artist, or organization — let\'s work together to tell your story.' }
                },
                
                // The Southwest Awaits section
                {
                    id: generateUUID(),
                    type: 'heading',
                    data: { text: 'The Southwest Awaits', level: 2 }
                },
                
                {
                    id: generateUUID(),
                    type: 'paragraph',
                    data: { text: 'This is your insider\'s guide to the real Southwest — not the tourist version, not the Instagram version, but the one locals know and love.\n\nFrom the Organ Mountains to White Sands, from Las Cruces to El Paso, from the smallest towns to the biggest cities — we\'re here to show what makes this place special.' }
                },
                
                // Final image
                {
                    id: generateUUID(),
                    type: 'image',
                    data: { 
                        url: 'https://nicholasxdavis.github.io/BN-db1/img/yuccaclub.png',
                        alt: 'Yucca Club logo',
                        caption: 'Yucca Club - Your Southwest Guide'
                    }
                },
                
                // Final Message section
                {
                    id: generateUUID(),
                    type: 'heading',
                    data: { text: 'Welcome to Yucca Club. Welcome home.', level: 2 }
                },
                
                {
                    id: generateUUID(),
                    type: 'paragraph',
                    data: { text: 'Crafted with love in Las Cruces, New Mexico.' }
                },
                
                // Final blockquote
                {
                    id: generateUUID(),
                    type: 'blockquote',
                    data: { text: 'Yucca Club — Where the Southwest comes alive through authentic stories, local guides, and community connection.' }
                }
            ];
            
            // Set the blocks
            blocks = templateBlocks;
            
            // Also populate the form fields
            document.getElementById('content-title').value = 'Welcome to Yucca Club';
            
            // Generate unique slug with timestamp
            const timestamp = new Date().toISOString().slice(0, 19).replace(/[-:]/g, '').replace('T', '-');
            const uniqueSlug = `welcome-to-yucca-club-${timestamp}`;
            document.getElementById('content-slug').value = uniqueSlug;
            
            document.getElementById('content-category').value = 'Yucca-Club';
            document.getElementById('content-image').value = 'https://www.blacnova.net/ui/img/hero.png';
            document.getElementById('content-excerpt').value = 'Yucca Club was built to tell the stories that live here. From Las Cruces to El Paso, Alamogordo, Cloudcroft, Silver City, Ruidoso, Juárez, Tucson, Phoenix, Hatch, Deming, Mesilla, and everywhere in between.';
            
            // Render the blocks
            renderBlocks();
            
            // Show success message
            alert('Yucca Club welcome template loaded successfully! You can now customize the content as needed.');
        }
        
        function generatePreviewHTML() {
            let html = '<!DOCTYPE html><html><head><title>Content Preview</title><style>body{font-family:Arial,sans-serif;max-width:800px;margin:0 auto;padding:20px;line-height:1.6;}h1,h2,h3{color:#333;}blockquote{border-left:4px solid #ccc;margin:0;padding-left:20px;font-style:italic;}code{background:#f4f4f4;padding:2px 4px;border-radius:3px;}img{max-width:100%;height:auto;}</style></head><body>';
            
            blocks.forEach(block => {
                switch(block.type) {
                    case 'heading':
                        html += `<h1>${block.data.text}</h1>`;
                        break;
                    case 'subheading':
                        html += `<h2>${block.data.text}</h2>`;
                        break;
                    case 'paragraph':
                        html += `<p>${block.data.text.replace(/\n\n/g, '</p><p>')}</p>`;
                        break;
                    case 'blockquote':
                        html += `<blockquote>${block.data.text}</blockquote>`;
                        break;
                    case 'list':
                        html += '<ul>';
                        block.data.items.forEach(item => html += `<li>${item}</li>`);
                        html += '</ul>';
                        break;
                    case 'numbered-list':
                        html += '<ol>';
                        block.data.items.forEach(item => html += `<li>${item}</li>`);
                        html += '</ol>';
                        break;
                    case 'image':
                        if (block.data.url) {
                            html += `<img src="${block.data.url}" alt="${block.data.alt}">`;
                            if (block.data.caption) html += `<p><em>${block.data.caption}</em></p>`;
                        }
                        break;
                    case 'divider':
                        html += '<hr>';
                        break;
                }
            });
            
            html += '</body></html>';
            return html;
        }

        function renderBlocks() {
            const container = document.getElementById('blocks-container');
            container.innerHTML = '';
            
            // Update container class
            container.classList.toggle('has-blocks', blocks.length > 0);
            
            blocks.forEach((block, index) => {
                const wrapper = document.createElement('div');
                wrapper.className = 'block-item';
                wrapper.setAttribute('data-type', block.type);
                wrapper.setAttribute('data-index', index);
                
                const header = document.createElement('div');
                header.className = 'block-header';
                header.innerHTML = `<strong>${block.type.replace('-', ' ')}</strong>`;
                
                const actions = document.createElement('div');
                actions.className = 'block-actions';
                actions.innerHTML = `
                    <button type="button" onclick="duplicateBlock('${block.id}')" class="btn btn-secondary btn-sm" title="Duplicate Block"><i class="fas fa-copy"></i></button>
                    <button type="button" onclick="moveBlock('${block.id}', 'up')" class="btn btn-secondary btn-sm" ${index === 0 ? 'disabled' : ''} title="Move Up">↑</button>
                    <button type="button" onclick="moveBlock('${block.id}', 'down')" class="btn btn-secondary btn-sm" ${index === blocks.length - 1 ? 'disabled' : ''} title="Move Down">↓</button>
                    <button type="button" onclick="removeBlock('${block.id}')" class="btn btn-danger btn-sm" title="Delete Block">×</button>
                `;
                header.appendChild(actions);
                wrapper.appendChild(header);
                
                const content = document.createElement('div');
                content.className = 'block-content';
                
                // Render content based on block type
                switch(block.type) {
                    case 'heading':
                    case 'subheading':
                        const headingInput = document.createElement('input');
                        headingInput.type = 'text';
                        headingInput.value = block.data.text || '';
                        headingInput.oninput = (e) => {
                            block.data.text = e.target.value;
                            updateContent();
                        };
                        headingInput.style.width = '100%';
                        headingInput.style.padding = '0.75rem';
                        headingInput.style.fontSize = block.type === 'heading' ? '1.2rem' : '1rem';
                        headingInput.style.fontWeight = '600';
                        content.appendChild(headingInput);
                        break;
                        
                    case 'paragraph':
                        const paragraphTextarea = document.createElement('textarea');
                        paragraphTextarea.value = block.data.text || '';
                        paragraphTextarea.oninput = (e) => {
                            block.data.text = e.target.value;
                            updateContent();
                        };
                        paragraphTextarea.style.width = '100%';
                        paragraphTextarea.style.minHeight = '120px';
                        paragraphTextarea.style.padding = '0.75rem';
                        paragraphTextarea.style.resize = 'vertical';
                        content.appendChild(paragraphTextarea);
                        break;
                        
                    case 'image':
                        const imageUrlInput = document.createElement('input');
                        imageUrlInput.type = 'url';
                        imageUrlInput.placeholder = 'https://example.com/image.jpg';
                        imageUrlInput.value = block.data.url || '';
                        imageUrlInput.oninput = (e) => {
                            block.data.url = e.target.value;
                            updateContent();
                        };
                        imageUrlInput.style.width = '100%';
                        imageUrlInput.style.padding = '0.5rem';
                        imageUrlInput.style.marginBottom = '0.5rem';
                        
                        const imageAltInput = document.createElement('input');
                        imageAltInput.type = 'text';
                        imageAltInput.placeholder = 'Alt text for accessibility';
                        imageAltInput.value = block.data.alt || '';
                        imageAltInput.oninput = (e) => {
                            block.data.alt = e.target.value;
                            updateContent();
                        };
                        imageAltInput.style.width = '100%';
                        imageAltInput.style.padding = '0.5rem';
                        imageAltInput.style.marginBottom = '0.5rem';
                        
                        const imageCaptionInput = document.createElement('input');
                        imageCaptionInput.type = 'text';
                        imageCaptionInput.placeholder = 'Image caption (optional)';
                        imageCaptionInput.value = block.data.caption || '';
                        imageCaptionInput.oninput = (e) => {
                            block.data.caption = e.target.value;
                            updateContent();
                        };
                        imageCaptionInput.style.width = '100%';
                        imageCaptionInput.style.padding = '0.5rem';
                        
                        content.appendChild(imageUrlInput);
                        content.appendChild(imageAltInput);
                        content.appendChild(imageCaptionInput);
                        break;
                        
                    case 'gallery':
                        const galleryContainer = document.createElement('div');
                        galleryContainer.style.border = '1px dashed #ccc';
                        galleryContainer.style.padding = '1rem';
                        galleryContainer.style.borderRadius = '6px';
                        
                        const images = block.data.images || [];
                        images.forEach((img, idx) => {
                            const imgWrapper = document.createElement('div');
                            imgWrapper.style.marginBottom = '1rem';
                            imgWrapper.style.padding = '0.5rem';
                            imgWrapper.style.border = '1px solid #eee';
                            imgWrapper.style.borderRadius = '4px';
                            
                    const urlInput = document.createElement('input');
                    urlInput.type = 'url';
                            urlInput.placeholder = 'Image URL';
                            urlInput.value = img.url || '';
                            urlInput.oninput = (e) => {
                                img.url = e.target.value;
                                updateContent();
                            };
                    urlInput.style.width = '100%';
                    urlInput.style.padding = '0.5rem';
                    urlInput.style.marginBottom = '0.5rem';
                    
                    const altInput = document.createElement('input');
                    altInput.type = 'text';
                    altInput.placeholder = 'Alt text';
                            altInput.value = img.alt || '';
                            altInput.oninput = (e) => {
                                img.alt = e.target.value;
                                updateContent();
                            };
                    altInput.style.width = '100%';
                    altInput.style.padding = '0.5rem';
                            altInput.style.marginBottom = '0.5rem';
                            
                            const captionInput = document.createElement('input');
                            captionInput.type = 'text';
                            captionInput.placeholder = 'Caption';
                            captionInput.value = img.caption || '';
                            captionInput.oninput = (e) => {
                                img.caption = e.target.value;
                                updateContent();
                            };
                            captionInput.style.width = '100%';
                            captionInput.style.padding = '0.5rem';
                            
                            const removeBtn = document.createElement('button');
                            removeBtn.type = 'button';
                            removeBtn.textContent = 'Remove Image';
                            removeBtn.className = 'btn btn-danger btn-sm';
                            removeBtn.style.marginTop = '0.5rem';
                            removeBtn.onclick = () => {
                                images.splice(idx, 1);
                                renderBlocks();
                            };
                            
                            imgWrapper.appendChild(urlInput);
                            imgWrapper.appendChild(altInput);
                            imgWrapper.appendChild(captionInput);
                            imgWrapper.appendChild(removeBtn);
                            galleryContainer.appendChild(imgWrapper);
                        });
                        
                        const addImageBtn = document.createElement('button');
                        addImageBtn.type = 'button';
                        addImageBtn.textContent = '+ Add Image';
                        addImageBtn.className = 'btn btn-secondary btn-sm';
                        addImageBtn.onclick = () => {
                            images.push({ url: '', alt: '', caption: '' });
                            renderBlocks();
                        };
                        
                        content.appendChild(galleryContainer);
                        content.appendChild(addImageBtn);
                        break;
                        
                    case 'blockquote':
                        const quoteTextarea = document.createElement('textarea');
                        quoteTextarea.value = block.data.text || '';
                        quoteTextarea.oninput = (e) => {
                            block.data.text = e.target.value;
                            updateContent();
                        };
                        quoteTextarea.style.width = '100%';
                        quoteTextarea.style.minHeight = '100px';
                        quoteTextarea.style.padding = '0.75rem';
                        quoteTextarea.style.fontStyle = 'italic';
                        quoteTextarea.style.borderLeft = '4px solid #ccc';
                        content.appendChild(quoteTextarea);
                        break;
                        
                    case 'list':
                    case 'numbered-list':
                    const items = block.data.items || [];
                    items.forEach((item, idx) => {
                            const itemInput = document.createElement('input');
                            itemInput.type = 'text';
                            itemInput.value = item;
                            itemInput.oninput = (e) => {
                                items[idx] = e.target.value;
                                updateContent();
                            };
                            itemInput.style.width = '100%';
                            itemInput.style.padding = '0.5rem';
                            itemInput.style.marginBottom = '0.5rem';
                            itemInput.style.borderLeft = '3px solid #007bff';
                            itemInput.style.paddingLeft = '0.75rem';
                            content.appendChild(itemInput);
                        });
                        
                        const addItemBtn = document.createElement('button');
                        addItemBtn.type = 'button';
                        addItemBtn.textContent = '+ Add Item';
                        addItemBtn.className = 'btn btn-secondary btn-sm';
                        addItemBtn.onclick = () => {
                        items.push('');
                        renderBlocks();
                    };
                        content.appendChild(addItemBtn);
                        break;
                        
                        
                    case 'video':
                        const videoUrlInput = document.createElement('input');
                        videoUrlInput.type = 'url';
                        videoUrlInput.placeholder = 'Video URL (YouTube, Vimeo, etc.)';
                        videoUrlInput.value = block.data.url || '';
                        videoUrlInput.oninput = (e) => {
                            block.data.url = e.target.value;
                            updateContent();
                        };
                        videoUrlInput.style.width = '100%';
                        videoUrlInput.style.padding = '0.5rem';
                        videoUrlInput.style.marginBottom = '0.5rem';
                        
                        const videoTitleInput = document.createElement('input');
                        videoTitleInput.type = 'text';
                        videoTitleInput.placeholder = 'Video title';
                        videoTitleInput.value = block.data.title || '';
                        videoTitleInput.oninput = (e) => {
                            block.data.title = e.target.value;
                            updateContent();
                        };
                        videoTitleInput.style.width = '100%';
                        videoTitleInput.style.padding = '0.5rem';
                        videoTitleInput.style.marginBottom = '0.5rem';
                        
                        const videoDescTextarea = document.createElement('textarea');
                        videoDescTextarea.placeholder = 'Video description';
                        videoDescTextarea.value = block.data.description || '';
                        videoDescTextarea.oninput = (e) => {
                            block.data.description = e.target.value;
                            updateContent();
                        };
                        videoDescTextarea.style.width = '100%';
                        videoDescTextarea.style.minHeight = '80px';
                        videoDescTextarea.style.padding = '0.5rem';
                        
                        content.appendChild(videoUrlInput);
                        content.appendChild(videoTitleInput);
                        content.appendChild(videoDescTextarea);
                        break;
                        
                    case 'divider':
                        const dividerPreview = document.createElement('div');
                        dividerPreview.style.textAlign = 'center';
                        dividerPreview.style.padding = '1rem';
                        dividerPreview.style.color = '#6c757d';
                        dividerPreview.innerHTML = '<hr style="border: none; border-top: 2px solid #dee2e6; margin: 0;">';
                        content.appendChild(dividerPreview);
                        break;
                }
                
                wrapper.appendChild(content);
                container.appendChild(wrapper);
            });
            
            updateContent();
        }
        
        function updateContent() {
            document.getElementById('content-body').value = JSON.stringify(blocks, null, 2);
        }
        
        function moveBlock(id, dir) {
            const idx = blocks.findIndex(b => b.id === id);
            if (idx < 0) return;
            const swapWith = dir === 'up' ? idx - 1 : idx + 1;
            if (swapWith < 0 || swapWith >= blocks.length) return;
            const tmp = blocks[idx];
            blocks[idx] = blocks[swapWith];
            blocks[swapWith] = tmp;
            renderBlocks();
        }

        // Load maintenance status on page load
        loadMaintenanceStatus();
    </script>
</body>
</html>
