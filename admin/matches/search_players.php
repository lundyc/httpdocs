<?php
require_once __DIR__ . '/../../shared/includes/session_init.php';
require_once __DIR__ . '/../../portal/auth/middleware.php';
checkRole([1, 2, 3]);

$db = getDB();
$teamId = CLUB_TEAM_ID ?? 1;

$stmt = $db->prepare("
  SELECT p.id, p.name, p.position_id, pos.short_label AS position
  FROM players p
  LEFT JOIN positions pos ON p.position_id = pos.id
  WHERE p.team_id = :tid AND p.status = 'active'
  ORDER BY p.name
");
$stmt->execute([':tid' => $teamId]);

header('Content-Type: application/json');
echo json_encode($stmt->fetchAll(PDO::FETCH_ASSOC));
