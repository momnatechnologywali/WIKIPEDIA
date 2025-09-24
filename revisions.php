<?php
// File: revisions.php
// Full revision history and moderation (admin revert).
session_start();
include 'db.php';
 
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}
 
$article_id = $_GET['id'] ?? 0;
if (!$article_id) {
    header("Location: index.php");
    exit();
}
 
$is_admin = $_SESSION['role'] === 'admin';
 
// Fetch revisions
$stmt = $pdo->prepare("SELECT r.*, u.username AS editor FROM revisions r JOIN users u ON r.editor_id = u.id WHERE r.article_id = ? ORDER BY r.created_at DESC");
$stmt->execute([$article_id]);
$revisions = $stmt->fetchAll();
 
if (empty($revisions)) {
    echo "No revisions yet.";
    exit();
}
 
// Handle revert (admin only)
if ($_POST && $is_admin && isset($_POST['revert_id'])) {
    $revert_id = $_POST['revert_id'];
    $stmt = $pdo->prepare("SELECT title, content, references FROM revisions WHERE id = ?");
    $stmt->execute([$revert_id]);
    $rev = $stmt->fetch();
 
    if ($rev) {
        $stmt = $pdo->prepare("UPDATE articles SET title = ?, content = ?, references = ? WHERE id = ?");
        $stmt->execute([$rev['title'], $rev['content'], $rev['references'], $article_id]);
 
        // Add new revision for revert
        $stmt = $pdo->prepare("INSERT INTO revisions (article_id, title, content, references, editor_id, revision_notes) VALUES (?, ?, ?, ?, ?, 'Reverted to previous version')");
        $stmt->execute([$article_id, $rev['title'], $rev['content'], $rev['references'], $_SESSION['user_id']]);
 
        header("Location: revisions.php?id=$article_id");
        exit();
    }
}
?>
 
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Revisions - Wikiknow</title>
    <style>
        /* Internal CSS - Revision list */
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: 'Georgia', serif; background: #f8f9fa; }
        header { background: #1a1a1a; color: white; padding: 1rem; }
        .container { max-width: 800px; margin: 2rem auto; padding: 0 1rem; }
        .revision-list { background: white; border-radius: 10px; overflow: hidden; box-shadow: 0 4px 12px rgba(0,0,0,0.1); }
        .revision-item { padding: 1.5rem; border-bottom: 1px solid #eee; }
        .revision-item:last-child { border-bottom: none; }
        .revision-meta { color: #666; margin-bottom: 0.5rem; }
        .revision-content { margin-bottom: 1rem; }
        .revert-btn { background: #dc3545; color: white; padding: 0.5rem 1rem; border: none; border-radius: 5px; cursor: pointer; }
        @media (max-width: 768px) { .revision-item { padding: 1rem; } }
    </style>
</head>
<body>
    <header>
        <a href="article.php?id=<?php echo $article_id; ?>" style="color: white;">Back to Article</a>
    </header>
    <div class="container">
        <div class="revision-list">
            <?php foreach ($revisions as $rev): ?>
                <div class="revision-item">
                    <div class="revision-meta">
                        <?php echo date('M j, Y H:i', strtotime($rev['created_at'])); ?> by <?php echo htmlspecialchars($rev['editor']); ?>
                        <?php if ($rev['revision_notes']): ?> - <?php echo htmlspecialchars($rev['revision_notes']); ?><?php endif; ?>
                    </div>
                    <div class="revision-content">
                        <h4><?php echo htmlspecialchars($rev['title']); ?></h4>
                        <p><?php echo substr(nl2br(htmlspecialchars($rev['content'])), 0, 200); ?>...</p>
                    </div>
                    <?php if ($is_admin): ?>
                        <form method="POST" style="display: inline;">
                            <input type="hidden" name="revert_id" value="<?php echo $rev['id']; ?>">
                            <button type="submit" class="revert-btn" onclick="return confirm('Revert to this version?')">Revert</button>
                        </form>
                    <?php endif; ?>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
</body>
</html>
