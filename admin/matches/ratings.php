<?php
require_once __DIR__ . '/../../shared/includes/session_init.php';
require_once __DIR__ . '/../../shared/includes/simple_session.php';
require_once __DIR__ . '/../../portal/auth/auth_functions.php';
require_once __DIR__ . '/../../portal/auth/middleware.php';

checkRole([1, 2, 3]);
$db = getDB();

$fixtureId = (int)($_GET['fixture_id'] ?? 0);
if (!$fixtureId) die('Missing fixture_id');
$embedded = (int)($_GET['embedded'] ?? 0);
$returnTo = $_GET['return_to'] ?? '';

/* Players */
$players = $db->query("SELECT id, name FROM players ORDER BY name")->fetchAll(PDO::FETCH_ASSOC);

/* Existing ratings */
$existingRatings = [];
$ratingsStmt = $db->prepare("
        SELECT player_id, rating, comment
        FROM match_ratings
        WHERE fixture_id = :fx
");
$ratingsStmt->execute([':fx' => $fixtureId]);
foreach ($ratingsStmt->fetchAll(PDO::FETCH_ASSOC) as $row) {
        $existingRatings[(int)$row['player_id']] = $row;
}

/* Save */
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
          $fixtureId = (int)($_POST['fixture_id'] ?? 0);
          if (!$fixtureId) die('Missing fixture_id');
          $returnToPost = $_POST['return_to'] ?? '';

          if (!empty($_POST['rating']) && is_array($_POST['rating'])) {
                    foreach ($_POST['rating'] as $pid => $rating) {
                              $pid    = (int)$pid;
                              $rating = is_numeric($rating) ? (float)$rating : null;
                              $comment = $_POST['comment'][$pid] ?? null;

                              $check = $db->prepare("SELECT id FROM match_ratings WHERE fixture_id = :fx AND player_id = :pid LIMIT 1");
                              $check->execute([':fx' => $fixtureId, ':pid' => $pid]);
                              $rid = $check->fetchColumn();

                              if ($rid) {
                                        $up = $db->prepare("UPDATE match_ratings SET rating=:r, comment=:c WHERE id=:id");
                                        $up->execute([':r' => $rating, ':c' => $comment, ':id' => $rid]);
                              } else {
                                        $ins = $db->prepare("INSERT INTO match_ratings (fixture_id, player_id, rating, comment, rated_by)
                             VALUES (:fx, :pid, :r, :c, :rb)");
                                        $ins->execute([':fx' => $fixtureId, ':pid' => $pid, ':r' => $rating, ':c' => $comment, ':rb' => $_SESSION['user_id'] ?? null]);
                              }
                    }
          }
          if ($returnToPost === 'edit') {
                    header("Location: edit.php?id={$fixtureId}&tab=ratings&saved=1");
          } else {
                    header("Location: view.php?id=" . $fixtureId . "#ratings");
          }
          exit;
}

/* Page shell */
$pageTitle        = 'Player Ratings';
$pageContextLabel = 'Match operations';
$pageTitleText    = 'Player Ratings';
$activeNav        = 'matches';

require_once __DIR__ . '/../includes/layout_start.php';
if (!$embedded) {
          require_once __DIR__ . '/../includes/navigation.php';
}
?>
<main class="flex-1 bg-slate-900/40 backdrop-blur">
        <?php if (!$embedded): ?>
                <?php require_once __DIR__ . '/../includes/header.php'; ?>
        <?php endif; ?>

        <section class="<?= $embedded ? 'px-4 py-6' : 'mx-auto max-w-5xl px-6 py-10'; ?>">
                <div class="rounded-3xl border border-white/5 bg-slate-900/70 <?= $embedded ? 'p-4' : 'p-6 shadow-maroon-lg'; ?>">
                        <h3 class="text-cream font-semibold text-lg mb-4">Player Ratings</h3>

                        <form method="post">
                                <input type="hidden" name="fixture_id" value="<?= $fixtureId ?>">
                                <?php if ($embedded && !empty($returnTo)): ?>
                                        <input type="hidden" name="return_to" value="<?= htmlspecialchars($returnTo); ?>">
                                <?php endif; ?>

                                <table class="w-full text-sm border-separate border-spacing-y-2">
                                        <thead class="text-xs uppercase text-slate-400 bg-slate-900/60">
                                                <tr>
                                                        <th class="px-3 py-2">Player</th>
                                                        <th class="px-3 py-2">Rating (1-10)</th>
                                                        <th class="px-3 py-2">Comment</th>
                                                </tr>
                                        </thead>
                                        <tbody class="text-slate-200">
                                                <?php foreach ($players as $p): ?>
                                                        <tr class="bg-slate-900/60 hover:bg-slate-800/60 rounded-xl transition">
                                                                <td class="px-3 py-2"><?= htmlspecialchars($p['name']) ?></td>
                                                                <td class="px-3 py-2"><input type="number" step="0.1" min="0" max="10" name="rating[<?= (int)$p['id'] ?>]" value="<?= htmlspecialchars($existingRatings[(int)$p['id']]['rating'] ?? ''); ?>" class="w-24 rounded-md bg-slate-800/70 border border-white/10 text-slate-100 px-2 py-1"></td>
                                                                <td class="px-3 py-2"><input type="text" name="comment[<?= (int)$p['id'] ?>]" value="<?= htmlspecialchars($existingRatings[(int)$p['id']]['comment'] ?? ''); ?>" class="w-full rounded-md bg-slate-800/70 border border-white/10 text-slate-100 px-2 py-1"></td>
                                                        </tr>
                                                <?php endforeach; ?>
                                        </tbody>
                                </table>

                                <div class="flex justify-end mt-4">
                                        <button class="px-4 py-2 text-sm font-semibold text-cream bg-maroon border border-maroon/50 rounded-md hover:bg-maroon/80 transition">
                                                Save Ratings
                                        </button>
                                </div>
                        </form>
                </div>
        </section>

        <?php if (!$embedded): ?>
                <?php require_once __DIR__ . '/../includes/footer.php'; ?>
        <?php endif; ?>
</main>
<?php require_once __DIR__ . '/../includes/layout_end.php'; ?>
