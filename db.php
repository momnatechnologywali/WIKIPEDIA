<?php
// File: db.php
// Database connection file. Include this in all other PHP files.
 
$host = 'localhost'; // Assuming MySQL on localhost; adjust if needed
$dbname = 'dbdwj2traxeyul';
$username = 'um4u5gpwc3dwc';
$password = 'neqhgxo10ioe';
 
try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    die("Connection failed: " . $e->getMessage());
}
 
// Function to hash password
function hashPassword($password) {
    return password_hash($password, PASSWORD_DEFAULT);
}
 
// Function to verify password
function verifyPassword($password, $hash) {
    return password_verify($password, $hash);
}
?>
