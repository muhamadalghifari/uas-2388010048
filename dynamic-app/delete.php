<?php
require_once 'auth.php';
require_once 'config.php';
requireLogin();

$db  = getDB();
$id  = intval($_GET['id'] ?? 0);
$uid = currentUserId();

$stmt = $db->prepare("DELETE FROM movies WHERE id = ? AND user_id = ?");
$stmt->execute([$id, $uid]);

header('Location: index.php');
exit;
