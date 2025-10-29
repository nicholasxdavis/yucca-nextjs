<?php
// Community Page - User Posts
require_once '../../config.php';
require_once '../../auth_handler.php';

// Check if user is logged in
$is_logged_in = is_logged_in();
$user_email = $is_logged_in ? htmlspecialchars($_SESSION['user_email']) : '';
$user_id = $_SESSION['user_id'] ?? null;

// Get user's post usage for current month
$post_usage = [
    'current' => 0,
    'limit' => 5,
    'remaining' => 5
];

$community_posts = [];
$user_posts_count = 0;

if ($is_logged_in) {
    try {
        $conn = db_connect();
        $current_month = date('Y-m');
        
        // Get user's posts count
        $stmt = $conn->prepare("SELECT COUNT(*) as post_count FROM user_posts WHERE user_id = ?");
        $stmt->bind_param("i", $user_id);
        $stmt->execute();
        $result = $stmt->get_result();
        $user_posts_count = $result->fetch_assoc()['post_count'];
        $stmt->close();
        
        // Get post usage for current month
        $stmt = $conn->prepare("SELECT post_count FROM post_usage WHERE user_id = ? AND month = ?");
        $stmt->bind_param("is", $user_id, $current_month);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($row = $result->fetch_assoc()) {
            $post_usage['current'] = $row['post_count'];
            $post_usage['remaining'] = max(0, $post_usage['limit'] - $row['post_count']);
        }
        
        $stmt->close();
        $conn->close();
    } catch (Exception $e) {
        error_log("Post usage error: " . $e->getMessage());
    }
}

// Get published community posts
try {
    $conn = db_connect();
    $stmt = $conn->prepare("SELECT up.*, u.email as user_email FROM user_posts up JOIN users u ON up.user_id = u.id WHERE up.status = 'published' ORDER BY up.created_at DESC LIMIT 20");
    $stmt->execute();
    $result = $stmt->get_result();
    
    while ($row = $result->fetch_assoc()) {
        $community_posts[] = $row;
    }
    
    $stmt->close();
    $conn->close();
} catch (Exception $e) {
    error_log("Community posts error: " . $e->getMessage());
}

