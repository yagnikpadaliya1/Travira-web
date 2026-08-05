<?php
// Default XAMPP database credentials
$host = 'localhost';
$dbname = 'travira_db'; // Make sure you create this database in phpMyAdmin
$username = 'root';
$password = ''; // XAMPP's default password is an empty string

try {
    // Create a new PDO instance
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8", $username, $password);
    
    // Set PDO to throw exceptions on errors so we can catch them easily
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
} catch (PDOException $e) {
    // If connection fails, stop the script and show the error
    die("Database connection failed: " . $e->getMessage());
}
?>