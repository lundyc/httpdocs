<?php
require_once __DIR__ . '/../../shared/includes/session_init.php';
require_once __DIR__ . '/../../shared/includes/simple_session.php';
require_once __DIR__ . '/../../portal/auth/auth_functions.php';
require_once __DIR__ . '/../../portal/auth/middleware.php';

checkRole([1, 2, 3]);
$db = getDB();

/* -------------------------------------------------------------
   CLUB CONTEXT
------------------------------------------------------------- */
$teamId = CLUB_TEAM_ID;
$stmtClub = $db->prepare("SELECT name FROM teams WHERE id = :id");
$stmtClub->execute([':id' => $teamId]);
$clubName = $stmtClub->fetchColumn() ?: 'Unknown Club';

/* -------------------------------------------------------------
   FETCH MATCHES + VEO STATUS
------------------------------------------------------------- */
$sql = "
    SELECT f.id, f.match_date, f.match_time, f.home_score, f.away_score, f.status,
           s.name AS season_name, c.name AS competition_name,
           ht.name AS home_team, at.name AS away_team,
           v.name AS venue_name,
           vm.id AS veo_id, vm.status AS veo_status
    FROM fixtures f
    LEFT JOIN seasons s ON f.season_id = s.id
    LEFT JOIN competitions c ON f.competition_id = c.id
    LEFT JOIN teams ht ON f.home_team_id = ht.id
    LEFT JOIN teams at ON f.away_team_id = at.id
    LEFT JOIN venues v ON f.venue_id = v.id
    LEFT JOIN veo_matches vm ON vm.fixture_id = f.id
    WHERE (f.home_team_id = :team_id OR f.away_team_id = :team_id)
    ORDER BY f.match_date DESC, f.match_time DESC
";
$stmt = $db->prepare($sql);
$stmt->execute([':team_id' => $teamId]);
$matches = $stmt->fetchAll(PDO::FETCH_ASSOC);

/* -------------------------------------------------------------
   PAGE CHROME
------------------------------------------------------------- */
$pageTitle       = 'Match Management - My Club Hub Admin';
$pageContextLabel = 'Match operations';
$pageTitleText   = "Match Centre – $clubName";

