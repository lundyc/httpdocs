<?php

/**
 * Admin Invite Management Page
 * /admin/invites/index.php
 * 
 * Displays all invites with ability to search, expire, and delete
 * Restricted to Admin and Super Admin roles only
 */

require_once __DIR__ . '/../../shared/includes/session_init.php';
require_once __DIR__ . '/../../shared/includes/simple_session.php';
require_once __DIR__ . '/../../portal/auth/auth_functions.php';
require_once __DIR__ . '/../../portal/auth/middleware.php';

// Restrict access to Admin (2) and Super Admin (1) only
checkRole([1, 2]);

$db = getDB();
$message = '';
$error = '';

// Generate CSRF token for forms
if (!isset($_SESSION['csrf_token'])) {
          $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

// Handle POST actions (Expire, Delete)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
          // Verify CSRF token
          if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
                    $error = 'Invalid security token. Please try again.';
          } else {
                    $action = $_POST['action'] ?? '';
                    $inviteId = (int)($_POST['invite_id'] ?? 0);

                    try {
                              if ($action === 'expire' && $inviteId > 0) {
                                        // Expire the invite
                                        $stmt = $db->prepare("UPDATE invites SET status = 'expired', used_at = NOW() WHERE id = ? AND status = 'pending'");
                                        $result = $stmt->execute([$inviteId]);

                                        if ($stmt->rowCount() > 0) {
                                                  $message = 'Invite expired successfully.';
                                                  logAction($_SESSION['user_id'], "Expired invite ID: $inviteId");
                                        } else {
                                                  $error = 'Invite not found or already processed.';
                                        }
                              } elseif ($action === 'delete' && $inviteId > 0) {
                                        // Delete the invite
                                        $stmt = $db->prepare("DELETE FROM invites WHERE id = ?");
                                        $result = $stmt->execute([$inviteId]);

                                        if ($stmt->rowCount() > 0) {
                                                  $message = 'Invite deleted successfully.';
                                                  logAction($_SESSION['user_id'], "Deleted invite ID: $inviteId");
                                        } else {
                                                  $error = 'Invite not found.';
                                        }
                              }
                    } catch (Exception $e) {
                              $error = 'Error processing request: ' . $e->getMessage();
                    }
          }
}

// Handle search functionality
$searchEmail = trim($_GET['search_email'] ?? '');
$searchStatus = trim($_GET['search_status'] ?? '');

// Build search query
$whereConditions = [];
$searchParams = [];

if (!empty($searchEmail)) {
          $whereConditions[] = "i.email LIKE ?";
          $searchParams[] = '%' . $searchEmail . '%';
}

if (!empty($searchStatus)) {
          $whereConditions[] = "i.status = ?";
          $searchParams[] = $searchStatus;
}

$whereClause = '';
if (!empty($whereConditions)) {
          $whereClause = 'WHERE ' . implode(' AND ', $whereConditions);
}

// Fetch all invites with role and creator information
$sql = "
    SELECT 
        i.id,
        i.email,
        i.status,
        i.created_at,
        i.expires_at,
        i.used_at,
        r.name AS role_name,
        u.name AS created_by_name
    FROM invites i
    LEFT JOIN roles r ON i.role_id = r.id
    LEFT JOIN users u ON i.created_by = u.id
    $whereClause
    ORDER BY i.created_at DESC
";

$stmt = $db->prepare($sql);
$stmt->execute($searchParams);
$invites = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Set page variables for layout
$pageTitle = 'Invite Management - My Club Hub Admin';
$pageContextLabel = 'User Management';
$pageTitleText = 'Invite Management';
$pageBadges = [
          ['text' => 'Admin Panel', 'variant' => 'gold'],
          ['text' => count($invites) . ' Total Invites', 'variant' => 'neutral']
];
$pageActions = [
          [
                    'label' => 'New Invite',
                    'href' => '/admin/invites/create.php',
                    'variant' => 'primary',
                    'icon' => 'fa-solid fa-plus'
          ]
];

require_once __DIR__ . '/../includes/layout_start.php';

$activeNav = 'invites';
require_once __DIR__ . '/../includes/navigation.php';
?>

