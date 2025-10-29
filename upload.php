<?php
/**
 * Upload Page - Editor Access Only
 * Simple interface for editors to upload new blog posts
 */

require_once 'config.php';

// Check if user is editor or admin
if (!is_editor() && !is_admin()) {
    header('Location: index.php');
    exit;
}

$conn = null;
try {
    $conn = db_connect();
    $user_email = htmlspecialchars($_SESSION['user_email']);
    $user_role = $_SESSION['user_role'] ?? 'user';
} catch (Exception $e) {
    error_log("Upload page database error: " . $e->getMessage());
}

if ($conn) {
    try {
        $conn->close();
    } catch (Exception $e) {
        // Ignore
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Upload Content - Yucca Club</title>
    <link rel="icon" type="image/png" href="ui/img/favicon.png">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        :root { --desert-sand: #F5F1E9; --yucca-yellow: #a8aa19; --lobo-gray: #63666A; --off-white: #FFFFFF; --darker-sand: #ede9df; }
        html[data-theme='dark'] { --desert-sand: #1a1a1a; --yucca-yellow: #b8ba20; --lobo-gray: #d1d1d1; --off-white: #252525; --darker-sand: #111111; }
        body { background-color: var(--desert-sand); color: var(--lobo-gray); font-family: 'Lato', sans-serif; }
        .container { max-width: 900px; margin: 0 auto; padding: 2rem 1rem; }
        .upload-card { background: var(--off-white); padding: 2rem; border-radius: 12px; box-shadow: 0 2px 8px rgba(0,0,0,0.05); margin-bottom: 1rem; }
        .form-group { margin-bottom: 1.5rem; }
        .form-group label { display: block; margin-bottom: 0.5rem; font-weight: 700; }
        .form-group input, .form-group textarea, .form-group select { width: 100%; padding: 0.75rem; border: 1px solid var(--darker-sand); border-radius: 6px; background: var(--desert-sand); }
        .form-group textarea { min-height: 150px; resize: vertical; }
        .btn { padding: 0.75rem 1.5rem; border-radius: 6px; border: none; cursor: pointer; font-weight: 700; }
        .btn-primary { background: var(--yucca-yellow); color: #fff; }
        .btn-secondary { background: var(--darker-sand); color: var(--lobo-gray); }
        .header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 2rem; }
        .success-message { background: #4ecdc4; color: white; padding: 1rem; border-radius: 6px; margin-bottom: 1rem; display: none; }
        .error-message { background: #ff6b6b; color: white; padding: 1rem; border-radius: 6px; margin-bottom: 1rem; display: none; }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1 style="font-size: 2rem; font-weight: 700;">Upload Content</h1>
            <div>
                <span style="font-size: 0.9rem; opacity: 0.7;">Logged in as: <?= $user_email ?> (<?= ucfirst($user_role) ?>)</span>
                <a href="index.php" class="btn btn-secondary" style="margin-left: 1rem; display: inline-block;">Back to Site</a>
            </div>
        </div>

        <div id="success-message" class="success-message">
            <i class="fas fa-check-circle"></i> Content uploaded successfully!
        </div>

        <div id="error-message" class="error-message"></div>

        <div class="upload-card">
            <h2 style="margin-bottom: 1rem;">Publish New Content</h2>
            <form id="upload-form">
                <div class="form-group">
                    <label>Content Type</label>
                    <select id="content-type" name="type" required>
                        <option value="stories">Story</option>
                        <option value="guides">Guide</option>
                    </select>
                </div>

                <div class="form-group">
                    <label>Title *</label>
                    <input type="text" id="title" name="title" required>
                </div>

                <div class="form-group">
                    <label>Category</label>
                    <input type="text" id="category" name="category" placeholder="e.g., Adventure, Food, Art">
                </div>

                <div class="form-group">
                    <label>Featured Image URL</label>
                    <input type="url" id="featured-image" name="featured_image" placeholder="https://...">
                    <p style="font-size: 0.875rem; opacity: 0.7; margin-top: 0.5rem;">Enter the full URL of your image (e.g., https://example.com/image.jpg)</p>
                </div>

                <div class="form-group">
                    <label>Excerpt</label>
                    <textarea id="excerpt" name="excerpt" placeholder="Brief description of the content"></textarea>
                </div>

                <div class="form-group">
                    <label>Content *</label>
                    <textarea id="content" name="content" style="min-height: 300px;" placeholder="Write your content here... (HTML supported)" required></textarea>
                </div>

                <div class="form-group">
                    <label>Status</label>
                    <select id="status" name="status">
                        <option value="draft">Draft</option>
                        <option value="published">Published</option>
                    </select>
                </div>

                <div style="display: flex; gap: 1rem;">
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-paper-plane"></i> Publish Content
                    </button>
                    <button type="reset" class="btn btn-secondary">Clear Form</button>
                </div>
            </form>
        </div>

        <div class="upload-card">
            <h3 style="margin-bottom: 1rem;">Quick Guide</h3>
            <ul style="line-height: 2;">
                <li><strong>Title:</strong> The main title of your post</li>
                <li><strong>Category:</strong> Helps organize content</li>
                <li><strong>Featured Image:</strong> Enter the full image URL (host images externally)</li>
                <li><strong>Excerpt:</strong> Short preview text</li>
                <li><strong>Content:</strong> Full post content - use the "Toggle Rich Builder" in the admin panel for advanced formatting</li>
                <li><strong>Status:</strong> Draft saves for later, Published goes live</li>
            </ul>
            <p style="margin-top: 1rem; padding: 1rem; background: var(--darker-sand); border-radius: 6px;">
                <strong>💡 Tip:</strong> For a visual editor with drag-and-drop sections, images, headings, and more, use the admin panel's content editor!
            </p>
        </div>
    </div>

    <script>
        document.getElementById('upload-form').onsubmit = async (e) => {
            e.preventDefault();
            
            const formData = new FormData(e.target);
            const type = document.getElementById('content-type').value;
            const title = document.getElementById('title').value;
            
            // Generate slug from title
            const slug = title.toLowerCase()
                .replace(/[^a-z0-9]+/g, '-')
                .replace(/^-|-$/g, '');
            
            formData.append('title', title);
            formData.append('slug', slug);
            formData.append('action', 'create');
            
            try {
                const response = await fetch(`api/content_api.php?type=${type}&action=create`, {
                    method: 'POST',
                    body: formData
                });
                
                const data = await response.json();
                
                if (data.success) {
                    document.getElementById('success-message').style.display = 'block';
                    document.getElementById('upload-form').reset();
                    
                    setTimeout(() => {
                        document.getElementById('success-message').style.display = 'none';
                    }, 3000);
                } else {
                    document.getElementById('error-message').textContent = 'Error: ' + data.error;
                    document.getElementById('error-message').style.display = 'block';
                }
            } catch (error) {
                document.getElementById('error-message').textContent = 'Error: Failed to upload content';
                document.getElementById('error-message').style.display = 'block';
            }
        };
    </script>
</body>
</html>


