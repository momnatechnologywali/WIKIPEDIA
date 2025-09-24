<?php
// File: create_article.php
// Article creation page.
session_start();
include 'db.php';
 
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}
 
$user_id = $_SESSION['user_id'];
$error = '';
$success = '';
if ($_POST) {
    $title = $_POST['title'] ?? '';
    $content = $_POST['content'] ?? '';
    $category = $_POST['category'] ?? 'General';
    $image_url = $_POST['image_url'] ?? '';
    $references = $_POST['references'] ?? '';
 
    if ($title && $content) {
        $stmt = $pdo->prepare("INSERT INTO articles (title, content, category, image_url, references, author_id, status) VALUES (?, ?, ?, ?, ?, ?, 'draft')");
        if ($stmt->execute([$title, $content, $category, $image_url, $references, $user_id])) {
            $success = 'Article created as draft! <a href="index.php">Back to home</a>';
        } else {
            $error = 'Failed to create article.';
        }
    } else {
        $error = 'Title and content are required.';
    }
}
 
// Fetch categories
$stmt = $pdo->query("SELECT * FROM categories");
$categories = $stmt->fetchAll();
?>
 
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Create Article - Wikiknow</title>
    <style>
        /* Internal CSS - Rich editor-like */
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: 'Georgia', serif; background: #f8f9fa; }
        header { background: #1a1a1a; color: white; padding: 1rem; text-align: center; }
        .container { max-width: 800px; margin: 2rem auto; padding: 0 1rem; }
        form { background: white; padding: 2rem; border-radius: 10px; box-shadow: 0 4px 12px rgba(0,0,0,0.1); }
        input, select, textarea { width: 100%; padding: 0.75rem; margin-bottom: 1rem; border: 1px solid #ddd; border-radius: 5px; font-size: 1rem; }
        textarea { height: 300px; resize: vertical; }
        button { width: 100%; padding: 1rem; background: #28a745; color: white; border: none; border-radius: 5px; cursor: pointer; font-size: 1rem; }
        button:hover { background: #218838; }
        .error { color: #dc3545; margin-bottom: 1rem; }
        .success { color: #28a745; margin-bottom: 1rem; }
        @media (max-width: 768px) { .container { padding: 0 0.5rem; } }
    </style>
</head>
<body>
    <header>
        <h1>Create New Article</h1>
        <a href="index.php" style="color: white;">Back to Home</a>
    </header>
    <div class="container">
        <form method="POST">
            <?php if ($error): ?><p class="error"><?php echo htmlspecialchars($error); ?></p><?php endif; ?>
            <?php if ($success): ?><p class="success"><?php echo $success; ?></p><?php endif; ?>
            <input type="text" name="title" placeholder="Article Title" required>
            <select name="category">
                <?php foreach ($categories as $cat): ?>
                    <option value="<?php echo htmlspecialchars($cat['name']); ?>"><?php echo htmlspecialchars($cat['name']); ?></option>
                <?php endforeach; ?>
            </select>
            <input type="url" name="image_url" placeholder="Image URL (optional)">
            <textarea name="content" placeholder="Article Content (use Markdown for formatting)" required></textarea>
            <textarea name="references" placeholder="References (optional)"></textarea>
            <button type="submit">Create Draft</button>
        </form>
    </div>
</body>
</html>
