<?php
require_once __DIR__ . '/../auth/auth_functions.php';
require_once __DIR__ . '/../auth/middleware.php';

requireLogin();

$db = getDB();
$sessionContext = $GLOBALS['myclubhub_session'] ?? [];
$currentUserId = $sessionContext['user_id'] ?? null;
$stmt = $db->prepare("SELECT u.name, r.name AS role_name FROM users u JOIN roles r ON u.role_id = r.id WHERE u.id = ?");
$stmt->execute([$currentUserId]);
$user = $stmt->fetch(PDO::FETCH_ASSOC) ?: ['name' => 'Team Member', 'role_name' => 'Club Staff'];

$userRoleId = $sessionContext['role_id'] ?? null;

$pageTitle = 'Training · My Club Hub Portal';
$pageContextLabel = 'Team operations';
$pageTitleText = 'Training plans';
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

$activeNav = 'training';
require_once __DIR__ . '/../includes/navigation.php';
?>
<main class="flex-1 bg-slate-900/40 backdrop-blur">
          <?php require_once __DIR__ . '/../includes/header.php'; ?>

          <section class="mx-auto max-w-6xl px-6 py-10">
                    <div class="grid gap-6 xl:grid-cols-3">
                              <div class="xl:col-span-2 rounded-3xl border border-white/5 bg-slate-900/70 p-6 shadow-maroon-lg">
                                        <h2 class="text-lg font-semibold text-cream">Session planner</h2>
                                        <p class="mt-2 text-xs uppercase tracking-[0.24em] text-slate-400">Coming soon</p>
                                        <div class="mt-6 rounded-2xl border border-dashed border-white/10 p-8 text-slate-300">
                                                  Training session templates, attendance tracking, and objectives will be managed here.
                                        </div>
                              </div>
                              <div class="rounded-3xl border border-white/5 bg-slate-900/70 p-6 shadow-maroon-lg">
                                        <h3 class="text-base font-semibold text-cream">Quick actions</h3>
                                        <div class="mt-5 space-y-3 text-xs font-semibold uppercase tracking-[0.24em] text-slate-200">
                                                  <a href="#" class="flex items-center justify-between rounded-2xl border border-white/5 px-4 py-2 transition hover:border-gold/40 hover:bg-gold/10 hover:text-gold">
                                                            New session
                                                            <span>&rarr;</span>
                                                  </a>
                                                  <a href="#" class="flex items-center justify-between rounded-2xl border border-white/5 px-4 py-2 transition hover:border-gold/40 hover:bg-gold/10 hover:text-gold">
                                                            Templates
                                                            <span>&rarr;</span>
                                                  </a>
                                                  <a href="#" class="flex items-center justify-between rounded-2xl border border-white/5 px-4 py-2 transition hover:border-gold/40 hover:bg-gold/10 hover:text-gold">
                                                            Attendance
                                                            <span>&rarr;</span>
                                                  </a>
                                        </div>
                              </div>
                    </div>
          </section>

          <?php require_once __DIR__ . '/../includes/footer.php'; ?>
</main>
<?php require_once __DIR__ . '/../includes/layout_end.php'; ?>
