<?php
/*
 * ADMIN LOGIN PAGE
 * ----------------
 * Checks username and password against the admins collection.
 * Uses PHP's password_verify() for secure bcrypt comparison.
 * On success, stores session data and redirects to dashboard.
 */
session_start();

// If already logged in, skip the login page
if (isset($_SESSION['admin_logged_in']) && $_SESSION['admin_logged_in'] === true) {
    header('Location: dashboard.php');
    exit;
}

$error      = '';
$typed_user = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    require_once '../config/db.php';

    $username   = trim($_POST['username'] ?? '');
    $password   = $_POST['password'] ?? '';
    $typed_user = $username;

    if ($username === '' || $password === '') {
        $error = 'Please enter both username and password.';
    } else {
        try {
            // Fetch admin record by username
            $doc = $adminsCollection->findOne(['username' => $username]);
            $admin = docToArray($doc);

            // Verify password against the stored bcrypt hash
            if ($admin && isset($admin['password_hash']) && password_verify($password, $admin['password_hash'])) {
                $_SESSION['admin_logged_in'] = true;
                $_SESSION['admin_username']  = $admin['username'];
                header('Location: dashboard.php');
                exit;
            } else {
                $error = 'Invalid username or password.';
            }
        } catch (Exception $e) {
            $error = 'Database error: ' . $e->getMessage();
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Login — TravelSite</title>
    <link rel="stylesheet" href="../css/style.css">
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body>

<div class="admin-login-page">

    <!-- Left Panel: Decorative -->
    <div class="login-panel-left">
        <div class="login-panel-brand">
            <i class="fa-solid fa-plane-departure"></i> TravelSite
        </div>
        <div class="login-panel-copy">
            <h2>Manage your travel packages and bookings.</h2>
            <p>Your admin portal for adding trips, reviewing orders, and tracking customer bookings.</p>
        </div>
    </div>

    <!-- Right Panel: Login Form -->
    <div class="login-panel-right">
        <div class="login-box">

            <div class="login-box-header">
                <span class="login-badge">
                    <i class="fa-solid fa-shield-halved"></i> Admin Portal
                </span>
                <h1>Sign In</h1>
                <p>Enter your credentials to access the dashboard.</p>
            </div>

            <?php if ($error): ?>
                <p class="error-msg">
                    <i class="fa-solid fa-circle-exclamation"></i>
                    <?php echo htmlspecialchars($error); ?>
                </p>
            <?php endif; ?>

            <form method="POST" action="index.php">

                <div class="form-group">
                    <label for="username">Username</label>
                    <input type="text"
                           id="username"
                           name="username"
                           placeholder="Enter username"
                           value="<?php echo htmlspecialchars($typed_user); ?>"
                           required autofocus>
                </div>

                <div class="form-group">
                    <label for="password">Password</label>
                    <input type="password"
                           id="password"
                           name="password"
                           placeholder="Enter password"
                           required>
                </div>

                <button type="submit" class="btn-primary">
                    Sign In <i class="fa-solid fa-arrow-right"></i>
                </button>

            </form>

            <!-- Default credentials hint for the examiner -->
            <div class="login-hint-box">
                <p><strong>Default Credentials:</strong></p>
                <p>Username: <code>admin</code> &nbsp;|&nbsp; Password: <code>admin123</code></p>
            </div>

            <a href="../index.php" class="login-back-link">
                <i class="fa-solid fa-arrow-left"></i> Back to TravelSite
            </a>

        </div>
    </div>
</div>

</body>
</html>
