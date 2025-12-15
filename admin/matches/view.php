<?php
require_once __DIR__ . '/../../shared/includes/session_init.php';
require_once __DIR__ . '/../../shared/includes/simple_session.php';
require_once __DIR__ . '/../../portal/auth/auth_functions.php';
require_once __DIR__ . '/../../portal/auth/middleware.php';

checkRole([1, 2, 3]);
$db = getDB();

$fixtureId = (int)($_GET['id'] ?? 0);
if (!$fixtureId) die('Missing match ID');

/* Fixture */
$stmtFx = $db->prepare("
  SELECT f.*,
         s.name AS season_name,
         c.name AS competition_name,
         ht.name AS home_team,
         at.name AS away_team,
         v.name AS venue_name
  FROM fixtures f
  LEFT JOIN seasons s ON f.season_id = s.id
  LEFT JOIN competitions c ON f.competition_id = c.id
  LEFT JOIN teams ht ON f.home_team_id = ht.id
  LEFT JOIN teams at ON f.away_team_id = at.id
  LEFT JOIN venues v ON f.venue_id = v.id
  WHERE f.id = :id
");
$stmtFx->execute([':id' => $fixtureId]);
$fixture = $stmtFx->fetch(PDO::FETCH_ASSOC);
if (!$fixture) die('Fixture not found.');

/* Lineup */
$stmtLU = $db->prepare("
  SELECT ml.*, p.name AS player_name, pos.short_label AS pos_short
  FROM match_lineups ml
  LEFT JOIN players p ON ml.player_id = p.id
  LEFT JOIN positions pos ON ml.position_id = pos.id
  WHERE ml.fixture_id = :fx
  ORDER BY ml.shirt_number ASC, ml.id ASC
");
$stmtLU->execute([':fx' => $fixtureId]);
$rows = $stmtLU->fetchAll(PDO::FETCH_ASSOC);

$starters = array_values(array_filter($rows, fn($r) => (int)$r['is_substitute'] === 0));
$subs     = array_values(array_filter($rows, fn($r) => (int)$r['is_substitute'] === 1));

$nameById = [];
foreach ($rows as $r) {
        if (!empty($r['player_id'])) {
                $nameById[(int)$r['player_id']] = $r['player_name'] ?? 'Unknown';
        }
}

/* Related data */
$stmtStats = $db->prepare("SELECT * FROM match_stats WHERE fixture_id = :fx");
$stmtStats->execute([':fx' => $fixtureId]);
$statsRows = $stmtStats->fetchAll(PDO::FETCH_ASSOC);
$teamStats = ['home' => [], 'away' => []];
foreach ($statsRows as $statRow) {
        if ((int)($statRow['team_id'] ?? 0) === (int)($fixture['home_team_id'] ?? 0)) {
                $teamStats['home'] = $statRow;
        } elseif ((int)($statRow['team_id'] ?? 0) === (int)($fixture['away_team_id'] ?? 0)) {
                $teamStats['away'] = $statRow;
        }
}
$hasStats = !empty($teamStats['home']) || !empty($teamStats['away']);

$stmtVeo = $db->prepare("SELECT * FROM veo_matches WHERE fixture_id = :fx");
$stmtVeo->execute([':fx' => $fixtureId]);
$veoMatch = $stmtVeo->fetch(PDO::FETCH_ASSOC);

$stmtRatings = $db->prepare("
  SELECT mr.*, p.name AS player_name
  FROM match_ratings mr
  LEFT JOIN players p ON mr.player_id = p.id
  WHERE mr.fixture_id = :fx
  ORDER BY p.name ASC
");
$stmtRatings->execute([':fx' => $fixtureId]);
$ratingsData = $stmtRatings->fetchAll(PDO::FETCH_ASSOC);

$stmtNotes = $db->prepare("SELECT * FROM match_notes WHERE fixture_id = :fx ORDER BY created_at DESC");
$stmtNotes->execute([':fx' => $fixtureId]);
$notesData = $stmtNotes->fetchAll(PDO::FETCH_ASSOC);

$cardsStmt = $db->prepare("
  SELECT md.*, p.name AS player_name
  FROM match_discipline md
  LEFT JOIN players p ON md.player_id = p.id
  WHERE md.fixture_id = :fx
  ORDER BY md.minute IS NULL, md.minute ASC, p.name ASC
");
$cardsStmt->execute([':fx' => $fixtureId]);
$disciplineCards = $cardsStmt->fetchAll(PDO::FETCH_ASSOC);
$cardsByPlayer = [];
$homeCardTotals = ['yellow' => 0, 'red' => 0];
$awayCardTotals = ['yellow' => 0, 'red' => 0];
foreach ($disciplineCards as $card) {
        $pid = (int)($card['player_id'] ?? 0);
        $type = ($card['card_type'] ?? 'yellow') === 'red' ? 'red' : 'yellow';
        if ($pid && isset($nameById[$pid])) {
                $cardsByPlayer[$pid][] = $card;
                $homeCardTotals[$type]++;
        } else {
                $awayCardTotals[$type]++;
        }
}

$statFields = [
        'possession' => 'Possession (%)',
        'shots_total' => 'Shots',
        'shots_on_target' => 'Shots on Target',
        'corners' => 'Corners',
        'freekicks' => 'Free Kicks',
        'fouls' => 'Fouls',
        'offsides' => 'Offsides',
        'yellow_cards' => 'Yellow Cards',
        'red_cards' => 'Red Cards',
];

$pageTitle = 'Match View';
$pageContextLabel = 'Match overview';
$pageTitleText = 'Match Details - ' . ($fixture['home_team'] ?? 'Home') . ' vs ' . ($fixture['away_team'] ?? 'Away');
require_once __DIR__ . '/../includes/layout_start.php';
$activeNav = 'matches';
require_once __DIR__ . '/../includes/navigation.php';
?>
<style>
        .sub {
                display: inline-flex;
                align-items: center;
        }

        .sub img {
                width: auto;
                height: 1.3rem;
                margin: 0 0.3rem;
        }

        .tab-pane.hidden {
                display: none;
        }
</style>
<main class="flex-1 bg-slate-900/40 backdrop-blur">
        <?php require_once __DIR__ . '/../includes/header.php'; ?>

        <section class="mx-auto max-w-6xl px-6 py-10">
                <div class="rounded-3xl border border-white/5 bg-slate-900/70 p-6 shadow-maroon-lg">

                        <div class="mb-6 flex flex-col md:flex-row md:items-center md:justify-between gap-4">
                                <div>
                                        <h2 class="text-xl font-semibold text-cream">
                                                <?= htmlspecialchars($fixture['home_team'] ?? 'Home'); ?> vs <?= htmlspecialchars($fixture['away_team'] ?? 'Away'); ?>
                                        </h2>
                                        <p class="text-xs text-slate-400 mt-1">
                                                <?= htmlspecialchars($fixture['competition_name'] ?? 'Competition'); ?>
                                                <?php if (!empty($fixture['season_name'])): ?> &middot; <?= htmlspecialchars($fixture['season_name']); ?><?php endif; ?>
                                        </p>
                                </div>
                                <div class="flex flex-wrap gap-2">
                                        <a href="edit.php?id=<?= (int)$fixtureId; ?>" class="btn-sm border border-gold/30 text-gold hover:bg-gold/20 px-3 py-1 rounded-md text-xs font-semibold transition">
                                                <i class="fa-solid fa-pen-to-square mr-2"></i>Edit Match
                                        </a>
                                        <a href="lineup.php?fixture_id=<?= (int)$fixtureId; ?>" class="btn-sm border border-emerald-400/30 text-emerald-300 hover:bg-emerald-500/20 px-3 py-1 rounded-md text-xs font-semibold transition">
                                                <i class="fa-solid fa-people-group mr-2"></i>Edit Line-Up
                                        </a>
                                </div>
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-3 text-center border-y border-white/10 py-4 mb-6 gap-4 md:gap-0">
                                <div class="text-slate-300">
                                        <p class="font-semibold">
                                                <?= $fixture['match_date'] ? date('j M Y', strtotime($fixture['match_date'])) : 'Date TBC'; ?>
                                        </p>
                                        <p class="text-xs text-slate-500">
                                                <?= !empty($fixture['match_time']) ? substr($fixture['match_time'], 0, 5) : 'TBC'; ?>
                                        </p>
                                </div>
                                <div>
                                        <p class="text-3xl font-bold text-gold">
                                                <?= htmlspecialchars($fixture['home_score'] ?? '0'); ?> - <?= htmlspecialchars($fixture['away_score'] ?? '0'); ?>
                                        </p>
                                        <p class="text-xs text-slate-400 uppercase">
                                                <?= htmlspecialchars($fixture['status'] ?? 'scheduled'); ?>
                                        </p>
                                </div>
                                <div class="text-slate-300">
                                        <p class="font-semibold">
                                                <?= htmlspecialchars($fixture['venue_name'] ?? 'Venue TBC'); ?>
                                        </p>
                                        <p class="text-xs text-slate-500">Venue</p>
                                </div>
                        </div>

                        <ul class="flex border-b border-white/10 text-sm text-slate-300 overflow-x-auto">
                                <li class="mr-6"><a href="#lineupTab" class="tab-link active text-gold">Line-Up</a></li>
                                <li class="mr-6"><a href="#statsTab" class="tab-link">Stats</a></li>
                                <li class="mr-6"><a href="#ratingsTab" class="tab-link">Ratings</a></li>
                                <li class="mr-6"><a href="#notesTab" class="tab-link">Coach Notes</a></li>
                                <li class="mr-6"><a href="#veoTab" class="tab-link">VEO</a></li>
                        </ul>

                        <div class="tab-content mt-6">

                                <div id="lineupTab" class="tab-pane">
                                        <?php if (!$starters && !$subs): ?>
                                                <p class="text-slate-400 text-sm">No lineup recorded yet.</p>
                                        <?php else: ?>
                                                <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                                                        <div>
                                                                <h3 class="text-lg font-semibold text-gold mb-3">Starting XI</h3>
                                                                <?php if (!$starters): ?>
                                                                        <p class="text-slate-400 text-sm">No players in starting lineup.</p>
                                                                <?php else: ?>
                                                                        <ul class="divide-y divide-white/10">
                                                                                <?php foreach ($starters as $p): ?>
                                                                                        <?php
                                                                                        $playerId = (int)($p['player_id'] ?? 0);
                                                                                        $playerCards = $cardsByPlayer[$playerId] ?? [];
                                                                                        ?>
                                                                                        <li class="py-2 flex items-center justify-between">
                                                                                                <div class="flex items-center gap-3">
                                                                                                        <div class="w-8 text-center font-bold text-cream"><?= htmlspecialchars($p['shirt_number'] ?? ''); ?></div>
                                                                                                        <div>
                                                                                                                <div class="text-cream font-semibold flex flex-wrap items-center gap-2">
                                                                                                                        <?= htmlspecialchars($p['player_name'] ?? ''); ?>
                                                                                                                        <?php if (!empty($p['captain'])): ?><span class="ml-1 text-gold text-xs font-bold">&copy;</span><?php endif; ?>
                                                                                                                        <?php foreach ($playerCards as $card): ?>
        <span class="inline-flex items-center gap-1 text-xs font-semibold <?= ($card['card_type'] ?? 'yellow') === 'red' ? 'text-rose-300' : 'text-amber-300'; ?>"><?= ($card['card_type'] ?? 'yellow') === 'red' ? 'Red Card' : 'Yellow Card'; ?><?php if (isset($card['minute']) && $card['minute'] !== '' && $card['minute'] !== null): ?> <span><?= (int)$card['minute']; ?>'</span><?php endif; ?></span>
    <?php endforeach; ?>
                                                                                                                </div>
                                                                                                                <?php if (!empty($p['pos_short'])): ?>
                                                                                                                        <div class="text-xs text-slate-400"><?= htmlspecialchars($p['pos_short']); ?></div>
                                                                                                                <?php endif; ?>
                                                                                                        </div>
                                                                                                </div>
                                                                                        </li>
                                                                                <?php endforeach; ?>
                                                                        </ul>
                                                                <?php endif; ?>
                                                        </div>

                                                        <div>
                                                                <h3 class="text-lg font-semibold text-slate-300 mb-3">Substitutes</h3>
                                                                <?php if (!$subs): ?>
                                                                        <p class="text-slate-400 text-sm">No substitutes recorded.</p>
                                                                <?php else: ?>
                                                                        <ul class="divide-y divide-white/10">
                                                                                <?php foreach ($subs as $p): ?>
                                                                                        <?php
                                                                                        $minute = (isset($p['minutes_played']) && $p['minutes_played'] !== '' && $p['minutes_played'] !== null)
                                                                                                ? (int)$p['minutes_played'] : null;
                                                                                        $replacedName = (!empty($p['player_replaced_id']) && isset($nameById[(int)$p['player_replaced_id']]))
                                                                                                ? $nameById[(int)$p['player_replaced_id']]
                                                                                                : null;
                                                                                        $playerId = (int)($p['player_id'] ?? 0);
                                                                                        $playerCards = $cardsByPlayer[$playerId] ?? [];
                                                                                        ?>
                                                                                        <li class="py-2">
                                                                                                <div class="flex items-center gap-3">
                                                                                                        <div class="w-8 text-center font-bold text-slate-300"><?= htmlspecialchars($p['shirt_number'] ?? ''); ?></div>
                                                                                                        <div>
                                                                                                                <div class="text-slate-200 font-semibold flex flex-wrap items-center gap-2">
                                                                                                                        <span><?= htmlspecialchars($p['player_name'] ?? ''); ?></span>
                                                                                                                        <?php if ($replacedName): ?>
                                                                                                                                <span class="sub"><img src="/shared/assets/images/event.substitution.png" alt="Substitution icon"></span>
                                                                                                                        <?php endif; ?>
                                                                                                                        <?php if ($minute !== null || $replacedName): ?>
                                                                                                                                <span class="text-xs text-slate-400">
                                                                                                                                        <?php if ($minute !== null): ?>
                                                                                                                                                <?= $minute ?>'
                                                                                                                                        <?php endif; ?>
                                                                                                                                        <?php if ($replacedName): ?>
                                                                                                                                                <?= $minute !== null ? ' ' : '' ?>Replaced <?= htmlspecialchars($replacedName); ?>
                                                                                                                                        <?php endif; ?>
                                                                                                                                </span>
                                                                                                                        <?php endif; ?>
                                                                                                                        <?php foreach ($playerCards as $card): ?>
                                                                                                                                <span class="inline-flex items-center gap-1 text-xs font-semibold <?= ($card['card_type'] ?? 'yellow') === 'red' ? 'text-rose-300' : 'text-amber-300'; ?>">
                                                                                                                                        <?= ($card['card_type'] ?? 'yellow') === 'red' ? 'Red Card' : 'Yellow Card'; ?><?php if (isset($card['minute']) && $card['minute'] !== '' && $card['minute'] !== null): ?> <span><?= (int)$card['minute']; ?>'</span><?php endif; ?>
                                                                                                                                </span>
                                                                                                                        <?php endforeach; ?>
                                                                                                                </div>
                                                                                                                <?php if (!empty($p['pos_short'])): ?>
                                                                                                                        <div class="text-xs text-slate-400"><?= htmlspecialchars($p['pos_short']); ?></div>
                                                                                                                <?php endif; ?>
                                                                                                        </div>
                                                                                                </div>
                                                                                        </li>
                                                                                <?php endforeach; ?>
                                                                        </ul>
                                                                <?php endif; ?>
                                                        </div>
                                                </div>
                                        <?php endif; ?>
                                </div>


                                <div id="statsTab" class="tab-pane hidden">
                                        <?php if (!$hasStats): ?>
                                                <p class="text-slate-400 text-sm">No stats recorded for this match yet.</p>
                                        <?php else: ?>
                                                <div class="overflow-x-auto">
                                                        <table class="w-full text-sm text-slate-200 border-separate border-spacing-y-2">
                                                                <thead class="text-xs uppercase text-slate-400 bg-slate-900/60">
                                                                        <tr>
                                                                                <th class="px-3 py-2 text-right"><?= htmlspecialchars($fixture['home_team'] ?? 'Home'); ?></th>
                                                                                <th class="px-3 py-2 text-center">Statistic</th>
                                                                                <th class="px-3 py-2 text-left"><?= htmlspecialchars($fixture['away_team'] ?? 'Away'); ?></th>
                                                                        </tr>
                                                                </thead>
                                                                <tbody class="divide-y divide-white/5">
                                                                        <?php foreach ($statFields as $fieldKey => $label): ?>
                                                                                <?php
                                                                                $homeRaw = $teamStats['home'][$fieldKey] ?? null;
                                                                                $awayRaw = $teamStats['away'][$fieldKey] ?? null;
                                                                                if ($fieldKey === 'yellow_cards') {
                                                                                        $homeDisplay = htmlspecialchars((string)$homeCardTotals['yellow']);
                                                                                        $awayDisplay = htmlspecialchars((string)$awayCardTotals['yellow']);
                                                                                } elseif ($fieldKey === 'red_cards') {
                                                                                        $homeDisplay = htmlspecialchars((string)$homeCardTotals['red']);
                                                                                        $awayDisplay = htmlspecialchars((string)$awayCardTotals['red']);
                                                                                } else {
                                                                                        $homeDisplay = ($homeRaw !== null && $homeRaw !== '') ? htmlspecialchars((string)$homeRaw) . ($fieldKey === 'possession' ? '%' : '') : '&mdash;';
                                                                                        $awayDisplay = ($awayRaw !== null && $awayRaw !== '') ? htmlspecialchars((string)$awayRaw) . ($fieldKey === 'possession' ? '%' : '') : '&mdash;';
                                                                                }
                                                                                ?>
                                                                                <tr>
                                                                                        <td class="px-3 py-2 text-right font-semibold"><?= $homeDisplay; ?></td>
                                                                                        <td class="px-3 py-2 text-center text-slate-300"><?= htmlspecialchars($label); ?></td>
                                                                                        <td class="px-3 py-2 text-left font-semibold"><?= $awayDisplay; ?></td>
                                                                                </tr>
                                                                        <?php endforeach; ?>
                                                                </tbody>
                                                        </table>
                                                </div>
                                        <?php endif; ?>
                                </div>

                                <div id="ratingsTab" class="tab-pane hidden">
                                        <a href="ratings.php?fixture_id=<?= (int)$fixtureId; ?>" class="btn-sm border border-emerald-400/30 text-emerald-300 hover:bg-emerald-500/20 px-3 py-1 rounded-md text-xs font-semibold transition mb-3 inline-block">
                                                Edit Ratings
                                        </a>
                                        <?php if (!$ratingsData): ?>
                                                <p class="text-slate-400 text-sm">No ratings recorded.</p>
                                        <?php else: ?>
                                                <table class="w-full text-sm text-slate-200 border-separate border-spacing-y-1">
                                                        <thead class="text-xs uppercase text-slate-400 bg-slate-900/60">
                                                                <tr>
                                                                        <th class="text-left px-3 py-2">Player</th>
                                                                        <th class="text-right px-3 py-2">Rating</th>
                                                                        <th class="text-left px-3 py-2">Comment</th>
                                                                </tr>
                                                        </thead>
                                                        <tbody class="divide-y divide-white/5">
                                                                <?php foreach ($ratingsData as $rating): ?>
                                                                        <tr>
                                                                                <td class="px-3 py-2"><?= htmlspecialchars($rating['player_name'] ?? 'Unknown'); ?></td>
                                                                                <td class="px-3 py-2 text-right text-gold font-semibold"><?= number_format((float)($rating['rating'] ?? 0), 1); ?></td>
                                                                                <td class="px-3 py-2"><?= htmlspecialchars($rating['comment'] ?? ''); ?></td>
                                                                        </tr>
                                                                <?php endforeach; ?>
                                                        </tbody>
                                                </table>
                                        <?php endif; ?>
                                </div>

                                <div id="notesTab" class="tab-pane hidden">
                                        <form method="post" action="notes.php" class="mb-4 space-y-2">
                                                <input type="hidden" name="fixture_id" value="<?= (int)$fixtureId; ?>">
                                                <textarea name="content" rows="2" class="w-full rounded-md bg-slate-800/70 text-slate-100 p-2" placeholder="Add note..."></textarea>
                                                <div class="flex flex-wrap items-center gap-3">
                                                        <select name="note_type" class="bg-slate-800/70 text-slate-200 rounded-md text-sm px-2 py-1">
                                                                <option value="general">General</option>
                                                                <option value="tactical">Tactical</option>
                                                                <option value="postmatch">Post-Match</option>
                                                        </select>
                                                        <button class="btn-sm border border-emerald-400/30 text-emerald-300 hover:bg-emerald-500/20 px-3 py-1 rounded-md text-xs font-semibold transition">
                                                                Add Note
                                                        </button>
                                                </div>
                                        </form>
                                        <?php if ($notesData): ?>
                                                <ul class="space-y-2">
                                                        <?php foreach ($notesData as $note): ?>
                                                                <li class="rounded-lg border border-white/5 bg-slate-900/60 p-3">
                                                                        <p class="text-slate-200 text-sm"><?= htmlspecialchars($note['content'] ?? ''); ?></p>
                                                                        <p class="text-xs text-slate-400 mt-1">
                                                                                [<?= htmlspecialchars(ucfirst($note['note_type'] ?? 'general')); ?>] &middot; <?= !empty($note['created_at']) ? date('d M Y H:i', strtotime($note['created_at'])) : ''; ?>
                                                                        </p>
                                                                </li>
                                                        <?php endforeach; ?>
                                                </ul>
                                        <?php else: ?>
                                                <p class="text-slate-400 text-sm">No notes recorded yet.</p>
                                        <?php endif; ?>
                                </div>

                                <div id="veoTab" class="tab-pane hidden">
                                        <?php if ($veoMatch): ?>
                                                <h5 class="text-cream font-semibold mb-3"><?= htmlspecialchars($veoMatch['title'] ?? 'VEO Match'); ?></h5>
                                                <iframe src="<?= htmlspecialchars($veoMatch['video_url'] ?? ''); ?>" frameborder="0" width="100%" height="400" class="rounded-xl"></iframe>
                                                <a href="/admin/veo/view.php?id=<?= (int)($veoMatch['id'] ?? 0); ?>" class="btn-sm border border-gold/30 text-gold hover:bg-gold/20 px-3 py-1 rounded-md text-xs font-semibold transition mt-3 inline-block">
                                                        Manage in VEO Admin
                                                </a>
                                        <?php else: ?>
                                                <p class="text-slate-400 text-sm mb-3">No VEO match linked yet.</p>
                                                <a href="/admin/veo/veo_link.php?fixture_id=<?= (int)$fixtureId; ?>" class="btn-sm border border-emerald-400/30 text-emerald-300 hover:bg-emerald-500/20 px-3 py-1 rounded-md text-xs font-semibold transition">
                                                        Link VEO Match
                                                </a>
                                        <?php endif; ?>
                                </div>

                        </div>
                </div>
        </section>

        <?php require_once __DIR__ . '/../includes/footer.php'; ?>
</main>
<?php require_once __DIR__ . '/../includes/layout_end.php'; ?>

<script>
        (function() {
                const tabs = document.querySelectorAll('.tab-link');
                const panes = document.querySelectorAll('.tab-pane');

                tabs.forEach(link => {
                        link.addEventListener('click', event => {
                                event.preventDefault();
                                const targetId = link.getAttribute('href');
                                if (!targetId) return;

                                tabs.forEach(l => l.classList.remove('active', 'text-gold'));
                                panes.forEach(p => p.classList.add('hidden'));

                                const target = document.querySelector(targetId);
                                if (target) {
                                        target.classList.remove('hidden');
                                        link.classList.add('active', 'text-gold');
                                }
                        });
                });
        })();
</script>


