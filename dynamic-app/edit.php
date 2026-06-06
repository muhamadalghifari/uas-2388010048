<?php
require_once 'auth.php';
require_once 'config.php';
requireLogin();

$db  = getDB();
$uid = currentUserId();
$id  = intval($_GET['id'] ?? 0);

$stmt = $db->prepare("SELECT * FROM movies WHERE id = ? AND user_id = ?");
$stmt->execute([$id, $uid]);
$movie = $stmt->fetch();

if (!$movie) { header('Location: index.php'); exit; }

$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title  = trim($_POST['title'] ?? '');
    $genre  = trim($_POST['genre'] ?? '');
    $year   = intval($_POST['year'] ?? 0);
    $desc   = trim($_POST['description'] ?? '');
    $status = $_POST['status'] ?? 'want_to_watch';
    $rating = ($status === 'watched' && !empty($_POST['rating'])) ? intval($_POST['rating']) : null;

    if (!$title) {
        $error = 'Title is required.';
    } else {
        $stmt = $db->prepare("UPDATE movies SET title=?,genre=?,year=?,description=?,status=?,rating=? WHERE id=? AND user_id=?");
        $stmt->execute([$title, $genre, $year ?: null, $desc, $status, $rating, $id, $uid]);
        header('Location: index.php');
        exit;
    }
}

