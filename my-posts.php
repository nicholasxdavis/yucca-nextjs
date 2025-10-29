<?php
// My Posts Page - User's personal posts dashboard
require_once 'config.php';

if (!is_logged_in()) {
    header('Location: index.php');
    exit;
}

$user_id = $_SESSION['user_id'];
$user_email = htmlspecialchars($_SESSION['user_email']);
$user_role = $_SESSION['user_role'] ?? 'user';

// Get user's posts
$user_posts = [];
$post_usage = [
    'current' => 0,
    'limit' => 5,
    'remaining' => 5
];

try {
    $conn = db_connect();
    
    // Get user's posts
    $stmt = $conn->prepare("SELECT * FROM user_posts WHERE user_id = ? ORDER BY created_at DESC");
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    while ($row = $result->fetch_assoc()) {
        $user_posts[] = $row;
    }
    $stmt->close();
    
    // Get post usage for current month
    $current_month = date('Y-m');
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
    error_log("My posts error: " . $e->getMessage());
}

$page_title = "My Posts - Yucca Club";
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
    <title>My Posts | Manage Your Las Cruces & El Paso Stories | Yucca Club</title>
    
    <!-- SEO Meta Tags -->
    <meta name="description" content="Manage your community posts and stories on Yucca Club. Track your contributions to Las Cruces, El Paso, and Southern New Mexico local content.">
    <meta name="keywords" content="my posts Yucca Club, Las Cruces posts, El Paso posts, manage stories, community contributions, Southern New Mexico content, Borderland posts">
    <meta name="author" content="Yucca Club">
    <meta name="robots" content="noindex, nofollow">
    <meta name="language" content="en-US">
    <meta name="geo.region" content="US-NM">
    <meta name="geo.placename" content="Las Cruces">
    <meta name="geo.position" content="32.3199;-106.7637">
    <meta name="ICBM" content="32.3199, -106.7637">
    <meta name="theme-color" content="#b8ba20">
    
    <!-- Canonical URL -->
    <link rel="canonical" href="https://www.yuccaclub.com/my-posts.php">
    
    <!-- Open Graph / Facebook -->
    <meta property="og:type" content="website">
    <meta property="og:url" content="https://www.yuccaclub.com/my-posts.php">
    <meta property="og:title" content="My Posts | Manage Your Las Cruces & El Paso Stories | Yucca Club">
    <meta property="og:description" content="Manage your community posts and stories on Yucca Club. Track your contributions to Las Cruces, El Paso, and Southern New Mexico local content.">
    <meta property="og:image" content="https://www.yuccaclub.com/ui/img/social-share.png">
    <meta property="og:image:width" content="1200">
    <meta property="og:image:height" content="630">
    <meta property="og:image:alt" content="Yucca Club My Posts - Manage Your Stories">
    <meta property="og:site_name" content="Yucca Club">
    <meta property="og:locale" content="en_US">
    
    <!-- Twitter -->
    <meta name="twitter:card" content="summary_large_image">
    <meta name="twitter:url" content="https://www.yuccaclub.com/my-posts.php">
    <meta name="twitter:title" content="My Posts | Manage Your Las Cruces & El Paso Stories | Yucca Club">
    <meta name="twitter:description" content="Manage your community posts and stories on Yucca Club. Track your contributions to Las Cruces, El Paso, and Southern New Mexico local content.">
    <meta name="twitter:image" content="https://www.yuccaclub.com/ui/img/social-share.png">
    <meta name="twitter:image:alt" content="Yucca Club My Posts - Manage Your Stories">
    
    <!-- Favicon -->
    <link rel="icon" type="image/png" href="ui/img/favicon.png">
    <link rel="apple-touch-icon" href="ui/img/favicon.png">
    <link rel="shortcut icon" href="ui/img/favicon.png">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Lato:wght@400;700&family=Lora:wght@500;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="ui/css/styles.css">
    <link rel="stylesheet" href="ui/css/enhancements.css">
    <style>
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
            width: 80px;
            height: 80px;
            border-radius: 50%;
            background: var(--yucca-yellow);
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 2rem;
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
            grid-template-columns: repeat(auto-fit, minmax(120px, 1fr));
            gap: 1rem;
            margin-top: 1rem;
        }
        .stat-item {
            text-align: center;
            padding: 1rem;
            background: white;
            border-radius: 8px;
            border: 1px solid var(--desert-sand);
        }
        .stat-value {
            font-size: 1.5rem;
            font-weight: 700;
            color: var(--yucca-yellow);
            margin-bottom: 0.25rem;
        }
        .stat-label {
            font-size: 0.875rem;
            opacity: 0.7;
        }
        .posts-container {
            display: grid;
            gap: 1.5rem;
        }
        .post-card {
            background: var(--off-white);
            padding: 1.5rem;
            border-radius: 12px;
            transition: transform 0.3s, box-shadow 0.3s;
            border: 2px solid transparent;
        }
        .post-card:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(0,0,0,0.1);
            border-color: var(--yucca-yellow);
        }
        .post-header {
            display: flex;
            justify-content: space-between;
            align-items: start;
            margin-bottom: 1rem;
        }
        .post-title {
            font-size: 1.5rem;
            margin-bottom: 0.5rem;
            color: var(--lobo-gray);
        }
        .post-meta {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            color: #666;
            font-size: 0.875rem;
            margin-bottom: 1rem;
            flex-wrap: wrap;
        }
        .post-meta i {
            opacity: 0.7;
        }
        .post-excerpt {
            line-height: 1.6;
            margin-bottom: 1rem;
            color: var(--lobo-gray);
        }
        .post-actions {
            display: flex;
            gap: 0.5rem;
            flex-wrap: wrap;
        }
        .btn {
            padding: 0.5rem 1rem;
            border: none;
            border-radius: 6px;
            cursor: pointer;
            font-size: 0.875rem;
            font-weight: 600;
            transition: all 0.3s;
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            text-decoration: none;
        }
        .btn-primary {
            background: var(--yucca-yellow);
            color: white;
        }
        .btn-primary:hover {
            opacity: 0.9;
            transform: translateY(-1px);
        }
        .btn-secondary {
            background: #e0e0e0;
            color: var(--lobo-gray);
        }
        .btn-secondary:hover {
            background: #d0d0d0;
        }
        .btn-danger {
            background: #dc3545;
            color: white;
        }
        .btn-danger:hover {
            background: #c82333;
        }
        .status-badge {
            display: inline-block;
            padding: 0.25rem 0.75rem;
            border-radius: 6px;
            font-size: 0.75rem;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        .status-pending { background: #fff3cd; color: #856404; }
        .status-approved { background: #d4edda; color: #155724; }
        .status-published { background: #d1ecf1; color: #0c5460; }
        .status-rejected { background: #f8d7da; color: #721c24; }
        .empty-state {
            text-align: center;
            padding: 4rem 2rem;
            opacity: 0.7;
        }
        .empty-state i {
            font-size: 4rem;
            margin-bottom: 1rem;
            opacity: 0.3;
        }
        
        @media (max-width: 768px) {
            .profile-header {
                flex-direction: column;
                text-align: center;
            }
            .profile-stats {
                grid-template-columns: repeat(2, 1fr);
            }
            .post-header {
                flex-direction: column;
                gap: 1rem;
            }
            .post-actions {
                justify-content: center;
            }
        }
    </style>
</head>
<body>
    <!-- Header -->
    <header class="site-header">
        <div class="container header-content">
            <a href="index.php" class="site-logo">
                <img class="logo-light" src="ui/img/logo.png" alt="Yucca Club Logo" style="width:180px;">
                <img class="logo-dark" src="ui/img/logo_dark.png" alt="Yucca Club Logo Dark" style="width:180px;">
            </a>
            <nav class="primary-nav">
                <ul>
                    <li><a href="nav/stories/index.php">Stories</a></li>
                    <li><a href="nav/guides/index.php">Guides</a></li>
                    <li><a href="nav/events/index.php">Events</a></li>
                    <li><a href="https://yucca.printify.me/" target="_blank">Shop</a></li>
                    <li><a href="nav/community/index.php">Community</a></li>
                    <li><a href="nav/membership/index.php">Membership</a></li>
                </ul>
            </nav>
            <div class="header-actions">
                <span class="desktop-only" style="font-size: 14px; font-weight: 700;"><?= $user_email ?></span>
                <a href="index.php?logout=true" aria-label="Logout" class="desktop-only">
                    <i class="fas fa-sign-out-alt" aria-hidden="true"></i>
                </a>
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
                        <div class="mobile-user-info"><?= $user_email ?></div>
                        <a href="index.php?logout=true">
                            <i class="fas fa-sign-out-alt"></i>Log Out
                        </a>
                        <button id="mobile-theme-toggle">
                            <i class="fas fa-moon"></i>
                            <span>Theme</span>
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </header>

    <!-- Main Content -->
    <main>
        <div class="container">
            <!-- Hero Section -->
            <header class="text-center py-8" style="padding: 2rem 1rem; text-align: center; background: linear-gradient(135deg, var(--desert-sand) 0%, var(--off-white) 100%); border-radius: 12px; margin-bottom: 2rem;">
                <div class="flex justify-center mb-4">
                    <!-- File Alt Icon -->
                    <svg xmlns="http://www.w3.org/2000/svg" 
                         fill="none" 
                         viewBox="0 0 24 24" 
                         stroke-width="1.5" 
                         stroke="currentColor" 
                         class="w-16 h-16"
                         style="color: #b8ba20;">
                      <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 14.25v-2.625a3.375 3.375 0 00-3.375-3.375h-1.5A1.125 1.125 0 0113.5 7.125v-1.5a3.375 3.375 0 00-3.375-3.375H8.25m0 12.75h7.5m-7.5 3H12M10.5 2.25H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 00-9-9z" />
                    </svg>
                </div>
                <h1 style="font-size: 2.5rem; margin-bottom: 1rem; color: var(--lobo-gray);">My Posts</h1>
                <p style="font-size: 1.1rem; max-width: 600px; margin: 0 auto; opacity: 0.8;">Manage your community contributions and track your publishing journey</p>
            </header>

            <!-- Profile Container -->
            <div class="profile-container">
                <div class="profile-header">
                    <div class="profile-avatar">
                        <?= strtoupper(substr($user_email, 0, 1)) ?>
                    </div>
                    <div class="profile-info">
                        <h2><?= htmlspecialchars(explode('@', $user_email)[0]) ?></h2>
                        <p><?= $user_email ?></p>
                        <p style="margin-top: 0.5rem;">
                            <span class="status-badge status-<?= $user_role ?>"><?= ucfirst($user_role) ?></span>
                        </p>
                    </div>
                    <?php if ($post_usage['remaining'] > 0): ?>
                    <div style="margin-left: auto;">
                        <a href="create-post.php" class="btn btn-primary" style="display: inline-flex; align-items: center; gap: 0.5rem; padding: 0.75rem 1.5rem; background: var(--yucca-yellow); color: white; border-radius: 8px; text-decoration: none; font-weight: 700;">
                            <i class="fas fa-plus"></i> Create New Post
                        </a>
                    </div>
                    <?php endif; ?>
                </div>
                
                <div class="profile-stats">
                    <div class="stat-item">
                        <div class="stat-value"><?= count($user_posts) ?></div>
                        <div class="stat-label">Total Posts</div>
                    </div>
                    <div class="stat-item">
                        <div class="stat-value"><?= $post_usage['current'] ?></div>
                        <div class="stat-label">This Month</div>
                    </div>
                    <div class="stat-item">
                        <div class="stat-value"><?= $post_usage['remaining'] ?></div>
                        <div class="stat-label">Remaining</div>
                    </div>
                    <div class="stat-item">
                        <div class="stat-value"><?= count(array_filter($user_posts, function($p) { return $p['status'] === 'published'; })) ?></div>
                        <div class="stat-label">Published</div>
                    </div>
                </div>
            </div>

            <!-- Posts Section Header -->
            <div style="margin-bottom: 1.5rem;">
                <h2 style="font-size: 1.5rem; color: var(--lobo-gray); font-weight: 700;">Your Posts</h2>
            </div>

            <div class="posts-container">
                <?php if (count($user_posts) === 0): ?>
                <div class="empty-state">
                    <i class="fas fa-file-alt"></i>
                    <p style="font-size: 1.25rem; margin-bottom: 0.5rem; font-weight: 700;">No posts yet</p>
                    <p>Start sharing your stories with the community!</p>
                    <?php if ($post_usage['remaining'] > 0): ?>
                    <a href="create-post.php" class="btn btn-primary" style="margin-top: 1rem;">
                        <i class="fas fa-plus"></i> Create Your First Post
                    </a>
                    <?php endif; ?>
                </div>
                <?php else: ?>
                    <?php foreach ($user_posts as $post): ?>
                    <div class="post-card">
                        <div class="post-header">
                            <div>
                                <h3 class="post-title"><?= htmlspecialchars($post['title']) ?></h3>
                                <div class="post-meta">
                                    <i class="fas fa-calendar"></i>
                                    <span><?= date('M j, Y', strtotime($post['created_at'])) ?></span>
                                    <span>•</span>
                                    <i class="fas fa-tag"></i>
                                    <span><?= $post['category'] ?: 'No category' ?></span>
                                </div>
                            </div>
                            <span class="status-badge status-<?= $post['status'] ?>"><?= ucfirst($post['status']) ?></span>
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
                        if (strlen($excerpt) > 200) {
                            $excerpt = substr($excerpt, 0, 200) . '...';
                        }
                        ?>
                        <p class="post-excerpt"><?= htmlspecialchars($excerpt) ?></p>
                        
                        <div class="post-actions">
                            <?php if ($post['status'] === 'published'): ?>
                            <a href="view-post.php?slug=<?= htmlspecialchars($post['slug']) ?>&type=community" class="btn btn-primary">
                                <i class="fas fa-eye"></i> View Post
                            </a>
                            <?php endif; ?>
                            
                            <?php if ($post['status'] === 'pending'): ?>
                            <span class="btn btn-secondary" style="cursor: default;">
                                <i class="fas fa-clock"></i> Under Review
                            </span>
                            <?php endif; ?>
                            
                            <?php if ($post['status'] === 'rejected'): ?>
                            <span class="btn btn-danger" style="cursor: default;">
                                <i class="fas fa-times"></i> Rejected
                            </span>
                            <?php endif; ?>
                            
                            <button class="btn btn-secondary" onclick="viewPostDetails(<?= $post['id'] ?>)">
                                <i class="fas fa-info-circle"></i> Details
                            </button>
                        </div>
                    </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>

            <nav class="pagination fade-in-on-scroll" aria-label="My posts navigation">
                <a href="#" class="prev disabled" aria-disabled="true">Previous</a>
                <a href="#" class="current" aria-current="page">1</a>
                <a href="#">2</a>
                <a href="#">3</a>
                <a href="#" class="next">Next</a>
            </nav>
        </div>
    </main>
    
    <!-- Footer -->
    <footer class="site-footer">
        <div class="container">
            <p>&copy; <?= date('Y') ?> Yucca Club. All Rights Reserved. | 
                <a href="privacy_policy.php" style="color: inherit;">Privacy Policy</a>
            </p>
        </div>
    </footer>
    
    <div id="toast-container" role="status" aria-live="polite"></div>
    <button id="back-to-top" aria-label="Back to top"><i class="fas fa-arrow-up" aria-hidden="true"></i></button>
    
    <script>
        async function viewPostDetails(postId) {
            try {
                const response = await fetch(`api/user_posts_api.php?action=get&id=${postId}`);
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
                                <strong>Status:</strong> <span class="status-badge status-${post.status}">${post.status}</span><br>
                                <strong>Category:</strong> ${post.category || 'None'}<br>
                                <strong>Submitted:</strong> ${new Date(post.created_at).toLocaleString()}<br>
                                <strong>Last Updated:</strong> ${new Date(post.updated_at).toLocaleString()}
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
    </script>
    
    <script src="ui/js/if-then.js"></script>
    <script src="ui/js/main.js"></script>
    <script src="ui/js/jquery-loader.js"></script>
</body>
</html>
