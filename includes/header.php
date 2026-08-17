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
    <title>Travira | Explore &bull; Connect &bull; Experience</title>
    
    <!-- Link to Main CSS Stylesheet -->
    <link rel="stylesheet" href="css/style.css">
    
    <!-- FontAwesome for icons (optional, for location markers & search icons) -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body>

    <!-- Header / Navigation Bar -->
    <header class="navbar">
        <div class="nav-container">
            <a href="index.php" class="logo nav-logo-img">
                <img src="images/ui/travira_logo.png" alt="Travira" height="44">
            </a>

            <!-- Navbar Search Bar -->
            <form action="index.php" method="GET" class="nav-search-form">
                <div class="nav-search-wrap">
                    <i class="fa-solid fa-magnifying-glass nav-search-icon"></i>
                    <input type="text" name="query"
                           placeholder="Search destinations..."
                           value="<?php echo htmlspecialchars($_GET['query'] ?? ''); ?>"
                           class="nav-search-input">
                </div>
            </form>

            <nav>
                <ul class="nav-links">
                    <li><a href="index.php">Home</a></li>
                    <?php if (isset($_SESSION['user_id'])): ?>
                        <li>
                            <a href="user_logout.php" class="nav-logout-btn">
                                <i class="fa-solid fa-right-from-bracket"></i>
                                <?php echo htmlspecialchars($_SESSION['user_name']); ?>
                            </a>
                        </li>
                    <?php else: ?>
                        <li><a href="login.php" class="nav-login-btn">Sign In</a></li>
                        <li><a href="register.php" class="nav-register-btn">Register</a></li>
                    <?php endif; ?>
                    <li><a href="admin/index.php" class="admin-btn">Admin Portal</a></li>
                </ul>
            </nav>
        </div>
    </header>