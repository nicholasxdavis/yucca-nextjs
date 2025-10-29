<?php
// Membership Page
require_once '../../config.php';
require_once '../../auth_handler.php';

// Check if user is logged in
$is_logged_in = is_logged_in();
$user_email = $is_logged_in ? htmlspecialchars($_SESSION['user_email']) : '';
$user_id = $_SESSION['user_id'] ?? null;

$page_title = "Membership - Yucca Club";
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $page_title ?></title>
    <link rel="icon" type="image/png" href="../../ui/img/favicon.png">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Lato:wght@400;700&family=Lora:wght@500;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="../../ui/css/styles.css">
</head>
<body>
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
                    <li><a href="index.php" class="active">Membership</a></li>
                </ul>
            </nav>
            <div class="header-actions">
                <?php if ($is_logged_in): ?>
                    <span class="desktop-only" style="font-size: 14px; font-weight: 700;"><?= $user_email ?></span>
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

    <main>
        <div class="container membership-container">
            <header class="text-center py-8">
                <div class="flex justify-center mb-4">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-16 h-16" style="color: #b8ba20;">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 3v2.25m6.364.386-1.591 1.591M21 12h-2.25m-.386 6.364-1.591-1.591M12 18.75V21m-4.773-4.227-1.591 1.591M5.25 12H3m4.227-4.773L5.636 5.636M15.75 12a3.75 3.75 0 1 1-7.5 0 3.75 3.75 0 0 1 7.5 0Z" />
                    </svg>
                </div>
                <h1 class="text-5xl font-serif mb-2">Community Membership</h1>
                <p class="text-xl max-w-2xl mx-auto">
                    Support independent journalism and get exclusive access to premium content, 
                    ad-free reading, and early story previews.
                </p>
            </header>
            
            <div class="pricing-grid">
                <div class="pricing-card">
                    <h2>Free</h2>
                    <div class="price">$0</div>
                    <p class="description">Access to limited content</p>
                    <ul class="features">
                        <li><i class="fas fa-check"></i> Monthly newsletter</li>
                        <li><i class="fas fa-check"></i> Public stories & guides</li>
                        <li><i class="fas fa-check"></i> Community discussions</li>
                    </ul>
                </div>
                
                <div class="pricing-card featured">
                    <h2>Member</h2>
                    <div class="price">$4<span>/month</span></div>
                    <p class="description">Just $1 per week</p>
                    <ul class="features">
                        <li><i class="fas fa-check"></i> Ad-free experience</li>
                        <li><i class="fas fa-check"></i> Exclusive content</li>
                        <li><i class="fas fa-check"></i> Early access to stories</li>
                        <li><i class="fas fa-check"></i> Support local journalism</li>
                        <li><i class="fas fa-check"></i> Member-only community</li>
                        <li><i class="fas fa-check"></i> Monthly member newsletter</li>
                    </ul>
                    <a href="#" class="cta-button" style="opacity: 0.7; cursor: not-allowed;">Coming Soon</a>
                </div>
            </div>
            
            <section class="faq-section">
                <h2>Frequently Asked Questions</h2>
                <div class="faq-item">
                    <button class="faq-question" aria-expanded="false">
                        What do I get with membership?
                        <i class="fas fa-chevron-down"></i>
                    </button>
                    <div class="faq-answer" hidden>
                        <p>Members get ad-free reading, exclusive stories, early access to content, and support our local journalism efforts.</p>
                    </div>
                </div>
                <div class="faq-item">
                    <button class="faq-question" aria-expanded="false">
                        Can I cancel anytime?
                        <i class="fas fa-chevron-down"></i>
                    </button>
                    <div class="faq-answer" hidden>
                        <p>Yes! You can cancel your membership at any time with no questions asked.</p>
                    </div>
                </div>
                <div class="faq-item">
                    <button class="faq-question" aria-expanded="false">
                        How does my support help?
                        <i class="fas fa-chevron-down"></i>
                    </button>
                    <div class="faq-answer" hidden>
                        <p>Your membership directly supports our team of local writers and photographers, helping us create quality content about the Southwest.</p>
                    </div>
                </div>
            </section>
        </div>
    </main>

    <footer class="site-footer">
        <div class="container">
            <div class="footer-content site-footer-main">
                <p>&copy; <?= date('Y') ?> Yucca Club. All Rights Reserved.</p>
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
    
    <!-- Contact Modal -->
    <div class="modal-overlay" id="contact-modal" role="dialog" aria-modal="true" aria-labelledby="contact-modal-title">
        <div class="modal-content">
            <button class="modal-close" aria-label="Close dialog">&times;</button>
            <h2 id="contact-modal-title">Get In Touch</h2>
            <p>Have a question about membership? We'd love to hear from you.</p>
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

    <div id="toast-container" role="status" aria-live="polite"></div>
    <button id="back-to-top" aria-label="Back to top"><i class="fas fa-arrow-up" aria-hidden="true"></i></button>

    <script src="../../ui/js/if-then.js"></script>
    <script src="../../ui/js/main.js"></script>
</body>
</html>