require_once __DIR__ . '/../includes/layout_start.php';
$activeNav = 'matches';
require_once __DIR__ . '/../includes/navigation.php';
?>
<main class="flex-1 bg-slate-900/40 backdrop-blur">
          <?php require_once __DIR__ . '/../includes/header.php'; ?>

          <section class="mx-auto max-w-6xl px-6 py-10">
                    <div class="rounded-3xl border border-white/5 bg-slate-900/70 p-6 shadow-maroon-lg">

                              <div class="flex flex-col gap-4 border-b border-white/10 pb-5 md:flex-row md:items-center md:justify-between">
                                        <div>
                                                  <h2 class="text-xl font-semibold text-cream">Match Management – <?= htmlspecialchars($clubName); ?></h2>
                                                  <p class="mt-1 text-xs uppercase tracking-[0.24em] text-slate-400">Sorted by most recent fixtures first</p>
                                        </div>
                              </div>

                              <?php if (empty($matches)): ?>
                                        <div class="mt-6 rounded-2xl border border-white/5 bg-slate-900/60 p-6 text-center text-sm text-slate-300">
                                                  No matches found for <?= htmlspecialchars($clubName); ?>.
                                        </div>
                              <?php else: ?>
                                        <div class="mt-6 overflow-x-auto">
                                                  <table class="w-full border-separate border-spacing-y-2 text-sm">
                                                            <thead class="text-xs uppercase tracking-[0.2em] text-slate-400 bg-slate-900/80">
                                                                      <tr class="text-left">
                                                                                <th class="px-4 py-3">Match</th>
                                                                                <th class="px-4 py-3 w-[10%] text-center">Date</th>
                                                                                <th class="px-4 py-3 w-[10%] text-center">Result</th>
                                                                                <th class="px-4 py-3 w-[10%] text-center">Competition</th>
                                                                                <th class="px-4 py-3 w-[10%] text-center">Season</th>
                                                                                <th class="px-4 py-3 w-[10%] text-center">Venue</th>
                                                                                <th class="px-4 py-3 w-[10%] text-center">VEO</th>
                                                                                <th class="px-4 py-3 text-right">Actions</th>
                                                                      </tr>
                                                            </thead>
                                                            <tbody class="text-slate-200">
                                                                      <?php foreach ($matches as $m): ?>
                                                                                <?php
                                                                                $dateLabel = !empty($m['match_date']) ? date('d/m/Y', strtotime($m['match_date'])) : '—';
                                                                                $timeLabel = !empty($m['match_time']) ? substr($m['match_time'], 0, 5) : '—';
                                                                                $status    = strtolower($m['status'] ?? '');
                                                                                $homeScore = $m['home_score'] ?? '-';
                                                                                $awayScore = $m['away_score'] ?? '-';

                                                                                $statusClass = match ($status) {
                                                                                          'played'                  => 'bg-emerald-500/20 text-emerald-300 border border-emerald-400',
                                                                                          'scheduled', 'upcoming'    => 'bg-amber-500/20 text-amber-300 border border-amber-400',
                                                                                          'postponed'               => 'bg-rose-500/20 text-rose-300 border border-rose-400',
                                                                                          'cancelled'               => 'bg-gray-500/20 text-gray-300 border border-gray-400',
                                                                                          'abandoned'               => 'bg-red-700/30 text-red-300 border border-red-400',
                                                                                          default                   => 'bg-slate-600/20 text-slate-300 border border-slate-400',
                                                                                };

                                                                                $veoStatus = $m['veo_id']
                                                                                          ? '<span class="inline-flex items-center justify-center rounded-full bg-emerald-500/20 text-emerald-300 border border-emerald-400 px-2 py-0.5 text-xs font-semibold">Linked</span>'
                                                                                          : '<span class="inline-flex items-center justify-center rounded-full bg-slate-500/20 text-slate-300 border border-slate-400 px-2 py-0.5 text-xs font-semibold">None</span>';
                                                                                ?>
                                                                                <tr class="bg-slate-900/60 hover:bg-slate-800/60 rounded-xl transition">
                                                                                          <td class="px-4 py-3 font-semibold text-cream text-left">
                                                                                                    <div class="flex flex-col items-start">
                                                                                                              <div>
                                                                                                                        <span class="inline-flex items-center justify-center rounded-md bg-gold/20 text-gold text-xs px-2 py-0.5 mr-2 font-semibold">
                                                                                                                                  <?= htmlspecialchars($homeScore); ?>
                                                                                                                        </span>
                                                                                                                        <?= htmlspecialchars($m['home_team']); ?>
                                                                                                              </div>
                                                                                                              <div>
                                                                                                                        <span class="inline-flex items-center justify-center rounded-md bg-gold/20 text-gold text-xs px-2 py-0.5 mr-2 font-semibold">
                                                                                                                                  <?= htmlspecialchars($awayScore); ?>
                                                                                                                        </span>
                                                                                                                        <?= htmlspecialchars($m['away_team']); ?>
                                                                                                              </div>
                                                                                                    </div>
                                                                                          </td>
                                                                                          <td class="px-4 py-3 text-center"><?= $dateLabel ?><br><span class="text-xs text-slate-400"><?= $timeLabel ?></span></td>
                                                                                          <td class="px-4 py-3 text-center">
                                                                                                    <span class="inline-flex items-center justify-center rounded-full <?= $statusClass ?> px-2 py-0.5 text-xs font-semibold uppercase tracking-wide">
                                                                                                              <?= ucfirst($status); ?>
                                                                                                    </span>
                                                                                          </td>
                                                                                          <td class="px-4 py-3 text-center"><span class="text-xs text-slate-400"><?= htmlspecialchars($m['competition_name']); ?></span></td>
                                                                                          <td class="px-4 py-3 text-center"><?= htmlspecialchars($m['season_name']); ?></td>
                                                                                          <td class="px-4 py-3 text-center"><?= htmlspecialchars($m['venue_name']); ?></td>
                                                                                          <td class="px-4 py-3 text-center"><?= $veoStatus; ?></td>
                                                                                          <td class="px-4 py-3 text-right">
                                                                                                    <div class="flex justify-end items-center gap-2">
                                                                                                              <a href="/admin/matches/view.php?id=<?= (int)$m['id']; ?>"
                                                                                                                        class="inline-flex items-center justify-center rounded-full border border-white/20 w-8 h-8 text-slate-300 hover:text-gold hover:border-gold/40 transition"
                                                                                                                        title="View Match"><i class="fa-solid fa-eye"></i></a>
                                                                                                              <a href="/admin/matches/edit.php?id=<?= (int)$m['id']; ?>"
                                                                                                                        class="inline-flex items-center justify-center rounded-full border border-white/20 w-8 h-8 text-slate-300 hover:text-gold hover:border-gold/40 transition"
                                                                                                                        title="Edit Match"><i class="fa-solid fa-pen-to-square"></i></a>
                                                                                                              <?php if (!$m['veo_id']): ?>
                                                                                                                        <a href="/admin/veo/veo_link.php?fixture_id=<?= (int)$m['id']; ?>"
                                                                                                                                  class="inline-flex items-center justify-center rounded-full border border-emerald-400/30 w-8 h-8 text-emerald-300 hover:bg-emerald-500/20 transition"
                                                                                                                                  title="Link VEO"><i class="fa-solid fa-video"></i></a>
                                                                                                              <?php endif; ?>
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
</main>
<?php require_once __DIR__ . '/../includes/layout_end.php'; ?>