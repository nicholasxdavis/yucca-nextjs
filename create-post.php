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
                </ul>
            </nav>
            <div class="header-actions">
                <span class="desktop-only" style="font-size: 14px; font-weight: 700;"><?= $user_email ?></span>
                <a href="my-posts.php" id="my-posts" aria-label="My posts" title="My posts" class="desktop-only" style="font-size: 14px; color: var(--yucca-yellow); margin-right: 0.5rem;">
                    <i class="fas fa-file-alt" aria-hidden="true"></i>
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
                </div>
                
                <div id="content-sections">
                    <!-- Sections will be added here -->
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
            
            // Check if there are any content sections
            const sections = document.querySelectorAll('.content-section');
            if (sections.length === 0) {
                errors.push('Please add at least one content section to your post');
            }
            
            if (errors.length > 0) {
                showToast('❌ Please fix the following issues:\n• ' + errors.join('\n• '), 'error', 8000);
                return;
            }
            
            // Build content array from sections
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
            
            // Build final content JSON
            const finalContent = {
                intro: intro,
                sections: contentArray
            };
            
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
        });
    </script>
    
    <script src="ui/js/if-then.js"></script>
    <script src="ui/js/main.js"></script>
    <script src="ui/js/jquery-loader.js"></script>
</body>
</html>
