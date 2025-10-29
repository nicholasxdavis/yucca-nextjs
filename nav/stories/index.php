<?php
// Stories Page - Connected to Database
require_once '../../config.php';
require_once '../../auth_handler.php';

// Check if user is logged in
$is_logged_in = is_logged_in();
$user_email = $is_logged_in ? htmlspecialchars($_SESSION['user_email']) : '';
$user_id = $_SESSION['user_id'] ?? null;

// Get real stories from database
$stories = [];
$featured_story = null;
$user_posts_count = 0;

try {
    $conn = db_connect();
    
    // Get all published stories excluding placeholders
    $stmt = $conn->prepare("SELECT * FROM stories WHERE status = 'published' AND title NOT LIKE '%placeholder%' ORDER BY created_at DESC");
    $stmt->execute();
    $result = $stmt->get_result();
    
    while ($row = $result->fetch_assoc()) {
        $stories[] = $row;
    }
    
    // Get featured story (latest with image)
    $stmt = $conn->prepare("SELECT * FROM stories WHERE status = 'published' AND featured_image IS NOT NULL AND featured_image != '' AND title NOT LIKE '%placeholder%' ORDER BY created_at DESC LIMIT 1");
    $stmt->execute();
    $result = $stmt->get_result();
    $featured_story = $result->fetch_assoc();
    
    // Get user's posts count if logged in
    if ($is_logged_in) {
        $stmt = $conn->prepare("SELECT COUNT(*) as post_count FROM user_posts WHERE user_id = ?");
        $stmt->bind_param("i", $user_id);
        $stmt->execute();
        $result = $stmt->get_result();
        $user_posts_count = $result->fetch_assoc()['post_count'];
    }
    
    $stmt->close();
    $conn->close();
} catch (Exception $e) {
    error_log("Stories page error: " . $e->getMessage());
}

