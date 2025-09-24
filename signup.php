<?php
// File: signup.php
// User signup page.
session_start();
include 'db.php';
 
if (isset($_SESSION['user_id'])) {
    header("Location: index.php");
    exit();
}
 
$error = '';
$success = '';
if ($_POST) {
    $username = $_POST['username'] ?? '';
    $email = $_POST['email'] ?? '';
    $password = $_POST['password'] ?? '';
 
    if ($username && $email && $password) {
        // Check if user exists
        $stmt = $pdo->prepare("SELECT id FROM users WHERE username = ? OR email = ?");
        $stmt->execute([$username, $email]);
        if ($stmt->fetch()) {
            $error = 'Username or email already exists.';
        } else {
            $hashed = hashPassword($password);
            $stmt = $pdo->prepare("INSERT INTO users (username, email, password) VALUES (?, ?, ?)");
            if ($stmt->execute([$username, $email, $hashed])) {
                $success = 'Account created! Please login.';
            } else {
                $error = 'Signup failed. Try again.';
            }
        }
    } else {
        $error = 'Please fill all fields.';
    }
}
?>
 
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Signup - Wikiknow</title>
    <style>
        /* Internal CSS - Matching login style */
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: 'Georgia', serif; display: flex; justify-content: center; align-items: center; min-height: 100vh; background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); }
        .signup-form { background: white; padding: 2rem; border-radius: 10px; box-shadow: 0 8px 32px rgba(0,0,0,0.1); width: 100%; max-width: 400px; }
        h2 { text-align: center; margin-bottom: 1.5rem; color: #333; }
        input { width: 100%; padding: 0.75rem; margin-bottom: 1rem; border: 1px solid #ddd; border-radius: 5px; font-size: 1rem; }
        button { width: 100%; padding: 0.75rem; background: #28a745; color: white; border: none; border-radius: 5px; cursor: pointer; font-size: 1rem; transition: background 0.3s; }
        button:hover { background: #218838; }
        .error { color: #dc3545; text-align: center; margin-bottom: 1rem; }
        .success { color: #28a745; text-align: center; margin-bottom: 1rem; }
        .links { text-align: center; margin-top: 1rem; }
        .links a { color: #007bff; text-decoration: none; }
        @media (max-width: 480px) { .signup-form { margin: 1rem; padding: 1.5rem; } }
    </style>
</head>
<body>
    <form class="signup-form" method="POST">
        <h2>Sign up for Wikiknow</h2>
        <?php if ($error): ?><p class="error"><?php echo htmlspecialchars($error); ?></p><?php endif; ?>
        <?php if ($success): ?><p class="success"><?php echo htmlspecialchars($success); ?></p><?php endif; ?>
        <input type="text" name="username" placeholder="Username" required>
        <input type="email" name="email" placeholder="Email" required>
        <input type="password" name="password" placeholder="Password" required>
        <button type="submit">Sign Up</button>
        <div class="links">
            <a href="login.php">Already have an account? Login</a>
        </div>
    </form>
</body>
</html>
