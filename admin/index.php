<?php
/*
 * ADMIN LOGIN PAGE
 * ----------------
 * Checks username and password against the admins table.
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
            $stmt = $pdo->prepare("SELECT * FROM admins WHERE username = :u LIMIT 1");
            $stmt->execute([':u' => $username]);
            $admin = $stmt->fetch(PDO::FETCH_ASSOC);

            // Verify password against the stored bcrypt hash
            if ($admin && password_verify($password, $admin['password_hash'])) {
                $_SESSION['admin_logged_in'] = true;
                $_SESSION['admin_username']  = $admin['username'];
                header('Location: dashboard.php');
                exit;
            } else {
                $error = 'Invalid username or password.';
            }
        } catch (PDOException $e) {
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
    <title>Admin Login — Travira</title>
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap">
    <style>
        *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }

        body {
            font-family: 'Inter', sans-serif;
            min-height: 100vh;
            background: url('../images/ui/new_sky_bg.jpg') center/cover no-repeat fixed;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            padding: 20px;
        }

        /* top-left back link */
        .back-home {
            position: fixed;
            top: 18px; left: 22px;
            font-size: 0.82rem;
            color: #1e293b;
            text-decoration: none;
            background: rgba(255,255,255,0.75);
            backdrop-filter: blur(6px);
            padding: 6px 14px;
            border-radius: 20px;
            border: 1px solid rgba(255,255,255,0.6);
            display: flex; align-items: center; gap: 6px;
            transition: background 0.2s;
        }
        .back-home:hover { background: rgba(255,255,255,0.92); }

        /* floating card */
        .auth-card {
            background: rgba(255, 255, 255, 0.72);
            backdrop-filter: blur(20px);
            -webkit-backdrop-filter: blur(20px);
            border: 1px solid rgba(255, 255, 255, 0.85);
            border-radius: 20px;
            padding: 36px 38px 32px;
            width: 100%;
            max-width: 400px;
            box-shadow: 0 8px 32px rgba(15, 23, 42, 0.14);
            text-align: center;
        }

        /* logo at top */
        .auth-logo-wrap {
            width: 72px; height: 72px;
            background: #fff;
            border-radius: 18px;
            box-shadow: 0 4px 16px rgba(0,0,0,0.10);
            display: flex; align-items: center; justify-content: center;
            margin: 0 auto 20px;
            overflow: hidden;
        }
        .auth-logo-wrap img {
            width: 60px; height: 60px;
            object-fit: contain;
        }

        /* admin badge */
        .admin-badge {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            background: rgba(37,99,235,0.1);
            color: #2563eb;
            font-size: 0.72rem;
            font-weight: 700;
            padding: 4px 12px;
            border-radius: 20px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            border: 1px solid rgba(37,99,235,0.2);
            margin-bottom: 14px;
        }

        .auth-card h1 {
            font-size: 1.3rem;
            font-weight: 700;
            color: #0f172a;
            margin-bottom: 6px;
        }
        .auth-card p.sub {
            font-size: 0.85rem;
            color: #64748b;
            margin-bottom: 24px;
            line-height: 1.5;
        }

        /* error */
        .auth-error {
            background: #fef2f2;
            border: 1px solid #fecaca;
            color: #b91c1c;
            border-radius: 10px;
            padding: 10px 14px;
            font-size: 0.84rem;
            text-align: left;
            margin-bottom: 16px;
        }

        /* fields */
        .field { margin-bottom: 14px; text-align: left; }
        .field label {
            display: block;
            font-size: 0.78rem;
            font-weight: 600;
            color: #475569;
            margin-bottom: 5px;
        }
        .field-wrap { position: relative; }
        .field-wrap .ficon {
            position: absolute; left: 12px; top: 50%;
            transform: translateY(-50%);
            font-size: 0.82rem; color: #94a3b8;
        }
        .field input {
            width: 100%;
            padding: 10px 12px 10px 34px;
            border: 1.5px solid rgba(203,213,225,0.8);
            border-radius: 10px;
            font-size: 0.9rem;
            font-family: inherit;
            background: rgba(255,255,255,0.65);
            color: #0f172a;
            outline: none;
            transition: border-color 0.2s, background 0.2s;
        }
        .field input:focus {
            border-color: #2563eb;
            background: rgba(255,255,255,0.95);
        }
        .field input::placeholder { color: #b0bec5; }

        /* submit */
        .btn-submit {
            width: 100%;
            padding: 11px;
            background: #0f172a;
            color: #fff;
            border: none;
            border-radius: 10px;
            font-size: 0.95rem;
            font-weight: 600;
            font-family: inherit;
            cursor: pointer;
            margin-top: 6px;
            transition: background 0.2s, transform 0.15s;
        }
        .btn-submit:hover { background: #1e293b; transform: translateY(-1px); }

        /* credentials hint */
        .hint-box {
            background: rgba(255,255,255,0.55);
            border: 1px solid rgba(203,213,225,0.5);
            border-radius: 10px;
            padding: 12px 14px;
            margin-top: 18px;
            font-size: 0.8rem;
            color: #64748b;
            line-height: 1.7;
            text-align: left;
        }
        .hint-box strong { color: #475569; }
        .hint-box code {
            background: rgba(255,255,255,0.8);
            border: 1px solid rgba(203,213,225,0.6);
            padding: 2px 7px;
            border-radius: 4px;
            font-size: 0.78rem;
            color: #2563eb;
        }

        /* eye toggle */
        .eye-btn {
            position: absolute; right: 11px; top: 50%;
            transform: translateY(-50%);
            background: none; border: none; cursor: pointer;
            color: #94a3b8; font-size: 0.82rem; padding: 2px;
        }
        .eye-btn:hover { color: #475569; }

        @media (max-width: 440px) {
            .auth-card { padding: 28px 22px 24px; }
        }
    </style>
</head>
<body>

<a href="../index.php" class="back-home">&#8592; Back to Travira</a>

<div class="auth-card">

    <!-- Logo -->
    <div class="auth-logo-wrap">
        <img src="../images/ui/travira_logo.png" alt="Travira"
             onerror="this.style.display='none';this.parentElement.innerHTML='<span style=\'font-size:2rem\'>✈️</span>'">
    </div>

    <span class="admin-badge">🛡 Admin Portal</span>

    <h1>Admin Sign In</h1>
    <p class="sub">Enter your credentials to access the dashboard.</p>

    <?php if ($error): ?>
        <div class="auth-error">⚠ <?php echo htmlspecialchars($error); ?></div>
    <?php endif; ?>

    <form method="POST" action="index.php">

        <div class="field">
            <label for="username">Username</label>
            <div class="field-wrap">
                <span class="ficon">👤</span>
                <input type="text" id="username" name="username"
                       placeholder="Enter username"
                       value="<?php echo htmlspecialchars($typed_user); ?>"
                       required autofocus>
            </div>
        </div>

        <div class="field">
            <label for="password">Password</label>
            <div class="field-wrap">
                <span class="ficon">🔒</span>
                <input type="password" id="password" name="password"
                       placeholder="Enter password" required>
                <button type="button" class="eye-btn" onclick="togglePwd()"
                        title="Show/hide password">👁</button>
            </div>
        </div>

        <button type="submit" class="btn-submit">Sign In →</button>

    </form>

    <!-- Default credentials hint -->
    <div class="hint-box">
        <p><strong>Default Credentials:</strong></p>
        <p>Username: <code>admin</code> &nbsp;|&nbsp; Password: <code>admin123</code></p>
    </div>

</div>

<script>
function togglePwd() {
    const f = document.getElementById('password');
    f.type = f.type === 'password' ? 'text' : 'password';
}
</script>
</body>
</html>
