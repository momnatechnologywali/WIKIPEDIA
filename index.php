<?php
// File: index.php
// Homepage: Displays featured and recent articles, search bar, categories.
session_start();
include 'db.php';
 
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}
 
// Fetch featured articles (e.g., published with high views, but for simplicity, latest published)
$stmt = $pdo->query("SELECT a.id, a.title, a.content, a.category, a.image_url, u.username AS author, a.created_at 
                     FROM articles a 
                     JOIN users u ON a.author_id = u.id 
                     WHERE a.status = 'published' 
                     ORDER BY a.created_at DESC 
                     LIMIT 5");
$recent_articles = $stmt->fetchAll();
 
// Fetch categories
$stmt = $pdo->query("SELECT * FROM categories LIMIT 10");
$categories = $stmt->fetchAll();
?>
 
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Wikiknow - Home</title>
    <style>
        /* Internal CSS for stunning, Wikipedia-like design */
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: 'Georgia', serif; line-height: 1.6; color: #333; background: #f8f9fa; }
        header { background: linear-gradient(135deg, #1a1a1a, #333); color: white; padding: 1rem 0; position: sticky; top: 0; z-index: 100; }
        nav { max-width: 1200px; margin: 0 auto; display: flex; justify-content: space-between; align-items: center; padding: 0 2rem; }
        .logo { font-size: 2rem; font-weight: bold; }
        .search-bar { flex: 1; max-width: 500px; margin: 0 2rem; }
        .search-bar input { width: 100%; padding: 0.5rem; border: none; border-radius: 20px; font-size: 1rem; }
        .user-menu { display: flex; align-items: center; gap: 1rem; }
        .user-menu a { color: white; text-decoration: none; padding: 0.5rem 1rem; border-radius: 5px; transition: background 0.3s; }
        .user-menu a:hover { background: rgba(255,255,255,0.1); }
        main { max-width: 1200px; margin: 2rem auto; padding: 0 2rem; }
        .featured { margin-bottom: 3rem; }
        .featured h2 { font-size: 2.5rem; margin-bottom: 1rem; color: #1a1a1a; }
        .article-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(300px, 1fr)); gap: 2rem; }
        .article-card { background: white; border-radius: 10px; overflow: hidden; box-shadow: 0 4px 12px rgba(0,0,0,0.1); transition: transform 0.3s; }
        .article-card:hover { transform: translateY(-5px); }
        .article-card img { width: 100%; height: 200px; object-fit: cover; }
        .article-card h3 { padding: 1rem; font-size: 1.5rem; }
        .article-card p { padding: 0 1rem 1rem; }
        .categories { display: flex; flex-wrap: wrap; gap: 1rem; margin-bottom: 2rem; }
        .category-btn { background: #007bff; color: white; padding: 0.5rem 1rem; border-radius: 20px; text-decoration: none; transition: background 0.3s; }
        .category-btn:hover { background: #0056b3; }
        footer { background: #1a1a1a; color: white; text-align: center; padding: 1rem; margin-top: 3rem; }
        @media (max-width: 768px) { nav { flex-direction: column; gap: 1rem; } .search-bar { max-width: 100%; margin: 0; } .article-grid { grid-template-columns: 1fr; } }
    </style>
</head>
<body>
    <header>
        <nav>
            <div class="logo">Wikiknow</div>
            <div class="search-bar">
                <input type="text" id="searchInput" placeholder="Search articles...">
            </div>
            <div class="user-menu">
                <a href="create_article.php">Create Article</a>
                <a href="profile.php">Profile</a>
                <a href="logout.php">Logout</a>
            </div>
        </nav>
    </header>
    <main>
        <section class="featured">
            <h2>Recent Articles</h2>
            <div class="article-grid" id="articleGrid">
                <?php foreach ($recent_articles as $article): ?>
                    <div class="article-card">
                        <?php if ($article['image_url']): ?>
                            <img src="<?php echo htmlspecialchars($article['image_url']); ?>" alt="<?php echo htmlspecialchars($article['title']); ?>">
                        <?php endif; ?>
                        <h3><a href="article.php?id=<?php echo $article['id']; ?>"><?php echo htmlspecialchars($article['title']); ?></a></h3>
                        <p>By <?php echo htmlspecialchars($article['author']); ?> | <?php echo htmlspecialchars($article['category']); ?> | <?php echo date('M j, Y', strtotime($article['created_at'])); ?></p>
                    </div>
                <?php endforeach; ?>
            </div>
        </section>
        <section>
            <h2>Categories</h2>
            <div class="categories">
                <?php foreach ($categories as $cat): ?>
                    <a href="search.php?category=<?php echo urlencode($cat['name']); ?>" class="category-btn"><?php echo htmlspecialchars($cat['name']); ?></a>
                <?php endforeach; ?>
            </div>
        </section>
    </main>
    <footer>
        <p>&copy; 2025 Wikiknow. All rights reserved.</p>
    </footer>
    <script>
        // Internal JS for search redirection
        document.getElementById('searchInput').addEventListener('keypress', function(e) {
            if (e.key === 'Enter') {
                const query = this.value;
                if (query) {
                    window.location.href = `search.php?q=${encodeURIComponent(query)}`;
                }
            }
        });
    </script>
</body>
</html>
