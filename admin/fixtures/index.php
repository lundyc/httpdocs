<?php
require_once __DIR__ . '/../../shared/includes/session_init.php';
require_once __DIR__ . '/../../shared/includes/simple_session.php';
require_once __DIR__ . '/../../portal/auth/auth_functions.php';
require_once __DIR__ . '/../../portal/auth/middleware.php';

// Using simple PHP sessions now
checkRole([1, 2, 3]);
$db = getDB();

/* -------------------------------------------------------------
   LOAD CLUB TEAM CONFIG
------------------------------------------------------------- */
$teamId = CLUB_TEAM_ID;
$stmtClub = $db->prepare("SELECT name FROM teams WHERE id = :id");
$stmtClub->execute([':id' => $teamId]);
$clubName = $stmtClub->fetchColumn() ?: 'Unknown Club';

/* -------------------------------------------------------------
   FILTERS
------------------------------------------------------------- */
$seasonId = $_GET['season_id'] ?? '';
$competitionId = $_GET['competition_id'] ?? '';

$where = 'WHERE (f.home_team_id = :team_id OR f.away_team_id = :team_id)';
$params = [':team_id' => $teamId];

if ($seasonId !== '') {
    $where .= " AND f.season_id = :season_id";
    $params[':season_id'] = $seasonId;
}
if ($competitionId !== '') {
    $where .= " AND f.competition_id = :competition_id";
    $params[':competition_id'] = $competitionId;
}

/* -------------------------------------------------------------
   FETCH FIXTURES
------------------------------------------------------------- */
$sql = "SELECT f.id, f.match_date, f.match_time, f.home_score, f.away_score, f.status,
               s.name AS season_name, c.name AS competition_name,
               ht.name AS home_team, at.name AS away_team,
               v.name AS venue_name
        FROM fixtures f
        LEFT JOIN seasons s ON f.season_id = s.id
        LEFT JOIN competitions c ON f.competition_id = c.id
        LEFT JOIN teams ht ON f.home_team_id = ht.id
        LEFT JOIN teams at ON f.away_team_id = at.id
        LEFT JOIN venues v ON f.venue_id = v.id
        $where
        ORDER BY f.match_date DESC, f.match_time DESC";
$stmt = $db->prepare($sql);
$stmt->execute($params);
$fixtures = $stmt->fetchAll(PDO::FETCH_ASSOC);

/* -------------------------------------------------------------
   CALCULATE SUMMARY STATS
------------------------------------------------------------- */
$totalFixtures = count($fixtures);
$playedFixtures = $wins = $losses = $draws = 0;
$goalsScored = $goalsConceded = 0;
$nextFixture = null;
$nextFixtureDateTime = null;

foreach ($fixtures as $fixture) {
    $status = strtolower($fixture['status'] ?? 'scheduled');
    $isHome = ($fixture['home_team'] === $clubName);
    $ourScore = $isHome ? ($fixture['home_score'] ?? 0) : ($fixture['away_score'] ?? 0);
    $oppScore = $isHome ? ($fixture['away_score'] ?? 0) : ($fixture['home_score'] ?? 0);

    if ($status === 'played' && is_numeric($ourScore) && is_numeric($oppScore)) {
        $playedFixtures++;
        $goalsScored += $ourScore;
        $goalsConceded += $oppScore;

        if ($ourScore == $oppScore) $draws++;
        elseif ($ourScore > $oppScore) $wins++;
        else $losses++;
    }

    if (!in_array($status, ['played', 'cancelled', 'postponed'])) {
        $dateString = $fixture['match_date'] ?? '';
        if ($dateString !== '') {
            $timeRaw = $fixture['match_time'] ?? '';
            $timeString = strlen($timeRaw) >= 5 ? substr($timeRaw, 0, 5) : '00:00';
            $dateTime = DateTime::createFromFormat('Y-m-d H:i', "$dateString $timeString") ?: DateTime::createFromFormat('Y-m-d', $dateString);
            if ($dateTime && ($nextFixtureDateTime === null || $dateTime < $nextFixtureDateTime)) {
                $nextFixtureDateTime = $dateTime;
                $nextFixture = $fixture;
            }
        }
    }
}

/* -------------------------------------------------------------
   PAGE CONFIG
------------------------------------------------------------- */
$pageTitle = 'Fixtures Management - My Club Hub Admin';
$pageContextLabel = 'Match operations';
$pageTitleText = "Fixtures Centre – $clubName";

require_once __DIR__ . '/../includes/layout_start.php';
$activeNav = 'fixtures';
require_once __DIR__ . '/../includes/navigation.php';

