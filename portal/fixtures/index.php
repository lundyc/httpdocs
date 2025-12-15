<?php
require_once __DIR__ . '/../auth/auth_functions.php';
require_once __DIR__ . '/../auth/middleware.php';

requireLogin();

$db = getDB();
$sessionContext = $GLOBALS['myclubhub_session'] ?? [];
$currentUserId = $sessionContext['user_id'] ?? null;
$stmt = $db->prepare("SELECT u.name, u.email, u.role_id, r.name AS role_name FROM users u JOIN roles r ON u.role_id = r.id WHERE u.id = ?");
$stmt->execute([$currentUserId]);
$user = $stmt->fetch(PDO::FETCH_ASSOC) ?: ['name' => 'Team Member', 'email' => '', 'role_id' => 99, 'role_name' => 'Club Staff'];

// Filters
$competition = trim((string) ($_GET['competition'] ?? ''));
$venueFilter = trim((string) ($_GET['venue'] ?? ''));

$where = ["status != 'cancelled'"];
$params = [];
if ($competition !== '') {
          $where[] = 'competition = ?';
          $params[] = $competition;
}
if ($venueFilter !== '') {
          if (in_array(strtolower($venueFilter), ['home', 'away'], true)) {
                    $where[] = 'home_away = ?';
                    $params[] = strtolower($venueFilter) === 'home' ? 'home' : 'away';
          }
}
$whereSql = 'WHERE ' . implode(' AND ', $where);

$stmtF = $db->prepare("SELECT * FROM fixtures {$whereSql} ORDER BY match_date ASC, match_time ASC");
$stmtF->execute($params);
$fixtures = $stmtF->fetchAll(PDO::FETCH_ASSOC);

$now = new DateTime();
$upcoming = [];
$past = [];
foreach ($fixtures as $fx) {
          $dt = !empty($fx['match_date']) ? new DateTime($fx['match_date'] . ' ' . ($fx['match_time'] ?? '00:00:00')) : clone $now;
          if ($dt >= $now) {
                    $upcoming[] = $fx;
          } else {
                    $past[] = $fx;
          }
}

$competitions = $db->query("SELECT DISTINCT competition FROM fixtures WHERE competition IS NOT NULL AND competition != '' ORDER BY competition ASC")->fetchAll(PDO::FETCH_COLUMN);

$userRoleId = $sessionContext['role_id'] ?? null;
$isManager = ($userRoleId !== null && $userRoleId <= 3); // 1=Super,2=Admin,3=Manager

$pageTitle = 'Fixtures · My Club Hub Portal';
$pageContextLabel = 'Team operations';
$pageTitleText = 'Fixtures';
$pageBadges = [
          ['text' => $user['role_name'], 'variant' => 'gold'],
          ['text' => date('l j F Y'), 'variant' => 'neutral'],
];
$pageActions = [
          ['label' => 'Back to dashboard', 'href' => '/portal/', 'variant' => 'secondary', 'icon' => 'fa-solid fa-arrow-left'],
          ['label' => 'Logout', 'href' => '/portal/auth/logout.php', 'variant' => 'primary', 'icon' => 'fa-solid fa-power-off'],
];

$additionalHeadContent = '';

require_once __DIR__ . '/../includes/layout_start.php';

