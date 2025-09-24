<?php
// File: profile.php
// User profile management.
session_start();
include 'db.php';
 
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}
 
$user_id = $_SESSION['user_id'];
$stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
$stmt->execute([$user_id]);
$user = $stmt->fetch();
 
$error = '';
$success = '';
if ($_POST) {
    $bio = $_POST['bio'] ?? $user['bio'];
    $profile_image = $_POST['profile_image'] ?? $user['profile_image'];
 
    $stmt = $pdo->prepare("UPDATE users SET bio = ?, profile_image = ? WHERE id = ?");
    if ($stmt->execute([$bio, $profile_image, $user_id])) {
        $success = 'Profile updated!';
        $user['bio'] = $bio;
        $user['profile_image'] = $profile_image;
    } else {
        $error = 'Update failed.';
    }
}
 
// Fetch user's articles
$stmt = $pdo->prepare("SELECT * FROM articles WHERE author_id = ? ORDER BY created_at DESC");
$stmt->execute([$user_id]);
$articles = $stmt->fetchAll();
?>
 
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Profile - Wikiknow</title>
    <style>
        /* Internal CSS - Profile page */
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: 'Georgia', serif; background: #f8f9fa; }
        header { background: #1a1a1a; color: white; padding: 1rem; }
        .container { max-width: 800px; margin: 2rem auto; padding: 0 1rem; }
        .profile-card { background: white; padding: 2rem; border-radius: 10px; box-shadow: 0 4px 12px rgba(0,0,0,0.1); margin-bottom: 2rem; }
        .profile-img { width: 100px; height: 100px; border-radius: 50%; object-fit: cover; }
        .articles-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(250px, 1fr)); gap: 1rem; }
        .article-item { background: #f1f1f1; padding: 1rem; border-radius: 5px; }
        .error { color: #dc3545; margin-bottom: 1rem; }
        .success { color: #28a745; margin-bottom: 1rem; }
        @media (max-width: 768px) { .articles-grid { grid-template-columns: 1fr; } }
    </style>
</head>
<body>
    <header>
        <a href="index.php" style="color: white;">Back to Home</a>
    </header>
    <div class="container">
        <div class="profile-card">
            <?php if ($error): ?><p class="error"><?php echo htmlspecialchars($error); ?></p><?php endif; ?>
            <?php if ($success): ?><p class="success"><?php echo htmlspecialchars($success); ?></p><?php endif; ?>
            <img src="<?php echo htmlspecialchars($user['profile_image'] ?? 'https://via.placeholder.com/100'); ?>" alt="Profile" class="profile-img">
            <h2><?php echo htmlspecialchars($user['username']); ?></h2>
            <p><?php echo htmlspecialchars($user['email']); ?></p>
            <form method="POST">
                <textarea name="bio" placeholder="Bio"><?php echo htmlspecialchars($user['bio']); ?></textarea>
                <input type="url" name="profile_image" value="<?php echo htmlspecialchars($user['profile_image']); ?>" placeholder="Profile Image URL">
                <button type="submit">Update Profile</button>
            </form>
        </div>
        <div class="profile-card">
            <h3>Your Articles</h3>
            <div class="articles-grid">
                <?php foreach ($articles as $art): ?>
                    <div class="article-item">
                        <h4><a href="article.php?id=<?php echo $art['id']; ?>"><?php echo htmlspecialchars($art['title']); ?></a></h4>
                        <p><?php echo date('M j, Y', strtotime($art['created_at'])); ?> | <?php echo htmlspecialchars($art['status']); ?></p>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    </div>
</body>
</html>
