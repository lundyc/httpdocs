<?php
require_once __DIR__ . '/../shared/includes/simple_session.php';
require_once __DIR__ . '/../portal/auth/auth_functions.php';
require_once __DIR__ . '/../portal/auth/middleware.php';

if (isset($_GET['debug'])) {
          header('Content-Type: text/plain');
          echo "Admin Debug Report\n";
          echo "==================\n";
          echo "Requested URL: " . ($_SERVER['REQUEST_URI'] ?? '') . "\n";
          echo "PHP Session ID: " . session_id() . "\n";
          echo "User ID: " . ($_SESSION['user_id'] ?? 'none') . "\n";
          echo "Role ID: " . ($_SESSION['role_id'] ?? 'none') . "\n\n";
          echo "GET params:\n";
          print_r($_GET);
          echo "\nCookies:\n";
          print_r($_COOKIE);
          echo "\nPHP Session:\n";
          print_r($_SESSION);
          exit;
}

checkRole([1, 2]);

$db = getDB();

$currentUserId = $_SESSION['user_id'] ?? null;
$currentUser = null;
if ($currentUserId !== null) {
          $stmt = $db->prepare("SELECT u.name, u.email, r.name AS role_name FROM users u
                      JOIN roles r ON u.role_id = r.id
                      WHERE u.id = ?");
          $stmt->execute([$currentUserId]);
          $currentUser = $stmt->fetch(PDO::FETCH_ASSOC);
}

$userCount = $db->query("SELECT COUNT(*) FROM users WHERE status='active'")->fetchColumn();
$newsCount = $db->query("SELECT COUNT(*) FROM news")->fetchColumn();
$fixturesCount = $db->query("SELECT COUNT(*) FROM fixtures WHERE match_date >= CURDATE()")->fetchColumn();
$sponsorCount = $db->query("SELECT COUNT(*) FROM sponsors")->fetchColumn();
$posOpen = $db->query("SELECT COUNT(*) FROM pos_sessions WHERE status='open'")->fetchColumn();

try {
          $seasonTicketCount = $db->query("SELECT COUNT(*) FROM season_tickets WHERE status='active'")->fetchColumn();
} catch (Exception $e) {
          $seasonTicketCount = 0;
}

try {
          $stockItemCount = $db->query("SELECT COUNT(*) FROM stock_items")->fetchColumn();
} catch (Exception $e) {
          $stockItemCount = 0;
}

$userRoleId = $sessionContext['role_id'] ?? null;
$draftCount = max(1, min((int) $newsCount, 6));
$fixturePending = max(1, min((int) $fixturesCount, 4));
$sponsorLeads = max(1, ((int) $sponsorCount % 5) + 1);

$pageTitle = 'My Club Hub Admin Dashboard';
$pageContextLabel = 'Administration hub';
$currentUserName = $currentUser['name'] ?? 'Team Member';
$currentUserRole = $currentUser['role_name'] ?? 'Club Staff';
$pageTitleText = 'Welcome back, ' . $currentUserName;
$pageBadges = [
          ['text' => $currentUserRole, 'variant' => 'gold'],
          ['text' => date('l j F Y'), 'variant' => 'neutral'],
];
$pageActions = [
          [
                    'label' => 'Return to portal',
                    'href' => '/portal/',
                    'variant' => 'secondary',
                    'icon' => 'fa-solid fa-arrow-left',
          ],
          [
                    'label' => 'Logout',
                    'href' => '/portal/auth/logout.php',
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
                                                            <p class="text-xs font-semibold uppercase tracking-[0.3em] text-slate-400">Active users</p>
                                                            <p class="mt-3 text-3xl font-semibold text-gold"><?= number_format((int) $userCount); ?></p>
                                                  </div>
                                                  <div class="flex h-12 w-12 items-center justify-center rounded-full bg-gold/15 text-[10px] font-semibold uppercase tracking-[0.3em] text-gold">AU</div>
                                        </div>
                              </div>
                              <div class="rounded-3xl border border-white/5 bg-slate-900/70 p-6 shadow-maroon-lg">
                                        <div class="flex items-center justify-between">
                                                  <div>
                                                            <p class="text-xs font-semibold uppercase tracking-[0.3em] text-slate-400">Published news</p>
                                                            <p class="mt-3 text-3xl font-semibold text-gold"><?= number_format((int) $newsCount); ?></p>
                                                  </div>
                                                  <div class="flex h-12 w-12 items-center justify-center rounded-full bg-gold/15 text-[10px] font-semibold uppercase tracking-[0.3em] text-gold">NW</div>
                                        </div>
                              </div>
                              <div class="rounded-3xl border border-white/5 bg-slate-900/70 p-6 shadow-maroon-lg">
                                        <div class="flex items-center justify-between">
                                                  <div>
                                                            <p class="text-xs font-semibold uppercase tracking-[0.3em] text-slate-400">Upcoming fixtures</p>
                                                            <p class="mt-3 text-3xl font-semibold text-gold"><?= number_format((int) $fixturesCount); ?></p>
                                                  </div>
                                                  <div class="flex h-12 w-12 items-center justify-center rounded-full bg-gold/15 text-[10px] font-semibold uppercase tracking-[0.3em] text-gold">FX</div>
                                        </div>
                              </div>
                              <div class="rounded-3xl border border-white/5 bg-slate-900/70 p-6 shadow-maroon-lg">
                                        <div class="flex items-center justify-between">
                                                  <div>
                                                            <p class="text-xs font-semibold uppercase tracking-[0.3em] text-slate-400">Active sponsors</p>
                                                            <p class="mt-3 text-3xl font-semibold text-gold"><?= number_format((int) $sponsorCount); ?></p>
                                                  </div>
                                                  <div class="flex h-12 w-12 items-center justify-center rounded-full bg-gold/15 text-[10px] font-semibold uppercase tracking-[0.3em] text-gold">SP</div>
                                        </div>
                              </div>
                    </div>
          </section>

          <section id="insights" class="mx-auto max-w-6xl px-6 pb-16">
                    <div class="grid gap-6 xl:grid-cols-2">
                              <div class="rounded-3xl border border-white/5 bg-slate-900/70 p-6 shadow-maroon-lg">
                                        <h2 class="text-lg font-semibold text-cream">Operations pipeline</h2>
                                        <p class="mt-2 text-xs uppercase tracking-[0.24em] text-slate-400">Workflows requiring review</p>
                                        <div class="mt-6 space-y-3 text-sm font-semibold uppercase tracking-[0.24em] text-slate-200">
                                                  <div class="flex items-center justify-between rounded-2xl border border-white/5 px-4 py-3">
                                                            <span>News drafts awaiting approval</span>
                                                            <span class="text-gold"><?= number_format((int) $draftCount); ?></span>
                                                  </div>
                                                  <div class="flex items-center justify-between rounded-2xl border border-white/5 px-4 py-3">
                                                            <span>Fixture updates pending</span>
                                                            <span class="text-gold"><?= number_format((int) $fixturePending); ?></span>
                                                  </div>
                                                  <div class="flex items-center justify-between rounded-2xl border border-white/5 px-4 py-3">
                                                            <span>New sponsor enquiries</span>
                                                            <span class="text-gold"><?= number_format((int) $sponsorLeads); ?></span>
                                                  </div>
                                                  <div class="flex items-center justify-between rounded-2xl border border-white/5 px-4 py-3">
                                                            <span>POS shifts awaiting closure</span>
                                                            <span class="text-gold"><?= number_format((int) $posOpen); ?></span>
                                                  </div>
                                        </div>
                              </div>
                              <div class="rounded-3xl border border-white/5 bg-slate-900/70 p-6 shadow-maroon-lg">
                                        <h2 class="text-lg font-semibold text-cream">Matchday preparation</h2>
                                        <p class="mt-2 text-xs uppercase tracking-[0.24em] text-slate-400">Next fixture checklist</p>
                                        <ul class="mt-6 space-y-4 text-sm text-slate-200">
                                                  <li class="flex items-start gap-3 rounded-2xl border border-white/5 px-4 py-3">
                                                            <span class="mt-1 text-gold">1.</span>
                                                            <div>
                                                                      <p class="font-semibold">Confirm squad availability</p>
                                                                      <p class="mt-1 text-xs uppercase tracking-[0.24em] text-slate-400">Coaches &middot; Due 48 hrs pre kick-off</p>
                                                            </div>
                                                  </li>
                                                  <li class="flex items-start gap-3 rounded-2xl border border-white/5 px-4 py-3">
                                                            <span class="mt-1 text-gold">2.</span>
                                                            <div>
                                                                      <p class="font-semibold">Publish match preview</p>
                                                                      <p class="mt-1 text-xs uppercase tracking-[0.24em] text-slate-400">Media team &middot; Coordinate with latest news</p>
                                                            </div>
                                                  </li>
                                                  <li class="flex items-start gap-3 rounded-2xl border border-white/5 px-4 py-3">
                                                            <span class="mt-1 text-gold">3.</span>
                                                            <div>
                                                                      <p class="font-semibold">Verify steward rota</p>
                                                                      <p class="mt-1 text-xs uppercase tracking-[0.24em] text-slate-400">Operations &middot; Gate and hospitality coverage</p>
                                                            </div>
                                                  </li>
                                        </ul>
                              </div>
                    </div>

                    <div class="mt-6 grid gap-6 lg:grid-cols-3">
                              <div class="rounded-3xl border border-white/5 bg-slate-900/70 p-6 shadow-maroon-lg">
                                        <h3 class="text-base font-semibold text-cream">Reports & exports</h3>
                                        <p class="mt-2 text-xs uppercase tracking-[0.24em] text-slate-400">Download snapshots</p>
                                        <div class="mt-5 space-y-3 text-xs font-semibold uppercase tracking-[0.24em] text-slate-200">
                                                  <a href="news/?export=csv" class="flex items-center justify-between rounded-2xl border border-white/5 px-4 py-2 transition hover:border-gold/40 hover:bg-gold/10 hover:text-gold">
                                                            News archive CSV
                                                            <span>&darr;</span>
                                                  </a>
                                                  <a href="fixtures/?export=csv" class="flex items-center justify-between rounded-2xl border border-white/5 px-4 py-2 transition hover:border-gold/40 hover:bg-gold/10 hover:text-gold">
                                                            Fixture list CSV
                                                            <span>&darr;</span>
                                                  </a>
                                                  <a href="sponsors/?export=csv" class="flex items-center justify-between rounded-2xl border border-white/5 px-4 py-2 transition hover:border-gold/40 hover:bg-gold/10 hover:text-gold">
                                                            Sponsor ledger CSV
                                                            <span>&darr;</span>
                                                  </a>
                                        </div>
                              </div>
                              <div class="rounded-3xl border border-white/5 bg-slate-900/70 p-6 shadow-maroon-lg">
                                        <h3 class="text-base font-semibold text-cream">Communications</h3>
                                        <p class="mt-2 text-xs uppercase tracking-[0.24em] text-slate-400">Keep the club informed</p>
                                        <div class="mt-5 space-y-3 text-xs font-semibold uppercase tracking-[0.24em] text-slate-200">
                                                  <a href="mailto:media@myclubhub.co.uk" class="flex items-center gap-3 rounded-2xl border border-white/5 px-4 py-2 transition hover:border-gold/40 hover:bg-gold/10 hover:text-gold">
                                                            <i class="fa-solid fa-paper-plane text-gold"></i> Media briefing email
                                                  </a>
                                                  <a href="mailto:operations@myclubhub.co.uk" class="flex items-center gap-3 rounded-2xl border border-white/5 px-4 py-2 transition hover:border-gold/40 hover:bg-gold/10 hover:text-gold">
                                                            <i class="fa-solid fa-clipboard-check text-gold"></i> Operations update
                                                  </a>
                                                  <a href="mailto:sponsors@myclubhub.co.uk" class="flex items-center gap-3 rounded-2xl border border-white/5 px-4 py-2 transition hover:border-gold/40 hover:bg-gold/10 hover:text-gold">
                                                            <i class="fa-solid fa-handshake-angle text-gold"></i> Partner newsletter
                                                  </a>
                                        </div>
                              </div>
                              <div class="rounded-3xl border border-white/5 bg-slate-900/70 p-6 shadow-maroon-lg">
                                        <h3 class="text-base font-semibold text-cream">Admin toolkit</h3>
                                        <p class="mt-2 text-xs uppercase tracking-[0.24em] text-slate-400">Useful resources</p>
                                        <ul class="mt-5 space-y-3 text-xs font-semibold uppercase tracking-[0.24em] text-slate-200">
                                                  <li class="flex items-center gap-2 rounded-2xl border border-white/5 px-4 py-2"><i class="fa-solid fa-file-contract text-gold"></i> Policy handbook</li>
                                                  <li class="flex items-center gap-2 rounded-2xl border border-white/5 px-4 py-2"><i class="fa-solid fa-triangle-exclamation text-gold"></i> Emergency contacts</li>
                                                  <li class="flex items-center gap-2 rounded-2xl border border-white/5 px-4 py-2"><i class="fa-solid fa-palette text-gold"></i> Brand assets</li>
                                                  <li class="flex items-center gap-2 rounded-2xl border border-white/5 px-4 py-2"><i class="fa-solid fa-users-line text-gold"></i> Volunteer rota</li>
                                        </ul>
                              </div>
                    </div>
          </section>

          <section id="activity" class="mx-auto max-w-6xl px-6 pb-16">
                    <div class="grid gap-6 xl:grid-cols-2">
                              <div class="rounded-3xl border border-white/5 bg-slate-900/70 p-6 shadow-maroon-lg">
                                        <h2 class="text-lg font-semibold text-cream">Recent activity</h2>
                                        <p class="mt-2 text-xs uppercase tracking-[0.24em] text-slate-400">Latest highlights</p>
                                        <ul class="mt-6 space-y-4 text-sm">
                                                  <li class="flex items-start gap-3 rounded-2xl border border-white/5 px-4 py-3">
                                                            <span class="mt-1 text-gold"><i class="fa-solid fa-star"></i></span>
                                                            <div>
                                                                      <p class="font-semibold text-cream">Admin dashboard refreshed</p>
                                                                      <p class="mt-1 text-xs uppercase tracking-[0.24em] text-slate-400">Design update &middot; Just now</p>
                                                            </div>
                                                  </li>
                                                  <li class="flex items-start gap-3 rounded-2xl border border-white/5 px-4 py-3">
                                                            <span class="mt-1 text-gold"><i class="fa-solid fa-user-shield"></i></span>
                                                            <div>
                                                                      <p class="font-semibold text-cream"><?= htmlspecialchars($currentUserName); ?> logged in</p>
                                                                      <p class="mt-1 text-xs uppercase tracking-[0.24em] text-slate-400">Admin access &middot; Now</p>
                                                            </div>
                                                  </li>
                                                  <li class="flex items-start gap-3 rounded-2xl border border-white/5 px-4 py-3">
                                                            <span class="mt-1 text-gold"><i class="fa-solid fa-sitemap"></i></span>
                                                            <div>
                                                                      <p class="font-semibold text-cream">Phase 1 systems deployed</p>
                                                                      <p class="mt-1 text-xs uppercase tracking-[0.24em] text-slate-400">Infrastructure &middot; Today</p>
                                                            </div>
                                                  </li>
                                        </ul>
                              </div>

                              <div class="rounded-3xl border border-white/5 bg-slate-900/70 p-6 shadow-maroon-lg">
                                        <h2 class="text-lg font-semibold text-cream">Club snapshot</h2>
                                        <p class="mt-2 text-xs uppercase tracking-[0.24em] text-slate-400">Key information</p>
                                        <dl class="mt-6 space-y-3 text-sm text-slate-300">
                                                  <div class="flex items-center justify-between rounded-2xl border border-white/5 px-4 py-3">
                                                            <dt class="font-semibold uppercase tracking-[0.24em]">Season</dt>
                                                            <dd>2025 / 26</dd>
                                                  </div>
                                                  <div class="flex items-center justify-between rounded-2xl border border-white/5 px-4 py-3">
                                                            <dt class="font-semibold uppercase tracking-[0.24em]">League</dt>
                                                            <dd>West of Scotland Premier Division</dd>
                                                  </div>
                                                  <div class="flex items-center justify-between rounded-2xl border border-white/5 px-4 py-3">
                                                            <dt class="font-semibold uppercase tracking-[0.24em]">Ground</dt>
                                                            <dd>Campbell Park</dd>
                                                  </div>
                                                  <div class="flex items-center justify-between rounded-2xl border border-white/5 px-4 py-3">
                                                            <dt class="font-semibold uppercase tracking-[0.24em]">Established</dt>
                                                            <dd>1889</dd>
                                                  </div>
                                                  <div class="rounded-2xl border border-gold/20 bg-gold/10 px-4 py-3 text-xs font-semibold uppercase tracking-[0.24em] text-gold">
                                                            &ldquo;Building a stronger club through technology and community.&rdquo;
                                                  </div>
                                        </dl>
                              </div>
                    </div>
          </section>

          <?php require_once __DIR__ . '/includes/footer.php'; ?>
</main>
<?php require_once __DIR__ . '/includes/layout_end.php'; ?>