$page_title = "Community - Yucca Club";
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=5.0, user-scalable=yes">
    <meta name="mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-status-bar-style" content="default">
    <meta name="apple-mobile-web-app-title" content="Yucca Club">
    <meta name="format-detection" content="telephone=no">
    <title>Community Posts | Las Cruces & El Paso Local Stories | Yucca Club</title>
    
    <!-- SEO Meta Tags -->
    <meta name="description" content="Join the Yucca Club community! Share and discover local stories, experiences, and insights from Las Cruces, El Paso, and Southern New Mexico residents.">
    <meta name="keywords" content="Las Cruces community, El Paso community, Southern New Mexico stories, Borderland community, local experiences, Las Cruces residents, El Paso residents, New Mexico community, West Texas community">
    <meta name="author" content="Yucca Club">
    <meta name="robots" content="index, follow">
    <meta name="language" content="en-US">
    <meta name="geo.region" content="US-NM">
    <meta name="geo.placename" content="Las Cruces">
    <meta name="geo.position" content="32.3199;-106.7637">
    <meta name="ICBM" content="32.3199, -106.7637">
    <meta name="theme-color" content="#b8ba20">
    
    <!-- Canonical URL -->
    <link rel="canonical" href="https://www.yuccaclub.com/nav/community/">
    
    <!-- Open Graph / Facebook -->
    <meta property="og:type" content="website">
    <meta property="og:url" content="https://www.yuccaclub.com/nav/community/">
    <meta property="og:title" content="Community Posts | Las Cruces & El Paso Local Stories | Yucca Club">
    <meta property="og:description" content="Join the Yucca Club community! Share and discover local stories, experiences, and insights from Las Cruces, El Paso, and Southern New Mexico residents.">
    <meta property="og:image" content="https://www.yuccaclub.com/ui/img/social-share.png">
    <meta property="og:image:width" content="1200">
    <meta property="og:image:height" content="630">
    <meta property="og:image:alt" content="Yucca Club Community - Las Cruces El Paso Local Stories">
    <meta property="og:site_name" content="Yucca Club">
    <meta property="og:locale" content="en_US">
    
    <!-- Twitter -->
    <meta name="twitter:card" content="summary_large_image">
    <meta name="twitter:url" content="https://www.yuccaclub.com/nav/community/">
    <meta name="twitter:title" content="Community Posts | Las Cruces & El Paso Local Stories | Yucca Club">
    <meta name="twitter:description" content="Join the Yucca Club community! Share and discover local stories, experiences, and insights from Las Cruces, El Paso, and Southern New Mexico residents.">
    <meta name="twitter:image" content="https://www.yuccaclub.com/ui/img/social-share.png">
    <meta name="twitter:image:alt" content="Yucca Club Community - Las Cruces El Paso Local Stories">
    
    <!-- Favicon -->
    <link rel="icon" type="image/png" href="../../ui/img/favicon.png">
    <link rel="apple-touch-icon" href="../../ui/img/favicon.png">
    <link rel="shortcut icon" href="../../ui/img/favicon.png">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Lato:wght@400;700&family=Lora:wght@500;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="../../ui/css/styles.css">
    <link rel="stylesheet" href="../../ui/css/enhancements.css">
    <style>
        /* Community Page Styles - Matching Stories/Guides Design */
        .community-hero {
            padding: 2rem 1rem;
            text-align: center;
            background: linear-gradient(135deg, var(--desert-sand) 0%, var(--off-white) 100%);
        }
        
        .community-hero h1 {
            font-size: 2.5rem;
            margin-bottom: 1rem;
            color: var(--lobo-gray);
        }
        
        .community-hero p {
            font-size: 1.1rem;
            max-width: 600px;
            margin: 0 auto 2rem;
            opacity: 0.8;
        }
        
        .profile-container {
            background: var(--off-white);
            border-radius: 12px;
            padding: 2rem;
            margin-bottom: 2rem;
            box-shadow: 0 4px 12px rgba(0,0,0,0.1);
            border: 2px solid var(--desert-sand);
        }
        
        .profile-header {
            display: flex;
            align-items: center;
            gap: 1.5rem;
            margin-bottom: 1.5rem;
        }
        
        .profile-avatar {
            width: 60px;
            height: 60px;
            border-radius: 50%;
            background: var(--yucca-yellow);
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.5rem;
            color: white;
            font-weight: 700;
        }
        
        .profile-info h2 {
            margin-bottom: 0.25rem;
            color: var(--lobo-gray);
        }
        
        .profile-info p {
            opacity: 0.7;
            margin: 0;
        }
        
        .profile-stats {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(100px, 1fr));
            gap: 1rem;
        }
        
        .stat-item {
            text-align: center;
            padding: 0.75rem;
            background: white;
            border-radius: 8px;
            border: 1px solid var(--desert-sand);
        }
        
        .stat-value {
            font-size: 1.25rem;
            font-weight: 700;
            color: var(--yucca-yellow);
            margin-bottom: 0.25rem;
        }
        
        .stat-label {
            font-size: 0.875rem;
            opacity: 0.7;
        }
        
        .status-alert {
            padding: 1rem 1.5rem;
            border-radius: 8px;
            margin-bottom: 2rem;
            display: flex;
            align-items: center;
            gap: 0.75rem;
            border: 2px solid;
        }
        
        .status-alert.success {
            background: #d4edda;
            border-color: #28a745;
            color: #155724;
        }
        
        .status-alert.warning {
            background: #fff3cd;
            border-color: #ffc107;
            color: #856404;
        }
        
        .status-alert.info {
            background: #d1ecf1;
            border-color: #17a2b8;
            color: #0c5460;
        }
        
        .status-alert i {
            font-size: 1.5rem;
        }
        
        .status-alert p {
            margin: 0;
            flex: 1;
        }
        
        .community-posts {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 1.5rem;
            margin-top: 2rem;
        }
        
        .post-card {
            background: var(--off-white);
            border-radius: 8px;
            overflow: hidden;
            transition: transform 0.3s, box-shadow 0.3s;
            border: 2px solid transparent;
        }
        
        .post-card:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(0,0,0,0.1);
            border-color: var(--yucca-yellow);
        }
        
        .post-card img {
            width: 100%;
            height: 200px;
            object-fit: cover;
        }
        
        .post-card-content {
            padding: 1.25rem;
        }
        
        .post-card h3 {
            font-size: 1.25rem;
            margin-bottom: 0.5rem;
            color: var(--lobo-gray);
            line-height: 1.3;
        }
        
        .post-meta {
            display: flex;
            align-items: center;
            gap: 0.75rem;
            margin-bottom: 0.75rem;
            font-size: 0.85rem;
            color: #666;
            flex-wrap: wrap;
        }
        
        .post-meta i {
            opacity: 0.7;
        }
        
        .post-excerpt {
            font-size: 0.9rem;
            line-height: 1.5;
            color: var(--lobo-gray);
            margin-bottom: 1rem;
        }
        
        .read-more-btn {
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            color: var(--yucca-yellow);
            text-decoration: none;
            font-weight: 700;
            font-size: 0.9rem;
            transition: color 0.3s;
        }
        
        .read-more-btn:hover {
            color: var(--lobo-gray);
        }
        
        .empty-state {
            text-align: center;
            padding: 4rem 2rem;
            opacity: 0.7;
            grid-column: 1/-1;
        }
        
        .empty-state i {
            font-size: 4rem;
            margin-bottom: 1rem;
            opacity: 0.3;
            color: var(--yucca-yellow);
        }
        
        .empty-state h3 {
            font-size: 1.5rem;
            margin-bottom: 0.5rem;
            color: var(--lobo-gray);
        }
        
        .empty-state p {
            font-size: 1rem;
            color: var(--lobo-gray);
        }
        
        /* Mobile Responsive */
        @media (max-width: 768px) {
            .community-hero {
                padding: 1.5rem 1rem;
            }
            
            .community-hero h1 {
                font-size: 2rem;
            }
            
            .community-hero p {
                font-size: 1rem;
            }
            
            .profile-header {
                flex-direction: column;
                text-align: center;
            }
            
            .profile-stats {
                grid-template-columns: repeat(2, 1fr);
            }
            
            .community-posts {
                grid-template-columns: 1fr;
                gap: 1rem;
                margin-top: 1.5rem;
            }
            
            .post-card-content {
                padding: 1rem;
            }
            
            .post-card h3 {
                font-size: 1.1rem;
            }
            
            .post-meta {
                gap: 0.5rem;
                font-size: 0.8rem;
            }
            
            .post-card img {
                height: 150px;
            }
            
            .status-alert {
                padding: 0.875rem 1rem;
                font-size: 0.9rem;
            }
        }
        
        @media (max-width: 480px) {
            .community-hero h1 {
                font-size: 1.75rem;
            }
            
            .post-card h3 {
                font-size: 1rem;
            }
        }
        
        /* Loading States */
        .shimmer {
            background: linear-gradient(90deg, #f0f0f0 25%, #e0e0e0 50%, #f0f0f0 75%);
            background-size: 200% 100%;
            animation: shimmer 1.5s infinite;
        }
        
        @keyframes shimmer {
            0% { background-position: -200% 0; }
            100% { background-position: 200% 0; }
        }
        
        .post-card.loading {
            opacity: 0.7;
        }
        
        .post-card.loading .post-card-content {
            background: #f5f5f5;
        }
    </style>
</head>
<body>
    <div id="top-loader-bar"></div>
    
    <header class="site-header">
        <div class="container header-content">
            <a href="../../index.php" class="site-logo">
                <img class="logo-light" src="../../ui/img/logo.png" alt="Yucca Club Logo" style="width:180px;">
                <img class="logo-dark" src="../../ui/img/logo_dark.png" alt="Yucca Club Logo Dark" style="width:180px;">
            </a>
            <nav class="primary-nav">
                <ul>
                    <li><a href="../stories/index.php">Stories</a></li>
                    <li><a href="../guides/index.php">Guides</a></li>
                    <li><a href="../events/index.php">Events</a></li>
                    <li><a href="https://yucca.printify.me/" target="_blank">Shop</a></li>
                    <li><a href="index.php" class="active">Community</a></li>
                    <li><a href="../membership/index.php">Membership</a></li>
                </ul>
            </nav>
            <div class="header-actions">
                <?php if ($is_logged_in): ?>
                    <span class="desktop-only" style="font-size: 14px; font-weight: 700;"><?= $user_email ?></span>
                    <?php if ($user_posts_count > 0): ?>
                    <a href="../../my-posts.php" id="my-posts" aria-label="My posts" title="My posts" class="desktop-only" style="font-size: 14px; color: var(--yucca-yellow); margin-right: 0.5rem;">
                        <i class="fas fa-file-alt" aria-hidden="true"></i>
                    </a>
                    <?php endif; ?>
                    <a href="../../create-post.php" id="create-post" aria-label="Create post" title="Create post" class="desktop-only" style="font-size: 14px; color: var(--yucca-yellow); margin-right: 0.5rem;">
                        <i class="fas fa-edit" aria-hidden="true"></i>
                    </a>
                    <a href="../../index.php?logout=true" aria-label="Logout" class="desktop-only">
                        <i class="fas fa-sign-out-alt" aria-hidden="true"></i>
                    </a>
                <?php else: ?>
                    <a href="#" id="account-trigger" aria-label="Account" class="desktop-only">
                        <i class="fas fa-user" aria-hidden="true"></i>
                    </a>
                <?php endif; ?>
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
                        <?php if ($is_logged_in): ?>
                            <div class="mobile-user-info"><?= $user_email ?></div>
                            <?php if ($user_posts_count > 0): ?>
                            <a href="../../my-posts.php">
                                <i class="fas fa-file-alt"></i>My Posts
                            </a>
                            <?php endif; ?>
                            <a href="../../create-post.php">
                                <i class="fas fa-edit"></i>Create Post
                            </a>
                            <a href="../../index.php?logout=true">
                                <i class="fas fa-sign-out-alt"></i>Log Out
                            </a>
                        <?php else: ?>
                            <a href="#" id="mobile-account-trigger">
                                <i class="fas fa-user"></i>Log In
                            </a>
                        <?php endif; ?>
                        <button id="mobile-theme-toggle">
                            <i class="fas fa-moon"></i>
                            <span>Theme</span>
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </header>
    
    <div class="live-conditions-bar" id="live-conditions" aria-live="polite">
        <span>Loading regional conditions...</span>
    </div>

    <main>
        <div id="shimmer-loader">
            <div class="container">
                <div class="community-hero">
                    <div class="shimmer" style="height: 60px; width: 300px; margin: 0 auto 1rem;"></div>
                    <div class="shimmer" style="height: 20px; width: 500px; margin: 0 auto 2rem;"></div>
                </div>
                <div class="community-posts">
                    <div class="shimmer" style="height: 350px; border-radius: 8px;"></div>
                    <div class="shimmer" style="height: 350px; border-radius: 8px;"></div>
                    <div class="shimmer" style="height: 350px; border-radius: 8px;"></div>
                </div>
            </div>
        </div>
        
        <div class="container community-container hidden">
            <!-- Hero Section -->
            <section class="community-hero">
                <div class="flex justify-center mb-4">
                    <!-- Users Icon -->
                    <svg xmlns="http://www.w3.org/2000/svg" 
                         fill="none" 
                         viewBox="0 0 24 24" 
                         stroke-width="1.5" 
                         stroke="currentColor" 
                         class="w-16 h-16"
                         style="color: #b8ba20;">
                      <path stroke-linecap="round" stroke-linejoin="round" d="M15 19.128a9.38 9.38 0 002.625.372 9.337 9.337 0 004.121-.952 4.125 4.125 0 00-7.533-2.493M15 19.128v-.003c0-1.113-.285-2.16-.786-3.07M15 19.128v.106A12.318 12.318 0 018.624 21c-2.331 0-4.512-.645-6.374-1.766l-.001-.109a6.375 6.375 0 0111.964-3.07M12 6.375a3.375 3.375 0 11-6.75 0 3.375 3.375 0 016.75 0zm8.25 2.25a2.625 2.625 0 11-5.25 0 2.625 2.625 0 015.25 0z" />
                    </svg>
                </div>
                <h1>Community Posts</h1>
                <p>Share your stories, experiences, and insights with the Yucca Club community. Discover authentic voices from El Paso, Las Cruces, and the Borderland region.</p>
            </section>

            <?php if ($is_logged_in): ?>
            <!-- Profile Container -->
            <div class="profile-container">
                <div class="profile-header">
                    <div class="profile-avatar">
                        <?= strtoupper(substr($user_email, 0, 1)) ?>
                    </div>
                    <div class="profile-info">
                        <h2><?= htmlspecialchars(explode('@', $user_email)[0]) ?></h2>
                        <p><?= $user_email ?></p>
                    </div>
                </div>
                
                <div class="profile-stats">
                    <div class="stat-item">
                        <div class="stat-value"><?= $post_usage['current'] ?></div>
                        <div class="stat-label">This Month</div>
                    </div>
                    <div class="stat-item">
                        <div class="stat-value"><?= $post_usage['remaining'] ?></div>
                        <div class="stat-label">Remaining</div>
                    </div>
                    <div class="stat-item">
                        <div class="stat-value"><?= $post_usage['limit'] ?></div>
                        <div class="stat-label">Monthly Limit</div>
                    </div>
                </div>
            </div>
            <?php endif; ?>
            
            <?php if ($is_logged_in): ?>
                <?php if ($post_usage['remaining'] > 0): ?>
                <div class="status-alert success">
                    <i class="fas fa-check-circle"></i>
                    <p><strong><?= $post_usage['remaining'] ?></strong> post<?= $post_usage['remaining'] > 1 ? 's' : '' ?> remaining this month. 
                        <a href="../../create-post.php" style="color: inherit; text-decoration: underline; font-weight: 700;">Create a post</a>
                    </p>
                </div>
                <?php else: ?>
                <div class="status-alert warning">
                    <i class="fas fa-exclamation-triangle"></i>
                    <p>You've reached your 5 post limit for this month. Come back on <?= date('F 1', strtotime('first day of next month')) ?>!</p>
                </div>
                <?php endif; ?>
            <?php else: ?>
                <div class="status-alert info">
                    <i class="fas fa-info-circle"></i>
                    <p>Join our community! <a href="#" id="account-trigger-inline" style="color: inherit; text-decoration: underline; font-weight: 700;">Sign up</a> to create up to 5 posts per month and connect with fellow Southwest enthusiasts.</p>
                </div>
            <?php endif; ?>
            
            <!-- Community Posts Grid -->
            <div class="community-posts">
                <?php if (count($community_posts) === 0): ?>
                <div class="empty-state">
                    <i class="fas fa-comments"></i>
                    <h3>No Community Posts Yet</h3>
                    <p>Be the first to share your story with the community!</p>
                    <?php if ($is_logged_in && $post_usage['remaining'] > 0): ?>
                    <a href="../../create-post.php" class="cta-button" style="display: inline-block; margin-top: 1rem;">
                        <i class="fas fa-edit"></i> Create First Post
                    </a>
                    <?php endif; ?>
                </div>
                <?php else: ?>
                    <?php foreach ($community_posts as $post): ?>
                    <article class="post-card">
                        <?php if (!empty($post['featured_image'])): ?>
                        <img src="<?= htmlspecialchars($post['featured_image']) ?>" alt="<?= htmlspecialchars($post['title']) ?>" loading="lazy">
                        <?php endif; ?>
                        
                        <div class="post-card-content">
                            <?php if (!empty($post['category'])): ?>
                            <span style="display: inline-block; background: var(--yucca-yellow); color: white; padding: 0.2rem 0.6rem; border-radius: 4px; font-size: 0.7rem; font-weight: 700; margin-bottom: 0.5rem;">
                                <?= htmlspecialchars($post['category']) ?>
                            </span>
                            <?php endif; ?>
                            
                            <h3><?= htmlspecialchars($post['title']) ?></h3>
                            
                            <div class="post-meta">
                                <i class="fas fa-user"></i>
                                <span><?= htmlspecialchars(explode('@', $post['user_email'])[0]) ?></span>
                                <span>•</span>
                                <i class="fas fa-calendar"></i>
                                <span><?= date('M j, Y', strtotime($post['created_at'])) ?></span>
                            </div>
                            
                            <?php 
                            $content = json_decode($post['content'], true);
                            $excerpt = '';
                            if ($content && isset($content['intro'])) {
                                $excerpt = $content['intro'];
                            } else {
                                $excerpt = $post['content'];
                            }
                            $excerpt = strip_tags($excerpt);
                            if (strlen($excerpt) > 150) {
                                $excerpt = substr($excerpt, 0, 150) . '...';
                            }
                            ?>
                            <p class="post-excerpt"><?= htmlspecialchars($excerpt) ?></p>
                            
                            <a href="../../view-post.php?slug=<?= htmlspecialchars($post['slug']) ?>&type=community" class="read-more-btn">
                                <i class="fas fa-arrow-right"></i> Read More
                            </a>
                        </div>
                    </article>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>

            <nav class="pagination fade-in-on-scroll" aria-label="Community posts navigation">
                <a href="#" class="prev disabled" aria-disabled="true">Previous</a>
                <a href="#" class="current" aria-current="page">1</a>
                <a href="#">2</a>
                <a href="#">3</a>
                <a href="#" class="next">Next</a>
            </nav>
        </div>
    </main>

    <footer class="site-footer">
        <div class="container">
            <p>&copy; <?= date('Y') ?> Yucca Club. All Rights Reserved.</p>
        </div>
    </footer>

    <!-- Account Modal -->
    <?php if (!$is_logged_in): ?>
    <div class="modal-overlay" id="account-modal" role="dialog" aria-modal="true" aria-labelledby="account-modal-title">
        <div class="modal-content">
            <button class="modal-close" aria-label="Close dialog">&times;</button>
            <h2 id="account-modal-title">Member Access</h2>
            <p>Log in or create an account to access exclusive content.</p>
            <form class="modal-form" method="POST" action="">
                <input type="hidden" name="action" value="login">
                <label for="account-email" class="visually-hidden">Email</label>
                <input id="account-email" type="email" name="email" class="form-input" placeholder="your-email@example.com" required autocomplete="email">
                <label for="account-password" class="visually-hidden">Password</label>
                <input id="account-password" type="password" name="password" class="form-input" placeholder="Password" required autocomplete="current-password">
                <button type="submit" class="cta-button">Log In</button>
                <p class="form-link"><a href="../../reset_password.php">Forgot password?</a></p>
                <p class="form-link"><a href="../../index.php">Need an account? Register here.</a></p>
            </form>
        </div>
    </div>
    <?php endif; ?>

    <div id="toast-container" role="status" aria-live="polite"></div>
    <button id="back-to-top" aria-label="Back to top"><i class="fas fa-arrow-up" aria-hidden="true"></i></button>

    <script src="../../ui/js/if-then.js"></script>
    <script src="../../ui/js/main.js"></script>
    <script src="../../ui/js/jquery-loader.js"></script>
    <script>
        // Enhanced Mobile UI and Community Features
        document.addEventListener('DOMContentLoaded', () => {
            // Hide shimmer loader and show content after load
            setTimeout(() => {
                document.getElementById('shimmer-loader').style.display = 'none';
                document.querySelector('.community-container').classList.remove('hidden');
            }, 300);
            
            // Mobile Post Card Interactions
            const postCards = document.querySelectorAll('.post-card');
            postCards.forEach(card => {
                // Add touch feedback for mobile
                card.addEventListener('touchstart', function() {
                    this.style.transform = 'scale(0.98)';
                });
                
                card.addEventListener('touchend', function() {
                    this.style.transform = '';
                });
                
                // Add click tracking for analytics (future feature)
                card.addEventListener('click', function() {
                    const title = this.querySelector('h3')?.textContent;
                    if (title) {
                        console.log('Community post clicked:', title);
                        // Future: Send analytics event
                    }
                });
            });
            
            // Enhanced Mobile Menu for Community
            const mobileMenuTrigger = document.getElementById('mobile-menu-trigger');
            const mobileMenuDropdown = document.getElementById('mobile-menu-dropdown');
            
            if (mobileMenuTrigger && mobileMenuDropdown) {
                mobileMenuTrigger.addEventListener('click', (e) => {
                    e.stopPropagation();
                    mobileMenuDropdown.classList.toggle('show');
                });
                
                // Close mobile menu when clicking outside
                document.addEventListener('click', (e) => {
                    if (!mobileMenuTrigger.contains(e.target) && !mobileMenuDropdown.contains(e.target)) {
                        mobileMenuDropdown.classList.remove('show');
                    }
                });
            }
            
            // Lazy Loading Enhancement
            const images = document.querySelectorAll('img[loading="lazy"]');
            if ('IntersectionObserver' in window) {
                const imageObserver = new IntersectionObserver((entries, observer) => {
                    entries.forEach(entry => {
                        if (entry.isIntersecting) {
                            const img = entry.target;
                            img.classList.add('fade-in');
                            observer.unobserve(img);
                        }
                    });
                });
                
                images.forEach(img => imageObserver.observe(img));
            }
            
            // Mobile Performance Optimization
            if (window.innerWidth <= 768) {
                // Reduce animation complexity on mobile
                document.documentElement.style.setProperty('--animation-duration', '0.2s');
                
                // Optimize images for mobile
                const images = document.querySelectorAll('img');
                images.forEach(img => {
                    if (img.src && !img.src.includes('w_')) {
                        // Future: Add mobile-optimized image URLs
                        console.log('Mobile image optimization opportunity:', img.src);
                    }
                });
            }
            
            // SEO Enhancement: Track scroll depth
            let maxScrollDepth = 0;
            window.addEventListener('scroll', () => {
                const scrollDepth = Math.round((window.scrollY / (document.body.scrollHeight - window.innerHeight)) * 100);
                if (scrollDepth > maxScrollDepth) {
                    maxScrollDepth = scrollDepth;
                    // Future: Send scroll depth analytics
                    if (maxScrollDepth >= 75) {
                        console.log('High engagement: 75%+ scroll depth');
                    }
                }
            });
        });
        
        // Account Modal Handlers
        const openModal = (modal) => modal.classList.add('visible');
        const closeModal = (modal) => modal.classList.remove('visible');
        
        // Close modal handlers
        document.querySelectorAll('.modal-close').forEach(closeBtn => {
            closeBtn.addEventListener('click', () => {
                const modal = closeBtn.closest('.modal-overlay');
                if (modal) closeModal(modal);
            });
        });
        
        // Close modal on overlay click
        document.querySelectorAll('.modal-overlay').forEach(modal => {
            modal.addEventListener('click', (e) => {
                if (e.target === modal) closeModal(modal);
            });
        });
        
        // Account modal triggers - wait for DOM to be ready
        setTimeout(() => {
            const accountModal = document.getElementById('account-modal');
            const accountTrigger = document.getElementById('account-trigger');
            const mobileAccountTrigger = document.getElementById('mobile-account-trigger');
            
            if (accountTrigger && accountModal) {
                accountTrigger.addEventListener('click', (e) => {
                    e.preventDefault();
                    e.stopPropagation();
                    openModal(accountModal);
                });
            }
            
            if (mobileAccountTrigger && accountModal) {
                mobileAccountTrigger.addEventListener('click', (e) => {
                    e.preventDefault();
                    e.stopPropagation();
                    openModal(accountModal);
                });
            }
        }, 100);
        
        // Trigger account modal from inline link
        const accountTriggerInline = document.getElementById('account-trigger-inline');
        if (accountTriggerInline) {
            accountTriggerInline.addEventListener('click', (e) => {
                e.preventDefault();
                if (accountModal) {
                    openModal(accountModal);
                }
            });
        }
        
        // Mobile-specific CSS adjustments
        if (window.innerWidth <= 768) {
            document.documentElement.style.setProperty('--mobile-optimized', 'true');
        }
    </script>
</body>
</html>

