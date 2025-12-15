<?php
require_once __DIR__ . '/../../shared/includes/session_init.php';
require_once __DIR__ . '/../../shared/includes/simple_session.php';
require_once __DIR__ . '/../../portal/auth/auth_functions.php';
require_once __DIR__ . '/../../portal/auth/middleware.php';

// Use simple PHP sessions

checkRole([1, 2]);

$db = getDB();

$message = '';
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
          try {
                    if (isset($_POST['action'])) {
                              switch ($_POST['action']) {
                                        case 'create':
                                                  $stmt = $db->prepare("INSERT INTO news (title, category, content, featured_image, created_by) VALUES (?, ?, ?, ?, ?)");
                                                  $stmt->execute([
                                                            $_POST['title'],
                                                            $_POST['category'],
                                                            $_POST['content'],
                                                            $_POST['featured_image'] ?: null,
                                                            $sessionContext['user_id'] ?? null,
                                                  ]);
                                                  logAction($sessionContext['user_id'] ?? null, "Created news post: " . $_POST['title']);
                                                  $message = 'News post created successfully!';
                                                  break;
                                        case 'update':
                                                  $stmt = $db->prepare("UPDATE news SET title = ?, category = ?, content = ?, featured_image = ? WHERE id = ?");
                                                  $stmt->execute([
                                                            $_POST['title'],
                                                            $_POST['category'],
                                                            $_POST['content'],
                                                            $_POST['featured_image'] ?: null,
                                                            $_POST['id'],
                                                  ]);
                                                  logAction($sessionContext['user_id'] ?? null, "Updated news post ID: " . $_POST['id']);
                                                  $message = 'News post updated successfully!';
                                                  break;
                                        case 'delete':
                                                  $stmt = $db->prepare("DELETE FROM news WHERE id = ?");
                                                  $stmt->execute([$_POST['id']]);
                                                  logAction($sessionContext['user_id'] ?? null, "Deleted news post ID: " . $_POST['id']);
                                                  $message = 'News post deleted successfully!';
                                                  break;
                              }
                    }
          } catch (Exception $e) {
                    $error = 'Error: ' . $e->getMessage();
          }
}

