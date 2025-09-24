<?php
// File: login.php
// User login page.
session_start();
include 'db.php';
 
if (isset($_SESSION['user_id'])) {
    header("Location: index.php");
    exit();
}
 
$error = '';
if ($_POST) {
    $email = $_POST['email'] ?? '';
    $password = $_POST['password'] ?? '';
 
    if ($email && $password) {
        $stmt = $pdo->prepare("SELECT id, password, role FROM users WHERE email = ?");
        $stmt->execute([$email]);
        $user = $stmt->fetch();
 
        if ($user && verifyPassword($password, $user['password'])) {
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['role'] = $user['role'];
            header("Location: index.php");
            exit();
        } else {
            $error = 'Invalid email or password.';
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
    <title>Login - Wikiknow</title>
    <style>
        /* Internal CSS - Elegant login form */
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: 'Georgia', serif; display: flex; justify-content: center; align-items: center; min-height: 100vh; background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); }
        .login-form { background: white; padding: 2rem; border-radius: 10px; box-shadow: 0 8px 32px rgba(0,0,0,0.1); width: 100%; max-width: 400px; }
        h2 { text-align: center; margin-bottom: 1.5rem; color: #333; }
        input { width: 100%; padding: 0.75rem; margin-bottom: 1rem; border: 1px solid #ddd; border-radius: 5px; font-size: 1rem; }
        button { width: 100%; padding: 0.75rem; background: #007bff; color: white; border: none; border-radius: 5px; cursor: pointer; font-size: 1rem; transition: background 0.3s; }
        button:hover { background: #0056b3; }
        .error { color: #dc3545; text-align: center; margin-bottom: 1rem; }
        .links { text-align: center; margin-top: 1rem; }
        .links a { color: #007bff; text-decoration: none; }
        @media (max-width: 480px) { .login-form { margin: 1rem; padding: 1.5rem; } }
    </style>
</head>
<body>
    <form class="login-form" method="POST">
        <h2>Login to Wikiknow</h2>
        <?php if ($error): ?><p class="error"><?php echo htmlspecialchars($error); ?></p><?php endif; ?>
        <input type="email" name="email" placeholder="Email" required>
        <input type="password" name="password" placeholder="Password" required>
        <button type="submit">Login</button>
        <div class="links">
            <a href="signup.php">Don't have an account? Sign up</a>
        </div>
    </form>
</body>
</html>
