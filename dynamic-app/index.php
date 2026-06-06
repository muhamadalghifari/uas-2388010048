<?php
require_once 'auth.php';
require_once 'config.php';
requireLogin();

$db = getDB();
$uid = currentUserId();

$filter = $_GET['filter'] ?? 'all';
$search = trim($_GET['search'] ?? '');

$where = "WHERE user_id = ?";
$params = [$uid];

if ($filter === 'watched') { $where .= " AND status = 'watched'"; }
elseif ($filter === 'want') { $where .= " AND status = 'want_to_watch'"; }

if ($search) {
    $where .= " AND (title LIKE ? OR genre LIKE ?)";
    $params[] = "%$search%";
    $params[] = "%$search%";
}

$stmt = $db->prepare("SELECT * FROM movies $where ORDER BY created_at DESC");
$stmt->execute($params);
$movies = $stmt->fetchAll();

$counts = $db->prepare("SELECT
    COUNT(*) as total,
    SUM(status='watched') as watched,
    SUM(status='want_to_watch') as want
    FROM movies WHERE user_id = ?");
$counts->execute([$uid]);
$stats = $counts->fetch();
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8"/>
<meta name="viewport" content="width=device-width, initial-scale=1.0"/>
<title>CineList — My Watchlist</title>
<link rel="preconnect" href="https://fonts.googleapis.com"/>
<link href="https://fonts.googleapis.com/css2?family=DM+Serif+Display:ital@0;1&family=DM+Sans:wght@300;400;500&family=Space+Mono&display=swap" rel="stylesheet"/>
<style>
  *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }
  :root {
    --bg: #08080f; --surface: #0f0f1a; --surface2: #13131f;
    --border: rgba(255,255,255,0.07); --border2: rgba(255,255,255,0.04);
    --gold: #e8c97e; --gold-dim: rgba(232,201,126,0.12); --gold-border: rgba(232,201,126,0.25);
    --text: #e8e8f0; --muted: rgba(232,232,240,0.4); --muted2: rgba(232,232,240,0.25);
    --green: #5ecb8a; --green-dim: rgba(94,203,138,0.12); --green-border: rgba(94,203,138,0.25);
    --rust: #c0623a;
  }
  body { font-family: 'DM Sans', sans-serif; background: var(--bg); color: var(--text); min-height: 100vh; }
  body::before { content: ''; position: fixed; inset: 0; background: radial-gradient(ellipse 80% 40% at 50% -10%, rgba(232,201,126,0.05) 0%, transparent 60%); pointer-events: none; z-index: 0; }

  /* NAV */
  nav { position: sticky; top: 0; z-index: 100; background: rgba(8,8,15,0.9); backdrop-filter: blur(12px); border-bottom: 1px solid var(--border); display: flex; justify-content: space-between; align-items: center; padding: 0 40px; height: 60px; }
  .nav-logo { font-family: 'DM Serif Display', serif; font-size: 20px; color: var(--gold); }
  .nav-right { display: flex; align-items: center; gap: 20px; }
  .nav-user { font-size: 13px; color: var(--muted); }
  .nav-user span { color: var(--gold); font-weight: 500; }
  .nav-logout { font-size: 12px; letter-spacing: 0.08em; color: var(--muted); text-decoration: none; padding: 6px 14px; border: 1px solid var(--border); border-radius: 6px; transition: border-color 0.2s, color 0.2s; }
  .nav-logout:hover { border-color: var(--gold-border); color: var(--gold); }

  /* MAIN */
  main { max-width: 1100px; margin: 0 auto; padding: 40px 24px; position: relative; z-index: 1; }

  /* HEADER */
  .page-header { margin-bottom: 40px; }
  .page-tag { font-family: 'Space Mono', monospace; font-size: 10px; letter-spacing: 0.2em; color: var(--gold); margin-bottom: 8px; }
  .page-title { font-family: 'DM Serif Display', serif; font-size: 36px; font-weight: 400; margin-bottom: 24px; }
  .page-title em { color: var(--gold); font-style: italic; }

  /* STATS */
  .stats { display: flex; gap: 16px; margin-bottom: 32px; flex-wrap: wrap; }
  .stat { background: var(--surface); border: 1px solid var(--border); border-radius: 12px; padding: 18px 24px; flex: 1; min-width: 120px; }
  .stat-n { font-family: 'DM Serif Display', serif; font-size: 32px; color: var(--gold); line-height: 1; margin-bottom: 4px; }
  .stat-l { font-size: 11px; letter-spacing: 0.1em; color: var(--muted); text-transform: uppercase; }

  /* CONTROLS */
  .controls { display: flex; gap: 12px; margin-bottom: 28px; flex-wrap: wrap; align-items: center; }
  .filters { display: flex; gap: 6px; }
  .filter-btn { padding: 8px 18px; border-radius: 100px; font-size: 12px; letter-spacing: 0.06em; text-decoration: none; border: 1px solid var(--border); color: var(--muted); transition: all 0.2s; }
  .filter-btn:hover, .filter-btn.active { border-color: var(--gold-border); color: var(--gold); background: var(--gold-dim); }
  .search-form { display: flex; gap: 8px; flex: 1; max-width: 340px; margin-left: auto; }
  .search-form input { flex: 1; background: var(--surface); border: 1px solid var(--border); border-radius: 8px; padding: 8px 14px; color: var(--text); font-family: 'DM Sans', sans-serif; font-size: 13px; outline: none; }
  .search-form input:focus { border-color: var(--gold-border); }
  .search-form button { padding: 8px 16px; background: var(--gold-dim); border: 1px solid var(--gold-border); border-radius: 8px; color: var(--gold); font-size: 12px; cursor: pointer; }
  .add-btn { display: inline-flex; align-items: center; gap: 8px; padding: 9px 20px; background: var(--gold-dim); border: 1px solid var(--gold-border); border-radius: 8px; color: var(--gold); text-decoration: none; font-size: 13px; letter-spacing: 0.06em; transition: background 0.2s; }
  .add-btn:hover { background: rgba(232,201,126,0.2); }

  /* MOVIES GRID */
  .movies-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(280px, 1fr)); gap: 16px; }
  .movie-card { background: var(--surface); border: 1px solid var(--border); border-radius: 14px; overflow: hidden; transition: transform 0.2s, border-color 0.2s; }
  .movie-card:hover { transform: translateY(-3px); border-color: rgba(232,201,126,0.2); }
  .movie-poster { height: 120px; display: flex; align-items: center; justify-content: center; font-size: 40px; position: relative; overflow: hidden; }
  .poster-drama { background: linear-gradient(135deg, #1a1020 0%, #2d1a35 100%); }
  .poster-scifi { background: linear-gradient(135deg, #0a1525 0%, #0d2540 100%); }
  .poster-comedy { background: linear-gradient(135deg, #1a1500 0%, #2d2500 100%); }
  .poster-crime { background: linear-gradient(135deg, #150a0a 0%, #2d1010 100%); }
  .poster-romance { background: linear-gradient(135deg, #1a0a15 0%, #2d1525 100%); }
  .poster-default { background: linear-gradient(135deg, #0f0f1a 0%, #1a1a2d 100%); }
  .movie-year-badge { position: absolute; top: 10px; right: 10px; font-family: 'Space Mono', monospace; font-size: 10px; color: var(--muted2); letter-spacing: 0.08em; }
  .movie-body { padding: 18px; }
  .movie-title { font-family: 'DM Serif Display', serif; font-size: 17px; color: var(--text); margin-bottom: 4px; line-height: 1.2; }
  .movie-genre { font-size: 11px; color: var(--muted); letter-spacing: 0.06em; margin-bottom: 10px; }
  .movie-desc { font-size: 12px; color: var(--muted); line-height: 1.6; margin-bottom: 14px; display: -webkit-box; -webkit-line-clamp: 2; -webkit-box-orient: vertical; overflow: hidden; }
  .movie-footer { display: flex; justify-content: space-between; align-items: center; }
  .status-badge { font-size: 10px; padding: 4px 10px; border-radius: 100px; letter-spacing: 0.08em; font-weight: 500; }
  .status-want { background: var(--gold-dim); color: var(--gold); border: 1px solid var(--gold-border); }
  .status-watched { background: var(--green-dim); color: var(--green); border: 1px solid var(--green-border); }
  .movie-rating { font-size: 12px; color: var(--gold); }
  .movie-actions { display: flex; gap: 8px; }
  .movie-actions a { font-size: 11px; letter-spacing: 0.06em; color: var(--muted); text-decoration: none; padding: 4px 10px; border: 1px solid var(--border); border-radius: 6px; transition: all 0.2s; }
  .movie-actions a:hover { color: var(--gold); border-color: var(--gold-border); }
  .movie-actions a.del:hover { color: #e05555; border-color: rgba(224,85,85,0.3); }

  /* EMPTY */
  .empty { text-align: center; padding: 80px 20px; }
  .empty-icon { font-size: 48px; margin-bottom: 16px; opacity: 0.4; }
  .empty h3 { font-family: 'DM Serif Display', serif; font-size: 22px; color: var(--text); margin-bottom: 8px; }
  .empty p { font-size: 14px; color: var(--muted); }

  @media(max-width:640px) { nav { padding: 0 16px; } main { padding: 24px 16px; } .controls { flex-direction: column; } .search-form { margin-left: 0; max-width: 100%; } }
</style>
</head>
<body>
<nav>
  <div class="nav-logo">CineList</div>
  <div class="nav-right">
    <div class="nav-user">Hello, <span><?= htmlspecialchars(currentUsername()) ?></span></div>
    <a href="logout.php" class="nav-logout">SIGN OUT</a>
  </div>
</nav>
<main>
  <div class="page-header">
    <div class="page-tag">YOUR FILM VAULT</div>
    <h1 class="page-title">My <em>Watchlist</em></h1>
  </div>

  <div class="stats">
    <div class="stat"><div class="stat-n"><?= $stats['total'] ?></div><div class="stat-l">Total Films</div></div>
    <div class="stat"><div class="stat-n"><?= $stats['watched'] ?></div><div class="stat-l">Watched</div></div>
    <div class="stat"><div class="stat-n"><?= $stats['want'] ?></div><div class="stat-l">Want to Watch</div></div>
  </div>

  <div class="controls">
    <div class="filters">
      <a href="?filter=all" class="filter-btn <?= $filter==='all'?'active':'' ?>">All</a>
      <a href="?filter=want" class="filter-btn <?= $filter==='want'?'active':'' ?>">Watchlist</a>
      <a href="?filter=watched" class="filter-btn <?= $filter==='watched'?'active':'' ?>">Watched</a>
    </div>
    <form class="search-form" method="GET">
      <input type="hidden" name="filter" value="<?= htmlspecialchars($filter) ?>"/>
      <input type="text" name="search" placeholder="Search films..." value="<?= htmlspecialchars($search) ?>"/>
      <button type="submit">GO</button>
    </form>
    <a href="add.php" class="add-btn">+ ADD FILM</a>
  </div>

  <?php if (empty($movies)): ?>
  <div class="empty">
    <div class="empty-icon">🎬</div>
    <h3>No films found</h3>
    <p>Add your first film to get started.</p>
  </div>
  <?php else: ?>
  <div class="movies-grid">
    <?php foreach ($movies as $m):
      $genre_lc = strtolower($m['genre'] ?? '');
      if (str_contains($genre_lc, 'drama') || str_contains($genre_lc, 'history')) $pc = 'poster-drama';
      elseif (str_contains($genre_lc, 'sci')) $pc = 'poster-scifi';
      elseif (str_contains($genre_lc, 'comedy') || str_contains($genre_lc, 'fantasy')) $pc = 'poster-comedy';
      elseif (str_contains($genre_lc, 'crime') || str_contains($genre_lc, 'thriller')) $pc = 'poster-crime';
      elseif (str_contains($genre_lc, 'romance') || str_contains($genre_lc, 'romantic')) $pc = 'poster-romance';
      else $pc = 'poster-default';
      $emojis = ['🎬','🎥','🎞️','📽️','🎭','🌟','🔭','🗡️','🎪','🌙'];
      $emoji = $emojis[crc32($m['title']) % count($emojis)];
    ?>
    <div class="movie-card">
      <div class="movie-poster <?= $pc ?>">
        <?= $emoji ?>
        <?php if ($m['year']): ?><div class="movie-year-badge"><?= $m['year'] ?></div><?php endif; ?>
      </div>
      <div class="movie-body">
        <div class="movie-title"><?= htmlspecialchars($m['title']) ?></div>
        <div class="movie-genre"><?= htmlspecialchars($m['genre'] ?? '—') ?></div>
        <?php if ($m['description']): ?>
        <div class="movie-desc"><?= htmlspecialchars($m['description']) ?></div>
        <?php endif; ?>
        <div class="movie-footer">
          <div>
            <?php if ($m['status'] === 'watched'): ?>
              <span class="status-badge status-watched">✓ WATCHED</span>
              <?php if ($m['rating']): ?>
              <span class="movie-rating" style="margin-left:8px"><?= str_repeat('★', $m['rating']) ?></span>
              <?php endif; ?>
            <?php else: ?>
              <span class="status-badge status-want">WATCHLIST</span>
            <?php endif; ?>
          </div>
          <div class="movie-actions">
            <a href="edit.php?id=<?= $m['id'] ?>">EDIT</a>
            <a href="delete.php?id=<?= $m['id'] ?>" class="del" onclick="return confirm('Remove this film?')">DEL</a>
          </div>
        </div>
      </div>
    </div>
    <?php endforeach; ?>
  </div>
  <?php endif; ?>
</main>
</body>
</html>
