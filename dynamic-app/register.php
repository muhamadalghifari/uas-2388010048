<?php
require_once 'auth.php';
require_once 'config.php';

if (isLoggedIn()) { header('Location: index.php'); exit; }

$error = '';
$success = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';
    $confirm  = $_POST['confirm'] ?? '';
    if (!$username || !$password || !$confirm) {
        $error = 'Please fill in all fields.';
    } elseif (strlen($username) < 3) {
        $error = 'Username must be at least 3 characters.';
    } elseif ($password !== $confirm) {
        $error = 'Passwords do not match.';
    } elseif (strlen($password) < 6) {
        $error = 'Password must be at least 6 characters.';
    } else {
        $db = getDB();
        $check = $db->prepare("SELECT id FROM users WHERE username = ?");
        $check->execute([$username]);
        if ($check->fetch()) {
            $error = 'Username already taken.';
        } else {
            $hash = password_hash($password, PASSWORD_DEFAULT);
            $stmt = $db->prepare("INSERT INTO users (username, password) VALUES (?, ?)");
            $stmt->execute([$username, $hash]);
            $success = 'Account created! You can now sign in.';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8"/>
<meta name="viewport" content="width=device-width, initial-scale=1.0"/>
<title>CineList — Register</title>
<link rel="preconnect" href="https://fonts.googleapis.com"/>
<link href="https://fonts.googleapis.com/css2?family=DM+Serif+Display:ital@0;1&family=DM+Sans:wght@300;400;500&display=swap" rel="stylesheet"/>
<style>
  *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }
  :root {
    --bg: #08080f; --surface: #0f0f1a; --border: rgba(255,255,255,0.07);
    --gold: #e8c97e; --gold-dim: rgba(232,201,126,0.15); --gold-border: rgba(232,201,126,0.25);
    --text: #e8e8f0; --muted: rgba(232,232,240,0.4); --error: #e05555; --success: #5ecb8a;
  }
  body { font-family: 'DM Sans', sans-serif; background: var(--bg); color: var(--text); min-height: 100vh; display: flex; align-items: center; justify-content: center; }
  body::before { content: ''; position: fixed; inset: 0; background: radial-gradient(ellipse 60% 50% at 50% 0%, rgba(232,201,126,0.07) 0%, transparent 70%); pointer-events: none; }
  .film-strip { position: fixed; top: 0; left: 0; right: 0; height: 6px; background: repeating-linear-gradient(90deg, var(--gold) 0, var(--gold) 10px, transparent 10px, transparent 20px); opacity: 0.25; }
  .card { background: var(--surface); border: 1px solid var(--border); border-radius: 20px; padding: 48px 40px; width: 100%; max-width: 400px; }
  .logo { font-family: 'DM Serif Display', serif; font-size: 28px; color: var(--gold); text-align: center; margin-bottom: 4px; }
  .logo-sub { text-align: center; font-size: 11px; letter-spacing: 0.18em; color: var(--muted); margin-bottom: 36px; }
  h2 { font-family: 'DM Serif Display', serif; font-size: 22px; font-weight: 400; margin-bottom: 6px; }
  .sub { font-size: 13px; color: var(--muted); margin-bottom: 28px; }
  .field { margin-bottom: 16px; }
  label { display: block; font-size: 11px; letter-spacing: 0.1em; color: var(--muted); margin-bottom: 8px; text-transform: uppercase; }
  input { width: 100%; background: rgba(255,255,255,0.04); border: 1px solid var(--border); border-radius: 8px; padding: 12px 16px; color: var(--text); font-family: 'DM Sans', sans-serif; font-size: 14px; outline: none; transition: border-color 0.2s; }
  input:focus { border-color: var(--gold-border); }
  .btn { width: 100%; margin-top: 8px; padding: 13px; background: var(--gold-dim); border: 1px solid var(--gold-border); border-radius: 8px; color: var(--gold); font-family: 'DM Sans', sans-serif; font-size: 13px; letter-spacing: 0.1em; cursor: pointer; transition: background 0.2s; }
  .btn:hover { background: rgba(232,201,126,0.22); }
  .error { background: rgba(224,85,85,0.1); border: 1px solid rgba(224,85,85,0.3); border-radius: 8px; padding: 10px 14px; font-size: 13px; color: var(--error); margin-bottom: 16px; }
  .success { background: rgba(94,203,138,0.1); border: 1px solid rgba(94,203,138,0.3); border-radius: 8px; padding: 10px 14px; font-size: 13px; color: var(--success); margin-bottom: 16px; }
  .login-link { text-align: center; margin-top: 20px; font-size: 13px; color: var(--muted); }
  .login-link a { color: var(--gold); text-decoration: none; }
</style>
</head>
<body>
<div class="film-strip"></div>
<div class="card">
  <div class="logo">CineList</div>
  <div class="logo-sub">YOUR PERSONAL FILM VAULT</div>
  <h2>Create account</h2>
  <p class="sub">Start your personal film vault</p>
  <?php if ($error): ?><div class="error"><?= htmlspecialchars($error) ?></div><?php endif; ?>
  <?php if ($success): ?><div class="success"><?= htmlspecialchars($success) ?></div><?php endif; ?>
  <form method="POST">
    <div class="field"><label>Username</label><input type="text" name="username" placeholder="Choose a username" value="<?= htmlspecialchars($_POST['username'] ?? '') ?>" required/></div>
    <div class="field"><label>Password</label><input type="password" name="password" placeholder="Min. 6 characters" required/></div>
    <div class="field"><label>Confirm Password</label><input type="password" name="confirm" placeholder="Repeat password" required/></div>
    <button type="submit" class="btn">CREATE ACCOUNT</button>
  </form>
  <div class="login-link">Already have an account? <a href="login.php">Sign in</a></div>
</div>
</body>
</html>
