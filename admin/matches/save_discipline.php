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

$playerIds = $_POST['player_id'] ?? [];
$cardTypes = $_POST['card_type'] ?? [];
$minutes   = $_POST['minute'] ?? [];

$db->beginTransaction();
try {
        $db->prepare("DELETE FROM match_discipline WHERE fixture_id = :fx")->execute([':fx' => $fixtureId]);

        $ins = $db->prepare("
                INSERT INTO match_discipline (fixture_id, player_id, card_type, minute, created_at)
                VALUES (:fx, :pid, :type, :minute, NOW())
        ");

        foreach ($playerIds as $idx => $pid) {
                $pid = (int)$pid;
                if ($pid === 0) continue;

                $typeRaw = $cardTypes[$idx] ?? 'yellow';
                $type = ($typeRaw === 'red') ? 'red' : 'yellow';

                $minute = null;
                if (isset($minutes[$idx]) && $minutes[$idx] !== '') {
                        $minuteVal = (int)$minutes[$idx];
                        if ($minuteVal >= 0) $minute = $minuteVal;
                }

                $ins->execute([
                        ':fx'    => $fixtureId,
                        ':pid'   => $pid,
                        ':type'  => $type,
                        ':minute'=> $minute
                ]);
        }

        $db->commit();
        if ($returnTo === 'edit') {
                header("Location: edit.php?id={$fixtureId}&tab=discipline&saved=1");
        } else {
                header("Location: view.php?id={$fixtureId}#discipline");
        }
        exit;
} catch (Exception $e) {
        $db->rollBack();
        error_log('Discipline save failed: ' . $e->getMessage());
        die('Error saving discipline.');
}