<main class="flex-1 bg-slate-900/40 backdrop-blur">
          <?php require_once __DIR__ . '/../includes/header.php'; ?>

          <section class="mx-auto max-w-6xl px-6 py-10">

                    <!-- Success/Error Messages -->
                    <?php if ($message): ?>
                              <div class="mb-6 rounded-xl border border-teal/30 bg-teal/10 p-4">
                                        <div class="flex items-center gap-3">
                                                  <i class="fa-solid fa-check-circle text-teal text-xl"></i>
                                                  <span class="font-semibold text-cream"><?= htmlspecialchars($message); ?></span>
                                        </div>
                              </div>
                    <?php endif; ?>

                    <?php if ($error): ?>
                              <div class="mb-6 rounded-xl border border-red-500/30 bg-red-900/30 p-4">
                                        <div class="flex items-center gap-3">
                                                  <i class="fa-solid fa-exclamation-triangle text-red-300 text-xl"></i>
                                                  <span class="font-semibold text-red-300"><?= htmlspecialchars($error); ?></span>
                                        </div>
                              </div>
                    <?php endif; ?>

                    <!-- Search Filters -->
                    <div class="mb-8 rounded-3xl border border-white/5 bg-slate-900/70 p-6 shadow-maroon-lg">
                              <h2 class="mb-4 text-lg font-semibold text-cream">Search Filters</h2>
                              <form method="GET" class="grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
                                        <div>
                                                  <label class="block text-sm font-semibold text-slate-300 mb-2">Email</label>
                                                  <input type="text" name="search_email" value="<?= htmlspecialchars($searchEmail); ?>"
                                                            placeholder="Search by email..."
                                                            class="w-full px-4 py-2 bg-slate-800 border border-slate-600 rounded-xl text-slate-100 placeholder-slate-400 focus:border-gold focus:outline-none transition">
                                        </div>
                                        <div>
                                                  <label class="block text-sm font-semibold text-slate-300 mb-2">Status</label>
                                                  <select name="search_status"
                                                            class="w-full px-4 py-2 bg-slate-800 border border-slate-600 rounded-xl text-slate-100 focus:border-gold focus:outline-none transition">
                                                            <option value="">All Statuses</option>
                                                            <option value="pending" <?= $searchStatus === 'pending' ? 'selected' : ''; ?>>Pending</option>
                                                            <option value="used" <?= $searchStatus === 'used' ? 'selected' : ''; ?>>Used</option>
                                                            <option value="expired" <?= $searchStatus === 'expired' ? 'selected' : ''; ?>>Expired</option>
                                                  </select>
                                        </div>
                                        <div class="flex items-end gap-2">
                                                  <button type="submit"
                                                            class="px-6 py-2 bg-gradient-to-r from-gold to-yellow-500 text-slate-900 font-bold rounded-xl hover:from-yellow-400 hover:to-gold transition transform hover:scale-105">
                                                            <i class="fa-solid fa-search mr-2"></i>Search
                                                  </button>
                                                  <a href="/admin/invites/"
                                                            class="px-4 py-2 bg-slate-700 text-slate-300 rounded-xl hover:bg-slate-600 transition">
                                                            <i class="fa-solid fa-times mr-1"></i>Clear
                                                  </a>
                                        </div>
                              </form>
                    </div>

                    <!-- Invites Table -->
                    <div class="rounded-3xl border border-white/5 bg-slate-900/70 shadow-maroon-lg overflow-hidden">
                              <div class="p-6 border-b border-white/5">
                                        <h2 class="text-xl font-bold text-cream">All Invites</h2>
                                        <p class="text-sm text-slate-400 mt-1">Manage user invitations and access</p>
                              </div>

                              <?php if (empty($invites)): ?>
                                        <div class="p-8 text-center">
                                                  <i class="fa-solid fa-inbox text-4xl text-slate-600 mb-4"></i>
                                                  <p class="text-lg font-semibold text-slate-400">No invites found</p>
                                                  <p class="text-sm text-slate-500 mt-1">
                                                            <?= !empty($searchEmail) || !empty($searchStatus) ? 'Try adjusting your search criteria.' : 'Create your first invite to get started.' ?>
                                                  </p>
                                        </div>
                              <?php else: ?>
                                        <div class="overflow-x-auto">
                                                  <table class="w-full">
                                                            <thead>
                                                                      <tr class="border-b border-white/5 bg-slate-800/50">
                                                                                <th class="px-6 py-4 text-left text-xs font-semibold uppercase tracking-wider text-slate-300">Email</th>
                                                                                <th class="px-6 py-4 text-left text-xs font-semibold uppercase tracking-wider text-slate-300">Role</th>
                                                                                <th class="px-6 py-4 text-left text-xs font-semibold uppercase tracking-wider text-slate-300">Status</th>
                                                                                <th class="px-6 py-4 text-left text-xs font-semibold uppercase tracking-wider text-slate-300">Created</th>
                                                                                <th class="px-6 py-4 text-left text-xs font-semibold uppercase tracking-wider text-slate-300">Expires</th>
                                                                                <th class="px-6 py-4 text-left text-xs font-semibold uppercase tracking-wider text-slate-300">Created By</th>
                                                                                <th class="px-6 py-4 text-left text-xs font-semibold uppercase tracking-wider text-slate-300">Actions</th>
                                                                      </tr>
                                                            </thead>
                                                            <tbody class="divide-y divide-white/5">
                                                                      <?php foreach ($invites as $invite): ?>
                                                                                <?php
                                                                                // Determine row styling based on status
                                                                                $rowClass = 'hover:bg-white/5 transition';
                                                                                if ($invite['status'] === 'pending') {
                                                                                          $rowClass .= ' bg-gold/5 border-l-4 border-gold/30';
                                                                                } elseif ($invite['status'] === 'used') {
                                                                                          $rowClass .= ' bg-teal/5 border-l-4 border-teal/30';
                                                                                } elseif ($invite['status'] === 'expired') {
                                                                                          $rowClass .= ' bg-red-900/10 border-l-4 border-red-500/30';
                                                                                }

                                                                                // Format dates
                                                                                $createdAt = new DateTime($invite['created_at']);
                                                                                $expiresAt = new DateTime($invite['expires_at']);
                                                                                $now = new DateTime();

                                                                                // Status badge styling
                                                                                $statusClass = 'inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium uppercase tracking-wide';
                                                                                if ($invite['status'] === 'pending') {
                                                                                          $statusClass .= ' bg-gold/20 text-gold border border-gold/30';
                                                                                } elseif ($invite['status'] === 'used') {
                                                                                          $statusClass .= ' bg-teal/20 text-teal border border-teal/30';
                                                                                } else {
                                                                                          $statusClass .= ' bg-red-900/30 text-red-300 border border-red-500/30';
                                                                                }
                                                                                ?>
                                                                                <tr class="<?= $rowClass; ?>">
                                                                                          <td class="px-6 py-4">
                                                                                                    <div class="font-semibold text-cream"><?= htmlspecialchars($invite['email']); ?></div>
                                                                                          </td>
                                                                                          <td class="px-6 py-4">
                                                                                                    <span class="text-slate-300"><?= htmlspecialchars($invite['role_name'] ?? 'Unknown'); ?></span>
                                                                                          </td>
                                                                                          <td class="px-6 py-4">
                                                                                                    <span class="<?= $statusClass; ?>">
                                                                                                              <?= htmlspecialchars($invite['status']); ?>
                                                                                                    </span>
                                                                                          </td>
                                                                                          <td class="px-6 py-4">
                                                                                                    <div class="text-sm text-slate-300">
                                                                                                              <?= $createdAt->format('M j, Y'); ?>
                                                                                                    </div>
                                                                                                    <div class="text-xs text-slate-500">
                                                                                                              <?= $createdAt->format('g:i A'); ?>
                                                                                                    </div>
                                                                                          </td>
                                                                                          <td class="px-6 py-4">
                                                                                                    <div class="text-sm <?= $expiresAt < $now ? 'text-red-300' : 'text-slate-300'; ?>">
                                                                                                              <?= $expiresAt->format('M j, Y'); ?>
                                                                                                    </div>
                                                                                                    <div class="text-xs text-slate-500">
                                                                                                              <?= $expiresAt->format('g:i A'); ?>
                                                                                                    </div>
                                                                                          </td>
                                                                                          <td class="px-6 py-4">
                                                                                                    <span class="text-slate-300"><?= htmlspecialchars($invite['created_by_name'] ?? 'Unknown'); ?></span>
                                                                                          </td>
                                                                                          <td class="px-6 py-4">
                                                                                                    <div class="flex items-center gap-2">
                                                                                                              <?php if ($invite['status'] === 'pending'): ?>
                                                                                                                        <!-- Expire Action -->
                                                                                                                        <form method="POST" class="inline">
                                                                                                                                  <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token']); ?>">
                                                                                                                                  <input type="hidden" name="action" value="expire">
                                                                                                                                  <input type="hidden" name="invite_id" value="<?= $invite['id']; ?>">
                                                                                                                                  <button type="submit"
                                                                                                                                            onclick="return confirm('Are you sure you want to expire this invite?')"
                                                                                                                                            class="inline-flex items-center px-3 py-1 bg-yellow-600 hover:bg-yellow-700 text-white text-xs font-semibold rounded-lg transition"
                                                                                                                                            title="Expire Invite">
                                                                                                                                            <i class="fa-solid fa-clock mr-1"></i>Expire
                                                                                                                                  </button>
                                                                                                                        </form>
                                                                                                              <?php endif; ?>

                                                                                                              <!-- Delete Action -->
                                                                                                              <form method="POST" class="inline">
                                                                                                                        <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token']); ?>">
                                                                                                                        <input type="hidden" name="action" value="delete">
                                                                                                                        <input type="hidden" name="invite_id" value="<?= $invite['id']; ?>">
                                                                                                                        <button type="submit"
                                                                                                                                  onclick="return confirm('Are you sure you want to delete this invite? This action cannot be undone.')"
                                                                                                                                  class="inline-flex items-center px-3 py-1 bg-red-600 hover:bg-red-700 text-white text-xs font-semibold rounded-lg transition"
                                                                                                                                  title="Delete Invite">
                                                                                                                                  <i class="fa-solid fa-trash mr-1"></i>Delete
                                                                                                                        </button>
                                                                                                              </form>
                                                                                                    </div>
                                                                                          </td>
                                                                                </tr>
                                                                      <?php endforeach; ?>
                                                            </tbody>
                                                  </table>
                                        </div>
                              <?php endif; ?>
                    </div>

                    <!-- Summary Statistics -->
                    <div class="mt-8 grid gap-4 sm:grid-cols-3">
                              <?php
                              $pendingCount = count(array_filter($invites, fn($i) => $i['status'] === 'pending'));
                              $usedCount = count(array_filter($invites, fn($i) => $i['status'] === 'used'));
                              $expiredCount = count(array_filter($invites, fn($i) => $i['status'] === 'expired'));
                              ?>

                              <div class="rounded-xl border border-white/5 bg-slate-900/50 p-4">
                                        <div class="flex items-center justify-between">
                                                  <div>
                                                            <p class="text-xs font-semibold uppercase tracking-[0.3em] text-slate-400">Pending</p>
                                                            <p class="mt-2 text-2xl font-semibold text-gold"><?= $pendingCount; ?></p>
                                                  </div>
                                                  <i class="fa-solid fa-clock text-2xl text-gold/50"></i>
                                        </div>
                              </div>

                              <div class="rounded-xl border border-white/5 bg-slate-900/50 p-4">
                                        <div class="flex items-center justify-between">
                                                  <div>
                                                            <p class="text-xs font-semibold uppercase tracking-[0.3em] text-slate-400">Used</p>
                                                            <p class="mt-2 text-2xl font-semibold text-teal"><?= $usedCount; ?></p>
                                                  </div>
                                                  <i class="fa-solid fa-check-circle text-2xl text-teal/50"></i>
                                        </div>
                              </div>

                              <div class="rounded-xl border border-white/5 bg-slate-900/50 p-4">
                                        <div class="flex items-center justify-between">
                                                  <div>
                                                            <p class="text-xs font-semibold uppercase tracking-[0.3em] text-slate-400">Expired</p>
                                                            <p class="mt-2 text-2xl font-semibold text-red-300"><?= $expiredCount; ?></p>
                                                  </div>
                                                  <i class="fa-solid fa-times-circle text-2xl text-red-300/50"></i>
                                        </div>
                              </div>
                    </div>

          </section>
</main>

<?php require_once __DIR__ . '/../includes/layout_end.php'; ?>