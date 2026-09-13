<?php
require_once 'config/db.php';
if (session_status() === PHP_SESSION_NONE) session_start();
if (isset($_SESSION['user_id'])) { header('Location: index.php'); exit; }

$error   = '';
$success = false;
$form    = ['full_name' => '', 'email' => ''];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $full_name = trim($_POST['full_name'] ?? '');
    $email     = trim($_POST['email'] ?? '');
    $password  = $_POST['password'] ?? '';
    $confirm   = $_POST['confirm_password'] ?? '';
    $form = ['full_name' => $full_name, 'email' => $email];

    if (!$full_name || !$email || !$password || !$confirm) {
        $error = 'Please fill in all fields.';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = 'Please enter a valid email.';
    } elseif (strlen($password) < 6) {
        $error = 'Password must be at least 6 characters.';
    } elseif ($password !== $confirm) {
        $error = 'Passwords do not match.';
    } else {
        try {
            $check = $pdo->prepare("SELECT user_id FROM users WHERE email = :e LIMIT 1");
            $check->execute([':e' => $email]);
            if ($check->fetch()) {
                $error = 'An account with this email already exists.';
            } else {
                $hash = password_hash($password, PASSWORD_BCRYPT);
                $ins  = $pdo->prepare("INSERT INTO users (full_name, email, password_hash) VALUES (:n,:e,:h)");
                $ins->execute([':n' => $full_name, ':e' => $email, ':h' => $hash]);
                $success = true;
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
    <title>Create Account — Travira</title>
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

        .auth-card {
            background: rgba(255, 255, 255, 0.72);
            backdrop-filter: blur(20px);
            -webkit-backdrop-filter: blur(20px);
            border: 1px solid rgba(255, 255, 255, 0.85);
            border-radius: 20px;
            padding: 32px 38px 28px;
            width: 100%;
            max-width: 420px;
            box-shadow: 0 8px 32px rgba(15, 23, 42, 0.14);
            text-align: center;
        }

        .auth-logo-wrap {
            width: 72px; height: 72px;
            background: #fff;
            border-radius: 18px;
            box-shadow: 0 4px 16px rgba(0,0,0,0.10);
            display: flex; align-items: center; justify-content: center;
            margin: 0 auto 18px;
            overflow: hidden;
        }
        .auth-logo-wrap img {
            width: 60px; height: 60px;
            object-fit: contain;
        }

        .auth-card h1 {
            font-size: 1.25rem;
            font-weight: 700;
            color: #0f172a;
            margin-bottom: 5px;
        }
        .auth-card p.sub {
            font-size: 0.84rem;
            color: #64748b;
            margin-bottom: 20px;
        }

        .auth-error {
            background: #fef2f2; border: 1px solid #fecaca;
            color: #b91c1c; border-radius: 10px;
            padding: 10px 14px; font-size: 0.84rem;
            text-align: left; margin-bottom: 14px;
        }
        .auth-success {
            background: #f0fdf4; border: 1px solid #bbf7d0;
            color: #166534; border-radius: 10px;
            padding: 14px; font-size: 0.88rem;
            margin-bottom: 14px;
        }
        .auth-success a { color: #2563eb; font-weight: 600; text-decoration: none; }

        .field { margin-bottom: 12px; text-align: left; }
        .field label {
            display: block;
            font-size: 0.78rem; font-weight: 600;
            color: #475569; margin-bottom: 5px;
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
            font-size: 0.9rem; font-family: inherit;
            background: rgba(255,255,255,0.65);
            color: #0f172a; outline: none;
            transition: border-color 0.2s, background 0.2s;
        }
        .field input:focus {
            border-color: #2563eb;
            background: rgba(255,255,255,0.95);
        }
        .field input::placeholder { color: #b0bec5; }

        .eye-btn {
            position: absolute; right: 11px; top: 50%;
            transform: translateY(-50%);
            background: none; border: none; cursor: pointer;
            color: #94a3b8; font-size: 0.82rem; padding: 2px;
        }

        .btn-submit {
            width: 100%;
            padding: 11px;
            background: #0f172a;
            color: #fff; border: none;
            border-radius: 10px;
            font-size: 0.95rem; font-weight: 600;
            font-family: inherit; cursor: pointer;
            margin-top: 4px;
            transition: background 0.2s, transform 0.15s;
        }
        .btn-submit:hover { background: #1e293b; transform: translateY(-1px); }

        .auth-divider {
            display: flex; align-items: center; gap: 10px;
            margin: 16px 0 0;
            font-size: 0.78rem; color: #94a3b8;
        }
        .auth-divider::before, .auth-divider::after {
            content: ''; flex: 1;
            height: 1px; background: rgba(148,163,184,0.4);
        }

        .auth-switch {
            margin-top: 12px;
            font-size: 0.84rem; color: #64748b;
        }
        .auth-switch a {
            color: #2563eb; font-weight: 600; text-decoration: none;
        }
        .auth-switch a:hover { text-decoration: underline; }

        @media (max-width: 440px) {
            .auth-card { padding: 24px 18px 20px; }
        }
    </style>
</head>
<body>

<a href="index.php" class="back-home">&#8592; Back to Travira</a>

<div class="auth-card">

    <div class="auth-logo-wrap">
        <img src="images/ui/travira_logo.png" alt="Travira"
             onerror="this.style.display='none';this.parentElement.innerHTML='<span style=\'font-size:2rem\'>✈️</span>'">
    </div>

    <h1>Create your account</h1>
    <p class="sub">Join Travira and start exploring the world.</p>

    <?php if ($error): ?>
        <div class="auth-error">⚠ <?php echo htmlspecialchars($error); ?></div>
    <?php endif; ?>

    <?php if ($success): ?>
        <div class="auth-success">
            ✅ Account created! <a href="login.php">Sign in now →</a>
        </div>
    <?php else: ?>

    <form method="POST" action="register.php">

        <div class="field">
            <label for="full_name">Full Name</label>
            <div class="field-wrap">
                <span class="ficon">👤</span>
                <input type="text" id="full_name" name="full_name"
                       placeholder="e.g. John Smith"
                       value="<?php echo htmlspecialchars($form['full_name']); ?>"
                       required autofocus>
            </div>
        </div>

        <div class="field">
            <label for="email">Email</label>
            <div class="field-wrap">
                <span class="ficon">✉</span>
                <input type="email" id="email" name="email"
                       placeholder="your@email.com"
                       value="<?php echo htmlspecialchars($form['email']); ?>"
                       required>
            </div>
        </div>

        <div class="field">
            <label for="password">Password</label>
            <div class="field-wrap">
                <span class="ficon">🔒</span>
                <input type="password" id="password" name="password"
                       placeholder="Min. 6 characters" required>
                <button type="button" class="eye-btn" onclick="togglePwd('password')">👁</button>
            </div>
        </div>

        <div class="field">
            <label for="confirm_password">Confirm Password</label>
            <div class="field-wrap">
                <span class="ficon">🔒</span>
                <input type="password" id="confirm_password" name="confirm_password"
                       placeholder="Repeat your password" required>
                <button type="button" class="eye-btn" onclick="togglePwd('confirm_password')">👁</button>
            </div>
        </div>

        <button type="submit" class="btn-submit">Create Account →</button>

    </form>

    <?php endif; ?>

    <div class="auth-divider">or</div>

    <p class="auth-switch">
        Already have an account? <a href="login.php">Sign in</a>
    </p>

</div>

<script>
function togglePwd(id) {
    const f = document.getElementById(id);
    f.type = f.type === 'password' ? 'text' : 'password';
}
</script>
</body>
</html>
