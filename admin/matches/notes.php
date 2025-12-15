<?php
require_once __DIR__ . '/../../shared/includes/session_init.php';
require_once __DIR__ . '/../../shared/includes/simple_session.php';
require_once __DIR__ . '/../../portal/auth/auth_functions.php';
require_once __DIR__ . '/../../portal/auth/middleware.php';

checkRole([1, 2, 3]);
$db = getDB();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
          header('Location: index.php');
          exit;
}

$fixtureId = (int)($_POST['fixture_id'] ?? 0);
$noteType  = $_POST['note_type'] ?? 'general';
$content   = trim($_POST['content'] ?? '');
$uid       = $_SESSION['user_id'] ?? null;
$returnTo  = $_POST['return_to'] ?? '';

if (!$fixtureId || $content === '') {
          if ($returnTo === 'edit') {
                    header("Location: edit.php?id={$fixtureId}&tab=coach-notes");
          } else {
                    header("Location: view.php?id=" . $fixtureId . "#notes");
          }
          exit;
}

$stmt = $db->prepare("INSERT INTO match_notes (fixture_id, note_type, content, created_by)
                      VALUES (:fx, :t, :c, :uid)");
$stmt->execute([':fx' => $fixtureId, ':t' => $noteType, ':c' => $content, ':uid' => $uid]);

if ($returnTo === 'edit') {
          header("Location: edit.php?id={$fixtureId}&tab=coach-notes&saved=1");
} else {
          header("Location: view.php?id=" . $fixtureId . "#notes");
}
exit;
