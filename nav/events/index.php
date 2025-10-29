<?php
require_once '../../config.php';
require_once '../../auth_handler.php';

// Check if user is logged in
$is_logged_in = is_logged_in();
$user_email = $is_logged_in ? htmlspecialchars($_SESSION['user_email']) : '';
$user_id = $_SESSION['user_id'] ?? null;

// Get Ticketmaster API key from environment
$ticketmaster_api_key = TICKETMASTER_API_KEY;
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Yucca Club | Events in Las Cruces & El Paso</title>
    <link rel="icon" type="image/png" href="../../ui/img/favicon.png">

    <meta name="description" content="The ultimate calendar of upcoming events in Southern New Mexico. Find festivals, markets, concerts, and cultural happenings in Las Cruces, El Paso, and Mesilla.">
    <meta name="keywords" content="Las Cruces events, El Paso events, what to do in Las Cruces, farmers market, local events calendar, Mesilla events">
    <meta name="author" content="Yucca Club">
    <link rel="canonical" href="https://www.yuccaclub.com/nav/events/">

    <meta property="og:type" content="website">
    <meta property="og:url" content="https://www.yuccaclub.com/nav/events/">
    <meta property="og:title" content="Yucca Club | Events in Las Cruces & El Paso">
    <meta property="og:description" content="The ultimate calendar of upcoming events in Southern New Mexico.">
    <meta property="og:image" content="https://www.yuccaclub.com/ui/img/social-share.png">

    <meta property="twitter:card" content="summary_large_image">
    <meta property="twitter:url" content="https://www.yuccaclub.com/nav/events/">
    <meta property="twitter:title" content="Yucca Club | Events in Las Cruces & El Paso">
    <meta property="twitter:description" content="The ultimate calendar of upcoming events in Southern New Mexico.">
    <meta property="twitter:image" content="https://www.yuccaclub.com/ui/img/social-share.png">

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Lato:wght@400;700&family=Lora:wght@500;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">

    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="../../ui/css/styles.css">

    <script type="application/ld+json">
        {
          "@context": "https://schema.org",
          "@type": "Event",
          "name": "Las Cruces & El Paso Area Events",
          "description": "A curated list of upcoming events in the Southern New Mexico region.",
          "startDate": "2025-10-03",
          "location": {
            "@type": "Place",
            "name": "Las Cruces & El Paso Area",
            "address": "Southern New Mexico"
          },
          "organizer": {
            "@type": "Organization",
            "name": "Yucca Club",
            "url": "https://www.yuccaclub.com"
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
                    <li><a href="../guides/index.php">Guides</a></li>
                    <li><a href="../events/index.php" class="active">Events</a></li>
                    <li><a href="https://yucca.printify.me/" target="_blank" rel="noopener noreferrer">Shop</a></li>
                    <li><a href="../community/index.php">Community</a></li>
                    <li><a href="../membership/index.php">Membership</a></li>
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
    
    <div class="live-conditions-bar" id="live-conditions" aria-live="polite">
        <span>Loading regional conditions...</span>
    </div>

    <main>
        <div id="shimmer-loader">
            <div class="max-w-4xl mx-auto px-6">
                <div style="height: 120px; margin-bottom: 2rem;" class="shimmer-placeholder"></div>
                <div class="shimmer-placeholder" style="height: 150px; margin-bottom: 1.5rem;"></div>
                <div class="shimmer-placeholder" style="height: 150px; margin-bottom: 1.5rem;"></div>
                <div class="shimmer-placeholder" style="height: 150px; margin-bottom: 1.5rem;"></div>
            </div>
        </div>

        <div class="container events-container hidden">
            <div class="max-w-4xl mx-auto">
<header class="text-center py-8">
    <div class="flex justify-center mb-4">
        <!-- Heroicon: Calendar -->
        <svg xmlns="http://www.w3.org/2000/svg" 
             fill="none" 
             viewBox="0 0 24 24" 
             stroke-width="1.5" 
             stroke="currentColor" 
             class="w-16 h-16"
             style="color: #b8ba20;">
          <path stroke-linecap="round" stroke-linejoin="round" d="M6.75 3v2.25M17.25 3v2.25M3 9.75h18M4.5 7.5h15a1.5 1.5 0 011.5 1.5v10.5a1.5 1.5 0 01-1.5 1.5h-15a1.5 1.5 0 01-1.5-1.5V9a1.5 1.5 0 011.5-1.5z" />
        </svg>
    </div>
    <h1 class="text-5xl font-serif mb-2">Upcoming Events</h1>
    <p class="text-xl max-w-2xl mx-auto">
        Your curated guide to what's happening in Las Cruces, El Paso, and the surrounding communities. 
        Never miss out on the best local happenings.
    </p>
</header>


                <section id="events-list" class="events-list">
                    </section>
            </div>
        </div>
    </main>
        <section class="membership-cta fade-in-on-scroll">
        <div class="container">
            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="icon-above-title">
              <path stroke-linecap="round" stroke-linejoin="round" d="M12 3v2.25m6.364.386-1.591 1.591M21 12h-2.25m-.386 6.364-1.591-1.591M12 18.75V21m-4.773-4.227-1.591 1.591M5.25 12H3m4.227-4.773L5.636 5.636M15.75 12a3.75 3.75 0 1 1-7.5 0 3.75 3.75 0 0 1 7.5 0Z" />
            </svg>
            <h2>Go Deeper Into The Southwest</h2>
            <p>Become a Yucca Club member for just $5/month to unlock exclusive stories, an ad-free experience, and support local, independent writing. </p>
            <a href="nav/membership/" class="cta-button">Explore Membership</a>
        </div>
    </section>
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
        document.addEventListener('DOMContentLoaded', () => {
            const eventsList = document.getElementById('events-list');
            const shimmerLoader = document.getElementById('shimmer-loader');
            const eventsContainer = document.querySelector('.events-container');

            // Ticketmaster API key from environment
            const ticketmasterApiKey = '<?php echo htmlspecialchars($ticketmaster_api_key); ?>';

            // Helper function to find the largest image by area (width * height)
            function getLargestImage(images) {
                if (!images || images.length === 0) return null;

                const largest = images.sort((a, b) => (b.width * b.height) - (a.width * a.height));
                return largest.length > 0 ? largest[0].url : null;
            }

            async function fetchEvents() {
                const today = new Date().toISOString().slice(0, 19) + 'Z';

                const ticketmasterUrl_LC = `https://app.ticketmaster.com/discovery/v2/events.json?city=Las%20Cruces&startDateTime=${today}&apikey=${ticketmasterApiKey}&sort=date,asc`;
                const ticketmasterUrl_EP = `https://app.ticketmaster.com/discovery/v2/events.json?city=El%20Paso&startDateTime=${today}&apikey=${ticketmasterApiKey}&sort=date,asc`;

                try {
                    // Only fetch from Ticketmaster URLs
                    const responses = await Promise.allSettled([
                        fetch(ticketmasterUrl_LC).then(res => res.json()),
                        fetch(ticketmasterUrl_EP).then(res => res.json())
                    ]);

                    const events = [];

                    // Process Ticketmaster results (both cities)
                    [responses[0], responses[1]].forEach(result => {
                        if (result.status === 'fulfilled' && result.value._embedded?.events) {
                            result.value._embedded.events.forEach(event => {
                                if (event._embedded?.venues?.[0]) {
                                    events.push({
                                        title: event.name,
                                        date: new Date(event.dates.start.dateTime),
                                        venue: event._embedded.venues[0].name,
                                        url: event.url,
                                        // Grab the largest image URL
                                        imageUrl: getLargestImage(event.images),
                                        source: 'Ticketmaster'
                                    });
                                }
                            });
                        } else if (result.status === 'rejected') {
                             console.error('Ticketmaster API Error:', result.reason);
                        }
                    });

                    events.sort((a, b) => a.date - b.date);
                    displayEvents(events);

                } catch (error) {
                    console.error('General Fetch Error:', error);
                    eventsList.innerHTML = '<p>There was an error fetching events. Please try again later.</p>';
                } finally {
                    shimmerLoader.style.display = 'none';
                    eventsContainer.classList.remove('hidden');
                }
            }

            function displayEvents(events) {
                if (events.length === 0) {
                    eventsList.innerHTML = '<p>No upcoming events found from available sources.</p>';
                    return;
                }

                const eventsByMonth = events.reduce((acc, event) => {
                    const month = event.date.toLocaleString('default', { month: 'long', year: 'numeric' });
                    if (!acc[month]) {
                        acc[month] = [];
                    }
                    acc[month].push(event);
                    return acc;
                }, {});

                let html = '';
                for (const month in eventsByMonth) {
                    html += `<h2 class="text-4xl font-serif mt-8 mb-4">${month}</h2>`;
                    eventsByMonth[month].forEach(event => {
                        // Image block - uses fixed width container
                        const imageHtml = event.imageUrl ? 
                            `<div class="event-image-container">
                                <img src="${event.imageUrl}" alt="${event.title} event poster" class="event-image">
                            </div>` : 
                            ''; // Empty if no image

                        html += `
                            <article class="event-item">
                                ${imageHtml}
                                <div class="event-info-wrapper">
                                    <div class="event-date">
                                        <span class="month">${event.date.toLocaleString('default', { month: 'short' })}</span>
                                        <span class="day">${event.date.getDate()}</span>
                                    </div>
                                    <div class="event-details-content">
                                        <div>
                                            <h3>${event.title}</h3>
                                            <div class="event-meta">
                                                <span><i class="fas fa-clock" aria-hidden="true"></i> ${event.date.toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' })}</span>
                                                <span><i class="fas fa-map-marker-alt" aria-hidden="true"></i> ${event.venue}</span>
                                            </div>
                                        </div>
                                        <a href="${event.url}" target="_blank" rel="noopener noreferrer" class="cta-button">View on ${event.source}</a>
                                    </div>
                                </div>
                            </article>
                        `;
                    });
                }
                eventsList.innerHTML = html;
            }

            fetchEvents();
        });
    </script>
</body>
</html>


