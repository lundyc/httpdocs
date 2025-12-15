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
if (!$fixtureId) die('Missing fixture ID.');
$returnTo = $_POST['return_to'] ?? '';

$db->beginTransaction();
try {
          $db->prepare("DELETE FROM match_lineups WHERE fixture_id = :fx")->execute([':fx' => $fixtureId]);

          $ins = $db->prepare("
    INSERT INTO match_lineups
      (fixture_id, player_id, shirt_number, position_id, is_substitute, minutes_played, player_replaced_id, captain)
    VALUES
      (:fx, :pid, :num, :pos, :sub, :mins, :rep, :cap)
  ");

          $playerIds   = $_POST['player_id'] ?? [];
          $numbers     = $_POST['shirt_number'] ?? [];
          $posIds      = $_POST['position_id'] ?? [];
          $startedSet  = $_POST['started'] ?? [];     // keyed by player_id
          $subsSet     = $_POST['is_sub'] ?? [];      // keyed by player_id
          $minutes     = $_POST['minute_on'] ?? [];
          $replacedSet = $_POST['player_replaced'] ?? [];
          $captainsSet = $_POST['captain'] ?? [];     // keyed by player_id

          $startedLookup = [];
          foreach ($startedSet as $pidKey => $value) {
                    $pidKey = (int)$pidKey;
                    if ($pidKey > 0) $startedLookup[$pidKey] = true;
          }

          $subsLookup = [];
          foreach ($subsSet as $pidKey => $value) {
                    $pidKey = (int)$pidKey;
                    if ($pidKey > 0) $subsLookup[$pidKey] = true;
          }

          $captainLookup = [];
          foreach ($captainsSet as $pidKey => $value) {
                    $pidKey = (int)$pidKey;
                    if ($pidKey > 0) $captainLookup[$pidKey] = true;
          }

          $minutesLookup = [];
          foreach ($minutes as $pidKey => $value) {
                    $pidKey = (int)$pidKey;
                    if ($pidKey > 0 && $value !== '' && $value !== null) {
                              $minutesLookup[$pidKey] = (int)$value;
                    }
          }

          $replacedLookup = [];
          foreach ($replacedSet as $pidKey => $value) {
                    $pidKey = (int)$pidKey;
                    if ($pidKey > 0 && $value !== '' && $value !== null) {
                              $replacedLookup[$pidKey] = (int)$value;
                    }
          }

          foreach ($playerIds as $i => $pid) {
                    $pid = (int)$pid;
                    if ($pid === 0) continue;

                    $num     = isset($numbers[$i]) && $numbers[$i] !== '' ? (int)$numbers[$i] : null;
                    $pos     = isset($posIds[$i]) && $posIds[$i] !== '' ? (int)$posIds[$i] : null;

                    // Enforce XOR: Sub OR Started (never both)
                    $started = !empty($startedLookup[$pid]) ? 1 : 0;
                    $isSub   = !empty($subsLookup[$pid]) ? 1 : 0;
                    if ($isSub)  $started = 0;
                    if ($started) $isSub  = 0;

                    // Minutes/replaced only valid for subs
                    $min  = ($isSub && array_key_exists($pid, $minutesLookup)) ? $minutesLookup[$pid] : null;
                    $rep  = ($isSub && array_key_exists($pid, $replacedLookup)) ? $replacedLookup[$pid] : null;
                    if ($rep === $pid) {
                              $rep = null;
                    }

                    $cap  = !empty($captainLookup[$pid]) ? 1 : 0;

                    $ins->execute([
                              ':fx'  => $fixtureId,
                              ':pid' => $pid,
                              ':num' => $num,
                              ':pos' => $pos,
                              ':sub' => $isSub,
                              ':mins' => $min,
                              ':rep' => $rep,
                              ':cap' => $cap
                    ]);
          }

          $db->commit();
          if ($returnTo === 'edit') {
                    header("Location: edit.php?id={$fixtureId}&tab=lineup&saved=1");
          } else {
                    header("Location: view.php?id={$fixtureId}#lineup");
          }
          exit;
} catch (Exception $e) {
          $db->rollBack();
          error_log('Lineup save failed: ' . $e->getMessage());
          die('Error saving lineup.');
}