$page_title = "Local Stories | El Paso Las Cruces Metro Area | Yucca Club";
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
    <title>Stories | Las Cruces & El Paso Local Stories | Yucca Club</title>
    <link rel="icon" type="image/png" href="../../ui/img/favicon.png">

    <!-- SEO Meta Tags - El Paso Las Cruces Metro Area Focus -->
    <meta name="description" content="Discover authentic stories from El Paso, Las Cruces, and Southern New Mexico. Local culture, food, events, and hidden gems in the Borderland region.">
    <meta name="keywords" content="El Paso stories, Las Cruces blog, Southern New Mexico culture, Borderland stories, El Paso Las Cruces metro area, local events El Paso, Las Cruces food, New Mexico culture, West Texas stories, Chihuahuan Desert">
    <meta name="author" content="Yucca Club">
    <meta name="geo.region" content="US-NM">
    <meta name="geo.placename" content="Las Cruces">
    <meta name="geo.position" content="32.3199;-106.7637">
    <meta name="ICBM" content="32.3199, -106.7637">
    <link rel="canonical" href="https://www.yuccaclub.com/nav/stories/">

    <!-- Open Graph / Facebook -->
    <meta property="og:type" content="website">
    <meta property="og:url" content="https://www.yuccaclub.com/nav/stories/">
    <meta property="og:title" content="Local Stories | El Paso Las Cruces Metro Area | Yucca Club">
    <meta property="og:description" content="Discover authentic stories from El Paso, Las Cruces, and Southern New Mexico. Local culture, food, events, and hidden gems in the Borderland region.">
    <meta property="og:image" content="https://www.yuccaclub.com/ui/img/social-share.png">
    <meta property="og:locale" content="en_US">

    <!-- Twitter -->
    <meta property="twitter:card" content="summary_large_image">
    <meta property="twitter:url" content="https://www.yuccaclub.com/nav/stories/">
    <meta property="twitter:title" content="Local Stories | El Paso Las Cruces Metro Area | Yucca Club">
    <meta property="twitter:description" content="Discover authentic stories from El Paso, Las Cruces, and Southern New Mexico. Local culture, food, events, and hidden gems in the Borderland region.">
    <meta property="twitter:image" content="https://www.yuccaclub.com/ui/img/social-share.png">

    <!-- Fonts & Icons -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Lato:wght@400;700&family=Lora:wght@500;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">

    <!-- Styles -->
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="../../ui/css/styles.css">
    <link rel="stylesheet" href="../../ui/css/enhancements.css">
    
    <!-- Mobile UI Styles -->
    <style>
        /* Mobile-First Responsive Design */
        .stories-hero {
            padding: 2rem 1rem;
            text-align: center;
            background: linear-gradient(135deg, var(--desert-sand) 0%, var(--off-white) 100%);
        }
        
        .stories-hero h1 {
            font-size: 2.5rem;
            margin-bottom: 1rem;
            color: var(--lobo-gray);
        }
        
        .stories-hero p {
            font-size: 1.1rem;
            max-width: 600px;
            margin: 0 auto 2rem;
            opacity: 0.8;
        }
        
        .featured-story-card {
            background: var(--off-white);
            border-radius: 12px;
            overflow: hidden;
            box-shadow: 0 4px 12px rgba(0,0,0,0.1);
            margin-bottom: 2rem;
            transition: transform 0.3s, box-shadow 0.3s;
        }
        
        .featured-story-card:hover {
            transform: translateY(-4px);
            box-shadow: 0 8px 24px rgba(0,0,0,0.15);
        }
        
        .featured-story-image {
            width: 100%;
            height: 300px;
            object-fit: cover;
        }
        
        .featured-story-content {
            padding: 1.5rem;
        }
        
        .featured-story-content h2 {
            font-size: 1.75rem;
            margin-bottom: 0.75rem;
            color: var(--lobo-gray);
        }
        
        .featured-story-meta {
            display: flex;
            align-items: center;
            gap: 1rem;
            margin-bottom: 1rem;
            font-size: 0.9rem;
            color: #666;
            flex-wrap: wrap;
        }
        
        .featured-story-meta i {
            opacity: 0.7;
        }
        
        .featured-story-excerpt {
            line-height: 1.6;
            margin-bottom: 1.5rem;
            color: var(--lobo-gray);
        }
        
        .stories-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 1.5rem;
            margin-top: 2rem;
        }
        
        .story-card {
            background: var(--off-white);
            border-radius: 8px;
            overflow: hidden;
            transition: transform 0.3s, box-shadow 0.3s;
            border: 2px solid transparent;
        }
        
        .story-card:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(0,0,0,0.1);
            border-color: var(--yucca-yellow);
        }
        
        .story-card img {
            width: 100%;
            height: 200px;
            object-fit: cover;
        }
        
        .story-card-content {
            padding: 1.25rem;
        }
        
        .story-card h3 {
            font-size: 1.25rem;
            margin-bottom: 0.5rem;
            color: var(--lobo-gray);
            line-height: 1.3;
        }
        
        .story-card-meta {
            display: flex;
            align-items: center;
            gap: 0.75rem;
            margin-bottom: 0.75rem;
            font-size: 0.85rem;
            color: #666;
            flex-wrap: wrap;
        }
        
        .story-card-meta i {
            opacity: 0.7;
        }
        
        .story-card-excerpt {
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
            .stories-hero {
                padding: 1.5rem 1rem;
            }
            
            .stories-hero h1 {
                font-size: 2rem;
            }
            
            .stories-hero p {
                font-size: 1rem;
            }
            
            .featured-story-image {
                height: 200px;
            }
            
            .featured-story-content {
                padding: 1rem;
            }
            
            .featured-story-content h2 {
                font-size: 1.5rem;
            }
            
            .featured-story-meta {
                gap: 0.5rem;
                font-size: 0.85rem;
            }
            
            .stories-grid {
                grid-template-columns: 1fr;
                gap: 1rem;
                margin-top: 1.5rem;
            }
            
            .story-card-content {
                padding: 1rem;
            }
            
            .story-card h3 {
                font-size: 1.1rem;
            }
            
            .story-card-meta {
                gap: 0.5rem;
                font-size: 0.8rem;
            }
            
            .story-card img {
                height: 150px;
            }
        }
        
        @media (max-width: 480px) {
            .stories-hero h1 {
                font-size: 1.75rem;
            }
            
            .featured-story-content h2 {
                font-size: 1.25rem;
            }
            
            .story-card h3 {
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
        
        .story-card.loading {
            opacity: 0.7;
        }
        
        .story-card.loading .story-card-content {
            background: #f5f5f5;
        }
    </style>

    <!-- JSON-LD for Rich Snippets - El Paso Las Cruces Metro Area -->
    <script type="application/ld+json">
        {
          "@context": "https://schema.org",
          "@type": "Blog",
          "name": "Yucca Club Stories - El Paso Las Cruces Metro Area",
          "url": "https://www.yuccaclub.com/nav/stories/",
          "description": "Authentic stories from El Paso, Las Cruces, and Southern New Mexico. Discover local culture, food, events, and hidden gems in the Borderland region.",
          "publisher": {
            "@type": "Organization",
            "name": "Yucca Club",
            "logo": {
              "@type": "ImageObject",
              "url": "https://www.yuccaclub.com/ui/img/logo.png"
            },
            "address": {
              "@type": "PostalAddress",
              "addressLocality": "Las Cruces",
              "addressRegion": "NM",
              "addressCountry": "US"
            }
          },
          "mainEntityOfPage": {
            "@type": "WebPage",
            "@id": "https://www.yuccaclub.com/nav/stories/"
          },
          "about": [
            {
              "@type": "Place",
              "name": "El Paso",
              "address": {
                "@type": "PostalAddress",
                "addressLocality": "El Paso",
                "addressRegion": "TX",
                "addressCountry": "US"
              }
            },
            {
              "@type": "Place", 
              "name": "Las Cruces",
              "address": {
                "@type": "PostalAddress",
                "addressLocality": "Las Cruces",
                "addressRegion": "NM",
                "addressCountry": "US"
              }
            }
          ],
          "keywords": "El Paso stories, Las Cruces blog, Southern New Mexico culture, Borderland stories, local events, Chihuahuan Desert"
        }
    </script>
</head>
<body>
    <div id="top-loader-bar"></div>
    
    <header class="site-header">
        <div class="container header-content">
            <a href="../../index.php" class="site-logo" aria-label="Yucca Club Homepage">
                <img class="logo-light" src="../../ui/img/logo.png" alt="Yucca Club Logo Light" style="width:180px; height:auto;">
                <img class="logo-dark" src="../../ui/img/logo_dark.png" alt="Yucca Club Logo Dark" style="width:180px; height:auto;">
            </a>
            <nav class="primary-nav" aria-label="Main Navigation">
                <ul>
                    <li><a href="index.php" class="active">Stories</a></li>
                    <li><a href="../guides/index.php">Guides</a></li>
                    <li><a href="../events/index.php">Events</a></li>
                    <li><a href="https://yucca.printify.me/" target="_blank" rel="noopener noreferrer">Shop</a></li>
                    <li><a href="../community/index.php">Community</a></li>
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
                                <i class="fas fa-sign-out-alt"></i>Logout
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
                <div class="stories-hero">
                    <div class="shimmer" style="height: 60px; width: 300px; margin: 0 auto 1rem;"></div>
                    <div class="shimmer" style="height: 20px; width: 500px; margin: 0 auto 2rem;"></div>
                </div>
                <div class="stories-grid">
                    <div class="shimmer" style="height: 450px; border-radius: 12px;"></div>
                    <div class="shimmer" style="height: 350px; border-radius: 8px;"></div>
                    <div class="shimmer" style="height: 350px; border-radius: 8px;"></div>
                    <div class="shimmer" style="height: 350px; border-radius: 8px;"></div>
                </div>
            </div>
        </div>
        
        <div class="container stories-container hidden">
            <!-- Hero Section -->
            <section class="stories-hero">
                <div class="flex justify-center mb-4">
                    <!-- Book Open Icon -->
                    <svg xmlns="http://www.w3.org/2000/svg" 
                         fill="none" 
                         viewBox="0 0 24 24" 
                         stroke-width="1.5" 
                         stroke="currentColor" 
                         class="w-16 h-16"
                         style="color: #b8ba20;">
                      <path stroke-linecap="round" stroke-linejoin="round" d="M12 6.042A8.967 8.967 0 006 3.75c-1.052 0-2.062.18-3 .512v14.25A8.987 8.987 0 016 18c2.305 0 4.408.867 6 2.292m0-14.25a8.966 8.966 0 016-2.292c1.052 0 2.062.18 3 .512v14.25A8.987 8.987 0 0018 18a8.967 8.967 0 00-6 2.292m0-14.25v14.25" />
                    </svg>
                </div>
                <h1>Stories from El Paso & Las Cruces</h1>
                <p>Discover authentic stories from the Borderland region. From local culture and hidden gems to food adventures and community events in Southern New Mexico and West Texas.</p>
            </section>

            <!-- Featured Story -->
            <?php if ($featured_story): ?>
            <article class="featured-story-card">
                <?php if (!empty($featured_story['featured_image'])): ?>
                <img src="<?= htmlspecialchars($featured_story['featured_image']) ?>" 
                     alt="<?= htmlspecialchars($featured_story['title']) ?>" 
                     class="featured-story-image" 
                     loading="lazy">
                <?php endif; ?>
                
                <div class="featured-story-content">
                    <?php if (!empty($featured_story['category'])): ?>
                    <span style="display: inline-block; background: var(--yucca-yellow); color: white; padding: 0.25rem 0.75rem; border-radius: 4px; font-size: 0.75rem; font-weight: 700; margin-bottom: 0.75rem;">
                        <?= htmlspecialchars($featured_story['category']) ?>
                    </span>
                    <?php endif; ?>
                    
                    <h2><?= htmlspecialchars($featured_story['title']) ?></h2>
                    
                    <div class="featured-story-meta">
                        <i class="fas fa-calendar"></i>
                        <span><?= date('M j, Y', strtotime($featured_story['created_at'])) ?></span>
                        <span>•</span>
                        <i class="fas fa-clock"></i>
                        <span><?= ceil(str_word_count(strip_tags($featured_story['content'])) / 200) ?> min read</span>
    </div>
                    
                    <?php 
                    $excerpt = strip_tags($featured_story['content']);
                    if (strlen($excerpt) > 300) {
                        $excerpt = substr($excerpt, 0, 300) . '...';
                    }
                    ?>
                    <p class="featured-story-excerpt"><?= htmlspecialchars($excerpt) ?></p>
                    
                    <a href="../../view-post.php?slug=<?= htmlspecialchars($featured_story['slug']) ?>&type=story" class="read-more-btn">
                        <i class="fas fa-arrow-right"></i> Read Full Story
                    </a>
                    </div>
                </article>
                <?php endif; ?>

            <!-- Stories Grid -->
            <div class="stories-grid">
                <?php if (count($stories) === 0): ?>
                <div class="empty-state">
                    <i class="fas fa-book-open"></i>
                    <h3>No Stories Yet</h3>
                    <p>We're working on bringing you amazing stories from El Paso, Las Cruces, and the Borderland region. Check back soon!</p>
                </div>
                <?php else: ?>
                <?php foreach (array_slice($stories, 1, 6) as $story): ?>
                    <article class="story-card">
                        <?php if (!empty($story['featured_image'])): ?>
                        <img src="<?= htmlspecialchars($story['featured_image']) ?>" 
                             alt="<?= htmlspecialchars($story['title']) ?>" 
                             loading="lazy">
                        <?php endif; ?>
                        
                        <div class="story-card-content">
                            <?php if (!empty($story['category'])): ?>
                            <span style="display: inline-block; background: var(--yucca-yellow); color: white; padding: 0.2rem 0.6rem; border-radius: 4px; font-size: 0.7rem; font-weight: 700; margin-bottom: 0.5rem;">
                                <?= htmlspecialchars($story['category']) ?>
                            </span>
                            <?php endif; ?>
                            
                            <h3><?= htmlspecialchars($story['title']) ?></h3>
                            
                            <div class="story-card-meta">
                                <i class="fas fa-calendar"></i>
                                <span><?= date('M j, Y', strtotime($story['created_at'])) ?></span>
                                <span>•</span>
                                <i class="fas fa-clock"></i>
                                <span><?= ceil(str_word_count(strip_tags($story['content'])) / 200) ?> min</span>
                            </div>
                            
                            <?php 
                            $excerpt = strip_tags($story['content']);
                            if (strlen($excerpt) > 150) {
                                $excerpt = substr($excerpt, 0, 150) . '...';
                            }
                            ?>
                            <p class="story-card-excerpt"><?= htmlspecialchars($excerpt) ?></p>
                            
                            <a href="../../view-post.php?slug=<?= htmlspecialchars($story['slug']) ?>&type=story" class="read-more-btn">
                                <i class="fas fa-arrow-right"></i> Read More
                            </a>
                    </div>
                </article>
                <?php endforeach; ?>
                <?php endif; ?>
            </div>

            <nav class="pagination fade-in-on-scroll" aria-label="Blog post navigation">
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
            <div class="footer-content site-footer-main">
                <p>&copy; 2025 Yucca Club. All Rights Reserved.</p>
                <nav class="footer-nav" aria-label="Footer Navigation">
                    <ul>
                        <li><a href="#" id="contact-trigger">Contact</a></li>
                        <li><a href="../../privacy_policy.php">Privacy Policy</a></li>
                    </ul>
                </nav>
            </div>
            <p class="sustainability-statement">
                Crafted with love in Las Cruces, New Mexico
            </p>
        </div>
    </footer>

    <!-- Account Modal -->
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
    
    <!-- Contact Modal -->
    <div class="modal-overlay" id="contact-modal" role="dialog" aria-modal="true" aria-labelledby="contact-modal-title">
        <div class="modal-content">
            <button class="modal-close" aria-label="Close dialog">&times;</button>
            <h2 id="contact-modal-title">Get In Touch</h2>
            <p>Have a question or a story idea? We'd love to hear from you.</p>
            <form class="modal-form">
                <label for="contact-name" class="visually-hidden">Name</label>
                <input id="contact-name" type="text" class="form-input" placeholder="Your Name" required autocomplete="name">
                <label for="contact-email" class="visually-hidden">Email</label>
                <input id="contact-email" type="email" class="form-input" placeholder="Your Email" required autocomplete="email">
                <label for="contact-message" class="visually-hidden">Message</label>
                <textarea id="contact-message" class="form-input" placeholder="Your Message" required></textarea>
                <button type="submit" class="cta-button">Send Message</button>
            </form>
        </div>
    </div>

    <div id="toast-container" role="status" aria-live="polite"></div>
    <button id="back-to-top" aria-label="Back to top"><i class="fas fa-arrow-up" aria-hidden="true"></i></button>

    <script src="../../ui/js/if-then.js"></script>
    <script src="../../ui/js/main.js"></script>
    <script src="../../ui/js/jquery-loader.js"></script>
    <script>
        // Enhanced Mobile UI and SEO Features
        document.addEventListener('DOMContentLoaded', () => {
            // Hide shimmer loader and show content after load
            setTimeout(() => {
                document.getElementById('shimmer-loader').style.display = 'none';
                document.querySelector('.stories-container').classList.remove('hidden');
            }, 300);
            
            // Mobile Story Card Interactions
            const storyCards = document.querySelectorAll('.story-card, .featured-story-card');
            storyCards.forEach(card => {
                // Add touch feedback for mobile
                card.addEventListener('touchstart', function() {
                    this.style.transform = 'scale(0.98)';
                });
                
                card.addEventListener('touchend', function() {
                    this.style.transform = '';
                });
                
                // Add click tracking for analytics (future feature)
                card.addEventListener('click', function() {
                    const title = this.querySelector('h2, h3')?.textContent;
                    if (title) {
                        console.log('Story clicked:', title);
                        // Future: Send analytics event
                    }
                });
            });
            
            // Enhanced Mobile Menu for Stories
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
            
            // Reading Time Calculation Enhancement
            const storyCards = document.querySelectorAll('.story-card, .featured-story-card');
            storyCards.forEach(card => {
                const content = card.querySelector('.story-card-excerpt, .featured-story-excerpt');
                if (content) {
                    const wordCount = content.textContent.split(' ').length;
                    const readTime = Math.ceil(wordCount / 200);
                    
                    // Update read time if it's different from what's shown
                    const timeElement = card.querySelector('.story-card-meta span:last-child, .featured-story-meta span:last-child');
                    if (timeElement && timeElement.textContent.includes('min')) {
                        timeElement.textContent = `${readTime} min${readTime > 1 ? '' : ''}`;
                    }
                }
            });
            
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
            
            // Local SEO Enhancement: Add structured data for local businesses
            const localBusinessSchema = {
                "@context": "https://schema.org",
                "@type": "LocalBusiness",
                "name": "Yucca Club",
                "description": "Local stories and culture from El Paso, Las Cruces, and Southern New Mexico",
                "address": {
                    "@type": "PostalAddress",
                    "addressLocality": "Las Cruces",
                    "addressRegion": "NM",
                    "addressCountry": "US"
                },
                "areaServed": [
                    {
                        "@type": "City",
                        "name": "El Paso",
                        "containedInPlace": {
                            "@type": "State",
                            "name": "Texas"
                        }
                    },
                    {
                        "@type": "City", 
                        "name": "Las Cruces",
                        "containedInPlace": {
                            "@type": "State",
                            "name": "New Mexico"
                        }
                    }
                ]
            };
            
            // Add local business schema
            const script = document.createElement('script');
            script.type = 'application/ld+json';
            script.textContent = JSON.stringify(localBusinessSchema);
            document.head.appendChild(script);
            
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
        });
        
        // Mobile-specific CSS adjustments
        if (window.innerWidth <= 768) {
            document.documentElement.style.setProperty('--mobile-optimized', 'true');
        }
    </script>
</body>
</html>

