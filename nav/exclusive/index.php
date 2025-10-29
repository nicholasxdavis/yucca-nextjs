<?php
// Exclusive Page - Pro deals and exclusive content
require_once '../../config.php';

$is_logged_in = is_logged_in();
$user_email = $is_logged_in ? htmlspecialchars($_SESSION['user_email']) : '';
$user_role = $_SESSION['user_role'] ?? 'user';
$is_pro = is_pro();

// Redirect non-pro users to membership page
if (!$is_pro) {
    header('Location: ../membership/index.php?upgrade=exclusive');
    exit;
}

// Get user posts count for conditional navigation
$user_posts_count = 0;
if ($is_logged_in) {
    try {
        $conn = db_connect();
        $stmt = $conn->prepare("SELECT COUNT(*) as count FROM user_posts WHERE user_id = ?");
        $stmt->bind_param("i", $_SESSION['user_id']);
        $stmt->execute();
        $result = $stmt->get_result();
        if ($row = $result->fetch_assoc()) {
            $user_posts_count = $row['count'];
        }
        $stmt->close();
        $conn->close();
    } catch (Exception $e) {
        error_log("Error getting user posts count: " . $e->getMessage());
    }
}

$page_title = "Exclusive Deals - Yucca Club";
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
    <title>Exclusive Deals | Pro Benefits for Las Cruces & El Paso | Yucca Club</title>
    
    <!-- SEO Meta Tags -->
    <meta name="description" content="Access exclusive deals and pro benefits on Yucca Club. Special offers for Las Cruces, El Paso, and Southern New Mexico locals. Premium content, discounts, and member-only perks.">
    <meta name="keywords" content="exclusive deals Las Cruces, El Paso discounts, Yucca Club pro, member benefits, local deals, Southern New Mexico offers, Borderland discounts">
    <meta name="author" content="Yucca Club">
    <meta name="robots" content="index, follow">
    <meta name="language" content="en-US">
    <meta name="geo.region" content="US-NM">
    <meta name="geo.placename" content="Las Cruces">
    <meta name="geo.position" content="32.3199;-106.7637">
    <meta name="ICBM" content="32.3199, -106.7637">
    <meta name="theme-color" content="#b8ba20">
    
    <!-- Canonical URL -->
    <link rel="canonical" href="https://www.yuccaclub.com/nav/exclusive/index.php">
    
    <!-- Open Graph / Facebook -->
    <meta property="og:type" content="website">
    <meta property="og:url" content="https://www.yuccaclub.com/nav/exclusive/index.php">
    <meta property="og:title" content="Exclusive Deals | Pro Benefits for Las Cruces & El Paso | Yucca Club">
    <meta property="og:description" content="Access exclusive deals and pro benefits on Yucca Club. Special offers for Las Cruces, El Paso, and Southern New Mexico locals.">
    <meta property="og:image" content="https://www.yuccaclub.com/ui/img/social-share.png">
    <meta property="og:image:width" content="1200">
    <meta property="og:image:height" content="630">
    <meta property="og:image:alt" content="Yucca Club Exclusive Deals">
    <meta property="og:site_name" content="Yucca Club">
    <meta property="og:locale" content="en_US">
    
    <!-- Twitter -->
    <meta name="twitter:card" content="summary_large_image">
    <meta name="twitter:url" content="https://www.yuccaclub.com/nav/exclusive/index.php">
    <meta name="twitter:title" content="Exclusive Deals | Pro Benefits for Las Cruces & El Paso | Yucca Club">
    <meta name="twitter:description" content="Access exclusive deals and pro benefits on Yucca Club. Special offers for Las Cruces, El Paso, and Southern New Mexico locals.">
    <meta name="twitter:image" content="https://www.yuccaclub.com/ui/img/social-share.png">
    <meta name="twitter:image:alt" content="Yucca Club Exclusive Deals">
    
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
        /* Exclusive Page Styles */
        .exclusive-hero {
            padding: 2rem 1rem;
            text-align: center;
            background: transparent;
        }
        
        .exclusive-hero h1 {
            font-size: 2.5rem;
            margin-bottom: 1rem;
            color: var(--lobo-gray);
        }
        
        .exclusive-hero p {
            font-size: 1.1rem;
            max-width: 600px;
            margin: 0 auto 2rem;
            opacity: 0.8;
        }
        
        .deals-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 2rem;
            margin: 2rem 0;
        }
        
        .deal-card {
            background: var(--off-white);
            border-radius: 12px;
            padding: 2rem;
            box-shadow: 0 4px 12px rgba(0,0,0,0.1);
            border: 2px solid transparent;
            transition: all 0.3s ease;
            position: relative;
            overflow: hidden;
        }
        
        .deal-card:hover {
            transform: translateY(-4px);
            box-shadow: 0 8px 25px rgba(0,0,0,0.15);
            border-color: var(--yucca-yellow);
        }
        
        .deal-card.featured {
            border-color: var(--yucca-yellow);
            background: linear-gradient(135deg, var(--off-white) 0%, #fefefe 100%);
        }
        
        .deal-card.featured::before {
            content: "FEATURED";
            position: absolute;
            top: 1rem;
            right: -2rem;
            background: var(--yucca-yellow);
            color: white;
            padding: 0.25rem 3rem;
            font-size: 0.75rem;
            font-weight: 700;
            transform: rotate(45deg);
            letter-spacing: 1px;
        }
        
        .deal-header {
            display: flex;
            align-items: center;
            gap: 1rem;
            margin-bottom: 1rem;
        }
        
        .deal-icon {
            width: 60px;
            height: 60px;
            border-radius: 50%;
            background: var(--yucca-yellow);
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.5rem;
            color: white;
        }
        
        .deal-title {
            font-size: 1.5rem;
            font-weight: 700;
            color: var(--lobo-gray);
            margin: 0;
        }
        
        .deal-subtitle {
            font-size: 1rem;
            color: #666;
            margin: 0;
        }
        
        .deal-description {
            line-height: 1.6;
            margin-bottom: 1.5rem;
            color: var(--lobo-gray);
        }
        
        .deal-benefits {
            list-style: none;
            padding: 0;
            margin-bottom: 1.5rem;
        }
        
        .deal-benefits li {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            margin-bottom: 0.5rem;
            color: var(--lobo-gray);
        }
        
        .deal-benefits li i {
            color: var(--yucca-yellow);
            font-size: 0.9rem;
        }
        
        .deal-price {
            font-size: 2rem;
            font-weight: 700;
            color: var(--yucca-yellow);
            margin-bottom: 0.5rem;
        }
        
        .deal-original-price {
            font-size: 1rem;
            color: #999;
            text-decoration: line-through;
            margin-bottom: 1rem;
        }
        
        .deal-button {
            width: 100%;
            padding: 1rem;
            background: var(--yucca-yellow);
            color: white;
            border: none;
            border-radius: 8px;
            font-size: 1.1rem;
            font-weight: 700;
            cursor: pointer;
            transition: all 0.3s ease;
            text-decoration: none;
            display: inline-block;
            text-align: center;
        }
        
        .deal-button:hover {
            background: var(--lobo-gray);
            transform: translateY(-2px);
        }
        
        .deal-button.secondary {
            background: transparent;
            border: 2px solid var(--yucca-yellow);
            color: var(--yucca-yellow);
        }
        
        .deal-button.secondary:hover {
            background: var(--yucca-yellow);
            color: white;
        }
        
        .membership-banner {
            background: linear-gradient(135deg, var(--yucca-yellow) 0%, #d4d600 100%);
            color: white;
            padding: 2rem;
            border-radius: 12px;
            text-align: center;
            margin: 3rem 0;
        }
        
        .membership-banner h2 {
            font-size: 2rem;
            margin-bottom: 1rem;
        }
        
        .membership-banner p {
            font-size: 1.1rem;
            margin-bottom: 1.5rem;
            opacity: 0.9;
        }
        
        .membership-banner .btn {
            background: white;
            color: var(--yucca-yellow);
            padding: 1rem 2rem;
            border-radius: 8px;
            text-decoration: none;
            font-weight: 700;
            font-size: 1.1rem;
            transition: all 0.3s ease;
        }
        
        .membership-banner .btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(0,0,0,0.2);
        }
        
        .coming-soon {
            text-align: center;
            padding: 4rem 2rem;
            opacity: 0.7;
        }
        
        .coming-soon i {
            font-size: 4rem;
            margin-bottom: 1rem;
            opacity: 0.3;
            color: var(--yucca-yellow);
        }
        
        .coming-soon h3 {
            font-size: 1.5rem;
            margin-bottom: 0.5rem;
            color: var(--lobo-gray);
        }
        
        .coming-soon p {
            font-size: 1rem;
            color: var(--lobo-gray);
        }
        
        @media (max-width: 768px) {
            .exclusive-hero h1 {
                font-size: 2rem;
            }
            
            .deals-grid {
                grid-template-columns: 1fr;
                gap: 1.5rem;
            }
            
            .deal-card {
                padding: 1.5rem;
            }
            
            .deal-header {
                flex-direction: column;
                text-align: center;
                gap: 0.5rem;
            }
            
            .membership-banner h2 {
                font-size: 1.5rem;
            }
        }
    </style>
</head>
<body>
    <!-- Header -->
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
                    <li><a href="../community/index.php">Community</a></li>
                    <li><a href="../membership/index.php">Membership</a></li>
                    <li><a href="index.php" class="active">Exclusive</a></li>
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
                                <i class="fas fa-user"></i>
                                <span>Sign In</span>
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

    <!-- Main Content -->
    <main>
        <div class="container">
            <!-- Hero Section -->
            <header class="exclusive-hero">
                <div class="flex justify-center mb-4">
                    <!-- Crown Icon -->
                    <svg xmlns="http://www.w3.org/2000/svg" 
                         fill="none" 
                         viewBox="0 0 24 24" 
                         stroke-width="1.5" 
                         stroke="currentColor" 
                         class="w-16 h-16"
                         style="color: #b8ba20;">
                      <path stroke-linecap="round" stroke-linejoin="round" d="M9.813 15.904 9 18.75l-.813-2.846a4.5 4.5 0 0 0-3.09-3.09L2.25 12l2.846-.813a4.5 4.5 0 0 0 3.09-3.09L9 5.25l.813 2.846a4.5 4.5 0 0 0 3.09 3.09L15.75 12l-2.846.813a4.5 4.5 0 0 0-3.09 3.09ZM18.259 8.715 18 9.75l-.259-1.035a3.375 3.375 0 0 0-2.455-2.456L14.25 6l1.036-.259a3.375 3.375 0 0 0 2.455-2.456L18 2.25l.259 1.035a3.375 3.375 0 0 0 2.456 2.456L21.75 6l-1.035.259a3.375 3.375 0 0 0-2.456 2.456ZM16.894 20.567 16.5 21.75l-.394-1.183a2.25 2.25 0 0 0-1.423-1.423L13.5 18.75l1.183-.394a2.25 2.25 0 0 0 1.423-1.423l.394-1.183.394 1.183a2.25 2.25 0 0 0 1.423 1.423l1.183.394-1.183.394a2.25 2.25 0 0 0-1.423 1.423Z" />
                    </svg>
                </div>
                <h1>Exclusive Deals</h1>
                <p>Unlock premium benefits and exclusive offers for Yucca Club members. Get access to special discounts, early releases, and member-only perks across Las Cruces, El Paso, and Southern New Mexico.</p>
            </header>

            <!-- Membership Banner -->
            <div class="membership-banner">
                <h2>🚀 Launching Soon!</h2>
                <p>We're working hard to bring you exclusive deals and pro benefits. Join our community to be the first to know when these amazing offers go live!</p>
                <a href="../membership/index.php" class="btn">Join Yucca Club Pro</a>
            </div>

            <!-- Coming Soon Section -->
            <div class="coming-soon">
                <i class="fas fa-gem"></i>
                <h3>Premium Deals Coming Soon</h3>
                <p>We're curating exclusive partnerships with local businesses across the Borderland region. Stay tuned for:</p>
            </div>

            <!-- Sample Deals Grid (Coming Soon) -->
            <div class="deals-grid">
                <div class="deal-card featured">
                    <div class="deal-header">
                        <div class="deal-icon">
                            <i class="fas fa-utensils"></i>
                        </div>
                        <div>
                            <h3 class="deal-title">Local Restaurant Discounts</h3>
                            <p class="deal-subtitle">Up to 20% off</p>
                        </div>
                    </div>
                    <div class="deal-description">
                        Get exclusive discounts at the best restaurants in Las Cruces, El Paso, and surrounding areas. From authentic Mexican cuisine to farm-to-table experiences.
                    </div>
                    <ul class="deal-benefits">
                        <li><i class="fas fa-check"></i> 20% off at select restaurants</li>
                        <li><i class="fas fa-check"></i> Priority reservations</li>
                        <li><i class="fas fa-check"></i> Chef's table access</li>
                        <li><i class="fas fa-check"></i> Monthly tasting events</li>
                    </ul>
                    <div class="deal-price">Coming Soon</div>
                    <button class="deal-button secondary" disabled>Notify Me</button>
                </div>

                <div class="deal-card">
                    <div class="deal-header">
                        <div class="deal-icon">
                            <i class="fas fa-hiking"></i>
                        </div>
                        <div>
                            <h3 class="deal-title">Adventure & Outdoor</h3>
                            <p class="deal-subtitle">Equipment & Tours</p>
                        </div>
                    </div>
                    <div class="deal-description">
                        Explore the beautiful landscapes of Southern New Mexico with exclusive deals on outdoor gear, guided tours, and adventure experiences.
                    </div>
                    <ul class="deal-benefits">
                        <li><i class="fas fa-check"></i> 15% off outdoor gear</li>
                        <li><i class="fas fa-check"></i> Free guided hikes</li>
                        <li><i class="fas fa-check"></i> Equipment rentals</li>
                        <li><i class="fas fa-check"></i> Photography workshops</li>
                    </ul>
                    <div class="deal-price">Coming Soon</div>
                    <button class="deal-button secondary" disabled>Notify Me</button>
                </div>

                <div class="deal-card">
                    <div class="deal-header">
                        <div class="deal-icon">
                            <i class="fas fa-shopping-bag"></i>
                        </div>
                        <div>
                            <h3 class="deal-title">Local Shopping</h3>
                            <p class="deal-subtitle">Boutiques & Markets</p>
                        </div>
                    </div>
                    <div class="deal-description">
                        Support local businesses with exclusive shopping deals. From artisan crafts to unique finds, discover the best of Borderland retail.
                    </div>
                    <ul class="deal-benefits">
                        <li><i class="fas fa-check"></i> 10% off local boutiques</li>
                        <li><i class="fas fa-check"></i> Early access to sales</li>
                        <li><i class="fas fa-check"></i> Personal shopping sessions</li>
                        <li><i class="fas fa-check"></i> Artisan meet & greets</li>
                    </ul>
                    <div class="deal-price">Coming Soon</div>
                    <button class="deal-button secondary" disabled>Notify Me</button>
                </div>

                <div class="deal-card">
                    <div class="deal-header">
                        <div class="deal-icon">
                            <i class="fas fa-spa"></i>
                        </div>
                        <div>
                            <h3 class="deal-title">Wellness & Spa</h3>
                            <p class="deal-subtitle">Self-Care & Relaxation</p>
                        </div>
                    </div>
                    <div class="deal-description">
                        Treat yourself to exclusive wellness experiences. From spa treatments to fitness classes, prioritize your well-being with special member rates.
                    </div>
                    <ul class="deal-benefits">
                        <li><i class="fas fa-check"></i> 25% off spa services</li>
                        <li><i class="fas fa-check"></i> Free fitness classes</li>
                        <li><i class="fas fa-check"></i> Wellness consultations</li>
                        <li><i class="fas fa-check"></i> Monthly retreats</li>
                    </ul>
                    <div class="deal-price">Coming Soon</div>
                    <button class="deal-button secondary" disabled>Notify Me</button>
                </div>

                <div class="deal-card">
                    <div class="deal-header">
                        <div class="deal-icon">
                            <i class="fas fa-car"></i>
                        </div>
                        <div>
                            <h3 class="deal-title">Transportation</h3>
                            <p class="deal-subtitle">Rides & Rentals</p>
                        </div>
                    </div>
                    <div class="deal-description">
                        Get around the Borderland region with ease. Exclusive deals on rideshare services, car rentals, and local transportation options.
                    </div>
                    <ul class="deal-benefits">
                        <li><i class="fas fa-check"></i> 15% off rideshare</li>
                        <li><i class="fas fa-check"></i> Free airport transfers</li>
                        <li><i class="fas fa-check"></i> Car rental discounts</li>
                        <li><i class="fas fa-check"></i> Priority booking</li>
                    </ul>
                    <div class="deal-price">Coming Soon</div>
                    <button class="deal-button secondary" disabled>Notify Me</button>
                </div>

                <div class="deal-card">
                    <div class="deal-header">
                        <div class="deal-icon">
                            <i class="fas fa-ticket-alt"></i>
                        </div>
                        <div>
                            <h3 class="deal-title">Events & Entertainment</h3>
                            <p class="deal-subtitle">Shows & Experiences</p>
                        </div>
                    </div>
                    <div class="deal-description">
                        Access exclusive events and entertainment experiences. From concerts to cultural events, enjoy the best of Borderland entertainment.
                    </div>
                    <ul class="deal-benefits">
                        <li><i class="fas fa-check"></i> VIP event access</li>
                        <li><i class="fas fa-check"></i> Early ticket sales</li>
                        <li><i class="fas fa-check"></i> Meet & greet passes</li>
                        <li><i class="fas fa-check"></i> Private screenings</li>
                    </ul>
                    <div class="deal-price">Coming Soon</div>
                    <button class="deal-button secondary" disabled>Notify Me</button>
                </div>
            </div>

            <!-- Newsletter Signup -->
            <div class="membership-banner" style="margin-top: 3rem;">
                <h2>📧 Stay Updated</h2>
                <p>Be the first to know when our exclusive deals go live! Join our newsletter for early access and special announcements.</p>
                <a href="../membership/index.php" class="btn">Subscribe to Updates</a>
            </div>
        </div>
    </main>
    
    <!-- Footer -->
    <footer class="site-footer">
        <div class="container">
            <p>&copy; <?= date('Y') ?> Yucca Club. All Rights Reserved. | 
                <a href="../../privacy_policy.php" style="color: inherit;">Privacy Policy</a>
            </p>
        </div>
    </footer>
    
    <div id="toast-container" role="status" aria-live="polite"></div>
    <button id="back-to-top" aria-label="Back to top"><i class="fas fa-arrow-up" aria-hidden="true"></i></button>
    
    <script src="../../ui/js/if-then.js"></script>
    <script src="../../ui/js/main.js"></script>
    <script src="../../ui/js/jquery-loader.js"></script>
</body>
</html>
