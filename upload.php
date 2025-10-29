<?php
/**
 * Enhanced Upload Page - Editor Access Only
 * Modern, user-friendly interface for editors to upload new content
 */

require_once 'config.php';

// Check if user is editor or admin
if (!is_editor() && !is_admin()) {
    header('Location: index.php');
    exit;
}

    $user_email = htmlspecialchars($_SESSION['user_email']);
    $user_role = $_SESSION['user_role'] ?? 'user';
$page_title = "Content Upload - Yucca Club";
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $page_title ?></title>
    <link rel="icon" type="image/png" href="ui/img/favicon.png">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Lato:wght@400;700&family=Lora:wght@500;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="ui/css/styles.css">
    <style>
        /* Enhanced Upload Page Styles */
        .upload-wrapper {
            max-width: 1200px;
            margin: 0 auto;
            padding: 2rem 1rem;
        }
        
        .upload-header {
            text-align: center;
            margin-bottom: 3rem;
            padding: 2rem;
            background: linear-gradient(135deg, var(--off-white), #f8f9fa);
            border-radius: 16px;
            box-shadow: 0 8px 32px rgba(0,0,0,0.1);
        }
        
        .upload-header h1 {
            font-size: 2.5rem;
            margin-bottom: 0.75rem;
            background: linear-gradient(135deg, var(--yucca-yellow), #8a8c15);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }
        
        .upload-header p {
            opacity: 0.8;
            font-size: 1.1rem;
            max-width: 600px;
            margin: 0 auto;
        }
        
        .user-info {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 1rem;
            margin-top: 1.5rem;
            padding: 1rem;
            background: white;
            border-radius: 12px;
            border: 2px solid #f0f0f0;
        }
        
        .user-avatar {
            width: 50px;
            height: 50px;
            border-radius: 50%;
            background: linear-gradient(135deg, var(--yucca-yellow), #8a8c15);
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.25rem;
            color: white;
            font-weight: 700;
        }
        
        .upload-card {
            background: var(--off-white);
            border-radius: 16px;
            padding: 2.5rem;
            box-shadow: 0 8px 32px rgba(0,0,0,0.1);
            border: 1px solid rgba(255,255,255,0.2);
            margin-bottom: 2rem;
        }
        
        .form-group {
            margin-bottom: 2rem;
        }
        
        .form-label {
            display: block;
            margin-bottom: 0.75rem;
            font-weight: 700;
            color: var(--lobo-gray);
            font-size: 1.1rem;
        }
        
        .form-input, .form-textarea, .form-select {
            width: 100%;
            padding: 1rem;
            border: 2px solid #e0e0e0;
            border-radius: 12px;
            font-family: inherit;
            font-size: 1rem;
            transition: all 0.3s ease;
            background: white;
            box-shadow: 0 2px 4px rgba(0,0,0,0.05);
        }
        
        .form-input:focus, .form-textarea:focus, .form-select:focus {
            outline: none;
            border-color: var(--yucca-yellow);
            box-shadow: 0 0 0 3px rgba(184,186,32,0.1);
            transform: translateY(-1px);
        }
        
        .form-textarea {
            resize: vertical;
            min-height: 200px;
        }
        
        .btn {
            padding: 1rem 2rem;
            border-radius: 12px;
            border: none;
            cursor: pointer;
            font-weight: 700;
            font-size: 1rem;
            transition: all 0.3s ease;
            display: inline-flex;
            align-items: center;
            gap: 0.75rem;
            text-decoration: none;
        }
        
        .btn-primary {
            background: linear-gradient(135deg, var(--yucca-yellow), #8a8c15);
            color: white;
            box-shadow: 0 4px 15px rgba(184,186,32,0.3);
        }
        
        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(184,186,32,0.4);
        }
        
        .btn-secondary {
            background: #f8f9fa;
            color: var(--lobo-gray);
            border: 2px solid #e9ecef;
        }
        
        .btn-secondary:hover {
            background: #e9ecef;
            transform: translateY(-1px);
        }
        
        .btn-danger {
            background: linear-gradient(135deg, #dc3545, #c82333);
            color: white;
            box-shadow: 0 4px 15px rgba(220,53,69,0.3);
        }
        
        .btn-danger:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(220,53,69,0.4);
        }
        
        .form-actions {
            display: flex;
            gap: 1rem;
            margin-top: 2rem;
            flex-wrap: wrap;
        }
        
        .success-message, .error-message {
            padding: 1.25rem 1.75rem;
            border-radius: 12px;
            margin-bottom: 2rem;
            display: none;
            font-weight: 600;
            box-shadow: 0 4px 12px rgba(0,0,0,0.1);
        }
        
        .success-message {
            background: linear-gradient(135deg, #28a745, #20c997);
            color: white;
        }
        
        .error-message {
            background: linear-gradient(135deg, #dc3545, #fd7e14);
            color: white;
        }
        
        .help-card {
            background: linear-gradient(135deg, #f8f9fa, #e9ecef);
            border-radius: 16px;
            padding: 2rem;
            border: 2px solid #e9ecef;
        }
        
        .help-card h3 {
            margin-bottom: 1.5rem;
            color: var(--lobo-gray);
            font-size: 1.5rem;
        }
        
        .help-list {
            list-style: none;
            padding: 0;
        }
        
        .help-list li {
            margin-bottom: 1rem;
            padding: 1rem;
            background: white;
            border-radius: 8px;
            border-left: 4px solid var(--yucca-yellow);
            box-shadow: 0 2px 4px rgba(0,0,0,0.05);
        }
        
        .help-list li strong {
            color: var(--yucca-yellow);
        }
        
        .tip-box {
            margin-top: 1.5rem;
            padding: 1.5rem;
            background: linear-gradient(135deg, rgba(184,186,32,0.1), rgba(138,140,21,0.1));
            border-radius: 12px;
            border: 2px solid rgba(184,186,32,0.2);
        }
        
        .tip-box strong {
            color: var(--yucca-yellow);
        }
        
        .char-counter {
            font-size: 0.85rem;
            color: #666;
            margin-top: 0.25rem;
            text-align: right;
        }
        
        .char-counter.warning {
            color: #dc3545;
        }
        
        /* Mobile Responsive */
        @media (max-width: 768px) {
            .upload-wrapper {
                padding: 1rem 0.5rem;
            }
            
            .upload-header h1 {
                font-size: 2rem;
            }
            
            .upload-card {
                padding: 1.5rem 1rem;
            }
            
            .form-actions {
                flex-direction: column;
            }
            
            .btn {
                width: 100%;
                justify-content: center;
            }
            
            .user-info {
                flex-direction: column;
                text-align: center;
            }
        }
        
        /* Loading States */
        .loading {
            opacity: 0.6;
            pointer-events: none;
        }
        
        .loading::after {
            content: '';
            position: absolute;
            top: 50%;
            left: 50%;
            width: 20px;
            height: 20px;
            margin: -10px 0 0 -10px;
            border: 2px solid var(--yucca-yellow);
            border-top: 2px solid transparent;
            border-radius: 50%;
            animation: spin 1s linear infinite;
        }
        
        @keyframes spin {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
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
                <a href="admin.php" class="desktop-only" style="font-size: 14px; color: var(--yucca-yellow); margin-right: 0.5rem;" title="Admin Panel">
                    <i class="fas fa-cog" aria-hidden="true"></i>
                </a>
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
                        <a href="admin.php">
                            <i class="fas fa-cog"></i>Admin Panel
                        </a>
                        <a href="index.php?logout=true">
                            <i class="fas fa-sign-out-alt"></i>Logout
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
    <main class="upload-wrapper">
        <div class="upload-header">
            <h1><i class="fas fa-upload"></i> Content Upload</h1>
            <p>Create and publish new stories and guides for the Yucca Club community</p>
            
            <div class="user-info">
                <div class="user-avatar">
                    <?= strtoupper(substr($user_email, 0, 1)) ?>
                </div>
            <div>
                    <div style="font-weight: 700; color: var(--lobo-gray);"><?= htmlspecialchars(explode('@', $user_email)[0]) ?></div>
                    <div style="font-size: 0.9rem; opacity: 0.7;"><?= ucfirst($user_role) ?> Editor</div>
                </div>
            </div>
        </div>

        <div id="success-message" class="success-message">
            <i class="fas fa-check-circle"></i> Content uploaded successfully! Redirecting to admin panel...
        </div>

        <div id="error-message" class="error-message"></div>

        <div class="upload-card">
            <h2 style="margin-bottom: 2rem; color: var(--lobo-gray);">
                <i class="fas fa-plus-circle"></i> Create New Content
            </h2>
            
            <form id="upload-form">
                <div class="form-group">
                    <label class="form-label" for="content-type">
                        <i class="fas fa-tag"></i> Content Type *
                    </label>
                    <select id="content-type" name="type" class="form-select" required>
                        <option value="">Select content type</option>
                        <option value="stories">📖 Story</option>
                        <option value="guides">🗺️ Guide</option>
                    </select>
                </div>

                <div class="form-group">
                    <label class="form-label" for="title">
                        <i class="fas fa-heading"></i> Title *
                    </label>
                    <input type="text" id="title" name="title" class="form-input" placeholder="Enter a compelling title..." required>
                    <div class="char-counter" id="title-counter">0/100 characters</div>
                </div>

                <div class="form-group">
                    <label class="form-label" for="category">
                        <i class="fas fa-folder"></i> Category
                    </label>
                    <input type="text" id="category" name="category" class="form-input" placeholder="e.g., Adventure, Food, Art, Culture">
                    <small style="color: #666; font-size: 0.85rem; margin-top: 0.25rem; display: block;">Helps organize and categorize your content</small>
                </div>

                <div class="form-group">
                    <label class="form-label" for="featured-image">
                        <i class="fas fa-image"></i> Featured Image URL
                    </label>
                    <input type="url" id="featured-image" name="featured_image" class="form-input" placeholder="https://example.com/your-image.jpg">
                    <small style="color: #666; font-size: 0.85rem; margin-top: 0.25rem; display: block;">Enter the full URL of your image (host images externally)</small>
                </div>

                <div class="form-group">
                    <label class="form-label" for="excerpt">
                        <i class="fas fa-align-left"></i> Excerpt
                    </label>
                    <textarea id="excerpt" name="excerpt" class="form-textarea" rows="3" placeholder="Brief description of the content..."></textarea>
                    <div class="char-counter" id="excerpt-counter">0/300 characters</div>
                </div>

                <div class="form-group">
                    <label class="form-label" for="content">
                        <i class="fas fa-edit"></i> Content *
                    </label>
                    <textarea id="content" name="content" class="form-textarea" rows="12" placeholder="Write your content here... (HTML supported)" required></textarea>
                    <div class="char-counter" id="content-counter">0 characters</div>
                </div>

                <div class="form-group">
                    <label class="form-label" for="status">
                        <i class="fas fa-eye"></i> Publication Status
                    </label>
                    <select id="status" name="status" class="form-select">
                        <option value="draft">📝 Draft (Save for later)</option>
                        <option value="published">🚀 Published (Go live immediately)</option>
                    </select>
                </div>

                <div class="form-actions">
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-paper-plane"></i> Publish Content
                    </button>
                    <button type="reset" class="btn btn-secondary">
                        <i class="fas fa-undo"></i> Clear Form
                    </button>
                    <a href="admin.php" class="btn btn-secondary">
                        <i class="fas fa-arrow-left"></i> Back to Admin
                    </a>
                </div>
            </form>
        </div>

        <div class="help-card">
            <h3><i class="fas fa-lightbulb"></i> Quick Guide</h3>
            <ul class="help-list">
                <li><strong>Title:</strong> The main title of your post - make it compelling and descriptive</li>
                <li><strong>Category:</strong> Helps organize content and improve discoverability</li>
                <li><strong>Featured Image:</strong> Enter the full image URL (host images externally for best performance)</li>
                <li><strong>Excerpt:</strong> Short preview text that appears in listings and social shares</li>
                <li><strong>Content:</strong> Full post content - HTML is supported for rich formatting</li>
                <li><strong>Status:</strong> Draft saves for later editing, Published goes live immediately</li>
            </ul>
            
            <div class="tip-box">
                <strong>💡 Pro Tip:</strong> For advanced formatting with drag-and-drop sections, images, headings, and more, use the admin panel's rich content editor! It provides a visual interface similar to modern page builders.
            </div>
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
        // Enhanced Upload Page JavaScript
        document.addEventListener('DOMContentLoaded', function() {
            // Character counters
            const titleInput = document.getElementById('title');
            const excerptTextarea = document.getElementById('excerpt');
            const contentTextarea = document.getElementById('content');
            
            const titleCounter = document.getElementById('title-counter');
            const excerptCounter = document.getElementById('excerpt-counter');
            const contentCounter = document.getElementById('content-counter');
            
            // Title counter
            titleInput.addEventListener('input', function() {
                const count = this.value.length;
                titleCounter.textContent = `${count}/100 characters`;
                titleCounter.className = count > 100 ? 'char-counter warning' : 'char-counter';
            });
            
            // Excerpt counter
            excerptTextarea.addEventListener('input', function() {
                const count = this.value.length;
                excerptCounter.textContent = `${count}/300 characters`;
                excerptCounter.className = count > 300 ? 'char-counter warning' : 'char-counter';
            });
            
            // Content counter
            contentTextarea.addEventListener('input', function() {
                const count = this.value.length;
                contentCounter.textContent = `${count} characters`;
            });
            
            // Auto-generate slug from title
            titleInput.addEventListener('input', function() {
                const slug = this.value.toLowerCase()
                    .replace(/[^a-z0-9\s-]/g, '')
                    .replace(/\s+/g, '-')
                    .replace(/-+/g, '-')
                    .replace(/^-|-$/g, '');
                
                // Store slug for form submission
                document.getElementById('upload-form').dataset.generatedSlug = slug;
            });
        });
        
        // Enhanced form submission
        document.getElementById('upload-form').addEventListener('submit', async function(e) {
            e.preventDefault();
            
            const formData = new FormData(e.target);
            const type = document.getElementById('content-type').value;
            const title = document.getElementById('title').value.trim();
            
            // Enhanced validation
            const errors = [];
            
            if (!title) {
                errors.push('Please enter a title');
            } else if (title.length < 5) {
                errors.push('Title must be at least 5 characters long');
            } else if (title.length > 100) {
                errors.push('Title must be less than 100 characters');
            }
            
            const content = document.getElementById('content').value.trim();
            if (!content) {
                errors.push('Please enter content');
            } else if (content.length < 50) {
                errors.push('Content must be at least 50 characters long');
            }
            
            if (errors.length > 0) {
                showToast('❌ Please fix the following issues:\n• ' + errors.join('\n• '), 'error', 6000);
                return;
            }
            
            // Generate slug from title
            const slug = title.toLowerCase()
                .replace(/[^a-z0-9\s-]/g, '')
                .replace(/\s+/g, '-')
                .replace(/-+/g, '-')
                .replace(/^-|-$/g, '');
            
            formData.append('title', title);
            formData.append('slug', slug);
            formData.append('action', 'create');
            
            // Show loading state
            const submitBtn = e.target.querySelector('button[type="submit"]');
            const originalBtnText = submitBtn.innerHTML;
            submitBtn.disabled = true;
            submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Publishing...';
            submitBtn.classList.add('loading');
            
            try {
                const response = await fetch(`api/content_api.php?type=${type}&action=create`, {
                    method: 'POST',
                    body: formData,
                    credentials: 'same-origin'
                });
                
                const data = await response.json();
                
                if (data.success) {
                    showToast('🎉 Content uploaded successfully! Redirecting to admin panel...', 'success', 3000);
                    setTimeout(() => {
                        window.location.href = 'admin.php';
                    }, 2000);
                } else {
                    showToast('❌ Error: ' + (data.error || 'Failed to upload content'), 'error', 5000);
                    submitBtn.disabled = false;
                    submitBtn.innerHTML = originalBtnText;
                    submitBtn.classList.remove('loading');
                }
            } catch (error) {
                showToast('❌ Error uploading content. Please check your connection and try again.', 'error', 5000);
                console.error('Upload error:', error);
                submitBtn.disabled = false;
                submitBtn.innerHTML = originalBtnText;
                submitBtn.classList.remove('loading');
            }
        });
        
        // Enhanced toast notification function
        function showToast(message, type = 'info', duration = 4000) {
            const toast = document.createElement('div');
            toast.className = 'toast ' + type;
            toast.innerHTML = message.replace(/\n/g, '<br>');
            toast.style.cssText = `
                position: fixed;
                bottom: 2rem;
                right: 2rem;
                background: ${type === 'success' ? 'linear-gradient(135deg, #28a745, #20c997)' : type === 'error' ? 'linear-gradient(135deg, #dc3545, #fd7e14)' : 'linear-gradient(135deg, #007bff, #6f42c1)'};
                color: white;
                padding: 1.25rem 1.75rem;
                border-radius: 12px;
                box-shadow: 0 8px 32px rgba(0,0,0,0.3);
                z-index: 10000;
                animation: slideInRight 0.4s ease;
                max-width: 90%;
                font-weight: 600;
                line-height: 1.4;
            `;
            
            document.body.appendChild(toast);
            
            setTimeout(() => {
                toast.style.animation = 'slideOutRight 0.4s ease';
                setTimeout(() => toast.remove(), 400);
            }, duration);
        }
        
        // Add CSS animations
        const style = document.createElement('style');
        style.textContent = `
            @keyframes slideInRight {
                from { transform: translateX(100%); opacity: 0; }
                to { transform: translateX(0); opacity: 1; }
            }
            @keyframes slideOutRight {
                from { transform: translateX(0); opacity: 1; }
                to { transform: translateX(100%); opacity: 0; }
            }
        `;
        document.head.appendChild(style);
    </script>
    
    <script src="ui/js/if-then.js"></script>
    <script src="ui/js/main.js"></script>
    <script src="ui/js/jquery-loader.js"></script>
</body>
</html>