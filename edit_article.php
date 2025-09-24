<?php
// File: edit_article.php
// Edit article with version control.
session_start();
include 'db.php';
 
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}
 
$article_id = $_GET['id'] ?? 0;
$user_id = $_SESSION['user_id'];
 
$stmt = $pdo->prepare("SELECT * FROM articles WHERE id = ? AND (author_id = ? OR ? = 'admin')");
$stmt->execute([$article_id, $user_id, $_SESSION['role'] ?? 'user']);
$article = $stmt->fetch();
 
if (!$article) {
    echo "Access denied or article not found.";
    exit();
}
 
$error = '';
$success = '';
if ($_POST) {
    $title = $_POST['title'] ?? '';
    $content = $_POST['content'] ?? '';
    $category = $_POST['category'] ?? $article['category'];
    $image_url = $_POST['image_url'] ?? $article['image_url'];
    $references = $_POST['references'] ?? $article['references'];
    $status = $_POST['status'] ?? $article['status'];
    $notes = $_POST['revision_notes'] ?? '';
 
    if ($title && $content) {
        // Save revision
        $stmt = $pdo->prepare("INSERT INTO revisions (article_id, title, content, references, editor_id, revision_notes) VALUES (?, ?, ?, ?, ?, ?)");
        $stmt->execute([$article_id, $title, $content, $references, $user_id, $notes]);
 
        // Update article
        $stmt = $pdo->prepare("UPDATE articles SET title = ?, content = ?, category = ?, image_url = ?, references = ?, status = ?, updated_at = CURRENT_TIMESTAMP WHERE id = ?");
        if ($stmt->execute([$title, $content, $category, $image_url, $references, $status, $article_id])) {
            $success = 'Article updated! <a href="article.php?id=' . $article_id . '">View Article</a>';
        } else {
            $error = 'Update failed.';
        }
    } else {
        $error = 'Title and content required.';
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
    <title>Edit Article - Wikiknow</title>
    <style>
        /* Internal CSS - Similar to create */
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: 'Georgia', serif; background: #f8f9fa; }
        header { background: #1a1a1a; color: white; padding: 1rem; text-align: center; }
        .container { max-width: 800px; margin: 2rem auto; padding: 0 1rem; }
        form { background: white; padding: 2rem; border-radius: 10px; box-shadow: 0 4px 12px rgba(0,0,0,0.1); }
        input, select, textarea { width: 100%; padding: 0.75rem; margin-bottom: 1rem; border: 1px solid #ddd; border-radius: 5px; font-size: 1rem; }
        textarea { height: 300px; resize: vertical; }
        button { width: 100%; padding: 1rem; background: #ffc107; color: #212529; border: none; border-radius: 5px; cursor: pointer; font-size: 1rem; }
        button:hover { background: #e0a800; }
        .error { color: #dc3545; margin-bottom: 1rem; }
        .success { color: #28a745; margin-bottom: 1rem; }
        @media (max-width: 768px) { .container { padding: 0 0.5rem; } }
    </style>
</head>
<body>
    <header>
        <h1>Edit Article</h1>
        <a href="article.php?id=<?php echo $article_id; ?>" style="color: white;">View Article</a>
    </header>
    <div class="container">
        <form method="POST">
            <?php if ($error): ?><p class="error"><?php echo htmlspecialchars($error); ?></p><?php endif; ?>
            <?php if ($success): ?><p class="success"><?php echo $success; ?></p><?php endif; ?>
            <input type="text" name="title" value="<?php echo htmlspecialchars($article['title']); ?>" required>
            <select name="category">
                <?php foreach ($categories as $cat): ?>
                    <option value="<?php echo htmlspecialchars($cat['name']); ?>" <?php echo $cat['name'] == $article['category'] ? 'selected' : ''; ?>><?php echo htmlspecialchars($cat['name']); ?></option>
                <?php endforeach; ?>
            </select>
            <input type="url" name="image_url" value="<?php echo htmlspecialchars($article['image_url']); ?>" placeholder="Image URL">
            <textarea name="content" required><?php echo htmlspecialchars($article['content']); ?></textarea>
            <textarea name="references"><?php echo htmlspecialchars($article['references']); ?></textarea>
            <input type="text" name="revision_notes" placeholder="Revision notes (optional)">
            <select name="status">
                <option value="draft" <?php echo $article['status'] == 'draft' ? 'selected' : ''; ?>>Draft</option>
                <option value="published" <?php echo $article['status'] == 'published' ? 'selected' : ''; ?>>Published</option>
                <?php if ($_SESSION['role'] === 'admin'): ?>
                    <option value="rejected" <?php echo $article['status'] == 'rejected' ? 'selected' : ''; ?>>Rejected</option>
                <?php endif; ?>
            </select>
            <button type="submit">Save Changes</button>
        </form>
    </div>
</body>
</html>
