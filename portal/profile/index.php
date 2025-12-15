<?php
require_once __DIR__ . '/../auth/auth_functions.php';
require_once __DIR__ . '/../auth/middleware.php';

requireLogin();

$db = getDB();
$sessionContext = $GLOBALS['myclubhub_session'] ?? [];
$currentUserId = $sessionContext['user_id'] ?? null;

// Fetch user and linked player profile if exists
$stmt = $db->prepare("SELECT u.id, u.name, u.email, u.role_id, r.name AS role_name
                      FROM users u JOIN roles r ON u.role_id = r.id WHERE u.id = ?");
$stmt->execute([$currentUserId]);
$user = $stmt->fetch(PDO::FETCH_ASSOC) ?: ['id' => 0, 'name' => 'Team Member', 'email' => '', 'role_id' => 99, 'role_name' => 'Club Staff'];

// Attempt to map user -> player via email (TODO: replace with explicit FK user_id on players table if available)
$player = null;
try {
          $stmtP = $db->prepare("SELECT p.* FROM players p WHERE p.email = ? LIMIT 1");
          $stmtP->execute([$user['email']]);
          $player = $stmtP->fetch(PDO::FETCH_ASSOC);
} catch (Exception $e) {
          // TODO: Add players.user_id and join on that when schema available
}

$userRoleId = $sessionContext['role_id'] ?? null;

$pageTitle = 'Profile · My Club Hub Portal';
$pageContextLabel = 'Team operations';
$pageTitleText = 'Your profile';
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

$activeNav = 'profile';
require_once __DIR__ . '/../includes/navigation.php';
?>
<main class="flex-1 bg-slate-900/40 backdrop-blur">
          <?php require_once __DIR__ . '/../includes/header.php'; ?>

          <section class="mx-auto max-w-6xl px-6 py-10">
                    <div class="grid gap-6 xl:grid-cols-3">
                              <div class="rounded-3xl border border-white/5 bg-slate-900/70 p-6 shadow-maroon-lg">
                                        <h2 class="text-lg font-semibold text-cream">Account</h2>
                                        <dl class="mt-6 space-y-3 text-sm text-slate-300">
                                                  <div class="flex items-center justify-between rounded-2xl border border-white/5 px-4 py-3">
                                                            <dt class="font-semibold uppercase tracking-[0.24em]">Name</dt>
                                                            <dd><?= htmlspecialchars($user['name']); ?></dd>
                                                  </div>
                                                  <div class="flex items-center justify-between rounded-2xl border border-white/5 px-4 py-3">
                                                            <dt class="font-semibold uppercase tracking-[0.24em]">Email</dt>
                                                            <dd><?= htmlspecialchars($user['email']); ?></dd>
                                                  </div>
                                                  <div class="flex items-center justify-between rounded-2xl border border-white/5 px-4 py-3">
                                                            <dt class="font-semibold uppercase tracking-[0.24em]">Role</dt>
                                                            <dd><?= htmlspecialchars($user['role_name']); ?></dd>
                                                  </div>
                                        </dl>
                              </div>
                              <div class="xl:col-span-2 rounded-3xl border border-white/5 bg-slate-900/70 p-6 shadow-maroon-lg">
                                        <h2 class="text-lg font-semibold text-cream">Player profile</h2>
                                        <?php if ($player): ?>
                                                  <div class="mt-6 grid gap-6 md:grid-cols-2">
                                                            <div class="rounded-2xl border border-white/5 p-4">
                                                                      <p class="text-xs font-semibold uppercase tracking-[0.24em] text-slate-400">Position</p>
                                                                      <p class="mt-1 text-cream"><?= htmlspecialchars($player['position'] ?? 'n/a'); ?></p>
                                                            </div>
                                                            <div class="rounded-2xl border border-white/5 p-4">
                                                                      <p class="text-xs font-semibold uppercase tracking-[0.24em] text-slate-400">DOB</p>
                                                                      <p class="mt-1 text-cream"><?= htmlspecialchars($player['dob'] ?? 'n/a'); ?></p>
                                                            </div>
                                                            <div class="rounded-2xl border border-white/5 p-4">
                                                                      <p class="text-xs font-semibold uppercase tracking-[0.24em] text-slate-400">Status</p>
                                                                      <p class="mt-1 text-cream"><?= htmlspecialchars($player['status'] ?? 'active'); ?></p>
                                                            </div>
                                                            <div class="rounded-2xl border border-white/5 p-4">
                                                                      <p class="text-xs font-semibold uppercase tracking-[0.24em] text-slate-400">Sponsor</p>
                                                                      <p class="mt-1 text-cream"><?= htmlspecialchars($player['sponsor'] ?? 'Unassigned'); ?></p>
                                                            </div>
                                                  </div>
                                                  <div class="mt-6 rounded-2xl border border-white/5 p-4 text-sm text-slate-300">
                                                            <p class="font-semibold uppercase tracking-[0.24em] text-slate-400">Notes</p>
                                                            <p class="mt-2"><?= htmlspecialchars($player['notes'] ?? 'No notes on file.'); ?></p>
                                                  </div>
                                        <?php else: ?>
                                                  <div class="mt-6 rounded-2xl border border-dashed border-white/10 p-8 text-slate-300">
                                                            No player profile found linked to your account.
                                                            <div class="mt-3 text-xs font-semibold uppercase tracking-[0.24em] text-slate-400">TODO: link players.user_id to users.id</div>
                                                  </div>
                                        <?php endif; ?>
                              </div>
                    </div>
          </section>

          <?php require_once __DIR__ . '/../includes/footer.php'; ?>
</main>
<?php require_once __DIR__ . '/../includes/layout_end.php'; ?>
