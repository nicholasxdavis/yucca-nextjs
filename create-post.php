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
        .post-editor-wrapper {
            max-width: 1200px;
            margin: 0 auto;
            padding: 2rem 1rem;
        }
        .post-editor-card {
            background: var(--off-white);
            border-radius: 12px;
            padding: 2rem;
            box-shadow: 0 4px 12px rgba(0,0,0,0.1);
        }
        .editor-header {
            margin-bottom: 2rem;
            padding-bottom: 1rem;
            border-bottom: 2px solid var(--desert-sand);
        }
        .editor-header h1 {
            margin-bottom: 0.5rem;
            font-size: 2rem;
        }
        .editor-header p {
            opacity: 0.7;
            font-size: 0.95rem;
        }
        .form-group {
            margin-bottom: 1.5rem;
        }
        .form-label {
            display: block;
            margin-bottom: 0.5rem;
            font-weight: 700;
            color: var(--lobo-gray);
        }
        .form-input, .form-select, .form-textarea {
            width: 100%;
            padding: 0.75rem;
            border: 2px solid #ddd;
            border-radius: 8px;
            font-family: inherit;
            transition: border-color 0.3s;
            background: white;
        }
        .form-input:focus, .form-select:focus, .form-textarea:focus {
            outline: none;
            border-color: var(--yucca-yellow);
        }
        .form-textarea {
            resize: vertical;
            min-height: 100px;
        }
        .content-section {
            background: white;
            border: 2px solid #e0e0e0;
            border-radius: 8px;
            padding: 1rem;
            margin-bottom: 1rem;
            transition: box-shadow 0.3s;
        }
        .content-section:hover {
            box-shadow: 0 2px 8px rgba(0,0,0,0.1);
        }
        .section-header {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            margin-bottom: 0.75rem;
            font-weight: 700;
            color: var(--lobo-gray);
        }
        .section-actions {
            display: flex;
            gap: 0.5rem;
            margin-top: 1rem;
            flex-wrap: wrap;
        }
        .btn-section {
            padding: 0.5rem 1rem;
            border: none;
            border-radius: 6px;
            cursor: pointer;
            font-size: 0.875rem;
            transition: all 0.3s;
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
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
        .content-preview {
            border: 1px dashed #ddd;
            border-radius: 6px;
            padding: 0.75rem;
            margin-top: 0.75rem;
            background: #f9f9f9;
        }
        .image-placeholder {
            width: 100%;
            height: 200px;
            border: 2px dashed var(--yucca-yellow);
            border-radius: 6px;
            display: flex;
            align-items: center;
            justify-content: center;
            background: rgba(184,186,32,0.05);
            cursor: pointer;
            transition: all 0.3s;
        }
        .image-placeholder:hover {
            border-color: var(--yucca-yellow);
            background: rgba(184,186,32,0.1);
        }
        .editor-toolbar {
            display: flex;
            gap: 0.5rem;
            flex-wrap: wrap;
            margin-bottom: 1.5rem;
            padding: 1rem;
            background: #f5f5f5;
            border-radius: 8px;
        }
        .submit-section {
            margin-top: 2rem;
            padding-top: 2rem;
            border-top: 2px solid var(--desert-sand);
        }
        .cancel-link {
            display: block;
            text-align: center;
            margin-top: 1rem;
            color: var(--lobo-gray);
            text-decoration: none;
            transition: color 0.3s;
        }
        .cancel-link:hover {
            color: var(--yucca-yellow);
        }
        
        /* Mobile Responsive */
        @media (max-width: 768px) {
            .post-editor-wrapper {
                padding: 1rem 0.5rem;
            }
            .post-editor-card {
                padding: 1.5rem 1rem;
                border-radius: 8px;
            }
            .editor-header h1 {
                font-size: 1.5rem;
            }
            .editor-toolbar {
                gap: 0.25rem;
            }
            .btn-section {
                font-size: 0.8rem;
                padding: 0.4rem 0.8rem;
            }
            .section-actions {
                gap: 0.25rem;
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
        let sectionCounter = 0;
        const contentSections = document.getElementById('content-sections');
        
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
                        </div>
                        <textarea class="section-content form-textarea" rows="5" placeholder="Write your paragraph here...">${data.text || ''}</textarea>
                    `;
                    break;
                case 'image':
                    html = `
                        <div class="section-header">
                            <i class="fas fa-image"></i>
                            <span>Image</span>
                        </div>
                        <input type="url" class="section-image-url form-input" placeholder="Image URL (e.g., https://example.com/image.jpg)" value="${data.url || ''}" style="margin-bottom: 0.5rem;">
                        <input type="text" class="section-image-alt form-input" placeholder="Alt text for accessibility (optional)" value="${data.alt || ''}">
                        <div class="content-preview">
                            ${data.url ? `<img src="${data.url}" alt="${data.alt || ''}" style="max-width: 100%; height: auto; border-radius: 4px;">` : '<div class="image-placeholder"><i class="fas fa-image" style="font-size: 3rem; opacity: 0.3;"></i></div>'}
                        </div>
                    `;
                    // Update preview on URL change
                    setTimeout(() => {
                        section.querySelector('.section-image-url')?.addEventListener('input', function(e) {
                            const preview = section.querySelector('.content-preview');
                            if (e.target.value) {
                                preview.innerHTML = `<img src="${e.target.value}" alt="" style="max-width: 100%; height: auto; border-radius: 4px;">`;
                            } else {
                                preview.innerHTML = '<div class="image-placeholder"><i class="fas fa-image" style="font-size: 3rem; opacity: 0.3;"></i></div>';
                            }
                        });
                    }, 0);
                    break;
                case 'heading':
                    html = `
                        <div class="section-header">
                            <i class="fas fa-heading"></i>
                            <span>Heading</span>
                        </div>
                        <input type="text" class="section-content form-input" placeholder="Enter heading text..." value="${data.text || ''}">
                    `;
                    break;
                case 'list':
                    html = `
                        <div class="section-header">
                            <i class="fas fa-list"></i>
                            <span>List</span>
                        </div>
                        <textarea class="section-content form-textarea" rows="5" placeholder="Enter list items, one per line...">${data.items || ''}</textarea>
                    `;
                    break;
            }
            
            html += `
                <div class="section-actions">
                    <button type="button" class="move-up btn-section btn-secondary">
                        <i class="fas fa-arrow-up"></i> Up
                    </button>
                    <button type="button" class="move-down btn-section btn-secondary">
                        <i class="fas fa-arrow-down"></i> Down
                    </button>
                    <button type="button" class="delete-section btn-section btn-danger">
                        <i class="fas fa-trash"></i> Delete
                    </button>
                </div>
            `;
            
            section.innerHTML = html;
            return section;
        }
        
        document.getElementById('add-paragraph').addEventListener('click', () => {
            contentSections.appendChild(createSection('paragraph'));
        });
        
        document.getElementById('add-image').addEventListener('click', () => {
            contentSections.appendChild(createSection('image'));
        });
        
        document.getElementById('add-heading').addEventListener('click', () => {
            contentSections.appendChild(createSection('heading'));
        });
        
        document.getElementById('add-list').addEventListener('click', () => {
            contentSections.appendChild(createSection('list'));
        });
        
        contentSections.addEventListener('click', (e) => {
            if (e.target.closest('.delete-section')) {
                e.target.closest('.content-section').remove();
            }
            
            if (e.target.closest('.move-up')) {
                const section = e.target.closest('.content-section');
                const prev = section.previousElementSibling;
                if (prev) {
                    section.parentNode.insertBefore(section, prev);
                }
            }
            
            if (e.target.closest('.move-down')) {
                const section = e.target.closest('.content-section');
                const next = section.nextElementSibling;
                if (next) {
                    section.parentNode.insertBefore(next, section);
                }
            }
        });
        
        document.getElementById('post-form').addEventListener('submit', async (e) => {
            e.preventDefault();
            
            const title = document.getElementById('post-title').value.trim();
            const category = document.getElementById('post-category').value;
            const featuredImage = document.getElementById('featured-image').value.trim();
            const intro = document.getElementById('post-intro').value.trim();
            
            // Validation
            if (!title) {
                showToast('Please enter a title for your post', 'error');
                document.getElementById('post-title').focus();
                return;
            }
            
            if (!intro) {
                showToast('Please write an introduction for your post', 'error');
                document.getElementById('post-intro').focus();
                return;
            }
            
            // Build content array from sections
            const sections = document.querySelectorAll('.content-section');
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
            
            // Show loading state
            const submitBtn = e.target.querySelector('button[type="submit"]');
            const originalBtnText = submitBtn.innerHTML;
            submitBtn.disabled = true;
            submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Submitting...';
            
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
                    showToast('✓ Post submitted successfully! Redirecting...', 'success');
                    setTimeout(() => {
                        window.location.href = 'nav/community/index.php';
                    }, 1500);
                } else {
                    showToast('Error: ' + (data.error || 'Failed to submit post'), 'error');
                    submitBtn.disabled = false;
                    submitBtn.innerHTML = originalBtnText;
                }
            } catch (error) {
                showToast('Error submitting post. Please try again.', 'error');
                console.error('Submission error:', error);
                submitBtn.disabled = false;
                submitBtn.innerHTML = originalBtnText;
            }
        });
        
        // Toast notification function
        function showToast(message, type = 'info') {
            const toast = document.createElement('div');
            toast.className = 'toast ' + type;
            toast.textContent = message;
            toast.style.cssText = `
                position: fixed;
                bottom: 2rem;
                right: 2rem;
                background: ${type === 'success' ? '#28a745' : type === 'error' ? '#dc3545' : '#007bff'};
                color: white;
                padding: 1rem 1.5rem;
                border-radius: 8px;
                box-shadow: 0 4px 12px rgba(0,0,0,0.3);
                z-index: 10000;
                animation: slideIn 0.3s ease;
                max-width: 90%;
            `;
            
            document.body.appendChild(toast);
            
            setTimeout(() => {
                toast.style.animation = 'slideOut 0.3s ease';
                setTimeout(() => toast.remove(), 300);
            }, 4000);
        }
    </script>
    
    <script src="ui/js/if-then.js"></script>
    <script src="ui/js/main.js"></script>
    <script src="ui/js/jquery-loader.js"></script>
</body>
</html>
