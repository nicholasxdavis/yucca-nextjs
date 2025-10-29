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
        
        /* Rich Builder Styles */
        .btn-outline {
            background: transparent;
            border: 1px solid #e9ecef;
            color: var(--lobo-gray);
        }
        
        .btn-outline:hover {
            background: #f8f9fa;
            border-color: var(--yucca-yellow);
        }
        
        .btn-sm {
            padding: 0.375rem 0.75rem;
            font-size: 0.875rem;
        }
        
        .blocks-container {
            display: grid;
            gap: 0.75rem;
            margin-top: 1rem;
        }
        
        .blocks-container.has-blocks {
            min-height: 200px;
        }
        
        .block-item {
            background: white;
            padding: 1rem;
            border-radius: 8px;
            border: 1px solid #ede9df;
            position: relative;
            transition: all 0.3s ease;
        }
        
        .block-item:hover {
            box-shadow: 0 4px 12px rgba(0,0,0,0.1);
            border-color: var(--yucca-yellow);
        }
        
        .block-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 0.5rem;
            padding-bottom: 0.5rem;
            border-bottom: 1px solid #f0f0f0;
        }
        
        .block-actions {
            display: flex;
            gap: 0.5rem;
        }
        
        .block-actions button {
            padding: 0.375rem 0.75rem;
            font-size: 0.75rem;
            border: 1px solid #e9ecef;
            background: white;
            border-radius: 4px;
            cursor: pointer;
            transition: all 0.2s;
        }
        
        .block-actions button:hover {
            background: #f8f9fa;
        }
        
        .block-content {
            margin-top: 0.5rem;
        }
        
        .block-content input,
        .block-content textarea {
            border: 1px solid #e9ecef;
            border-radius: 4px;
            font-family: inherit;
        }
        
        .block-content input:focus,
        .block-content textarea:focus {
            outline: none;
            border-color: var(--yucca-yellow);
            box-shadow: 0 0 0 2px rgba(184,186,32,0.1);
        }
        
        /* Mobile Responsive */
        @media (max-width: 768px) {
            .rich-toolbar {
                flex-direction: column;
                gap: 0.5rem;
            }
            
            .toolbar-section {
                width: 100%;
            }
            
            .toolbar-buttons {
                justify-content: center;
            }
            
            .block-actions {
                flex-wrap: wrap;
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
                    <li><a href="nav/exclusive/index.php">Exclusive</a></li>
                </ul>
            </nav>
            <div class="header-actions">
                <span class="desktop-only" style="font-size: 14px; font-weight: 700;"><?= $user_email ?></span>
                <?php if ($user_posts_count > 0): ?>
                <a href="my-posts.php" id="my-posts" aria-label="My posts" title="My posts" class="desktop-only" style="font-size: 14px; color: var(--yucca-yellow); margin-right: 0.5rem;">
                    <i class="fas fa-file-alt" aria-hidden="true"></i>
                </a>
                <?php endif; ?>
                <a href="create-post.php" id="create-post" aria-label="Create post" title="Create post" class="desktop-only" style="font-size: 14px; color: var(--yucca-yellow); margin-right: 0.5rem;">
                    <i class="fas fa-edit" aria-hidden="true"></i>
                </a>
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
                    <div style="display: flex; gap: 1rem; align-items: center; margin-bottom: 0.5rem;">
                        <button type="button" class="btn btn-secondary btn-sm" onclick="toggleBuilder()">
                            <i class="fas fa-magic"></i> Toggle Rich Builder
                        </button>
                        <span id="builder-status" style="font-size: 0.9rem; opacity: 0.8; font-weight: 600;">
                            Rich builder: off
                        </span>
                        <button type="button" id="load-template-btn" class="btn btn-primary btn-sm" onclick="loadTemplate()" style="display: none;">
                            <i class="fas fa-file-import"></i> Load Template
                        </button>
                    </div>
                    
                    <!-- Rich Builder -->
                    <div id="rich-builder" style="display: none; background: #F5F1E9; border: 2px solid #ede9df; border-radius: 8px; padding: 1rem; margin-top: 1rem;">
                        <!-- Toolbar -->
                        <div class="rich-toolbar" style="display: flex; flex-wrap: wrap; gap: 1rem; margin-bottom: 1rem; padding: 1rem; background: white; border-radius: 8px; border: 1px solid #e5e5e5;">
                            <div class="toolbar-section">
                                <h4 style="margin: 0; font-size: 0.9rem; color: var(--lobo-gray);">Text Blocks</h4>
                                <div class="toolbar-buttons" style="display: flex; gap: 0.5rem; flex-wrap: wrap;">
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
                                <div class="toolbar-buttons" style="display: flex; gap: 0.5rem; flex-wrap: wrap;">
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
                                <div class="toolbar-buttons" style="display: flex; gap: 0.5rem; flex-wrap: wrap;">
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
                        <div id="blocks-container" class="blocks-container" style="display: grid; gap: 0.75rem; margin-top: 1rem;"></div>
                        
                        <!-- Quick Actions -->
                        <div class="quick-actions" style="margin-top: 1rem; padding-top: 1rem; border-top: 1px solid #e5e5e5;">
                            <button type="button" class="btn btn-secondary btn-sm" onclick="addBlock('paragraph')">
                                <i class="fas fa-plus"></i> Add Paragraph
                            </button>
                            <button type="button" class="btn btn-secondary btn-sm" onclick="duplicateLastBlock()">
                                <i class="fas fa-copy"></i> Duplicate Last
                            </button>
                            <button type="button" class="btn btn-danger btn-sm" onclick="clearAllBlocks()">
                                <i class="fas fa-trash"></i> Clear All
                            </button>
                            <button type="button" id="preview-btn" class="btn btn-secondary btn-sm" onclick="previewContent()" style="display: none;">
                                <i class="fas fa-eye"></i> Preview
                            </button>
                            <button type="button" id="export-btn" class="btn btn-secondary btn-sm" onclick="exportContent()" style="display: none;">
                                <i class="fas fa-download"></i> Export
                            </button>
                        </div>
                    </div>
                    
                    <!-- Fallback textarea for non-rich content -->
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
        // Enhanced Upload Page JavaScript with Rich Builder
        let builderEnabled = false;
        let blocks = [];
        
        // Generate UUID function for cross-browser compatibility
        function generateUUID() {
            return 'xxxxxxxx-xxxx-4xxx-yxxx-xxxxxxxxxxxx'.replace(/[xy]/g, function(c) {
                const r = Math.random() * 16 | 0;
                const v = c == 'x' ? r : (r & 0x3 | 0x8);
                return v.toString(16);
            });
        }
        
        // Rich Builder Functions
        function toggleBuilder() {
            builderEnabled = !builderEnabled;
            const builder = document.getElementById('rich-builder');
            const status = document.getElementById('builder-status');
            const previewBtn = document.getElementById('preview-btn');
            const exportBtn = document.getElementById('export-btn');
            const loadTemplateBtn = document.getElementById('load-template-btn');
            
            builder.style.display = builderEnabled ? 'block' : 'none';
            status.textContent = `Rich builder: ${builderEnabled ? 'on' : 'off'}`;
            previewBtn.style.display = builderEnabled ? 'inline-flex' : 'none';
            exportBtn.style.display = builderEnabled ? 'inline-flex' : 'none';
            loadTemplateBtn.style.display = builderEnabled ? 'inline-flex' : 'none';
            
            if (builderEnabled && blocks.length === 0) {
                try {
                    const raw = document.getElementById('content').value.trim();
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
                    const raw = document.getElementById('content').value.trim();
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
                const loadTemplateBtn = document.getElementById('load-template-btn');
                
                builder.style.display = 'block';
                status.textContent = 'Rich builder: on';
                previewBtn.style.display = 'inline-flex';
                exportBtn.style.display = 'inline-flex';
                loadTemplateBtn.style.display = 'inline-flex';
            }
            
            // Clear existing blocks
            blocks = [];
            
            // Load a simplified template for upload page
            const templateBlocks = [
                {
                    id: generateUUID(),
                    type: 'heading',
                    data: { text: 'Welcome to Yucca Club', level: 1 }
                },
                {
                    id: generateUUID(),
                    type: 'paragraph',
                    data: { text: 'Yucca Club was built to tell the stories that live here. From Las Cruces to El Paso, Alamogordo, Cloudcroft, Silver City, Ruidoso, Juárez, Tucson, Phoenix, Hatch, Deming, Mesilla, and everywhere in between.\n\nThis is a space for real people, local stories, and honest perspectives.' }
                },
                {
                    id: generateUUID(),
                    type: 'subheading',
                    data: { text: 'What You\'ll Find', level: 2 }
                },
                {
                    id: generateUUID(),
                    type: 'list',
                    data: { 
                        items: [
                            'Local guides and hidden gems',
                            'Community stories and events',
                            'Southwest culture and traditions',
                            'Authentic local experiences'
                        ]
                    }
                },
                {
                    id: generateUUID(),
                    type: 'blockquote',
                    data: { text: 'Yucca Club — Where the Southwest comes alive through authentic stories, local guides, and community connection.' }
                }
            ];
            
            // Set the blocks
            blocks = templateBlocks;
            
            // Also populate the form fields
            document.getElementById('title').value = 'Welcome to Yucca Club';
            document.getElementById('category').value = 'Yucca-Club';
            document.getElementById('featured-image').value = 'https://www.blacnova.net/ui/img/hero.png';
            document.getElementById('excerpt').value = 'Yucca Club was built to tell the stories that live here. From Las Cruces to El Paso and everywhere in between.';
            
            // Render the blocks
            renderBlocks();
            
            // Show success message
            showToast('🎉 Yucca Club template loaded successfully! You can now customize the content as needed.', 'success', 5000);
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
            // Save blocks as JSON for storage
            document.getElementById('content').value = JSON.stringify(blocks, null, 2);
        }
        
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
        
        function moveBlock(blockId, direction) {
            const index = blocks.findIndex(block => block.id === blockId);
            
            if (direction === 'up' && index > 0) {
                [blocks[index], blocks[index - 1]] = [blocks[index - 1], blocks[index]];
                renderBlocks();
            } else if (direction === 'down' && index < blocks.length - 1) {
                [blocks[index], blocks[index + 1]] = [blocks[index + 1], blocks[index]];
                renderBlocks();
            }
        }
        
        // Character counters and form handling
        document.addEventListener('DOMContentLoaded', function() {
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