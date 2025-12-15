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
$homeId    = (int)($_POST['home_team_id'] ?? 0);
$awayId    = (int)($_POST['away_team_id'] ?? 0);
$homeStats = $_POST['home'] ?? [];
$awayStats = $_POST['away'] ?? [];

if (!$fixtureId || !$homeId || !$awayId) {
        die('Missing fixture or team data.');
}

/* -------------------------------------------------------------
   VALIDATE POSSESSION TOTAL
------------------------------------------------------------- */
$homePoss = isset($homeStats['possession']) ? (int)$homeStats['possession'] : 0;
$awayPoss = isset($awayStats['possession']) ? (int)$awayStats['possession'] : 0;
$totalPoss = $homePoss + $awayPoss;

if ($totalPoss !== 100) {
        // Redirect back with error message
        header("Location: stats.php?fixture_id={$fixtureId}&error=possession");
        exit;
}

/* -------------------------------------------------------------
   SAVE FUNCTION
------------------------------------------------------------- */
function saveStats(PDO $db, int $fixtureId, int $teamId, array $stats): void
{
$fields = ['possession', 'shots_total', 'shots_on_target', 'corners', 'freekicks', 'fouls', 'offsides', 'yellow_cards', 'red_cards'];
        $clean = [];
        foreach ($fields as $f) $clean[$f] = isset($stats[$f]) ? (int)$stats[$f] : 0;

        $check = $db->prepare("SELECT id FROM match_stats WHERE fixture_id = :fx AND team_id = :tid LIMIT 1");
        $check->execute([':fx' => $fixtureId, ':tid' => $teamId]);
        $existing = $check->fetchColumn();

        if ($existing) {
                $sql = "UPDATE match_stats
                SET possession = :pos,
                    shots_total = :st,
                    shots_on_target = :sot,
                    corners = :cor,
                    freekicks = :fk,
                    fouls = :foul,
                    offsides = :off,
                    yellow_cards = :yc,
                    red_cards = :rc,
                    updated_at = NOW()
                WHERE id = :id";
                $stmt = $db->prepare($sql);
                $stmt->execute([
                        ':pos' => $clean['possession'],
                        ':st' => $clean['shots_total'],
                        ':sot' => $clean['shots_on_target'],
                        ':cor' => $clean['corners'],
                        ':fk' => $clean['freekicks'],
                        ':foul' => $clean['fouls'],
                        ':off' => $clean['offsides'],
                        ':yc' => $clean['yellow_cards'],
                        ':rc' => $clean['red_cards'],
                        ':id' => $existing
                ]);
        } else {
                $sql = "INSERT INTO match_stats 
                (fixture_id, team_id, possession, shots_total, shots_on_target, corners, freekicks, fouls, offsides, yellow_cards, red_cards)
                VALUES (:fx, :tid, :pos, :st, :sot, :cor, :fk, :foul, :off, :yc, :rc)";
                $stmt = $db->prepare($sql);
                $stmt->execute([
                        ':fx' => $fixtureId,
                        ':tid' => $teamId,
                        ':pos' => $clean['possession'],
                        ':st' => $clean['shots_total'],
                        ':sot' => $clean['shots_on_target'],
                        ':cor' => $clean['corners'],
                        ':fk' => $clean['freekicks'],
                        ':foul' => $clean['fouls'],
                        ':off' => $clean['offsides'],
                        ':yc' => $clean['yellow_cards'],
                        ':rc' => $clean['red_cards']
                ]);
        }
}

/* -------------------------------------------------------------
   SAVE HOME + AWAY
------------------------------------------------------------- */
saveStats($db, $fixtureId, $homeId, $homeStats);
saveStats($db, $fixtureId, $awayId, $awayStats);

/* -------------------------------------------------------------
   REDIRECT
------------------------------------------------------------- */
header("Location: view.php?id={$fixtureId}#stats");
exit;