$activeNav = 'fixtures';
require_once __DIR__ . '/../includes/navigation.php';
?>
<main class="flex-1 bg-slate-900/40 backdrop-blur">
          <?php require_once __DIR__ . '/../includes/header.php'; ?>

          <section class="mx-auto max-w-6xl px-6 py-10">
                    <div class="rounded-3xl border border-white/5 bg-slate-900/70 p-6 shadow-maroon-lg">
                              <h2 class="text-lg font-semibold text-cream">Filters</h2>
                              <form method="get" class="mt-6 grid gap-4 md:grid-cols-3">
                                        <label class="text-xs font-semibold uppercase tracking-[0.24em] text-slate-400">
                                                  Competition
                                                  <select name="competition" class="mt-2 w-full rounded-2xl border border-white/10 bg-slate-800 px-4 py-2 text-sm text-cream focus:outline-none focus:ring-2 focus:ring-gold/30">
                                                            <option value="">All competitions</option>
                                                            <?php foreach ($competitions as $opt): ?>
                                                                      <option value="<?= htmlspecialchars($opt); ?>" <?= $competition === $opt ? 'selected' : ''; ?>><?= htmlspecialchars($opt); ?></option>
                                                            <?php endforeach; ?>
                                                  </select>
                                        </label>
                                        <label class="text-xs font-semibold uppercase tracking-[0.24em] text-slate-400">
                                                  Venue
                                                  <select name="venue" class="mt-2 w-full rounded-2xl border border-white/10 bg-slate-800 px-4 py-2 text-sm text-cream focus:outline-none focus:ring-2 focus:ring-gold/30">
                                                            <option value="">Home and away</option>
                                                            <option value="home" <?= strtolower($venueFilter) === 'home' ? 'selected' : ''; ?>>Home fixtures</option>
                                                            <option value="away" <?= strtolower($venueFilter) === 'away' ? 'selected' : ''; ?>>Away fixtures</option>
                                                  </select>
                                        </label>
                                        <div class="flex items-end gap-3">
                                                  <button type="submit" class="inline-flex items-center gap-2 rounded-full border border-gold/30 bg-gold px-5 py-2 text-xs font-semibold uppercase tracking-[0.24em] text-maroon transition hover:bg-gold/90">Apply</button>
                                                  <a href="/portal/fixtures/" class="inline-flex items-center gap-2 rounded-full border border-white/20 px-5 py-2 text-xs font-semibold uppercase tracking-[0.24em] text-slate-100 transition hover:bg-white/10">Reset</a>
                                        </div>
                              </form>
                    </div>
          </section>

          <section class="mx-auto max-w-6xl px-6 pb-12">
                    <h3 class="text-base font-semibold text-cream">Upcoming</h3>
                    <?php if (!empty($upcoming)): ?>
                              <div class="mt-4 grid gap-4">
                                        <?php foreach ($upcoming as $fx): ?>
                                                  <?php
                                                  $date = !empty($fx['match_date']) ? new DateTime($fx['match_date']) : null;
                                                  $time = !empty($fx['match_time']) ? new DateTime($fx['match_time']) : null;
                                                  $venue = !empty($fx['venue']) ? $fx['venue'] : (((($fx['home_away'] ?? 'home') === 'home') ? 'Home' : 'Away'));
                                                  ?>
                                                  <article class="flex flex-col gap-4 rounded-3xl border border-white/5 bg-slate-900/70 px-6 py-5 shadow-lg transition hover:-translate-y-1 hover:shadow-maroon-lg md:flex-row md:items-center md:justify-between">
                                                            <div class="space-y-2">
                                                                      <p class="text-xs font-semibold uppercase tracking-[0.28em] text-slate-400">
                                                                                <?= htmlspecialchars($fx['competition'] ?? 'Competition TBC'); ?>
                                                                      </p>
                                                                      <h4 class="text-lg font-semibold text-cream">My Club Hub vs <?= htmlspecialchars($fx['opponent'] ?? 'TBC'); ?></h4>
                                                                      <p class="text-xs font-semibold uppercase tracking-[0.24em] text-slate-400">
                                                                                <?= $date ? $date->format('l j F Y') : 'Date TBC'; ?>
                                                                                <?php if ($time): ?>
                                                                                          <span class="mx-2 text-slate-600">|</span><?= $time->format('g:i A'); ?>
                                                                                <?php endif; ?>
                                                                                <span class="mx-2 text-slate-600">|</span><?= htmlspecialchars($venue); ?>
                                                                      </p>
                                                            </div>
                                                            <?php if ($isManager): ?>
                                                                      <div class="flex flex-wrap gap-2 text-xs font-semibold uppercase tracking-[0.24em]">
                                                                                <a href="#" class="inline-flex items-center gap-2 rounded-full border border-white/20 px-4 py-2 text-slate-100 transition hover:bg-white/10"><i class="fa-solid fa-clipboard-check text-gold"></i> Mark squad</a>
                                                                                <a href="#" class="inline-flex items-center gap-2 rounded-full border border-white/20 px-4 py-2 text-slate-100 transition hover:bg-white/10"><i class="fa-solid fa-pen text-gold"></i> Edit</a>
                                                                      </div>
                                                            <?php endif; ?>
                                                  </article>
                                        <?php endforeach; ?>
                              </div>
                    <?php else: ?>
                              <div class="mt-4 rounded-3xl border border-dashed border-white/10 bg-slate-900/50 p-10 text-center">
                                        <p class="text-slate-300">No upcoming fixtures.</p>
                              </div>
                    <?php endif; ?>
          </section>

          <section class="mx-auto max-w-6xl px-6 pb-16">
                    <h3 class="text-base font-semibold text-cream">Recent results</h3>
                    <?php if (!empty($past)): ?>
                              <div class="mt-4 grid gap-4">
                                        <?php foreach ($past as $fx): ?>
                                                  <?php
                                                  $date = !empty($fx['match_date']) ? new DateTime($fx['match_date']) : null;
                                                  $venue = !empty($fx['venue']) ? $fx['venue'] : (((($fx['home_away'] ?? 'home') === 'home') ? 'Home' : 'Away'));
                                                  $hasScore = isset($fx['home_score'], $fx['away_score']) && $fx['home_score'] !== null && $fx['away_score'] !== null;
                                                  ?>
                                                  <article class="flex flex-col gap-4 rounded-3xl border border-white/5 bg-slate-900/70 px-6 py-5 shadow-lg transition hover:-translate-y-1 hover:shadow-maroon-lg md:flex-row md:items-center md:justify-between">
                                                            <div class="space-y-2">
                                                                      <p class="text-xs font-semibold uppercase tracking-[0.28em] text-slate-400">
                                                                                <?= htmlspecialchars($fx['competition'] ?? 'Competition TBC'); ?>
                                                                      </p>
                                                                      <h4 class="text-lg font-semibold text-cream">My Club Hub vs <?= htmlspecialchars($fx['opponent'] ?? 'TBC'); ?></h4>
                                                                      <p class="text-xs font-semibold uppercase tracking-[0.24em] text-slate-400">
                                                                                <?= $date ? $date->format('l j F Y') : 'Date TBC'; ?>
                                                                                <span class="mx-2 text-slate-600">|</span><?= htmlspecialchars($venue); ?>
                                                                      </p>
                                                            </div>
                                                            <div class="flex items-center gap-4">
                                                                      <?php if ($hasScore): ?>
                                                                                <div class="rounded-2xl border border-white/10 bg-slate-800 px-4 py-3 text-center">
                                                                                          <p class="text-xs font-semibold uppercase tracking-[0.24em] text-slate-400">Final score</p>
                                                                                          <p class="text-2xl font-semibold text-cream">
                                                                                                    <?= (int) $fx['home_score']; ?>
                                                                                                    <span class="mx-1 text-base text-slate-500">-</span>
                                                                                                    <?= (int) $fx['away_score']; ?>
                                                                                          </p>
                                                                                </div>
                                                                      <?php endif; ?>
                                                                      <?php if ($isManager): ?>
                                                                                <a href="#" class="inline-flex items-center gap-2 rounded-full border border-white/20 px-4 py-2 text-xs font-semibold uppercase tracking-[0.24em] text-slate-100 transition hover:bg-white/10"><i class="fa-solid fa-futbol text-gold"></i> Enter result</a>
                                                                      <?php endif; ?>
                                                            </div>
                                                  </article>
                                        <?php endforeach; ?>
                              </div>
                    <?php else: ?>
                              <div class="mt-4 rounded-3xl border border-dashed border-white/10 bg-slate-900/50 p-10 text-center">
                                        <p class="text-slate-300">No past fixtures with results.</p>
                              </div>
                    <?php endif; ?>
          </section>

          <?php require_once __DIR__ . '/../includes/footer.php'; ?>
</main>
<?php require_once __DIR__ . '/../includes/layout_end.php'; ?>

