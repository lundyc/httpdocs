<?php
// Enable error reporting for debugging
error_reporting(E_ALL);
ini_set('display_errors', 1);

try {
    require_once __DIR__ . "/auth/auth_functions.php";
    require_once __DIR__ . "/auth/middleware.php";

    // Make sure user is logged in
    requireLogin();

    $db = getDB();
} catch (Exception $e) {
    die("Portal Error: " . $e->getMessage());
}

// Get current user info
$currentUserId = $_SESSION['user_id'] ?? null;
$stmt = $db->prepare("SELECT u.name, u.email, r.name AS role_name FROM users u 
                      JOIN roles r ON u.role_id = r.id 
                      WHERE u.id = ?");
$stmt->execute([$currentUserId]);
$user = $stmt->fetch(PDO::FETCH_ASSOC) ?: ['name' => 'Team Member', 'email' => '', 'role_name' => 'Club Staff'];

// Get some quick stats for dashboard
$userCount = (int) $db->query("SELECT COUNT(*) FROM users WHERE status='active'")->fetchColumn();
// Latest announcements count (for info only)
$newsCount = (int) $db->query("SELECT COUNT(*) FROM news")->fetchColumn();

// Club team context (normalized schema)
$teamId = defined('CLUB_TEAM_ID') ? CLUB_TEAM_ID : 1;
$stmtClub = $db->prepare("SELECT name FROM teams WHERE id = :id");
$clubName = 'My Club Hub';
try {
    $stmtClub->execute([':id' => $teamId]);
    $clubName = $stmtClub->fetchColumn() ?: $clubName;
} catch (Exception $e) {
    // teams table might not exist in some setups; keep fallback name
}

// Upcoming fixtures count for the club
$fixturesCount = 0;
try {
    $stmtFxCount = $db->prepare("SELECT COUNT(*) FROM fixtures f WHERE f.status != 'cancelled' AND f.match_date >= CURDATE() AND (f.home_team_id = :team OR f.away_team_id = :team)");
    $stmtFxCount->execute([':team' => $teamId]);
    $fixturesCount = (int) $stmtFxCount->fetchColumn();
} catch (Exception $e) {
    // fallback to generic count if normalized columns are not present
    $fixturesCount = (int) ($db->query("SELECT COUNT(*) FROM fixtures WHERE match_date >= CURDATE()")?->fetchColumn() ?? 0);
}

// Next fixture and recent result (team-scoped, normalized schema)
$nextFixture = null;
$recentResult = null;
try {
    $stmtNext = $db->prepare("
                                SELECT f.match_date, f.match_time,
                                             ht.name AS home_team, at.name AS away_team,
                                             c.name AS competition_name,
                                             v.name AS venue_name,
                                             f.home_score, f.away_score, f.status
                                FROM fixtures f
                                LEFT JOIN teams ht ON f.home_team_id = ht.id
                                LEFT JOIN teams at ON f.away_team_id = at.id
                                LEFT JOIN competitions c ON f.competition_id = c.id
                                LEFT JOIN venues v ON f.venue_id = v.id
                                WHERE f.status NOT IN ('played','cancelled')
                                    AND (f.home_team_id = :team OR f.away_team_id = :team)
                                ORDER BY f.match_date ASC, f.match_time ASC
                                LIMIT 1
                    ");
    $stmtNext->execute([':team' => $teamId]);
    $nextFixture = $stmtNext->fetch(PDO::FETCH_ASSOC) ?: null;

    $stmtRecent = $db->prepare("
                                SELECT f.match_date, f.match_time,
                                             ht.name AS home_team, at.name AS away_team,
                                             c.name AS competition_name,
                                             v.name AS venue_name,
                                             f.home_score, f.away_score, f.status
                                FROM fixtures f
                                LEFT JOIN teams ht ON f.home_team_id = ht.id
                                LEFT JOIN teams at ON f.away_team_id = at.id
                                LEFT JOIN competitions c ON f.competition_id = c.id
                                LEFT JOIN venues v ON f.venue_id = v.id
                                WHERE f.status = 'played'
                                    AND (f.home_team_id = :team OR f.away_team_id = :team)
                                    AND f.home_score IS NOT NULL AND f.away_score IS NOT NULL
                                ORDER BY f.match_date DESC, f.match_time DESC
                                LIMIT 1
                    ");
    $stmtRecent->execute([':team' => $teamId]);
    $recentResult = $stmtRecent->fetch(PDO::FETCH_ASSOC) ?: null;
} catch (Exception $e) {
    // Fallback: generic next and recent queries without joins
    $stmtNext = $db->query("SELECT * FROM fixtures WHERE status != 'cancelled' AND match_date >= CURDATE() ORDER BY match_date ASC, match_time ASC LIMIT 1");
    $nextFixture = $stmtNext?->fetch(PDO::FETCH_ASSOC) ?: null;
    $stmtRecent = $db->query("SELECT * FROM fixtures WHERE status = 'played' AND home_score IS NOT NULL AND away_score IS NOT NULL ORDER BY match_date DESC, match_time DESC LIMIT 1");
    $recentResult = $stmtRecent?->fetch(PDO::FETCH_ASSOC) ?: null;
}

// Training attendance summary (basic placeholder: last 7 days attendance count)
try {
    $attendanceCount = (int) $db->query("SELECT COUNT(*) FROM attendance WHERE attended = 1 AND session_date >= DATE_SUB(CURDATE(), INTERVAL 7 DAY)")->fetchColumn();
} catch (Exception $e) {
    // TODO: confirm attendance schema and fields: attendance(session_id, user_id/player_id, status/attended, session_date)
    $attendanceCount = 0;
}

// Recent form and goals over last 5 matches (team-scoped)
$form = [];
$gf = 0;
$ga = 0;
try {
    $formStmt = $db->prepare("
                                SELECT f.home_score, f.away_score,
                                             ht.name AS home_team, at.name AS away_team
                                FROM fixtures f
                                LEFT JOIN teams ht ON f.home_team_id = ht.id
                                LEFT JOIN teams at ON f.away_team_id = at.id
                                WHERE f.status = 'played'
                                    AND (f.home_team_id = :team OR f.away_team_id = :team)
                                    AND f.home_score IS NOT NULL AND f.away_score IS NOT NULL
                                ORDER BY f.match_date DESC, f.match_time DESC
                                LIMIT 5
                    ");
    $formStmt->execute([':team' => $teamId]);
    $recent5 = $formStmt->fetchAll(PDO::FETCH_ASSOC);

    foreach ($recent5 as $row) {
        $isHome = ($row['home_team'] ?? '') === $clubName;
        $hs = (int)($row['home_score'] ?? 0);
        $as = (int)($row['away_score'] ?? 0);
        $our = $isHome ? $hs : $as;
        $opp = $isHome ? $as : $hs;
        $gf += $our;
        $ga += $opp;
        if ($our > $opp) {
            $form[] = 'W';
        } elseif ($our === $opp) {
            $form[] = 'D';
        } else {
            $form[] = 'L';
        }
    }
} catch (Exception $e) {
    // fallback: no form data
}
$gd = $gf - $ga;

// Upcoming training sessions (next 7 days)
$upcomingTraining = [];
try {
    $stmtTs = $db->query("SELECT id, session_date, session_time, venue, title FROM training_sessions WHERE session_date BETWEEN CURDATE() AND DATE_ADD(CURDATE(), INTERVAL 7 DAY) ORDER BY session_date ASC, session_time ASC LIMIT 4");
    $upcomingTraining = $stmtTs->fetchAll(PDO::FETCH_ASSOC);
} catch (Exception $e) {
    // TODO: Confirm training_sessions schema
    $upcomingTraining = [];
}

// Latest announcements (news)
$latestNews = $db->query("SELECT id, title, created_at FROM news ORDER BY created_at DESC LIMIT 3")->fetchAll(PDO::FETCH_ASSOC);

// Get user role for navigation
$userRoleId = $sessionContext['role_id'] ?? null;

$pageTitle = 'My Club Hub Portal';
$pageContextLabel = 'Team operations';
$pageTitleText = 'Welcome back, ' . htmlspecialchars($user['name']);
$pageBadges = [
    ['text' => $user['role_name'], 'variant' => 'gold'],
    ['text' => date('l j F Y'), 'variant' => 'neutral'],
];
$pageActions = [
    [
        'label' => 'Open Admin',
        'href' => '/auth/bridge.php?target=' . urlencode('/admin/'),
        'variant' => 'secondary',
        'icon' => 'fa-solid fa-sitemap',
        'target' => '_blank',
    ],
    [
        'label' => 'Logout',
        'href' => '/auth/logout.php',
        'variant' => 'primary',
        'icon' => 'fa-solid fa-power-off',
    ],
];

$additionalHeadContent = '';

require_once __DIR__ . '/includes/layout_start.php';

$activeNav = 'dashboard';
require_once __DIR__ . '/includes/navigation.php';
?>
<main class="flex-1 bg-slate-900/40 backdrop-blur">
    <?php require_once __DIR__ . '/includes/header.php'; ?>

    <section id="overview" class="mx-auto max-w-6xl px-6 py-10">
        <div class="grid gap-5 sm:grid-cols-2 xl:grid-cols-4">
            <div class="rounded-3xl border border-white/5 bg-slate-900/70 p-6 shadow-maroon-lg">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-xs font-semibold uppercase tracking-[0.3em] text-slate-400">Team form (last 5)</p>
                        <p class="mt-3 flex items-center gap-2 text-lg font-semibold text-cream">
                            <?php if (!empty($form)): ?>
                                <?php foreach ($form as $res): ?>
                                    <span class="inline-flex h-8 w-8 items-center justify-center rounded-full border border-white/10 <?php if ($res === 'W'): ?>bg-teal/20 text-teal<?php elseif ($res === 'D'): ?>bg-white/10 text-slate-200<?php else: ?>bg-red-900/30 text-red-300<?php endif; ?>"><?= $res; ?></span>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <span class="text-slate-400">No recent matches</span>
                            <?php endif; ?>
                        </p>
                    </div>
                </div>
            </div>
            <div class="rounded-3xl border border-white/5 bg-slate-900/70 p-6 shadow-maroon-lg">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-xs font-semibold uppercase tracking-[0.3em] text-slate-400">Goals (last 5)</p>
                        <p class="mt-3 text-3xl font-semibold text-gold"><?= number_format($gf); ?><span class="mx-2 text-base text-slate-500">GF</span> <span class="text-cream">/</span> <?= number_format($ga); ?><span class="mx-2 text-base text-slate-500">GA</span></p>
                        <p class="text-xs font-semibold uppercase tracking-[0.24em] text-slate-400">GD: <span class="text-cream"><?= ($gd >= 0 ? '+' : '') . number_format($gd); ?></span></p>
                    </div>
                </div>
            </div>
            <div class="rounded-3xl border border-white/5 bg-slate-900/70 p-6 shadow-maroon-lg">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-xs font-semibold uppercase tracking-[0.3em] text-slate-400">Upcoming matches</p>
                        <p class="mt-3 text-3xl font-semibold text-gold"><?= number_format($fixturesCount); ?></p>
                    </div>
                </div>
            </div>
            <div class="rounded-3xl border border-white/5 bg-slate-900/70 p-6 shadow-maroon-lg">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-xs font-semibold uppercase tracking-[0.3em] text-slate-400">Training (next 7 days)</p>
                        <p class="mt-3 text-3xl font-semibold text-gold"><?= number_format(count($upcomingTraining)); ?></p>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <section id="key-highlights" class="mx-auto max-w-6xl px-6 pb-8">
        <div class="grid gap-6 lg:grid-cols-3">
            <div class="rounded-3xl border border-white/5 bg-slate-900/70 p-6 shadow-maroon-lg">
                <h2 class="text-lg font-semibold text-cream">Next fixture</h2>
                <?php if ($nextFixture): ?>
                    <?php
                    $nfDate = !empty($nextFixture['match_date']) ? new DateTime($nextFixture['match_date']) : null;
                    $nfTime = !empty($nextFixture['match_time']) ? new DateTime($nextFixture['match_time']) : null;
                    // Derive opponent and venue from normalized schema when available
                    $hasTeams = isset($nextFixture['home_team']) || isset($nextFixture['away_team']);
                    if ($hasTeams) {
                        $isHomeNext = (($nextFixture['home_team'] ?? '') === ($clubName ?? ''));
                        $nfOpponent = $isHomeNext ? ($nextFixture['away_team'] ?? 'TBC') : ($nextFixture['home_team'] ?? 'TBC');
                        $nfVenue = $nextFixture['venue_name'] ?? ($isHomeNext ? 'Home' : 'Away');
                    } else {
                        $nfOpponent = $nextFixture['opponent'] ?? 'TBC';
                        $nfVenue = $nextFixture['venue'] ?? 'TBC';
                    }
                    ?>
                    <p class="mt-2 text-sm text-slate-300">My Club Hub vs <?= htmlspecialchars($nfOpponent); ?></p>
                    <p class="text-xs font-semibold uppercase tracking-[0.24em] text-slate-400">
                        <?= $nfDate ? $nfDate->format('l j F Y') : 'Date TBC'; ?>
                        <?php if ($nfTime): ?><span class="mx-2 text-slate-600">|</span><?= $nfTime->format('g:i A'); ?><?php endif; ?>
                            <br><?= htmlspecialchars($nfVenue); ?>
                    </p>
                    <div class="mt-4 text-xs font-semibold uppercase tracking-[0.24em]">
                        <a href="/portal/fixtures/" class="inline-flex items-center gap-2 rounded-full border border-white/20 px-4 py-2 text-slate-100 transition hover:bg-white/10"><i class="fa-solid fa-calendar-days text-gold"></i> View matches</a>
                    </div>
                <?php else: ?>
                    <p class="mt-2 text-sm text-slate-300">No upcoming fixture posted.</p>
                <?php endif; ?>
            </div>
            <div class="rounded-3xl border border-white/5 bg-slate-900/70 p-6 shadow-maroon-lg">
                <h2 class="text-lg font-semibold text-cream">Recent result</h2>
                <?php if ($recentResult): ?>
                    <?php
                    // Derive opponent from normalized schema when available
                    $hasTeamsR = isset($recentResult['home_team']) || isset($recentResult['away_team']);
                    if ($hasTeamsR) {
                        $isHomeRecent = (($recentResult['home_team'] ?? '') === ($clubName ?? ''));
                        $rrOpponent = $isHomeRecent ? ($recentResult['away_team'] ?? 'TBC') : ($recentResult['home_team'] ?? 'TBC');
                    } else {
                        $rrOpponent = $recentResult['opponent'] ?? 'TBC';
                    }
                    ?>
                    <p class="mt-2 text-sm text-slate-300">My Club Hub vs <?= htmlspecialchars($rrOpponent); ?></p>
                    <?php if (isset($recentResult['home_score'], $recentResult['away_score'])): ?>
                        <p class="text-2xl font-semibold text-cream mt-2">
                            <?= (int) $recentResult['home_score']; ?>
                            <span class="mx-1 text-base text-slate-500">-</span>
                            <?= (int) $recentResult['away_score']; ?>
                        </p>
                    <?php endif; ?>
                    <div class="mt-4 text-xs font-semibold uppercase tracking-[0.24em]">
                        <a href="/portal/fixtures/" class="inline-flex items-center gap-2 rounded-full border border-white/20 px-4 py-2 text-slate-100 transition hover:bg-white/10"><i class="fa-solid fa-futbol text-gold"></i> All results</a>
                    </div>
                <?php else: ?>
                    <p class="mt-2 text-sm text-slate-300">No recent result available.</p>
                <?php endif; ?>
            </div>
            <div class="rounded-3xl border border-white/5 bg-slate-900/70 p-6 shadow-maroon-lg">
                <h2 class="text-lg font-semibold text-cream">Training attendance</h2>
                <p class="mt-2 text-3xl font-semibold text-gold"><?= number_format($attendanceCount); ?></p>
                <p class="text-xs font-semibold uppercase tracking-[0.24em] text-slate-400">Attended in last 7 days</p>
                <div class="mt-4 text-xs font-semibold uppercase tracking-[0.24em]">
                    <a href="/portal/training/" class="inline-flex items-center gap-2 rounded-full border border-white/20 px-4 py-2 text-slate-100 transition hover:bg-white/10"><i class="fa-solid fa-dumbbell text-gold"></i> View training</a>
                </div>
            </div>
        </div>
    </section>

    <section id="team-updates" class="mx-auto max-w-6xl px-6 pb-16">
        <div class="grid gap-6 xl:grid-cols-3">
            <div class="xl:col-span-2 rounded-3xl border border-white/5 bg-slate-900/70 p-6 shadow-maroon-lg">
                <h2 class="text-lg font-semibold text-cream">Announcements</h2>
                <?php if (!empty($latestNews)): ?>
                    <ul class="mt-5 space-y-3 text-sm text-slate-300">
                        <?php foreach ($latestNews as $news): ?>
                            <li class="flex items-center justify-between rounded-2xl border border-white/5 px-4 py-3">
                                <span class="font-semibold"><?= htmlspecialchars($news['title']); ?></span>
                                <span class="text-xs font-semibold uppercase tracking-[0.24em] text-slate-400"><?= htmlspecialchars(date('j M Y', strtotime($news['created_at'] ?? 'now'))); ?></span>
                            </li>
                        <?php endforeach; ?>
                    </ul>
                <?php else: ?>
                    <div class="mt-4 rounded-2xl border border-dashed border-white/10 p-6 text-slate-400">No announcements posted.</div>
                <?php endif; ?>
            </div>
            <div class="rounded-3xl border border-white/5 bg-slate-900/70 p-6 shadow-maroon-lg">
                <h2 class="text-lg font-semibold text-cream">Training schedule</h2>
                <?php if (!empty($upcomingTraining)): ?>
                    <ul class="mt-5 space-y-3 text-sm text-slate-300">
                        <?php foreach ($upcomingTraining as $ts): ?>
                            <?php $dt = !empty($ts['session_date']) ? new DateTime($ts['session_date']) : null; ?>
                            <li class="flex items-center justify-between rounded-2xl border border-white/5 px-4 py-3">
                                <div>
                                    <p class="font-semibold"><?= htmlspecialchars($ts['title'] ?? 'Training'); ?></p>
                                    <p class="text-xs font-semibold uppercase tracking-[0.24em] text-slate-400">
                                        <?= $dt ? $dt->format('l j F Y') : 'Date TBC'; ?>
                                        <?php if (!empty($ts['session_time'])): ?><span class="mx-2 text-slate-600">|</span><?= htmlspecialchars($ts['session_time']); ?><?php endif; ?>
                                            <?php if (!empty($ts['venue'])): ?><span class="mx-2 text-slate-600">|</span><?= htmlspecialchars($ts['venue']); ?><?php endif; ?>
                                    </p>
                                </div>
                            </li>
                        <?php endforeach; ?>
                    </ul>
                <?php else: ?>
                    <div class="mt-4 rounded-2xl border border-dashed border-white/10 p-6 text-slate-400">No upcoming sessions posted. <span class="text-slate-500">TODO: integrate training_sessions</span></div>
                <?php endif; ?>
            </div>
        </div>
    </section>

    <!-- League Table + Top Scorer (Mock) -->
    <section id="league-and-scorers" class="mx-auto max-w-6xl px-6 pb-16">
        <div class="grid gap-6 lg:grid-cols-3">
            <!-- League Table -->
            <div class="lg:col-span-2 rounded-3xl border border-white/5 bg-slate-900/70 p-6 shadow-maroon-lg">
                <div class="flex items-center justify-between">
                    <h2 class="text-lg font-semibold text-cream">League Table</h2>
                    <div class="flex items-center gap-2">
                        <span class="rounded-full border border-white/10 bg-slate-800/70 px-3 py-1 text-xs font-semibold uppercase tracking-[0.24em] text-slate-300">2025/26</span>
                        <span class="text-[10px] uppercase tracking-[0.24em] text-slate-500">TODO: scrape from WOSFL</span>
                    </div>
                </div>
                <div class="mt-5">
                    <!-- Header -->
                    <div class="grid grid-cols-[48px_1fr_72px_64px_64px_120px_80px] items-center gap-2 rounded-2xl border border-white/10 bg-slate-800/50 px-4 py-2 text-[10px] font-semibold uppercase tracking-[0.24em] text-slate-400">
                        <div>Position</div>
                        <div>Club</div>
                        <div class="text-right">Played</div>
                        <div class="text-right">GA</div>
                        <div class="text-right">Pts</div>
                        <div class="text-center">Form</div>
                        <div class="text-center">Next</div>
                    </div>
                    <!-- Rows (mock data) -->
                    <ul class="mt-2 space-y-2">
                        <?php
                        // TODO: Replace with live WOSFL scrape + cache
                        $leagueRows = [
                            ['pos' => 1, 'delta' => '+', 'club' => 'My Club Hub', 'played' => 9, 'ga' => 7, 'pts' => 22, 'form' => ['W', 'W', 'D', 'W', 'W'], 'next' => 'DAR'],
                            ['pos' => 2, 'delta' => '–', 'club' => 'Darvel', 'played' => 10, 'ga' => 10, 'pts' => 21, 'form' => ['D', 'W', 'D', 'W', 'L'], 'next' => 'ARB'],
                            ['pos' => 3, 'delta' => '▼', 'club' => 'Auchinleck Talbot', 'played' => 10, 'ga' => 12, 'pts' => 19, 'form' => ['L', 'L', 'W', 'W', 'D'], 'next' => 'KIL'],
                            ['pos' => 4, 'delta' => '▲', 'club' => 'Cumnock Juniors', 'played' => 9, 'ga' => 9, 'pts' => 17, 'form' => ['W', 'L', 'W', 'D', 'W'], 'next' => 'BEI'],
                        ];
                        foreach ($leagueRows as $r):
                        ?>
                            <li class="grid grid-cols-[48px_1fr_72px_64px_64px_120px_80px] items-center gap-2 rounded-2xl border border-white/5 bg-slate-900/60 px-4 py-2">
                                <div class="flex items-center gap-2 text-slate-300">
                                    <span class="inline-flex h-6 w-6 items-center justify-center rounded-full border border-white/10 bg-slate-800 text-[10px] font-bold text-cream"><?= (int)$r['pos']; ?></span>
                                    <span class="text-[10px] <?php echo $r['delta'] === '▲' ? 'text-emerald-400' : ($r['delta'] === '▼' ? 'text-rose-400' : 'text-slate-500'); ?>"><?= htmlspecialchars($r['delta']); ?></span>
                                </div>
                                <div class="truncate text-sm font-semibold text-cream"><?= htmlspecialchars($r['club']); ?></div>
                                <div class="text-right text-sm text-slate-300"><?= (int)$r['played']; ?></div>
                                <div class="text-right text-sm text-slate-300"><?= (int)$r['ga']; ?></div>
                                <div class="text-right text-sm font-semibold text-gold"><?= (int)$r['pts']; ?></div>
                                <div class="flex items-center justify-center gap-1">
                                    <?php foreach ($r['form'] as $res):
                                        $cls = $res === 'W' ? 'bg-emerald-500/20 border-emerald-400 text-emerald-300' : ($res === 'L' ? 'bg-rose-500/20 border-rose-400 text-rose-300' : 'bg-slate-500/20 border-slate-400 text-slate-300');
                                    ?>
                                        <span class="inline-flex h-6 w-6 items-center justify-center rounded-full border <?= $cls; ?> text-[10px] font-bold"><?= $res; ?></span>
                                    <?php endforeach; ?>
                                </div>
                                <div class="flex items-center justify-center">
                                    <span class="inline-flex min-w-[40px] items-center justify-center rounded-full border border-white/10 bg-slate-800 px-2 py-1 text-[10px] font-semibold text-slate-300"><?= htmlspecialchars($r['next']); ?></span>
                                </div>
                            </li>
                        <?php endforeach; ?>
                    </ul>
                </div>
            </div>

            <!-- Top Scorer -->
            <div class="rounded-3xl border border-white/5 bg-slate-900/70 p-6 shadow-maroon-lg">
                <div class="flex items-center justify-between">
                    <h2 class="text-lg font-semibold text-cream">Top Scorer</h2>
                    <a href="#" class="text-[10px] font-semibold uppercase tracking-[0.24em] text-slate-400">view all</a>
                </div>
                <div class="mt-4 rounded-2xl border border-white/10 bg-gradient-to-br from-violet-600/20 to-indigo-600/10 p-4">
                    <div class="flex items-center gap-4">
                        <div class="h-14 w-14 flex-shrink-0 rounded-full border border-white/10 bg-white/10"></div>
                        <div>
                            <div class="text-sm font-semibold text-cream">Romelu Lukaku</div>
                            <div class="text-xs font-semibold uppercase tracking-[0.24em] text-slate-300"><span class="text-gold">16</span> goals</div>
                        </div>
                    </div>
                </div>
                <ul class="mt-4 space-y-2 text-sm">
                    <li class="flex items-center justify-between rounded-2xl border border-white/5 bg-slate-900/60 px-4 py-3">
                        <div class="flex items-center gap-3">
                            <div class="h-8 w-8 rounded-full border border-white/10 bg-white/10"></div>
                            <span class="text-cream">Bruno Fernandes</span>
                        </div>
                        <span class="text-slate-300">12 goals</span>
                    </li>
                    <li class="flex items-center justify-between rounded-2xl border border-white/5 bg-slate-900/60 px-4 py-3">
                        <div class="flex items-center gap-3">
                            <div class="h-8 w-8 rounded-full border border-white/10 bg-white/10"></div>
                            <span class="text-cream">Mason Mount</span>
                        </div>
                        <span class="text-slate-300">11 goals</span>
                    </li>
                    <li class="flex items-center justify-between rounded-2xl border border-white/5 bg-slate-900/60 px-4 py-3">
                        <div class="flex items-center gap-3">
                            <div class="h-8 w-8 rounded-full border border-white/10 bg-white/10"></div>
                            <span class="text-cream">Kei Kamara</span>
                        </div>
                        <span class="text-slate-300">9 goals</span>
                    </li>
                    <li class="flex items-center justify-between rounded-2xl border border-white/5 bg-slate-900/60 px-4 py-3">
                        <div class="flex items-center gap-3">
                            <div class="h-8 w-8 rounded-full border border-white/10 bg-white/10"></div>
                            <span class="text-cream">John McGinn</span>
                        </div>
                        <span class="text-slate-300">8 goals</span>
                    </li>
                </ul>
                <p class="mt-3 text-[10px] uppercase tracking-[0.24em] text-slate-500">TODO: replace mock with WOSFL top scorers feed</p>
            </div>
        </div>
    </section>

    <?php require_once __DIR__ . '/includes/footer.php'; ?>
</main>
<?php require_once __DIR__ . '/includes/layout_end.php'; ?>
