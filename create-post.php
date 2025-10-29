<?php
// Create Post Page - Allow all logged-in users
require_once 'config.php';

if (!is_logged_in()) {
    header('Location: index.php');
    exit;
}

$user_id = $_SESSION['user_id'];
$user_email = htmlspecialchars($_SESSION['user_email']);
$user_role = $_SESSION['user_role'] ?? 'user';

$page_title = "Create Community Post - Yucca Club";
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
    <link rel="stylesheet" href="ui/css/enhancements.css">
    <style>
        /* Enhanced Create Post Styles */
        .post-editor-wrapper {
            max-width: 1200px;
            margin: 0 auto;
            padding: 2rem 1rem;
        }
        
        .post-editor-card {
            background: var(--off-white);
            border-radius: 16px;
            padding: 2.5rem;
            box-shadow: 0 8px 32px rgba(0,0,0,0.1);
            border: 1px solid rgba(255,255,255,0.2);
        }
        
        .editor-header {
            margin-bottom: 2.5rem;
            padding-bottom: 1.5rem;
            border-bottom: 3px solid var(--desert-sand);
            text-align: center;
        }
        
        .editor-header h1 {
            margin-bottom: 0.75rem;
            font-size: 2.5rem;
            background: linear-gradient(135deg, var(--yucca-yellow), #8a8c15);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }
        
        .editor-header p {
            opacity: 0.8;
            font-size: 1.1rem;
            max-width: 600px;
            margin: 0 auto;
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
        
        .form-input, .form-select, .form-textarea {
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
        
        .form-input:focus, .form-select:focus, .form-textarea:focus {
            outline: none;
            border-color: var(--yucca-yellow);
            box-shadow: 0 0 0 3px rgba(184,186,32,0.1);
            transform: translateY(-1px);
        }
        
        .form-textarea {
            resize: vertical;
            min-height: 120px;
        }
        
        .content-section {
            background: white;
            border: 2px solid #f0f0f0;
            border-radius: 12px;
            padding: 1.5rem;
            margin-bottom: 1.5rem;
            transition: all 0.3s ease;
            position: relative;
        }
        
        .content-section:hover {
            box-shadow: 0 8px 25px rgba(0,0,0,0.1);
            border-color: var(--yucca-yellow);
            transform: translateY(-2px);
        }
        
        .section-header {
            display: flex;
            align-items: center;
            gap: 0.75rem;
            margin-bottom: 1rem;
            font-weight: 700;
            color: var(--lobo-gray);
            font-size: 1.1rem;
        }
        
        .section-actions {
            display: flex;
            gap: 0.75rem;
            margin-top: 1.5rem;
            flex-wrap: wrap;
        }
        
        .btn-section {
            padding: 0.75rem 1.25rem;
            border: none;
            border-radius: 8px;
            cursor: pointer;
            font-size: 0.9rem;
            font-weight: 600;
            transition: all 0.3s ease;
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
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
        
        .content-preview {
            border: 2px dashed #ddd;
            border-radius: 8px;
            padding: 1rem;
            margin-top: 1rem;
            background: #fafafa;
            transition: all 0.3s ease;
        }
        
        .content-preview:hover {
            border-color: var(--yucca-yellow);
            background: #f8f9fa;
        }
        
        .image-placeholder {
            width: 100%;
            height: 200px;
            border: 2px dashed var(--yucca-yellow);
            border-radius: 8px;
            display: flex;
            align-items: center;
            justify-content: center;
            background: rgba(184,186,32,0.05);
            cursor: pointer;
            transition: all 0.3s ease;
            flex-direction: column;
            gap: 0.5rem;
        }
        
        .image-placeholder:hover {
            border-color: var(--yucca-yellow);
            background: rgba(184,186,32,0.1);
            transform: scale(1.02);
        }
        
        .editor-toolbar {
            display: flex;
            gap: 1rem;
            flex-wrap: wrap;
            margin-bottom: 2rem;
            padding: 1.5rem;
            background: linear-gradient(135deg, #f8f9fa, #e9ecef);
            border-radius: 12px;
            border: 2px solid #e9ecef;
        }
        
        .submit-section {
            margin-top: 3rem;
            padding-top: 2.5rem;
            border-top: 3px solid var(--desert-sand);
            text-align: center;
        }
        
        .cancel-link {
            display: block;
            text-align: center;
            margin-top: 1.5rem;
            color: var(--lobo-gray);
            text-decoration: none;
            transition: all 0.3s ease;
            font-weight: 600;
        }
        
        .cancel-link:hover {
            color: var(--yucca-yellow);
            transform: translateY(-1px);
        }
        
        /* Enhanced Profile Container */
        .profile-container {
            background: linear-gradient(135deg, var(--off-white), #f8f9fa);
            border-radius: 16px;
            padding: 2.5rem;
            margin-bottom: 2.5rem;
            box-shadow: 0 8px 32px rgba(0,0,0,0.1);
            border: 1px solid rgba(255,255,255,0.2);
        }
        
        .profile-header {
            display: flex;
            align-items: center;
            gap: 2rem;
            margin-bottom: 2rem;
        }
        
        .profile-avatar {
            width: 80px;
            height: 80px;
            border-radius: 50%;
            background: linear-gradient(135deg, var(--yucca-yellow), #8a8c15);
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 2rem;
            color: white;
            font-weight: 700;
            box-shadow: 0 8px 25px rgba(184,186,32,0.3);
        }
        
        .profile-info h2 {
            margin-bottom: 0.5rem;
            color: var(--lobo-gray);
            font-size: 1.5rem;
        }
        
        .profile-info p {
            opacity: 0.8;
            margin: 0;
            font-size: 1.1rem;
        }
        
        .profile-stats {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(120px, 1fr));
            gap: 1.5rem;
        }
        
        .stat-item {
            text-align: center;
            padding: 1.5rem;
            background: white;
            border-radius: 12px;
            border: 2px solid #f0f0f0;
            transition: all 0.3s ease;
        }
        
        .stat-item:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 25px rgba(0,0,0,0.1);
            border-color: var(--yucca-yellow);
        }
        
        .stat-value {
            font-size: 1.5rem;
            font-weight: 700;
            color: var(--yucca-yellow);
            margin-bottom: 0.5rem;
        }
        
        .stat-label {
            font-size: 0.9rem;
            opacity: 0.8;
            font-weight: 600;
        }
        
        /* Mobile Responsive */
        @media (max-width: 768px) {
            .post-editor-wrapper {
                padding: 1rem 0.5rem;
            }
            
            .post-editor-card {
                padding: 1.5rem 1rem;
                border-radius: 12px;
            }
            
            .editor-header h1 {
                font-size: 2rem;
            }
            
            .editor-toolbar {
                gap: 0.5rem;
                padding: 1rem;
            }
            
            .btn-section {
                font-size: 0.85rem;
                padding: 0.6rem 1rem;
            }
            
            .section-actions {
                gap: 0.5rem;
            }
            
            .profile-header {
                flex-direction: column;
                text-align: center;
                gap: 1rem;
            }
            
            .profile-stats {
                grid-template-columns: repeat(2, 1fr);
                gap: 1rem;
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
        
        /* Rich Builder Styles */
        .btn-outline {
            background: transparent;
            border: 1px solid #ddd;
            color: #666;
            padding: 0.5rem 1rem;
            border-radius: 6px;
            font-size: 0.9rem;
            transition: all 0.2s ease;
            cursor: pointer;
        }
        
        .btn-outline:hover {
            background: #f8f9fa;
            border-color: #007bff;
            color: #007bff;
        }
        
        .btn-sm {
            padding: 0.4rem 0.8rem;
            font-size: 0.85rem;
        }
        
        .blocks-container {
            display: grid;
            gap: 0.75rem;
            margin-top: 1rem;
        }
        
        .block-item {
            background: white;
            border: 1px solid #e5e5e5;
            border-radius: 8px;
            padding: 1rem;
            position: relative;
            transition: all 0.2s ease;
        }
        
        .block-item:hover {
            border-color: #007bff;
            box-shadow: 0 2px 8px rgba(0, 123, 255, 0.1);
        }
        
        .block-item.dragging {
            opacity: 0.5;
            transform: rotate(2deg);
        }
        
        .block-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 0.5rem;
            padding-bottom: 0.5rem;
            border-bottom: 1px solid #f0f0f0;
        }
        
        .block-type {
            font-size: 0.8rem;
            color: #666;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        
        .block-actions {
            display: flex;
            gap: 0.5rem;
        }
        
        .block-actions button {
            background: none;
            border: none;
            color: #666;
            cursor: pointer;
            padding: 0.25rem;
            border-radius: 4px;
            transition: all 0.2s ease;
        }
        
        .block-actions button:hover {
            background: #f8f9fa;
            color: #333;
        }
        
        .block-actions .btn-danger:hover {
            background: #dc3545;
            color: white;
        }
        
        .block-content {
            margin-top: 0.5rem;
        }
        
        .block-content input,
        .block-content textarea {
            width: 100%;
            border: 1px solid #ddd;
            border-radius: 4px;
            padding: 0.5rem;
            font-size: 0.9rem;
        }
        
        .block-content textarea {
            resize: vertical;
            min-height: 60px;
        }
        
        .toolbar-section {
            display: flex;
            flex-direction: column;
            gap: 0.5rem;
        }
        
        .toolbar-section h4 {
            margin: 0;
            font-size: 0.9rem;
            color: #666;
            font-weight: 600;
        }
        
        .toolbar-buttons {
            display: flex;
            gap: 0.5rem;
            flex-wrap: wrap;
        }
        
        .quick-actions {
            margin-top: 1rem;
            padding-top: 1rem;
            border-top: 1px solid #e5e5e5;
            display: flex;
            gap: 0.5rem;
            flex-wrap: wrap;
        }
        
        @media (max-width: 768px) {
            .rich-toolbar {
                flex-direction: column;
                gap: 1rem;
            }
            
            .toolbar-section {
                width: 100%;
            }
            
            .toolbar-buttons {
                justify-content: flex-start;
            }
            
            .quick-actions {
                flex-direction: column;
            }
            
            .quick-actions button {
                width: 100%;
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
                    <li><a href="nav/community/index.php" class="active">Community</a></li>
                    <li><a href="nav/membership/index.php">Membership</a></li>
                    <li><a href="nav/exclusive/index.php">Exclusive</a></li>
                </ul>
            </nav>
            <div class="header-actions">
                <span class="desktop-only" style="font-size: 14px; font-weight: 700;"><?= $user_email ?></span>
                <a href="my-posts.php" id="my-posts" aria-label="My posts" title="My posts" class="desktop-only" style="font-size: 14px; color: var(--yucca-yellow); margin-right: 0.5rem;">
                    <i class="fas fa-file-alt" aria-hidden="true"></i>
                </a>
                <a href="create-post.php" id="create-post" aria-label="Create post" title="Create post" class="desktop-only" style="font-size: 14px; color: var(--yucca-yellow); margin-right: 0.5rem;">
                    <i class="fas fa-edit" aria-hidden="true"></i>
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
                        <a href="my-posts.php">
                            <i class="fas fa-file-alt"></i>My Posts
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
    <main class="post-editor-wrapper">
        <!-- Profile Container -->
        <div class="profile-container" style="background: var(--off-white); border-radius: 12px; padding: 2rem; margin-bottom: 2rem; box-shadow: 0 4px 12px rgba(0,0,0,0.1); border: 2px solid var(--desert-sand);">
            <div class="profile-header" style="display: flex; align-items: center; gap: 1.5rem; margin-bottom: 1.5rem;">
                <div class="profile-avatar" style="width: 60px; height: 60px; border-radius: 50%; background: var(--yucca-yellow); display: flex; align-items: center; justify-content: center; font-size: 1.5rem; color: white; font-weight: 700;">
                    <?= strtoupper(substr($user_email, 0, 1)) ?>
                </div>
                <div class="profile-info">
                    <h2 style="margin-bottom: 0.25rem; color: var(--lobo-gray);"><?= htmlspecialchars(explode('@', $user_email)[0]) ?></h2>
                    <p style="opacity: 0.7; margin: 0;"><?= $user_email ?></p>
                </div>
            </div>
            
            <div class="profile-stats" style="display: grid; grid-template-columns: repeat(auto-fit, minmax(100px, 1fr)); gap: 1rem;">
                <div class="stat-item" style="text-align: center; padding: 0.75rem; background: white; border-radius: 8px; border: 1px solid var(--desert-sand);">
                    <div class="stat-value" style="font-size: 1.25rem; font-weight: 700; color: var(--yucca-yellow); margin-bottom: 0.25rem;"><?= $user_role ?></div>
                    <div class="stat-label" style="font-size: 0.875rem; opacity: 0.7;">Role</div>
                </div>
                <div class="stat-item" style="text-align: center; padding: 0.75rem; background: white; border-radius: 8px; border: 1px solid var(--desert-sand);">
                    <div class="stat-value" style="font-size: 1.25rem; font-weight: 700; color: var(--yucca-yellow); margin-bottom: 0.25rem;">5</div>
                    <div class="stat-label" style="font-size: 0.875rem; opacity: 0.7;">Monthly Limit</div>
                </div>
            </div>
        </div>
        
        <div class="post-editor-card">
            <div class="editor-header">
                <h1><i class="fas fa-edit"></i> Create Community Post</h1>
                <p>You can post up to 5 times per month. All submissions are reviewed before publication.</p>
            </div>
        
            <form id="post-form">
                <div class="form-group">
                    <label class="form-label" for="post-title"><i class="fas fa-heading"></i> Title *</label>
                    <input type="text" id="post-title" class="form-input" placeholder="Give your post a compelling title" required>
                </div>
                
                <div class="form-group">
                    <label class="form-label" for="post-category"><i class="fas fa-tag"></i> Category</label>
                    <select id="post-category" class="form-select">
                        <option value="">Select a category</option>
                        <option value="Food & Drink">Food & Drink</option>
                        <option value="Outdoors">Outdoors</option>
                        <option value="Events">Events</option>
                        <option value="Culture">Culture</option>
                        <option value="News">News</option>
                        <option value="Other">Other</option>
                    </select>
                </div>
                
                <div class="form-group">
                    <label class="form-label" for="featured-image"><i class="fas fa-image"></i> Featured Image URL</label>
                    <input type="url" id="featured-image" class="form-input" placeholder="https://example.com/image.jpg">
                    <small style="color: #666; font-size: 0.85rem; margin-top: 0.25rem; display: block;">Optional: Add a cover image for your post</small>
                </div>
                
                <div class="form-group">
                    <label class="form-label" for="post-intro"><i class="fas fa-align-left"></i> Introduction/Lead Paragraph *</label>
                    <textarea id="post-intro" class="form-textarea" rows="4" placeholder="Write a brief introduction that hooks your readers..." required></textarea>
                </div>
                
                <div class="form-group">
                    <label class="form-label"><i class="fas fa-plus-circle"></i> Build Your Content</label>
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
                    
                    <!-- Legacy content sections (hidden when rich builder is enabled) -->
                    <div id="legacy-content-sections">
                    <div class="editor-toolbar">
                        <button type="button" id="add-paragraph" class="btn-section btn-primary">
                            <i class="fas fa-paragraph"></i> Add Text
                        </button>
                        <button type="button" id="add-image" class="btn-section btn-primary">
                            <i class="fas fa-image"></i> Add Image
                        </button>
                        <button type="button" id="add-heading" class="btn-section btn-primary">
                            <i class="fas fa-heading"></i> Add Heading
                        </button>
                        <button type="button" id="add-list" class="btn-section btn-primary">
                            <i class="fas fa-list"></i> Add List
                        </button>
                </div>
                
                <div id="content-sections">
                    <!-- Sections will be added here -->
                        </div>
                    </div>
                </div>
                
                <div class="submit-section">
                    <button type="submit" class="cta-button" style="width: 100%; font-size: 18px; padding: 1rem;">
                        <i class="fas fa-paper-plane"></i> Submit Post for Review
                    </button>
                    <a href="nav/community/index.php" class="cancel-link">
                        <i class="fas fa-times"></i> Cancel and Go Back
                    </a>
                </div>
            </form>
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
        // Enhanced Create Post JavaScript
        let sectionCounter = 0;
        const contentSections = document.getElementById('content-sections');
        
        // Add character counters
        function addCharacterCounters() {
            const titleInput = document.getElementById('post-title');
            const introTextarea = document.getElementById('post-intro');
            
            // Title counter
            const titleCounter = document.createElement('div');
            titleCounter.className = 'char-counter';
            titleCounter.style.cssText = 'font-size: 0.85rem; color: #666; margin-top: 0.25rem; text-align: right;';
            titleInput.parentNode.appendChild(titleCounter);
            
            titleInput.addEventListener('input', () => {
                const count = titleInput.value.length;
                titleCounter.textContent = `${count}/100 characters`;
                titleCounter.style.color = count > 100 ? '#dc3545' : '#666';
            });
            
            // Intro counter
            const introCounter = document.createElement('div');
            introCounter.className = 'char-counter';
            introCounter.style.cssText = 'font-size: 0.85rem; color: #666; margin-top: 0.25rem; text-align: right;';
            introTextarea.parentNode.appendChild(introCounter);
            
            introTextarea.addEventListener('input', () => {
                const count = introTextarea.value.length;
                introCounter.textContent = `${count}/500 characters`;
                introCounter.style.color = count > 500 ? '#dc3545' : '#666';
            });
        }
        
        // Enhanced section creation with better UX
        function createSection(type, data = {}) {
            const section = document.createElement('div');
            section.className = 'content-section';
            section.dataset.type = type;
            section.dataset.index = sectionCounter++;
            
            let html = '';
            switch(type) {
                case 'paragraph':
                    html = `
                        <div class="section-header">
                            <i class="fas fa-paragraph"></i>
                            <span>Text Paragraph</span>
                            <div class="section-counter" style="margin-left: auto; font-size: 0.8rem; opacity: 0.7;">Section ${sectionCounter}</div>
                        </div>
                        <textarea class="section-content form-textarea" rows="6" placeholder="Write your paragraph here... Share your story, thoughts, or insights with the community.">${data.text || ''}</textarea>
                        <div class="char-counter" style="font-size: 0.85rem; color: #666; margin-top: 0.25rem; text-align: right;"></div>
                    `;
                    break;
                case 'image':
                    html = `
                        <div class="section-header">
                            <i class="fas fa-image"></i>
                            <span>Image</span>
                            <div class="section-counter" style="margin-left: auto; font-size: 0.8rem; opacity: 0.7;">Section ${sectionCounter}</div>
                        </div>
                        <input type="url" class="section-image-url form-input" placeholder="https://example.com/your-image.jpg" value="${data.url || ''}" style="margin-bottom: 0.75rem;">
                        <input type="text" class="section-image-alt form-input" placeholder="Describe your image for accessibility (e.g., 'Sunset over Organ Mountains')" value="${data.alt || ''}">
                        <div class="content-preview">
                            ${data.url ? `<img src="${data.url}" alt="${data.alt || ''}" style="max-width: 100%; height: auto; border-radius: 8px; box-shadow: 0 4px 12px rgba(0,0,0,0.1);">` : '<div class="image-placeholder"><i class="fas fa-image" style="font-size: 3rem; opacity: 0.3;"></i><p style="margin: 0; opacity: 0.6;">Add an image URL above to preview</p></div>'}
                        </div>
                    `;
                    break;
                case 'heading':
                    html = `
                        <div class="section-header">
                            <i class="fas fa-heading"></i>
                            <span>Heading</span>
                            <div class="section-counter" style="margin-left: auto; font-size: 0.8rem; opacity: 0.7;">Section ${sectionCounter}</div>
                        </div>
                        <input type="text" class="section-content form-input" placeholder="Enter a compelling heading..." value="${data.text || ''}">
                        <div class="char-counter" style="font-size: 0.85rem; color: #666; margin-top: 0.25rem; text-align: right;"></div>
                    `;
                    break;
                case 'list':
                    html = `
                        <div class="section-header">
                            <i class="fas fa-list"></i>
                            <span>List</span>
                            <div class="section-counter" style="margin-left: auto; font-size: 0.8rem; opacity: 0.7;">Section ${sectionCounter}</div>
                        </div>
                        <textarea class="section-content form-textarea" rows="6" placeholder="Enter list items, one per line...&#10;&#10;Example:&#10;• First item&#10;• Second item&#10;• Third item">${data.items || ''}</textarea>
                        <div class="char-counter" style="font-size: 0.85rem; color: #666; margin-top: 0.25rem; text-align: right;"></div>
                    `;
                    break;
            }
            
            html += `
                <div class="section-actions">
                    <button type="button" class="move-up btn-section btn-secondary" title="Move section up">
                        <i class="fas fa-arrow-up"></i> Move Up
                    </button>
                    <button type="button" class="move-down btn-section btn-secondary" title="Move section down">
                        <i class="fas fa-arrow-down"></i> Move Down
                    </button>
                    <button type="button" class="duplicate-section btn-section btn-secondary" title="Duplicate this section">
                        <i class="fas fa-copy"></i> Duplicate
                    </button>
                    <button type="button" class="delete-section btn-section btn-danger" title="Delete this section">
                        <i class="fas fa-trash"></i> Delete
                    </button>
                </div>
            `;
            
            section.innerHTML = html;
            
            // Add character counters for text areas
            setTimeout(() => {
                const textarea = section.querySelector('.section-content');
                const counter = section.querySelector('.char-counter');
                if (textarea && counter) {
                    textarea.addEventListener('input', () => {
                        const count = textarea.value.length;
                        counter.textContent = `${count} characters`;
                        counter.style.color = count > 1000 ? '#dc3545' : '#666';
                    });
                }
                
                // Image preview functionality
                const imageUrlInput = section.querySelector('.section-image-url');
                if (imageUrlInput) {
                    imageUrlInput.addEventListener('input', function(e) {
                        const preview = section.querySelector('.content-preview');
                        const altInput = section.querySelector('.section-image-alt');
                        if (e.target.value) {
                            preview.innerHTML = `<img src="${e.target.value}" alt="${altInput.value || ''}" style="max-width: 100%; height: auto; border-radius: 8px; box-shadow: 0 4px 12px rgba(0,0,0,0.1);">`;
                        } else {
                            preview.innerHTML = '<div class="image-placeholder"><i class="fas fa-image" style="font-size: 3rem; opacity: 0.3;"></i><p style="margin: 0; opacity: 0.6;">Add an image URL above to preview</p></div>';
                        }
                    });
                }
            }, 0);
            
            return section;
        }
        
        // Enhanced event listeners
        document.getElementById('add-paragraph').addEventListener('click', () => {
            contentSections.appendChild(createSection('paragraph'));
            showToast('📝 Text section added! Start writing your story.', 'success');
        });
        
        document.getElementById('add-image').addEventListener('click', () => {
            contentSections.appendChild(createSection('image'));
            showToast('🖼️ Image section added! Add an image URL to preview.', 'success');
        });
        
        document.getElementById('add-heading').addEventListener('click', () => {
            contentSections.appendChild(createSection('heading'));
            showToast('📋 Heading section added! Add a compelling title.', 'success');
        });
        
        document.getElementById('add-list').addEventListener('click', () => {
            contentSections.appendChild(createSection('list'));
            showToast('📝 List section added! Add your items one per line.', 'success');
        });
        
        // Enhanced section management
        contentSections.addEventListener('click', (e) => {
            if (e.target.closest('.delete-section')) {
                const section = e.target.closest('.content-section');
                if (confirm('Are you sure you want to delete this section? This action cannot be undone.')) {
                    section.style.animation = 'fadeOut 0.3s ease';
                    setTimeout(() => section.remove(), 300);
                    showToast('🗑️ Section deleted', 'info');
                }
            }
            
            if (e.target.closest('.duplicate-section')) {
                const section = e.target.closest('.content-section');
                const newSection = section.cloneNode(true);
                newSection.dataset.index = sectionCounter++;
                contentSections.insertBefore(newSection, section.nextSibling);
                showToast('📋 Section duplicated', 'success');
            }
            
            if (e.target.closest('.move-up')) {
                const section = e.target.closest('.content-section');
                const prev = section.previousElementSibling;
                if (prev) {
                    section.parentNode.insertBefore(section, prev);
                    showToast('⬆️ Section moved up', 'info');
                }
            }
            
            if (e.target.closest('.move-down')) {
                const section = e.target.closest('.content-section');
                const next = section.nextElementSibling;
                if (next) {
                    section.parentNode.insertBefore(next, section);
                    showToast('⬇️ Section moved down', 'info');
                }
            }
        });
        
        // Enhanced form submission with better validation
        document.getElementById('post-form').addEventListener('submit', async (e) => {
            e.preventDefault();
            
            const title = document.getElementById('post-title').value.trim();
            const category = document.getElementById('post-category').value;
            const featuredImage = document.getElementById('featured-image').value.trim();
            const intro = document.getElementById('post-intro').value.trim();
            
            // Enhanced validation
            const errors = [];
            
            if (!title) {
                errors.push('Please enter a title for your post');
            } else if (title.length < 5) {
                errors.push('Title must be at least 5 characters long');
            } else if (title.length > 100) {
                errors.push('Title must be less than 100 characters');
            }
            
            if (!intro) {
                errors.push('Please write an introduction for your post');
            } else if (intro.length < 20) {
                errors.push('Introduction must be at least 20 characters long');
            } else if (intro.length > 500) {
                errors.push('Introduction must be less than 500 characters');
            }
            
            if (!category) {
                errors.push('Please select a category for your post');
            }
            
            // Check if there are any content sections or rich builder blocks
            const sections = document.querySelectorAll('.content-section');
            const hasRichBuilderContent = builderEnabled && blocks.length > 0;
            
            if (sections.length === 0 && !hasRichBuilderContent) {
                errors.push('Please add at least one content section to your post');
            }
            
            if (errors.length > 0) {
                showToast('❌ Please fix the following issues:\n• ' + errors.join('\n• '), 'error', 8000);
                return;
            }
            
            // Build content based on which editor is being used
            let finalContent;
            
            if (builderEnabled && blocks.length > 0) {
                // Use rich builder content
                finalContent = {
                    type: 'rich_builder',
                    blocks: blocks,
                    html: generatePreviewHTML()
                };
            } else {
                // Use legacy content sections
            const contentArray = [];
            
            sections.forEach(section => {
                const type = section.dataset.type;
                const data = {};
                
                if (type === 'paragraph' || type === 'list') {
                    data.text = section.querySelector('.section-content').value;
                } else if (type === 'heading') {
                    data.text = section.querySelector('.section-content').value;
                } else if (type === 'image') {
                    data.url = section.querySelector('.section-image-url').value;
                    data.alt = section.querySelector('.section-image-alt').value;
                }
                
                contentArray.push({ type, data });
            });
            
                finalContent = {
                intro: intro,
                sections: contentArray
            };
            }
            
            // Show enhanced loading state
            const submitBtn = e.target.querySelector('button[type="submit"]');
            const originalBtnText = submitBtn.innerHTML;
            submitBtn.disabled = true;
            submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Submitting Post...';
            submitBtn.classList.add('loading');
            
            try {
                const formData = new FormData();
                formData.append('title', title);
                formData.append('content', JSON.stringify(finalContent));
                formData.append('category', category);
                formData.append('featured_image', featuredImage);
                
                const response = await fetch('api/user_posts_api.php?action=create', {
                    method: 'POST',
                    body: formData
                });
                
                const data = await response.json();
                
                if (data.success) {
                    showToast('🎉 Post submitted successfully! Redirecting to community...', 'success', 3000);
                    setTimeout(() => {
                        window.location.href = 'nav/community/index.php';
                    }, 2000);
                } else {
                    showToast('❌ Error: ' + (data.error || 'Failed to submit post'), 'error', 5000);
                    submitBtn.disabled = false;
                    submitBtn.innerHTML = originalBtnText;
                    submitBtn.classList.remove('loading');
                }
            } catch (error) {
                showToast('❌ Error submitting post. Please check your connection and try again.', 'error', 5000);
                console.error('Submission error:', error);
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
            @keyframes fadeOut {
                from { opacity: 1; transform: scale(1); }
                to { opacity: 0; transform: scale(0.95); }
            }
        `;
        document.head.appendChild(style);
        
        // Initialize character counters when page loads
        document.addEventListener('DOMContentLoaded', () => {
            addCharacterCounters();
            
            // Initialize rich builder if needed
            const contentTextarea = document.getElementById('post-content');
            if (contentTextarea && contentTextarea.value.trim()) {
                try {
                    const existingContent = JSON.parse(contentTextarea.value);
                    if (existingContent.type === 'rich_builder' && existingContent.blocks) {
                        blocks = existingContent.blocks;
                        builderEnabled = true;
                        toggleBuilder();
                    }
                } catch (e) {
                    // Content is not JSON, ignore
                }
            }
        });
        
        // Rich Builder Functions
        let builderEnabled = false;
        let blocks = [];
        
        function generateUUID() {
            return 'xxxxxxxx-xxxx-4xxx-yxxx-xxxxxxxxxxxx'.replace(/[xy]/g, function(c) {
                const r = Math.random() * 16 | 0;
                const v = c == 'x' ? r : (r & 0x3 | 0x8);
                return v.toString(16);
            });
        }
        
        function toggleBuilder() {
            builderEnabled = !builderEnabled;
            const builder = document.getElementById('rich-builder');
            const status = document.getElementById('builder-status');
            const legacySections = document.getElementById('legacy-content-sections');
            const loadTemplateBtn = document.getElementById('load-template-btn');
            
            if (builderEnabled) {
                builder.style.display = 'block';
                legacySections.style.display = 'none';
                loadTemplateBtn.style.display = 'inline-block';
                status.textContent = 'Rich builder: on';
                status.style.color = '#28a745';
                
                // Convert existing content to blocks if any
                convertTextToBlocks();
            } else {
                builder.style.display = 'none';
                legacySections.style.display = 'block';
                loadTemplateBtn.style.display = 'none';
                status.textContent = 'Rich builder: off';
                status.style.color = '#666';
            }
        }
        
        function convertTextToBlocks() {
            const introTextarea = document.getElementById('post-intro');
            if (introTextarea && introTextarea.value.trim()) {
                // Convert intro to a paragraph block
                const introBlock = {
                    id: generateUUID(),
                    type: 'paragraph',
                    data: {
                        text: introTextarea.value.trim()
                    }
                };
                blocks = [introBlock];
                renderBlocks();
            }
        }
        
        function addBlock(type) {
            const block = {
                id: generateUUID(),
                type: type,
                data: {}
            };
            
            // Set default data based on block type
            switch(type) {
                case 'heading':
                    block.data = { level: 1, text: '' };
                    break;
                case 'subheading':
                    block.data = { level: 2, text: '' };
                    break;
                case 'paragraph':
                    block.data = { text: '' };
                    break;
                case 'image':
                    block.data = { url: '', alt: '', caption: '' };
                    break;
                case 'gallery':
                    block.data = { images: [] };
                    break;
                case 'blockquote':
                    block.data = { text: '', author: '' };
                    break;
                case 'list':
                    block.data = { items: [''] };
                    break;
                case 'numbered-list':
                    block.data = { items: [''] };
                    break;
                case 'video':
                    block.data = { url: '', title: '' };
                    break;
                case 'divider':
                    block.data = {};
                    break;
            }
            
            blocks.push(block);
            renderBlocks();
        }
        
        function removeBlock(blockId) {
            blocks = blocks.filter(block => block.id !== blockId);
            renderBlocks();
        }
        
        function duplicateLastBlock() {
            if (blocks.length > 0) {
                const lastBlock = { ...blocks[blocks.length - 1] };
                lastBlock.id = generateUUID();
                blocks.push(lastBlock);
                renderBlocks();
            }
        }
        
        function clearAllBlocks() {
            if (confirm('Are you sure you want to clear all blocks? This cannot be undone.')) {
                blocks = [];
                renderBlocks();
            }
        }
        
        function previewContent() {
            const html = generatePreviewHTML();
            const newWindow = window.open('', '_blank');
            newWindow.document.write(`
                <html>
                    <head>
                        <title>Content Preview</title>
                        <style>
                            body { font-family: Arial, sans-serif; max-width: 800px; margin: 0 auto; padding: 2rem; }
                            h1 { color: #333; border-bottom: 2px solid #007bff; padding-bottom: 0.5rem; }
                            h2 { color: #555; margin-top: 2rem; }
                            p { line-height: 1.6; margin: 1rem 0; }
                            blockquote { border-left: 4px solid #007bff; padding-left: 1rem; margin: 1rem 0; font-style: italic; }
                            img { max-width: 100%; height: auto; border-radius: 8px; }
                        </style>
                    </head>
                    <body>${html}</body>
                </html>
            `);
        }
        
        function exportContent() {
            const data = {
                blocks: blocks,
                html: generatePreviewHTML()
            };
            
            const blob = new Blob([JSON.stringify(data, null, 2)], { type: 'application/json' });
            const url = URL.createObjectURL(blob);
            const a = document.createElement('a');
            a.href = url;
            a.download = 'content-export.json';
            a.click();
            URL.revokeObjectURL(url);
        }
        
        function loadTemplate() {
            if (!confirm('This will replace all current content with a sample template. Continue?')) {
                return;
            }
            
            // Enable builder if not already enabled
            if (!builderEnabled) {
                toggleBuilder();
            }
            
            blocks = [];
            
            // Add sample blocks
            const templateBlocks = [
                {
                    id: generateUUID(),
                    type: 'heading',
                    data: { level: 1, text: 'Welcome to My Post' }
                },
                {
                    id: generateUUID(),
                    type: 'paragraph',
                    data: { text: 'This is a sample paragraph to get you started. You can edit or delete this content and add your own.' }
                },
                {
                    id: generateUUID(),
                    type: 'subheading',
                    data: { level: 2, text: 'Getting Started' }
                },
                {
                    id: generateUUID(),
                    type: 'paragraph',
                    data: { text: 'Use the toolbar above to add different types of content blocks. You can add headings, paragraphs, images, lists, and more.' }
                },
                {
                    id: generateUUID(),
                    type: 'list',
                    data: { items: ['Add your first list item', 'Add your second list item', 'Add your third list item'] }
                },
                {
                    id: generateUUID(),
                    type: 'blockquote',
                    data: { text: 'This is a sample quote. You can add inspiring quotes or important information here.', author: 'Sample Author' }
                }
            ];
            
            blocks = templateBlocks;
            renderBlocks();
            
            showToast('Template loaded successfully! You can now customize the content.', 'success');
        }
        
        function generatePreviewHTML() {
            let html = '';
            
            blocks.forEach(block => {
                const type = block.type;
                const data = block.data;
                
                switch(type) {
                    case 'heading':
                        html += `<h${data.level} style="font-size: ${data.level == 1 ? '2rem' : '1.5rem'}; font-weight: bold; margin: 1rem 0; color: #333;">${data.text}</h${data.level}>`;
                        break;
                    case 'subheading':
                        html += `<h${data.level} style="font-size: 1.5rem; font-weight: 600; margin: 0.8rem 0; color: #555;">${data.text}</h${data.level}>`;
                        break;
                    case 'paragraph':
                        html += `<p style="margin: 1rem 0; line-height: 1.6; color: #333;">${data.text}</p>`;
                        break;
                    case 'image':
                        if (data.url) {
                            html += `<div style="margin: 1rem 0; text-align: center;"><img src="${data.url}" alt="${data.alt}" style="max-width: 100%; height: auto; border-radius: 8px;">`;
                            if (data.caption) {
                                html += `<p style="font-style: italic; color: #666; margin-top: 0.5rem;">${data.caption}</p>`;
                            }
                            html += `</div>`;
                        }
                        break;
                    case 'gallery':
                        if (data.images && data.images.length > 0) {
                            html += `<div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 1rem; margin: 1rem 0;">`;
                            data.images.forEach(img => {
                                html += `<img src="${img.url}" alt="${img.alt}" style="width: 100%; height: auto; border-radius: 8px;">`;
                            });
                            html += `</div>`;
                        }
                        break;
                    case 'blockquote':
                        html += `<blockquote style="border-left: 4px solid #007bff; margin: 1rem 0; padding: 1rem 1.5rem; background: #f8f9fa; font-style: italic; color: #555;">${data.text}`;
                        if (data.author) {
                            html += `<footer style="margin-top: 0.5rem; font-size: 0.9rem; color: #666;">— ${data.author}</footer>`;
                        }
                        html += `</blockquote>`;
                        break;
                    case 'list':
                        html += `<ul style="margin: 1rem 0; padding-left: 2rem;">`;
                        data.items.forEach(item => {
                            html += `<li style="margin: 0.5rem 0; line-height: 1.5;">${item}</li>`;
                        });
                        html += `</ul>`;
                        break;
                    case 'numbered-list':
                        html += `<ol style="margin: 1rem 0; padding-left: 2rem;">`;
                        data.items.forEach(item => {
                            html += `<li style="margin: 0.5rem 0; line-height: 1.5;">${item}</li>`;
                        });
                        html += `</ol>`;
                        break;
                    case 'video':
                        if (data.url) {
                            html += `<div style="margin: 1rem 0; text-align: center;"><video controls style="max-width: 100%; height: auto; border-radius: 8px;"><source src="${data.url}" type="video/mp4">Your browser does not support the video tag.</video>`;
                            if (data.title) {
                                html += `<p style="font-weight: 600; margin-top: 0.5rem;">${data.title}</p>`;
                            }
                            html += `</div>`;
                        }
                        break;
                    case 'divider':
                        html += `<hr style="margin: 2rem 0; border: none; border-top: 2px solid #e5e5e5;">`;
                        break;
                }
            });
            
            return html;
        }
        
        function renderBlocks() {
            const container = document.getElementById('blocks-container');
            container.innerHTML = '';
            
            blocks.forEach((block, index) => {
                const blockElement = document.createElement('div');
                blockElement.className = 'block-item';
                blockElement.dataset.blockId = block.id;
                blockElement.draggable = true;
                
                let content = '';
                const type = block.type;
                const data = block.data;
                
                switch(type) {
                    case 'heading':
                        content = `
                            <div class="block-content">
                                <input type="text" placeholder="Enter heading text..." value="${data.text}" onchange="updateBlockData('${block.id}', 'text', this.value)">
                                <select onchange="updateBlockData('${block.id}', 'level', this.value)" style="margin-top: 0.5rem;">
                                    <option value="1" ${data.level == 1 ? 'selected' : ''}>H1</option>
                                    <option value="2" ${data.level == 2 ? 'selected' : ''}>H2</option>
                                    <option value="3" ${data.level == 3 ? 'selected' : ''}>H3</option>
                                </select>
                            </div>
                        `;
                        break;
                    case 'subheading':
                        content = `
                            <div class="block-content">
                                <input type="text" placeholder="Enter subheading text..." value="${data.text}" onchange="updateBlockData('${block.id}', 'text', this.value)">
                                <select onchange="updateBlockData('${block.id}', 'level', this.value)" style="margin-top: 0.5rem;">
                                    <option value="2" ${data.level == 2 ? 'selected' : ''}>H2</option>
                                    <option value="3" ${data.level == 3 ? 'selected' : ''}>H3</option>
                                    <option value="4" ${data.level == 4 ? 'selected' : ''}>H4</option>
                                </select>
                            </div>
                        `;
                        break;
                    case 'paragraph':
                        content = `
                            <div class="block-content">
                                <textarea placeholder="Enter paragraph text..." onchange="updateBlockData('${block.id}', 'text', this.value)">${data.text}</textarea>
                            </div>
                        `;
                        break;
                    case 'image':
                        content = `
                            <div class="block-content">
                                <input type="url" placeholder="Image URL..." value="${data.url}" onchange="updateBlockData('${block.id}', 'url', this.value)" style="margin-bottom: 0.5rem;">
                                <input type="text" placeholder="Alt text..." value="${data.alt}" onchange="updateBlockData('${block.id}', 'alt', this.value)" style="margin-bottom: 0.5rem;">
                                <input type="text" placeholder="Caption (optional)..." value="${data.caption}" onchange="updateBlockData('${block.id}', 'caption', this.value)">
                            </div>
                        `;
                        break;
                    case 'gallery':
                        content = `
                            <div class="block-content">
                                <p style="color: #666; font-size: 0.9rem;">Gallery functionality coming soon. For now, use individual image blocks.</p>
                            </div>
                        `;
                        break;
                    case 'blockquote':
                        content = `
                            <div class="block-content">
                                <textarea placeholder="Enter quote text..." onchange="updateBlockData('${block.id}', 'text', this.value)" style="margin-bottom: 0.5rem;">${data.text}</textarea>
                                <input type="text" placeholder="Author (optional)..." value="${data.author}" onchange="updateBlockData('${block.id}', 'author', this.value)">
                            </div>
                        `;
                        break;
                    case 'list':
                        content = `
                            <div class="block-content">
                                <div id="list-items-${block.id}">
                                    ${data.items.map((item, i) => `
                                        <input type="text" placeholder="List item ${i + 1}..." value="${item}" onchange="updateListItem('${block.id}', ${i}, this.value)" style="margin-bottom: 0.5rem;">
                                    `).join('')}
                                </div>
                                <button type="button" onclick="addListItem('${block.id}')" style="margin-top: 0.5rem; padding: 0.25rem 0.5rem; background: #f8f9fa; border: 1px solid #ddd; border-radius: 4px; cursor: pointer;">Add Item</button>
                            </div>
                        `;
                        break;
                    case 'numbered-list':
                        content = `
                            <div class="block-content">
                                <div id="numbered-items-${block.id}">
                                    ${data.items.map((item, i) => `
                                        <input type="text" placeholder="Item ${i + 1}..." value="${item}" onchange="updateNumberedItem('${block.id}', ${i}, this.value)" style="margin-bottom: 0.5rem;">
                                    `).join('')}
                                </div>
                                <button type="button" onclick="addNumberedItem('${block.id}')" style="margin-top: 0.5rem; padding: 0.25rem 0.5rem; background: #f8f9fa; border: 1px solid #ddd; border-radius: 4px; cursor: pointer;">Add Item</button>
                            </div>
                        `;
                        break;
                    case 'video':
                        content = `
                            <div class="block-content">
                                <input type="url" placeholder="Video URL..." value="${data.url}" onchange="updateBlockData('${block.id}', 'url', this.value)" style="margin-bottom: 0.5rem;">
                                <input type="text" placeholder="Video title (optional)..." value="${data.title}" onchange="updateBlockData('${block.id}', 'title', this.value)">
                            </div>
                        `;
                        break;
                    case 'divider':
                        content = `
                            <div class="block-content">
                                <p style="color: #666; font-size: 0.9rem; text-align: center; margin: 0;">Horizontal divider</p>
                            </div>
                        `;
                        break;
                }
                
                blockElement.innerHTML = `
                    <div class="block-header">
                        <span class="block-type">${type}</span>
                        <div class="block-actions">
                            <button type="button" onclick="duplicateBlock('${block.id}')" title="Duplicate">
                                <i class="fas fa-copy"></i>
                            </button>
                            <button type="button" onclick="moveBlock('${block.id}', 'up')" title="Move Up" ${index === 0 ? 'disabled' : ''}>
                                <i class="fas fa-arrow-up"></i>
                            </button>
                            <button type="button" onclick="moveBlock('${block.id}', 'down')" title="Move Down" ${index === blocks.length - 1 ? 'disabled' : ''}>
                                <i class="fas fa-arrow-down"></i>
                            </button>
                            <button type="button" onclick="removeBlock('${block.id}')" title="Delete" class="btn-danger">
                                <i class="fas fa-trash"></i>
                            </button>
                        </div>
                    </div>
                    ${content}
                `;
                
                container.appendChild(blockElement);
            });
            
            updateContent();
        }
        
        function updateBlockData(blockId, key, value) {
            const block = blocks.find(b => b.id === blockId);
            if (block) {
                block.data[key] = value;
                updateContent();
            }
        }
        
        function addListItem(blockId) {
            const block = blocks.find(b => b.id === blockId);
            if (block) {
                block.data.items.push('');
                renderBlocks();
            }
        }
        
        function updateListItem(blockId, index, value) {
            const block = blocks.find(b => b.id === blockId);
            if (block && block.data.items[index] !== undefined) {
                block.data.items[index] = value;
                updateContent();
            }
        }
        
        function addNumberedItem(blockId) {
            const block = blocks.find(b => b.id === blockId);
            if (block) {
                block.data.items.push('');
                renderBlocks();
            }
        }
        
        function updateNumberedItem(blockId, index, value) {
            const block = blocks.find(b => b.id === blockId);
            if (block && block.data.items[index] !== undefined) {
                block.data.items[index] = value;
                updateContent();
            }
        }
        
        function duplicateBlock(blockId) {
            const block = blocks.find(b => b.id === blockId);
            if (block) {
                const newBlock = { ...block };
                newBlock.id = generateUUID();
                const index = blocks.findIndex(b => b.id === blockId);
                blocks.splice(index + 1, 0, newBlock);
                renderBlocks();
            }
        }
        
        function moveBlock(blockId, direction) {
            const index = blocks.findIndex(b => b.id === blockId);
            if (index === -1) return;
            
            if (direction === 'up' && index > 0) {
                [blocks[index], blocks[index - 1]] = [blocks[index - 1], blocks[index]];
            } else if (direction === 'down' && index < blocks.length - 1) {
                [blocks[index], blocks[index + 1]] = [blocks[index + 1], blocks[index]];
            }
            
            renderBlocks();
        }
        
        function updateContent() {
            // Save blocks as JSON for storage
            const contentTextarea = document.getElementById('post-content');
            if (contentTextarea) {
                contentTextarea.value = JSON.stringify(blocks, null, 2);
            }
        }
    </script>
    
    <script src="ui/js/if-then.js"></script>
    <script src="ui/js/main.js"></script>
    <script src="ui/js/jquery-loader.js"></script>
</body>
</html>
