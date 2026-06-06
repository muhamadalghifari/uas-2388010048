<?php
require_once 'auth.php';
require_once 'config.php';

if (isLoggedIn()) { header('Location: index.php'); exit; }

$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';
    if ($username && $password) {
        $db = getDB();
        $stmt = $db->prepare("SELECT * FROM users WHERE username = ?");
        $stmt->execute([$username]);
        $user = $stmt->fetch();
        if ($user && password_verify($password, $user['password'])) {
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['username'] = $user['username'];
            header('Location: index.php');
            exit;
        } else {
            $error = 'Invalid username or password.';
        }
    } else {
        $error = 'Please fill in all fields.';
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8"/>
<meta name="viewport" content="width=device-width, initial-scale=1.0"/>
<title>CineList — Sign In</title>
<link rel="preconnect" href="https://fonts.googleapis.com"/>
<link href="https://fonts.googleapis.com/css2?family=DM+Serif+Display:ital@0;1&family=DM+Sans:wght@300;400;500&family=Space+Mono&display=swap" rel="stylesheet"/>
<style>
  *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }
  :root {
    --bg: #08080f;
    --surface: #0f0f1a;
    --border: rgba(255,255,255,0.07);
    --gold: #e8c97e;
    --gold-dim: rgba(232,201,126,0.15);
    --gold-border: rgba(232,201,126,0.25);
    --text: #e8e8f0;
    --muted: rgba(232,232,240,0.4);
    --rust: #c0623a;
    --error: #e05555;
  }
  body {
    font-family: 'DM Sans', sans-serif;
    background: var(--bg);
    color: var(--text);
    min-height: 100vh;
    display: flex; align-items: center; justify-content: center;
    position: relative; overflow: hidden;
  }
  body::before {
    content: '';
    position: fixed; inset: 0;
    background: radial-gradient(ellipse 60% 50% at 50% 0%, rgba(232,201,126,0.07) 0%, transparent 70%);
    pointer-events: none;
  }
  .film-strip {
    position: fixed; top: 0; left: 0; right: 0; height: 6px;
    background: repeating-linear-gradient(90deg, var(--gold) 0, var(--gold) 10px, transparent 10px, transparent 20px);
    opacity: 0.25;
  }
  .card {
    background: var(--surface);
    border: 1px solid var(--border);
    border-radius: 20px;
    padding: 48px 40px;
    width: 100%; max-width: 400px;
    position: relative;
  }
  .logo {
    font-family: 'DM Serif Display', serif;
    font-size: 28px; color: var(--gold);
    text-align: center; margin-bottom: 4px;
  }
  .logo-sub {
    text-align: center;
    font-size: 11px; letter-spacing: 0.18em;
    color: var(--muted); margin-bottom: 36px;
  }
  h2 {
    font-family: 'DM Serif Display', serif;
    font-size: 22px; font-weight: 400;
    margin-bottom: 6px;
  }
  .sub { font-size: 13px; color: var(--muted); margin-bottom: 28px; }
  .field { margin-bottom: 16px; }
  label {
    display: block;
    font-size: 11px; letter-spacing: 0.1em;
    color: var(--muted); margin-bottom: 8px; text-transform: uppercase;
  }
  input {
    width: 100%;
    background: rgba(255,255,255,0.04);
    border: 1px solid var(--border);
    border-radius: 8px;
    padding: 12px 16px;
    color: var(--text);
    font-family: 'DM Sans', sans-serif;
    font-size: 14px; outline: none;
    transition: border-color 0.2s;
  }
  input:focus { border-color: var(--gold-border); }
  .btn {
    width: 100%; margin-top: 8px;
    padding: 13px;
    background: var(--gold-dim);
    border: 1px solid var(--gold-border);
    border-radius: 8px;
    color: var(--gold);
    font-family: 'DM Sans', sans-serif;
    font-size: 13px; letter-spacing: 0.1em;
    cursor: pointer; transition: background 0.2s;
  }
  .btn:hover { background: rgba(232,201,126,0.22); }
  .error {
    background: rgba(224,85,85,0.1);
    border: 1px solid rgba(224,85,85,0.3);
    border-radius: 8px; padding: 10px 14px;
    font-size: 13px; color: var(--error);
    margin-bottom: 16px;
  }
  .register-link {
    text-align: center; margin-top: 20px;
    font-size: 13px; color: var(--muted);
  }
  .register-link a { color: var(--gold); text-decoration: none; }
</style>
</head>
<body>
<div class="film-strip"></div>
<div class="card">
  <div class="logo">CineList</div>
  <div class="logo-sub">YOUR PERSONAL FILM VAULT</div>
  <h2>Welcome back</h2>
  <p class="sub">Sign in to your watchlist</p>
  <?php if ($error): ?>
  <div class="error"><?= htmlspecialchars($error) ?></div>
  <?php endif; ?>
  <form method="POST">
    <div class="field">
      <label>Username</label>
      <input type="text" name="username" placeholder="Enter username" value="<?= htmlspecialchars($_POST['username'] ?? '') ?>" required/>
    </div>
    <div class="field">
      <label>Password</label>
      <input type="password" name="password" placeholder="Enter password" required/>
    </div>
    <button type="submit" class="btn">SIGN IN</button>
  </form>
  <div class="register-link">Don't have an account? <a href="register.php">Register</a></div>
</div>
</body>
</html>