$v = $_SERVER['REQUEST_METHOD'] === 'POST' ? $_POST : $movie;
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8"/>
<meta name="viewport" content="width=device-width, initial-scale=1.0"/>
<title>CineList — Edit Film</title>
<link rel="preconnect" href="https://fonts.googleapis.com"/>
<link href="https://fonts.googleapis.com/css2?family=DM+Serif+Display:ital@0;1&family=DM+Sans:wght@300;400;500&display=swap" rel="stylesheet"/>
<style>
  *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }
  :root { --bg:#08080f; --surface:#0f0f1a; --border:rgba(255,255,255,0.07); --gold:#e8c97e; --gold-dim:rgba(232,201,126,0.12); --gold-border:rgba(232,201,126,0.25); --text:#e8e8f0; --muted:rgba(232,232,240,0.4); --error:#e05555; }
  body { font-family:'DM Sans',sans-serif; background:var(--bg); color:var(--text); min-height:100vh; }
  body::before { content:''; position:fixed; inset:0; background:radial-gradient(ellipse 80% 40% at 50% -10%,rgba(232,201,126,0.05) 0%,transparent 60%); pointer-events:none; }
  nav { position:sticky; top:0; z-index:100; background:rgba(8,8,15,0.9); backdrop-filter:blur(12px); border-bottom:1px solid var(--border); display:flex; justify-content:space-between; align-items:center; padding:0 40px; height:60px; }
  .nav-logo { font-family:'DM Serif Display',serif; font-size:20px; color:var(--gold); }
  .nav-back { font-size:12px; letter-spacing:0.08em; color:var(--muted); text-decoration:none; padding:6px 14px; border:1px solid var(--border); border-radius:6px; transition:all 0.2s; }
  .nav-back:hover { border-color:var(--gold-border); color:var(--gold); }
  main { max-width:600px; margin:0 auto; padding:48px 24px; position:relative; z-index:1; }
  h1 { font-family:'DM Serif Display',serif; font-size:30px; font-weight:400; margin-bottom:8px; }
  h1 em { color:var(--gold); font-style:italic; }
  .sub { font-size:14px; color:var(--muted); margin-bottom:36px; }
  .form-card { background:var(--surface); border:1px solid var(--border); border-radius:16px; padding:36px; }
  .field { margin-bottom:20px; }
  label { display:block; font-size:11px; letter-spacing:0.1em; color:var(--muted); margin-bottom:8px; text-transform:uppercase; }
  input, select, textarea { width:100%; background:rgba(255,255,255,0.04); border:1px solid var(--border); border-radius:8px; padding:11px 14px; color:var(--text); font-family:'DM Sans',sans-serif; font-size:14px; outline:none; transition:border-color 0.2s; }
  input:focus, select:focus, textarea:focus { border-color:var(--gold-border); }
  select option { background:#0f0f1a; }
  textarea { resize:vertical; min-height:90px; }
  .row { display:grid; grid-template-columns:1fr 1fr; gap:16px; }
  .rating-group { display:flex; flex-direction:row-reverse; gap:4px; }
  .rating-opt { display:none; }
  .rating-opt + label { cursor:pointer; font-size:22px; color:var(--muted); transition:color 0.15s; letter-spacing:0; text-transform:none; }
  .rating-group input:checked ~ label, .rating-group label:hover, .rating-group label:hover ~ label { color:var(--gold); }
  #rating-section { display:none; }
  .btn-row { display:flex; gap:12px; margin-top:8px; }
  .btn-submit { flex:1; padding:13px; background:var(--gold-dim); border:1px solid var(--gold-border); border-radius:8px; color:var(--gold); font-family:'DM Sans',sans-serif; font-size:13px; letter-spacing:0.1em; cursor:pointer; transition:background 0.2s; }
  .btn-submit:hover { background:rgba(232,201,126,0.22); }
  .btn-cancel { padding:13px 20px; background:transparent; border:1px solid var(--border); border-radius:8px; color:var(--muted); font-family:'DM Sans',sans-serif; font-size:13px; text-decoration:none; display:flex; align-items:center; transition:all 0.2s; }
  .btn-cancel:hover { border-color:var(--gold-border); color:var(--gold); }
  .error { background:rgba(224,85,85,0.1); border:1px solid rgba(224,85,85,0.3); border-radius:8px; padding:10px 14px; font-size:13px; color:var(--error); margin-bottom:20px; }
</style>
</head>
<body>
<nav>
  <div class="nav-logo">CineList</div>
  <a href="index.php" class="nav-back">← BACK</a>
</nav>
<main>
  <h1>Edit <em>Film</em></h1>
  <p class="sub">Update details for "<?= htmlspecialchars($movie['title']) ?>"</p>
  <div class="form-card">
    <?php if ($error): ?><div class="error"><?= htmlspecialchars($error) ?></div><?php endif; ?>
    <form method="POST">
      <div class="field"><label>Film Title *</label><input type="text" name="title" value="<?= htmlspecialchars($v['title']) ?>" required/></div>
      <div class="row">
        <div class="field"><label>Genre</label><input type="text" name="genre" value="<?= htmlspecialchars($v['genre'] ?? '') ?>"/></div>
        <div class="field"><label>Year</label><input type="number" name="year" min="1900" max="2030" value="<?= htmlspecialchars($v['year'] ?? '') ?>"/></div>
      </div>
      <div class="field"><label>Description</label><textarea name="description"><?= htmlspecialchars($v['description'] ?? '') ?></textarea></div>
      <div class="field">
        <label>Status</label>
        <select name="status" id="status-select" onchange="document.getElementById('rating-section').style.display=this.value==='watched'?'block':'none'">
          <option value="want_to_watch" <?= ($v['status']==='want_to_watch')?'selected':'' ?>>Want to Watch</option>
          <option value="watched" <?= ($v['status']==='watched')?'selected':'' ?>>Watched</option>
        </select>
      </div>
      <div class="field" id="rating-section" style="<?= ($v['status']==='watched')?'display:block':'display:none' ?>">
        <label>Your Rating</label>
        <div class="rating-group">
          <?php for ($i=5;$i>=1;$i--): ?>
          <input type="radio" name="rating" id="r<?=$i?>" class="rating-opt" value="<?=$i?>" <?= (($v['rating']??0)==$i)?'checked':'' ?>/>
          <label for="r<?=$i?>">★</label>
          <?php endfor; ?>
        </div>
      </div>
      <div class="btn-row">
        <button type="submit" class="btn-submit">SAVE CHANGES</button>
        <a href="index.php" class="btn-cancel">Cancel</a>
      </div>
    </form>
  </div>
</main>
</body>
</html>
