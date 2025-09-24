<?php
// File: article.php
// View single article with edit/revisions if authorized.
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
 
$stmt = $pdo->prepare("SELECT a.*, u.username AS author FROM articles a JOIN users u ON a.author_id = u.id WHERE a.id = ? AND a.status = 'published'");
$stmt->execute([$article_id]);
$article = $stmt->fetch();
 
if (!$article) {
    echo "Article not found.";
    exit();
}
 
// Fetch revisions
$stmt = $pdo->prepare("SELECT r.*, u.username AS editor FROM revisions r JOIN users u ON r.editor_id = u.id WHERE r.article_id = ? ORDER BY r.created_at DESC");
$stmt->execute([$article_id]);
$revisions = $stmt->fetchAll();
 
$is_admin_or_author = ($_SESSION['role'] === 'admin' || $_SESSION['user_id'] == $article['author_id']);
?>
 
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($article['title']); ?> - Wikiknow</title>
    <style>
        /* Internal CSS - Article reading view */
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: 'Georgia', serif; background: #f8f9fa; }
        header { background: #1a1a1a; color: white; padding: 1rem; }
        .container { max-width: 800px; margin: 2rem auto; padding: 0 1rem; }
        .article { background: white; padding: 2rem; border-radius: 10px; box-shadow: 0 4px 12px rgba(0,0,0,0.1); }
        h1 { font-size: 2.5rem; margin-bottom: 1rem; }
        .meta { color: #666; margin-bottom: 2rem; }
        .content { line-height: 1.8; }
        .actions { margin-bottom: 2rem; }
        .actions a { display: inline-block; padding: 0.5rem 1rem; background: #007bff; color: white; text-decoration: none; border-radius: 5px; margin-right: 1rem; }
        .revisions { margin-top: 2rem; }
        .revision { background: #f1f1f1; padding: 1rem; margin-bottom: 1rem; border-radius: 5px; }
        @media (max-width: 768px) { .article { padding: 1rem; } h1 { font-size: 2rem; } }
    </style>
</head>
<body>
    <header>
        <a href="index.php" style="color: white;">Back to Home</a>
    </header>
    <div class="container">
        <article class="article">
            <?php if ($article['image_url']): ?>
                <img src="<?php echo htmlspecialchars($article['image_url']); ?>" alt="<?php echo htmlspecialchars($article['title']); ?>" style="width:100%; height:300px; object-fit:cover; border-radius:10px; margin-bottom:1rem;">
            <?php endif; ?>
            <h1><?php echo htmlspecialchars($article['title']); ?></h1>
            <div class="meta">By <?php echo htmlspecialchars($article['author']); ?> | <?php echo htmlspecialchars($article['category']); ?> | <?php echo date('M j, Y', strtotime($article['created_at'])); ?></div>
            <div class="content"><?php echo nl2br(htmlspecialchars($article['content'])); ?></div>
            <?php if ($article['references']): ?>
                <h3>References</h3>
                <div class="content"><?php echo nl2br(htmlspecialchars($article['references'])); ?></div>
            <?php endif; ?>
            <?php if ($is_admin_or_author): ?>
                <div class="actions">
                    <a href="edit_article.php?id=<?php echo $article_id; ?>">Edit</a>
                    <a href="revisions.php?id=<?php echo $article_id; ?>">View Revisions</a>
                </div>
            <?php endif; ?>
        </article>
        <section class="revisions">
            <h2>Revision History</h2>
            <?php foreach ($revisions as $rev): ?>
                <div class="revision">
                    <p><strong><?php echo date('M j, Y H:i', strtotime($rev['created_at'])); ?></strong> by <?php echo htmlspecialchars($rev['editor']); ?> 
                    <?php if ($rev['revision_notes']): ?> - <?php echo htmlspecialchars($rev['revision_notes']); ?><?php endif; ?></p>
                </div>
            <?php endforeach; ?>
        </section>
    </div>
</body>
</html>
