<?php
require_once 'config/db.php';
if (session_status() === PHP_SESSION_NONE) session_start();
if (isset($_SESSION['user_id'])) { header('Location: index.php'); exit; }

$error = '';
$typed_email = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email    = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $typed_email = $email;

    if (!$email || !$password) {
        $error = 'Please enter both email and password.';
    } else {
        try {
            $stmt = $pdo->prepare("SELECT * FROM users WHERE email = :email LIMIT 1");
            $stmt->execute([':email' => $email]);
            $user = $stmt->fetch(PDO::FETCH_ASSOC);
            if ($user && password_verify($password, $user['password_hash'])) {
                $_SESSION['user_id']    = $user['user_id'];
                $_SESSION['user_name']  = $user['full_name'];
                $_SESSION['user_email'] = $user['email'];
                header('Location: index.php'); exit;
            } else {
                $error = 'Incorrect email or password.';
            }
        } catch (PDOException $e) {
            $error = 'Error: ' . $e->getMessage();
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sign In — Travira</title>
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap">
    <style>
        *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }

        body {
            font-family: 'Inter', sans-serif;
            min-height: 100vh;
            background: url('images/ui/new_sky_bg.jpg') center/cover no-repeat fixed;
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

        /* divider */
        .auth-divider {
            display: flex; align-items: center; gap: 10px;
            margin: 18px 0 0;
            font-size: 0.78rem; color: #94a3b8;
        }
        .auth-divider::before, .auth-divider::after {
            content: ''; flex: 1;
            height: 1px; background: rgba(148,163,184,0.4);
        }

        /* switch */
        .auth-switch {
            margin-top: 14px;
            font-size: 0.84rem;
            color: #64748b;
        }
        .auth-switch a {
            color: #2563eb;
            font-weight: 600;
            text-decoration: none;
        }
        .auth-switch a:hover { text-decoration: underline; }

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

<a href="index.php" class="back-home">&#8592; Back to Travira</a>

<div class="auth-card">

    <!-- Logo -->
    <div class="auth-logo-wrap">
        <img src="images/ui/travira_logo.png" alt="Travira"
             onerror="this.style.display='none';this.parentElement.innerHTML='<span style=\'font-size:2rem\'>✈️</span>'">
    </div>

    <h1>Sign in to Travira</h1>
    <p class="sub">Welcome back! Enter your details to continue.</p>

    <?php if ($error): ?>
        <div class="auth-error">⚠ <?php echo htmlspecialchars($error); ?></div>
    <?php endif; ?>

    <form method="POST" action="login.php">

        <div class="field">
            <label for="email">Email</label>
            <div class="field-wrap">
                <span class="ficon">✉</span>
                <input type="email" id="email" name="email"
                       placeholder="your@email.com"
                       value="<?php echo htmlspecialchars($typed_email); ?>"
                       required autofocus>
            </div>
        </div>

        <div class="field">
            <label for="password">Password</label>
            <div class="field-wrap">
                <span class="ficon">🔒</span>
                <input type="password" id="password" name="password"
                       placeholder="Enter your password" required>
                <button type="button" class="eye-btn" onclick="togglePwd()"
                        title="Show/hide password">👁</button>
            </div>
        </div>

        <button type="submit" class="btn-submit">Get Started →</button>

    </form>

    <div class="auth-divider">or</div>

    <p class="auth-switch">
        Don't have an account? <a href="register.php">Register free</a>
    </p>

</div>

<script>
function togglePwd() {
    const f = document.getElementById('password');
    f.type = f.type === 'password' ? 'text' : 'password';
}
</script>
</body>
</html>
