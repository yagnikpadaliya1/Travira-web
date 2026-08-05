<?php
// Start a PHP session if one isn't already active
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Travel Explorer | Book Your Great Adventure</title>
    
    <!-- Link to Main CSS Stylesheet -->
    <link rel="stylesheet" href="css/style.css">
    
    <!-- FontAwesome for icons (optional, for location markers & search icons) -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body>

    <!-- Header / Navigation Bar -->
    <header class="navbar">
        <div class="nav-container">
            <a href="index.php" class="logo">
                <i class="fa-solid fa-plane-departure"></i> TravelSite
            </a>
            <nav>
                <ul class="nav-links">
                    <li><a href="index.php">Home</a></li>
                    <li><a href="search.php">Destinations</a></li>
                    <li><a href="admin/index.php" class="admin-btn">Admin Portal</a></li>
                </ul>
            </nav>
        </div>
    </header>