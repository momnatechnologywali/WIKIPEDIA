<?php
// File: search.php
// Search and category filtering.
session_start();
include 'db.php';
 
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}
 
$query = $_GET['q'] ?? '';
$category = $_GET['category'] ?? '';
 
$where = [];
$params = [];
if ($query) {
    $where[] = "(title LIKE ? OR content LIKE ?)";
    $params[] = "%$query%";
    $params[] = "%$query%";
}
if ($category) {
    $where[] = "category = ?";
    $params[] = $category;
}
 
$sql = "SELECT a.id, a.title, a.category, u.username AS author, a.created_at 
        FROM articles a JOIN users u ON a.author_id = u.id 
        WHERE " . (empty($where) ? "1=1" : implode(' AND ', $where)) . " AND status = 'published' 
        ORDER BY a.created_at DESC";
$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$articles = $stmt->fetchAll();
 
// Fetch all categories for filter
$stmt = $pdo->query("SELECT * FROM categories");
$categories = $stmt->fetchAll();
?>
 
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Search - Wikiknow</title>
    <style>
        /* Internal CSS - Search results */
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: 'Georgia', serif; background: #f8f9fa; }
        header { background: #1a1a1a; color: white; padding: 1rem; }
        .container { max-width: 1200px; margin: 2rem auto; padding: 0 2rem; }
        .search-form { margin-bottom: 2rem; }
        .search-form input { width: 70%; padding: 0.5rem; border: 1px solid #ddd; border-radius: 5px; }
        .search-form button { padding: 0.5rem 1rem; background: #007bff; color: white; border: none; border-radius: 5px; cursor: pointer; }
        .filters { display: flex; flex-wrap: wrap; gap: 1rem; margin-bottom: 2rem; }
        .category-filter { background: #6c757d; color: white; padding: 0.5rem 1rem; border-radius: 20px; text-decoration: none; }
        .results { display: grid; grid-template-columns: repeat(auto-fit, minmax(300px, 1fr)); gap: 2rem; }
        .result-card { background: white; padding: 1rem; border-radius: 10px; box-shadow: 0 2px 8px rgba(0,0,0,0.1); }
        .result-card h3 a { color: #007bff; text-decoration: none; }
        @media (max-width: 768px) { .search-form input { width: 100%; margin-bottom: 1rem; } .filters { justify-content: center; } }
    </style>
</head>
<body>
    <header>
        <a href="index.php" style="color: white;">Back to Home</a>
    </header>
    <div class="container">
        <form class="search-form" method="GET">
            <input type="text" name="q" value="<?php echo htmlspecialchars($query); ?>" placeholder="Search articles...">
            <button type="submit">Search</button>
        </form>
        <div class="filters">
            <a href="search.php" class="category-filter" style="<?php echo !$category ? 'background: #007bff;' : ''; ?>">All</a>
            <?php foreach ($categories as $cat): ?>
                <a href="search.php?category=<?php echo urlencode($cat['name']); ?>" class="category-filter" style="<?php echo $category == $cat['name'] ? 'background: #007bff;' : ''; ?>"><?php echo htmlspecialchars($cat['name']); ?></a>
            <?php endforeach; ?>
        </div>
        <div class="results">
            <?php if (empty($articles)): ?>
                <p>No articles found.</p>
            <?php else: ?>
                <?php foreach ($articles as $art): ?>
                    <div class="result-card">
                        <h3><a href="article.php?id=<?php echo $art['id']; ?>"><?php echo htmlspecialchars($art['title']); ?></a></h3>
                        <p>By <?php echo htmlspecialchars($art['author']); ?> | <?php echo htmlspecialchars($art['category']); ?></p>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </div>
</body>
</html>
