<?php
// Guides Page - Connected to Database
require_once '../../config.php';
require_once '../../auth_handler.php';

// Check if user is logged in
$is_logged_in = is_logged_in();
$user_email = $is_logged_in ? htmlspecialchars($_SESSION['user_email']) : '';
$user_id = $_SESSION['user_id'] ?? null;

// Get real guides from database
$guides = [];
$categories = [];
$user_posts_count = 0;

try {
    $conn = db_connect();
    
    // Get all published guides excluding placeholders
    $stmt = $conn->prepare("SELECT * FROM guides WHERE status = 'published' AND title NOT LIKE '%placeholder%' ORDER BY created_at DESC");
    $stmt->execute();
    $result = $stmt->get_result();
    
    while ($row = $result->fetch_assoc()) {
        $guides[] = $row;
        if (!empty($row['category']) && !in_array($row['category'], $categories)) {
            $categories[] = $row['category'];
        }
    }
    
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
    error_log("Guides page error: " . $e->getMessage());
}

$page_title = "In-Depth Guides - Yucca Club";
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
    <title>Guides | Las Cruces & El Paso Expert Travel Guide | Yucca Club</title>
    <link rel="icon" type="image/png" href="../../ui/img/favicon.png">

    <!-- SEO Meta Tags -->
    <meta name="description" content="Expert guides for Las Cruces, El Paso, and Southern New Mexico. Discover hiking trails, restaurants, events, and hidden gems in the Borderland region.">
    <meta name="keywords" content="Las Cruces guides, El Paso travel guide, Southern New Mexico hiking, Organ Mountains trails, White Sands National Park, Borderland region guide, Las Cruces restaurants, El Paso food, New Mexico culture, West Texas attractions, Chihuahuan Desert">
    <meta name="author" content="Yucca Club">
    <meta name="robots" content="index, follow">
    <meta name="language" content="en-US">
    <meta name="geo.region" content="US-NM">
    <meta name="geo.placename" content="Las Cruces">
    <meta name="geo.position" content="32.3199;-106.7637">
    <meta name="ICBM" content="32.3199, -106.7637">
    <meta name="theme-color" content="#b8ba20">
    <link rel="canonical" href="https://www.yuccaclub.com/nav/guides/">

    <!-- Open Graph / Facebook -->
    <meta property="og:type" content="website">
    <meta property="og:url" content="https://www.yuccaclub.com/nav/guides/">
    <meta property="og:title" content="Guides | Las Cruces & El Paso Expert Travel Guide | Yucca Club">
    <meta property="og:description" content="Expert guides for Las Cruces, El Paso, and Southern New Mexico. Discover hiking trails, restaurants, events, and hidden gems in the Borderland region.">
    <meta property="og:image" content="https://www.yuccaclub.com/ui/img/social-share.png">
    <meta property="og:image:width" content="1200">
    <meta property="og:image:height" content="630">
    <meta property="og:image:alt" content="Yucca Club Guides - Las Cruces El Paso Travel">
    <meta property="og:site_name" content="Yucca Club">
    <meta property="og:locale" content="en_US">

    <!-- Twitter -->
    <meta name="twitter:card" content="summary_large_image">
    <meta name="twitter:url" content="https://www.yuccaclub.com/nav/guides/">
    <meta name="twitter:title" content="Guides | Las Cruces & El Paso Expert Travel Guide | Yucca Club">
    <meta name="twitter:description" content="Expert guides for Las Cruces, El Paso, and Southern New Mexico. Discover hiking trails, restaurants, events, and hidden gems in the Borderland region.">
    <meta name="twitter:image" content="https://www.yuccaclub.com/ui/img/social-share.png">
    <meta name="twitter:image:alt" content="Yucca Club Guides - Las Cruces El Paso Travel">

    <!-- Fonts & Icons -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Lato:wght@400;700&family=Lora:wght@500;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    
    <!-- Styles -->
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="../../ui/css/styles.css">

    <!-- JSON-LD for Rich Snippets -->
    <script type="application/ld+json">
        {
          "@context": "https://schema.org",
          "@type": "CollectionPage",
          "name": "Yucca Club Guides",
          "url": "https://www.yuccaclub.com/nav/guides/",
          "description": "A curated collection of expert guides for exploring the best of Southern New Mexico, including hiking, dining, and day trips.",
          "publisher": {
            "@type": "Organization",
            "name": "Yucca Club",
            "logo": {
              "@type": "ImageObject",
              "url": "https://www.yuccaclub.com/ui/img/logo.png"
            }
          }
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
                    <li><a href="../stories/index.php">Stories</a></li>
                    <li><a href="index.php" class="active">Guides</a></li>
                    <li><a href="../events/index.php">Events</a></li>
                    <li><a href="https://yucca.printify.me/" target="_blank" rel="noopener noreferrer">Shop</a></li>
                    <li><a href="../community/index.php">Community</a></li>
                    <li><a href="../membership/index.php">Membership</a></li>
                    <li><a href="../exclusive/index.php">Exclusive</a></li>
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
                <div style="height: 100px; margin-bottom: 2rem;" class="shimmer-placeholder"></div>
                <div class="guides-grid">
                    <div class="shimmer-placeholder" style="height: 380px;"></div>
                    <div class="shimmer-placeholder" style="height: 380px;"></div>
                    <div class="shimmer-placeholder" style="height: 380px;"></div>
                    <div class="shimmer-placeholder" style="height: 380px;"></div>
                    <div class="shimmer-placeholder" style="height: 380px;"></div>
                    <div class="shimmer-placeholder" style="height: 380px;"></div>
                </div>
            </div>
        </div>
        
        <div class="container guides-container hidden">
<header class="text-center py-8">
    <div class="flex justify-center mb-4">
        <!-- Heroicon: Map Pin -->
        <svg xmlns="http://www.w3.org/2000/svg" 
             fill="none" 
             viewBox="0 0 24 24" 
             stroke-width="1.5" 
             stroke="currentColor" 
             class="w-16 h-16"
             style="color: #b8ba20;">
          <path stroke-linecap="round" stroke-linejoin="round" d="M12 11.25a2.25 2.25 0 100-4.5 2.25 2.25 0 000 4.5z" />
          <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 10.5c0 7.125-7.5 11.25-7.5 11.25S4.5 17.625 4.5 10.5a7.5 7.5 0 1115 0z" />
        </svg>
    </div>
    <h1 class="text-5xl font-serif mb-2">In-Depth Local Guides</h1>
    <p class="text-xl max-w-2xl mx-auto">
        Curated, actionable guides to help you explore the authentic Southwest, 
        from the best local eats to unforgettable outdoor adventures.
    </p>
</header>


            <div class="guide-filters" role="tablist" aria-label="Guide Categories">
                <button class="filter-btn active" data-filter="all" role="tab" aria-selected="true">All</button>
                <?php foreach (['Hiking', 'Food & Drink', 'Day Trips'] as $cat): ?>
                <?php if (in_array($cat, $categories)): ?>
                <button class="filter-btn" data-filter="<?= strtolower(str_replace([' ', '&'], ['-', ''], $cat)) ?>" role="tab"><?= $cat ?></button>
                <?php endif; ?>
                <?php endforeach; ?>
            </div>

            <div class="guides-grid">
                <?php if (count($guides) === 0): ?>
                <p style="text-align: center; padding: 3rem; grid-column: 1/-1; opacity: 0.7;">No guides yet. Check back soon!</p>
                <?php else: ?>
                    <?php foreach ($guides as $guide): ?>
                    <article class="guide-card fade-in-on-scroll" data-category="<?= strtolower(str_replace([' ', '&'], ['-', ''], $guide['category'] ?: '')) ?>">
                        <div class="card-image"><img src="<?= htmlspecialchars($guide['featured_image']) ?>" alt="<?= htmlspecialchars($guide['title']) ?>" loading="lazy"></div>
                        <div class="card-content">
                            <p class="card-tag"><?= htmlspecialchars($guide['category'] ?: 'Guide') ?></p>
                            <h2 class="card-title"><?= htmlspecialchars($guide['title']) ?></h2>
                            <a href="../../view-post.php?slug=<?= htmlspecialchars($guide['slug']) ?>&type=guide" class="card-cta">View Guide</a>
                        </div>
                    </article>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
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
    <script>
        // Hide shimmer loader and show content after load
        document.addEventListener('DOMContentLoaded', () => {
            setTimeout(() => {
                document.getElementById('shimmer-loader').style.display = 'none';
                document.querySelector('.guides-container').classList.remove('hidden');
            }, 300);
            
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
    </script>
</body>
</html>