/* -------------------------------------------------------------
   TEAM FORM + NEXT MATCH
------------------------------------------------------------- */
$formStmt = $db->prepare("
    SELECT f.home_score, f.away_score, ht.name AS home_team, at.name AS away_team, f.status, f.match_date
    FROM fixtures f
    LEFT JOIN teams ht ON f.home_team_id = ht.id
    LEFT JOIN teams at ON f.away_team_id = at.id
    WHERE f.status = 'played'
      AND (f.home_team_id = :team OR f.away_team_id = :team)
    ORDER BY f.match_date DESC
    LIMIT 5
");
$formStmt->execute([':team' => $teamId]);
$formFixtures = $formStmt->fetchAll(PDO::FETCH_ASSOC);

$formHistory = [];
foreach ($formFixtures as $fx) {
    $isHome = $fx['home_team'] === $clubName;
    $ourScore = $isHome ? $fx['home_score'] : $fx['away_score'];
    $oppScore = $isHome ? $fx['away_score'] : $fx['home_score'];
    $opponent = $isHome ? $fx['away_team'] : $fx['home_team'];
    $resultType = ($ourScore == $oppScore) ? 'D' : (($ourScore > $oppScore) ? 'W' : 'L');
    $formHistory[] = [
        'score' => "{$ourScore}-{$oppScore}",
        'result' => $resultType,
        'opponent' => $opponent,
    ];
}

/* Next fixture info */
$nextStmt = $db->prepare("
    SELECT f.match_date, f.match_time, ht.name AS home_team, at.name AS away_team, c.name AS competition_name
    FROM fixtures f
    LEFT JOIN teams ht ON f.home_team_id = ht.id
    LEFT JOIN teams at ON f.away_team_id = at.id
    LEFT JOIN competitions c ON f.competition_id = c.id
    WHERE f.status NOT IN ('played','cancelled')
      AND (f.home_team_id = :team OR f.away_team_id = :team)
    ORDER BY f.match_date ASC, f.match_time ASC
    LIMIT 1
");
$nextStmt->execute([':team' => $teamId]);
$next = $nextStmt->fetch(PDO::FETCH_ASSOC);
?>

<main class="flex-1 bg-slate-900/40 backdrop-blur">
    <?php require_once __DIR__ . '/../includes/header.php'; ?>

    <!-- 🔹 TEAM FORM + NEXT MATCH -->
    <section class="mx-auto max-w-6xl px-6 pt-10 pb-6">
        <div class="grid gap-6 sm:grid-cols-2">
            <!-- Team Form -->
            <div class="rounded-3xl border border-white/5 bg-slate-900/70 p-6 shadow-maroon-lg">
                <h3 class="text-sm font-semibold text-cream mb-4"><?= htmlspecialchars($clubName); ?> Form</h3>
                <div class="flex items-center gap-4 overflow-x-auto">
                    <?php if (!empty($formHistory)): ?>
                        <?php foreach ($formHistory as $form):
                            $badgeColor = $form['result'] === 'W' ? 'bg-emerald-500/20 border-emerald-400 text-emerald-300'
                                : ($form['result'] === 'L' ? 'bg-rose-500/20 border-rose-400 text-rose-300'
                                    : 'bg-slate-500/20 border-slate-400 text-slate-300');
                        ?>
                            <div class="flex flex-col items-center gap-1">
                                <span class="inline-flex items-center justify-center rounded-full border <?= $badgeColor; ?> text-xs font-bold px-2.5 py-1 min-w-[40px]"><?= htmlspecialchars($form['score']); ?></span>
                                <span class="text-[10px] text-slate-400"><?= htmlspecialchars($form['opponent']); ?></span>
                            </div>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <p class="text-slate-400 text-xs">No recent results.</p>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Next Match -->
            <div class="rounded-3xl border border-white/5 bg-slate-900/70 p-6 shadow-maroon-lg flex flex-col justify-center">
                <div class="flex items-center justify-between mb-2">
                    <h3 class="text-sm font-semibold text-cream">Next Match</h3>
                    <p class="text-xs text-slate-400"><?= htmlspecialchars($next['competition_name'] ?? ''); ?></p>
                </div>
                <?php if ($next): ?>
                    <div class="flex items-center justify-between">
                        <div class="text-center">
                            <div class="text-cream font-semibold text-sm"><?= htmlspecialchars($next['home_team']); ?></div>
                        </div>
                        <div class="text-center">
                            <p class="text-2xl font-bold text-gold"><?= htmlspecialchars(substr($next['match_time'], 0, 5) ?? 'TBC'); ?></p>
                            <p class="text-xs text-slate-400"><?= date('j M Y', strtotime($next['match_date'] ?? '')) ?: 'Date TBC'; ?></p>
                        </div>
                        <div class="text-center">
                            <div class="text-cream font-semibold text-sm"><?= htmlspecialchars($next['away_team']); ?></div>
                        </div>
                    </div>
                <?php else: ?>
                    <p class="text-slate-400 text-xs mt-3">No upcoming matches scheduled.</p>
                <?php endif; ?>
            </div>
        </div>
    </section>

    <!-- 🔹 SUMMARY STATS -->
    <section class="mx-auto max-w-6xl px-6 pt-4 pb-6">
        <div class="grid gap-5 sm:grid-cols-2 xl:grid-cols-4">

            <!-- 🟡 Total Fixtures -->
            <div class="rounded-3xl border border-white/5 bg-slate-900/70 p-6 shadow-maroon-lg">
                <p class="text-xs font-semibold uppercase tracking-[0.3em] text-slate-400 mb-3">Total Fixtures</p>
                <p class="text-5xl font-extrabold text-gold leading-none flex flex-col items-center justify-center text-center"><?= number_format($totalFixtures); ?></p>
            </div>

            <!-- 🟩 Record (Table Style) -->
            <div class="rounded-3xl border border-white/5 bg-slate-900/70 p-6 shadow-maroon-lg text-center">
                <p class="text-xs font-semibold uppercase tracking-[0.3em] text-slate-400 mb-4">Record</p>

                <div class="grid grid-cols-3 gap-0">
                    <!-- Labels -->
                    <span class="text-slate-200 font-semibold">W</span>
                    <span class="text-slate-200 font-semibold">D</span>
                    <span class="text-slate-200 font-semibold">L</span>

                    <!-- Divider -->
                    <div class="col-span-3 border-b border-white/20 my-0"></div>

                    <!-- Values -->
                    <span class="text-3xl font-bold text-white"><?= $wins; ?></span>
                    <span class="text-3xl font-bold text-white"><?= $draws; ?></span>
                    <span class="text-3xl font-bold text-white"><?= $losses; ?></span>
                </div>
            </div>

            <!-- ⚽ Win Rate -->
            <div class="rounded-3xl border border-white/5 bg-slate-900/70 p-6 shadow-maroon-lg">
                <p class="text-xs font-semibold uppercase tracking-[0.3em] text-slate-400 mb-3">Win Rate</p>
                <p class="text-5xl font-extrabold text-gold leading-none flex flex-col items-center justify-center text-center">
                    <?= $playedFixtures > 0 ? round(($wins / $playedFixtures) * 100, 1) : 0; ?>%
                </p>
                <p class="text-xs text-slate-400 mt-2 flex flex-col items-center justify-center text-center">of played fixtures</p>
            </div>

            <!-- 🎯 Goals -->
            <div class="rounded-3xl border border-white/5 bg-slate-900/70 p-6 shadow-maroon-lg">
                <p class="text-xs font-semibold uppercase tracking-[0.3em] text-slate-400 mb-3">Goals</p>
                <p class="text-5xl font-semibold text-cream leading-none flex flex-col items-center justify-center text-center">
                    <?= "{$goalsScored} / {$goalsConceded}"; ?>
                </p>
                <p class="text-xs text-slate-400 mt-2 flex flex-col items-center justify-center text-center">Scored / Conceded</p>
            </div>

        </div>
    </section>


    <!-- 🔹 FIXTURE TABLE -->
    <section class="mx-auto max-w-6xl px-6 pb-16">
        <div class="rounded-3xl border border-white/5 bg-slate-900/70 p-6 shadow-maroon-lg">
            <div class="flex flex-col gap-4 border-b border-white/10 pb-5 md:flex-row md:items-center md:justify-between">
                <div>
                    <h2 class="text-xl font-semibold text-cream">Fixture list – <?= htmlspecialchars($clubName); ?></h2>
                    <p class="mt-1 text-xs uppercase tracking-[0.24em] text-slate-400">Sorted by most recent fixtures first</p>
                </div>
            </div>

            <?php if (empty($fixtures)): ?>
                <div class="mt-6 rounded-2xl border border-white/5 bg-slate-900/60 p-6 text-center text-sm text-slate-300">
                    No fixtures found for <?= htmlspecialchars($clubName); ?>.
                </div>
            <?php else: ?>
                <div class="mt-6">
                    <table class="w-full border-separate border-spacing-y-2 text-sm">
                        <thead class="text-xs uppercase tracking-[0.2em] text-slate-400 bg-slate-900/80">
                            <tr class="text-left">
                                <th class="px-4 py-3">Match</th>
                                <th class="px-4 py-3 w-[12%] text-center">Date</th>
                                <th class="px-4 py-3 w-[12%] text-center">Result</th>
                                <th class="px-4 py-3 w-[12%] text-center">Competition</th>
                                <th class="px-4 py-3 w-[12%] text-center">Season</th>
                                <th class="px-4 py-3 w-[12%] text-center">Venue</th>
                                <th class="px-4 py-3 text-right">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="text-slate-200">
                            <?php foreach ($fixtures as $fixture): ?>
                                <?php
                                $dateLabel = !empty($fixture['match_date']) ? date('d/m/Y', strtotime($fixture['match_date'])) : '—';
                                $timeLabel = !empty($fixture['match_time']) ? substr($fixture['match_time'], 0, 5) : '—';
                                $status = strtolower($fixture['status'] ?? '');
                                $homeScore = $fixture['home_score'] ?? '-';
                                $awayScore = $fixture['away_score'] ?? '-';

                                $statusClass = match ($status) {
                                    'played' => 'bg-emerald-500/20 text-emerald-300 border border-emerald-400',
                                    'scheduled', 'upcoming' => 'bg-amber-500/20 text-amber-300 border border-amber-400',
                                    'postponed' => 'bg-rose-500/20 text-rose-300 border border-rose-400',
                                    'cancelled' => 'bg-gray-500/20 text-gray-300 border border-gray-400',
                                    'abandoned' => 'bg-red-700/30 text-red-300 border border-red-400',
                                    default => 'bg-slate-600/20 text-slate-300 border border-slate-400',
                                };
                                ?>
                                <tr class="bg-slate-900/60 hover:bg-slate-800/60 rounded-xl transition">
                                    <td class="px-4 py-3 font-semibold text-cream text-left">
                                        <div class="flex flex-col items-start">
                                            <div>
                                                <span class="inline-flex items-center justify-center rounded-md bg-gold/20 text-gold text-xs px-2 py-0.5 mr-2 font-semibold">
                                                    <?= htmlspecialchars($homeScore); ?>
                                                </span>
                                                <?= htmlspecialchars($fixture['home_team']); ?>
                                            </div>
                                            <div>
                                                <span class="inline-flex items-center justify-center rounded-md bg-gold/20 text-gold text-xs px-2 py-0.5 mr-2 font-semibold">
                                                    <?= htmlspecialchars($awayScore); ?>
                                                </span>
                                                <?= htmlspecialchars($fixture['away_team']); ?>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="px-4 py-3 w-[12%] text-center"><?= $dateLabel ?><br><span class="text-xs text-slate-400"><?= $timeLabel ?></span></td>
                                    <td class="px-4 py-3 w-[12%] text-center">
                                        <span class="inline-flex items-center justify-center rounded-full <?= $statusClass ?> px-2 py-0.5 text-xs font-semibold uppercase tracking-wide">
                                            <?= ucfirst($status); ?>
                                        </span>
                                    </td>
                                    <td class="px-4 py-3 w-[12%] text-center"><span class="text-xs text-slate-400"><?= htmlspecialchars($fixture['competition_name']); ?></span></td>
                                    <td class="px-4 py-3 w-[12%] text-center"><?= htmlspecialchars($fixture['season_name']); ?></td>
                                    <td class="px-4 py-3 w-[12%] text-center"><?= htmlspecialchars($fixture['venue_name']); ?></td>
                                    <td class="px-4 py-3 text-right">
                                        <div class="flex justify-end items-center gap-2">
                                            <a href="/admin/fixtures/edit.php?id=<?= (int)$fixture['id']; ?>"
                                                class="inline-flex items-center justify-center rounded-full border border-white/20 w-8 h-8 !text-slate-300 hover:!text-gold hover:!border-gold/40 transition"
                                                title="Edit Fixture">
                                                <i class="fa-solid fa-pen-to-square"></i>
                                            </a>
                                            <a href="/admin/fixtures/delete.php?id=<?= (int)$fixture['id']; ?>" onclick="return confirm('Are you sure you want to delete this fixture?');" class="inline-flex items-center justify-center rounded-full border border-rose-500/40 w-8 h-8 text-rose-400 hover:bg-rose-500/20 hover:text-rose-300 transition" title="Delete Fixture">
                                                <i class="fa-solid fa-trash"></i>
                                            </a>
                                        </div>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php endif; ?>
        </div>
    </section>
    <?php require_once __DIR__ . '/../includes/footer.php'; ?>
</main> <?php require_once __DIR__ . '/../includes/layout_end.php'; ?>