$stmt = $db->query("SELECT n.*, u.name AS author_name FROM news n 
                    LEFT JOIN users u ON n.created_by = u.id 
                    ORDER BY n.created_at DESC");
$newsPosts = $stmt->fetchAll(PDO::FETCH_ASSOC);

$currentUserId = $sessionContext['user_id'] ?? null;
$currentUser = null;
if ($currentUserId !== null) {
          $stmt = $db->prepare("SELECT u.name, r.name AS role_name FROM users u 
                      JOIN roles r ON u.role_id = r.id 
                      WHERE u.id = ?");
          $stmt->execute([$currentUserId]);
          $currentUser = $stmt->fetch(PDO::FETCH_ASSOC);
}

$totalPosts = count($newsPosts);
$categoryValues = array_filter(array_map('trim', array_column($newsPosts, 'category')));
$categoryCount = count(array_unique(array_map('strtolower', $categoryValues)));
$latestPost = $newsPosts[0] ?? null;
$latestPostTitle = $latestPost['title'] ?? null;
$latestPostDate = $latestPost['created_at'] ?? null;
$latestPostDisplay = $latestPostDate ? date('M j, Y', strtotime($latestPostDate)) : 'No posts yet';

$userPostCount = 0;
if ($currentUserId !== null) {
          foreach ($newsPosts as $post) {
                    if ((int) ($post['created_by'] ?? 0) === (int) $currentUserId) {
                              $userPostCount++;
                    }
          }
}

$formDefaults = [
          'title' => '',
          'category' => '',
          'content' => '',
          'featured_image' => '',
];

if (!empty($error) && $_SERVER['REQUEST_METHOD'] === 'POST') {
          $formDefaults = [
                    'title' => $_POST['title'] ?? '',
                    'category' => $_POST['category'] ?? '',
                    'content' => $_POST['content'] ?? '',
                    'featured_image' => $_POST['featured_image'] ?? '',
          ];
}

$pageTitle = 'News Management - My Club Hub Admin';
$pageContextLabel = 'Content management';
$pageTitleText = 'News Management';
$pageBadges = [
          ['text' => $currentUser['role_name'] ?? 'Editorial', 'variant' => 'gold'],
          ['text' => 'Total posts: ' . number_format($totalPosts), 'variant' => 'neutral'],
];
if ($currentUserId !== null) {
          $pageBadges[] = ['text' => 'Your posts: ' . number_format($userPostCount), 'variant' => 'neutral'];
}
$pageActions = [
          [
                    'label' => 'Back to dashboard',
                    'href' => '/admin/',
                    'variant' => 'secondary',
                    'icon' => 'fa-solid fa-arrow-left',
          ],
          [
                    'label' => 'Create news post',
                    'href' => '#news-form',
                    'variant' => 'primary',
                    'icon' => 'fa-solid fa-plus',
          ],
          [
                    'label' => 'Logout',
                    'href' => '/portal/auth/logout.php',
                    'variant' => 'secondary',
                    'icon' => 'fa-solid fa-power-off',
          ],
];

$additionalHeadContent = '';

require_once __DIR__ . '/../includes/layout_start.php';

$activeNav = 'news';
$userRoleId = $sessionContext['role_id'] ?? null;
require_once __DIR__ . '/../includes/navigation.php';
?>
<main class="flex-1 bg-slate-900/40 backdrop-blur">
          <?php require_once __DIR__ . '/../includes/header.php'; ?>

          <section class="mx-auto max-w-6xl px-6 pt-10 pb-6 space-y-6">
                    <?php if (!empty($message)): ?>
                              <div class="rounded-2xl border border-emerald-500/40 bg-emerald-500/10 px-5 py-4 text-sm text-emerald-200" role="alert">
                                        <?= htmlspecialchars($message); ?>
                              </div>
                    <?php endif; ?>
                    <?php if (!empty($error)): ?>
                              <div class="rounded-2xl border border-rose-500/40 bg-rose-500/10 px-5 py-4 text-sm text-rose-200" role="alert">
                                        <?= htmlspecialchars($error); ?>
                              </div>
                    <?php endif; ?>

                    <div class="grid gap-5 sm:grid-cols-2 xl:grid-cols-4">
                              <div class="rounded-3xl border border-white/5 bg-slate-900/70 p-5 shadow-maroon-lg">
                                        <p class="text-xs font-semibold uppercase tracking-[0.3em] text-slate-400">Total posts</p>
                                        <p class="mt-3 text-3xl font-semibold text-gold"><?= number_format($totalPosts); ?></p>
                              </div>
                              <div class="rounded-3xl border border-white/5 bg-slate-900/70 p-5 shadow-maroon-lg">
                                        <p class="text-xs font-semibold uppercase tracking-[0.3em] text-slate-400">Active categories</p>
                                        <p class="mt-3 text-3xl font-semibold text-gold"><?= number_format($categoryCount); ?></p>
                              </div>
                              <div class="rounded-3xl border border-white/5 bg-slate-900/70 p-5 shadow-maroon-lg">
                                        <p class="text-xs font-semibold uppercase tracking-[0.3em] text-slate-400">Latest update</p>
                                        <p class="mt-3 text-3xl font-semibold text-gold"><?= htmlspecialchars($latestPostDisplay); ?></p>
                                        <?php if ($latestPostTitle): ?>
                                                  <p class="mt-2 text-xs uppercase tracking-[0.24em] text-slate-400">
                                                            <?= htmlspecialchars(strlen($latestPostTitle) > 36 ? substr($latestPostTitle, 0, 33) . '...' : $latestPostTitle); ?>
                                                  </p>
                                        <?php else: ?>
                                                  <p class="mt-2 text-xs uppercase tracking-[0.24em] text-slate-400">No content published</p>
                                        <?php endif; ?>
                              </div>
                              <div class="rounded-3xl border border-white/5 bg-slate-900/70 p-5 shadow-maroon-lg">
                                        <p class="text-xs font-semibold uppercase tracking-[0.3em] text-slate-400">Your contributions</p>
                                        <p class="mt-3 text-3xl font-semibold text-gold"><?= number_format($userPostCount); ?></p>
                              </div>
                    </div>
          </section>

          <section id="news-form" class="mx-auto max-w-6xl px-6 pb-12">
                    <div class="rounded-3xl border border-white/5 bg-slate-900/70 p-6 shadow-maroon-lg">
                              <div class="flex flex-col gap-4 border-b border-white/10 pb-5 lg:flex-row lg:items-center lg:justify-between">
                                        <div>
                                                  <h2 class="text-xl font-semibold text-cream" id="form-title">Create New News Post</h2>
                                                  <p class="mt-1 text-xs uppercase tracking-[0.24em] text-slate-400">Share updates with the My Club Hub community</p>
                                        </div>
                                        <button type="button" onclick="resetForm()" class="inline-flex items-center gap-2 rounded-full border border-white/20 px-4 py-2 text-xs font-semibold uppercase tracking-[0.24em] text-slate-200 transition hover:bg-white/10">
                                                  <i class="fa-solid fa-rotate-left"></i> Reset form
                                        </button>
                              </div>

                              <form method="POST" class="mt-6 space-y-6">
                                        <input type="hidden" name="action" id="form-action" value="create">
                                        <input type="hidden" name="id" id="form-id" value="">

                                        <div class="grid gap-6 lg:grid-cols-2">
                                                  <div class="space-y-2">
                                                            <label for="title" class="text-xs font-semibold uppercase tracking-[0.24em] text-slate-300">Title</label>
                                                            <input type="text" id="title" name="title" value="<?= htmlspecialchars($formDefaults['title']); ?>" required class="w-full rounded-2xl border border-white/10 bg-slate-950/70 px-4 py-3 text-sm text-slate-100 placeholder:text-slate-500 focus:border-gold/40 focus:outline-none focus:ring-2 focus:ring-gold/30" placeholder="Matchday report">
                                                  </div>
                                                  <div class="space-y-2">
                                                            <label for="category" class="text-xs font-semibold uppercase tracking-[0.24em] text-slate-300">Category</label>
                                                            <input type="text" id="category" name="category" value="<?= htmlspecialchars($formDefaults['category']); ?>" required class="w-full rounded-2xl border border-white/10 bg-slate-950/70 px-4 py-3 text-sm text-slate-100 placeholder:text-slate-500 focus:border-gold/40 focus:outline-none focus:ring-2 focus:ring-gold/30" placeholder="Club News">
                                                  </div>
                                        </div>

                                        <div class="space-y-2">
                                                  <label for="featured_image" class="text-xs font-semibold uppercase tracking-[0.24em] text-slate-300">Featured image URL</label>
                                                  <input type="url" id="featured_image" name="featured_image" value="<?= htmlspecialchars($formDefaults['featured_image']); ?>" class="w-full rounded-2xl border border-white/10 bg-slate-950/70 px-4 py-3 text-sm text-slate-100 placeholder:text-slate-500 focus:border-gold/40 focus:outline-none focus:ring-2 focus:ring-gold/30" placeholder="https://example.com/image.jpg">
                                                  <p class="text-xs uppercase tracking-[0.2em] text-slate-500">Optional &middot; Leave blank for default imagery</p>
                                        </div>

                                        <div class="space-y-2">
                                                  <label for="content" class="text-xs font-semibold uppercase tracking-[0.24em] text-slate-300">Content</label>
                                                  <textarea id="content" name="content" rows="8" class="w-full rounded-2xl border border-white/10 bg-slate-950/70 px-4 py-3 text-sm text-slate-100 placeholder:text-slate-500 focus:border-gold/40 focus:outline-none focus:ring-2 focus:ring-gold/30" required placeholder="Share the latest update for supporters..."><?= htmlspecialchars($formDefaults['content']); ?></textarea>
                                        </div>

                                        <div class="flex flex-wrap items-center gap-3">
                                                  <button type="submit" id="submit-btn" class="inline-flex items-center gap-2 rounded-full border border-gold/30 bg-gold px-6 py-2 text-xs font-semibold uppercase tracking-[0.24em] text-maroon transition hover:bg-gold/90">
                                                            <i class="fa-solid fa-paper-plane"></i> Create News Post
                                                  </button>
                                                  <button type="button" onclick="resetForm()" class="inline-flex items-center gap-2 rounded-full border border-white/20 px-5 py-2 text-xs font-semibold uppercase tracking-[0.24em] text-slate-200 transition hover:bg-white/10">
                                                            <i class="fa-solid fa-ban"></i> Cancel edit
                                                  </button>
                                        </div>
                              </form>
                    </div>
          </section>

          <section class="mx-auto max-w-6xl px-6 pb-16">
                    <div class="rounded-3xl border border-white/5 bg-slate-900/70 p-6 shadow-maroon-lg">
                              <div class="flex flex-col gap-4 border-b border-white/10 pb-5 md:flex-row md:items-center md:justify-between">
                                        <div>
                                                  <h2 class="text-xl font-semibold text-cream">Published News</h2>
                                                  <p class="mt-1 text-xs uppercase tracking-[0.24em] text-slate-400">Manage and review existing articles</p>
                                        </div>
                              </div>

                              <?php if (empty($newsPosts)): ?>
                                        <div class="mt-6 rounded-2xl border border-white/5 bg-slate-900/60 p-6 text-center text-sm text-slate-300">
                                                  No news posts found. Use the form above to create the first story.
                                        </div>
                              <?php else: ?>
                                        <div class="mt-6 overflow-x-auto">
                                                  <table class="min-w-full divide-y divide-white/10 text-left text-sm">
                                                            <thead class="text-xs uppercase tracking-[0.24em] text-slate-400">
                                                                      <tr>
                                                                                <th class="px-4 py-3 font-semibold">Title</th>
                                                                                <th class="px-4 py-3 font-semibold">Category</th>
                                                                                <th class="px-4 py-3 font-semibold">Author</th>
                                                                                <th class="px-4 py-3 font-semibold">Created</th>
                                                                                <th class="px-4 py-3 font-semibold text-right">Actions</th>
                                                                      </tr>
                                                            </thead>
                                                            <tbody class="divide-y divide-white/5 text-slate-200">
                                                                      <?php foreach ($newsPosts as $post): ?>
                                                                                <tr class="transition hover:bg-white/5">
                                                                                          <td class="px-4 py-4 align-top">
                                                                                                    <p class="font-semibold text-cream"><?= htmlspecialchars($post['title']); ?></p>
                                                                                                    <p class="mt-1 text-xs uppercase tracking-[0.2em] text-slate-400">
                                                                                                              <?= htmlspecialchars(substr($post['content'], 0, 100)); ?><?= strlen($post['content']) > 100 ? '...' : ''; ?>
                                                                                                    </p>
                                                                                          </td>
                                                                                          <td class="px-4 py-4 align-top">
                                                                                                    <span class="inline-flex rounded-full border border-gold/30 bg-gold/10 px-3 py-1 text-xs font-semibold uppercase tracking-[0.2em] text-gold">
                                                                                                              <?= htmlspecialchars($post['category']); ?>
                                                                                                    </span>
                                                                                          </td>
                                                                                          <td class="px-4 py-4 align-top"><?= htmlspecialchars($post['author_name'] ?? 'Unknown'); ?></td>
                                                                                          <td class="px-4 py-4 align-top"><?= date('M j, Y', strtotime($post['created_at'])); ?></td>
                                                                                          <td class="px-4 py-4 align-top text-right">
                                                                                                    <div class="flex flex-wrap justify-end gap-2">
                                                                                                              <button type="button" class="inline-flex items-center gap-1 rounded-full border border-white/20 px-3 py-1.5 text-xs font-semibold uppercase tracking-[0.18em] text-slate-200 transition hover:bg-white/10" onclick="editPost(<?= htmlspecialchars(json_encode($post), ENT_QUOTES, 'UTF-8'); ?>)">
                                                                                                                        <i class="fa-solid fa-pen-to-square"></i> Edit
                                                                                                              </button>
                                                                                                              <form method="POST" onsubmit="return confirm('Are you sure you want to delete this news post?');" class="inline">
                                                                                                                        <input type="hidden" name="action" value="delete">
                                                                                                                        <input type="hidden" name="id" value="<?= (int) $post['id']; ?>">
                                                                                                                        <button type="submit" class="inline-flex items-center gap-1 rounded-full border border-rose-500/40 px-3 py-1.5 text-xs font-semibold uppercase tracking-[0.18em] text-rose-300 transition hover:bg-rose-500/10">
                                                                                                                                  <i class="fa-solid fa-trash"></i> Delete
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
          </section>

          <?php require_once __DIR__ . '/../includes/footer.php'; ?>
          <script>
                    function editPost(post) {
                              const parsed = typeof post === 'string' ? JSON.parse(post) : post;
                              document.getElementById('form-title').textContent = 'Edit News Post';
                              document.getElementById('form-action').value = 'update';
                              document.getElementById('form-id').value = parsed.id;
                              document.getElementById('title').value = parsed.title;
                              document.getElementById('category').value = parsed.category;
                              document.getElementById('content').value = parsed.content;
                              document.getElementById('featured_image').value = parsed.featured_image || '';
                              document.getElementById('submit-btn').textContent = 'Update News Post';
                              document.getElementById('submit-btn').className = 'inline-flex items-center gap-2 rounded-full border border-emerald-400/40 bg-emerald-400 px-6 py-2 text-xs font-semibold uppercase tracking-[0.24em] text-maroon transition hover:bg-emerald-300';

                              document.getElementById('news-form').scrollIntoView({
                                        behavior: 'smooth'
                              });
                    }

                    function resetForm() {
                              document.getElementById('form-title').textContent = 'Create New News Post';
                              document.getElementById('form-action').value = 'create';
                              document.getElementById('form-id').value = '';
                              document.getElementById('news-form').querySelector('form').reset();
                              document.getElementById('submit-btn').textContent = 'Create News Post';
                              document.getElementById('submit-btn').className = 'inline-flex items-center gap-2 rounded-full border border-gold/30 bg-gold px-6 py-2 text-xs font-semibold uppercase tracking-[0.24em] text-maroon transition hover:bg-gold/90';
                    }
          </script>
</main>
<?php require_once __DIR__ . '/../includes/layout_end.php'; ?>
