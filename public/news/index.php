<?php
require_once __DIR__ . "/../../shared/includes/db_session.php";

$db = getDB();

$page = isset($_GET['page']) ? max(1, (int) $_GET['page']) : 1;
$perPage = 6;
$offset = ($page - 1) * $perPage;

$category = $_GET['category'] ?? '';
$allowedCategories = ['Match Report', 'Club News', 'Community'];
$categoryWhere = '';
$categoryParams = [];

if ($category && in_array($category, $allowedCategories, true)) {
    $categoryWhere = 'WHERE category = ?';
    $categoryParams[] = $category;
}

$countStmt = $db->prepare("SELECT COUNT(*) FROM news {$categoryWhere}");
$countStmt->execute($categoryParams);
$totalPosts = (int) $countStmt->fetchColumn();
$totalPages = (int) ceil($totalPosts / $perPage);

$stmt = $db->prepare("SELECT * FROM news {$categoryWhere} ORDER BY created_at DESC LIMIT {$perPage} OFFSET {$offset}");
$stmt->execute($categoryParams);
$newsPosts = $stmt->fetchAll(PDO::FETCH_ASSOC);

$categoriesStmt = $db->query("SELECT DISTINCT category FROM news ORDER BY category");
$categories = $categoriesStmt->fetchAll(PDO::FETCH_COLUMN);

$currentPage = 'news';
$pageTitle = 'News';
$pageBanner = true;
$pageBannerEyebrow = 'Club bulletin';
$headerTitle = 'Stories from inside My Club Hub';
$headerSubtitle = 'Match reaction, player features, and community spotlights to keep you connected to the club.';

require_once __DIR__ . '/../../shared/includes/public_header.php';

function newsExcerpt(string $content, int $limit = 180): string
{
    $clean = trim(preg_replace('/\s+/', ' ', strip_tags($content)));
    if (function_exists('mb_strlen') && function_exists('mb_substr')) {
        return mb_strlen($clean) > $limit ? mb_substr($clean, 0, $limit) . '...' : $clean;
    }
    return strlen($clean) > $limit ? substr($clean, 0, $limit) . '...' : $clean;
}
?>

<div class="mx-auto max-w-7xl space-y-12 px-4 py-16 md:px-6 lg:px-8">
    <section class="rounded-3xl border border-maroon/10 bg-white/80 p-8 shadow-lg sm:p-10">
        <div class="flex flex-col gap-6 lg:flex-row lg:items-end lg:justify-between">
            <div class="space-y-4">
                <span class="inline-flex items-center gap-2 rounded-full border border-gold/30 px-4 py-1 text-xs font-semibold uppercase tracking-[0.3em] text-burgundy/80">
                    Filter
                </span>
                <h2 class="font-display text-3xl font-semibold text-maroon md:text-4xl">Dial into the content you want</h2>
                <p class="max-w-2xl text-sm text-charcoal/70">
                    Explore match reports, club updates, and community features. Filter by category to tailor the newsroom to your interests.
                </p>
            </div>
            <div class="flex flex-wrap gap-2">
                <a href="/news/"
                    class="inline-flex items-center gap-2 rounded-full border border-maroon/15 px-4 py-2 text-xs font-semibold uppercase tracking-[0.24em] transition
                    <?= empty($category) ? 'bg-maroon text-cream shadow-maroon-xl' : 'text-maroon hover:bg-maroon/10' ?>">
                    All news
                </a>
                <?php foreach ($categories as $cat): ?>
                    <a href="/news/?category=<?= urlencode($cat); ?>"
                        class="inline-flex items-center gap-2 rounded-full border border-maroon/15 px-4 py-2 text-xs font-semibold uppercase tracking-[0.24em] transition
                        <?= $category === $cat ? 'bg-gold text-maroon shadow-gold-soft' : 'text-maroon hover:bg-maroon/10' ?>">
                        <?= htmlspecialchars($cat); ?>
                    </a>
                <?php endforeach; ?>
            </div>
        </div>
    </section>

    <?php if (empty($newsPosts)): ?>
        <section class="rounded-3xl border border-dashed border-maroon/20 bg-white/70 p-12 text-center shadow-lg">
            <h3 class="font-display text-2xl text-maroon">No stories in this category yet</h3>
            <p class="mt-2 text-sm text-charcoal/70">
                We are capturing new content right now. Check back shortly or reset the filter to see every story.
            </p>
            <?php if ($category): ?>
                <a href="/news/" class="mt-6 inline-flex items-center gap-2 rounded-full border border-maroon/20 px-4 py-2 text-xs font-semibold uppercase tracking-[0.24em] text-maroon transition hover:bg-maroon hover:text-cream">
                    View all news
                </a>
            <?php endif; ?>
        </section>
    <?php else: ?>
        <section class="space-y-10">
            <div class="grid gap-6 md:grid-cols-2 lg:grid-cols-3">
                <?php foreach ($newsPosts as $post): ?>
                    <article
                        class="group flex h-full flex-col overflow-hidden rounded-3xl border border-maroon/10 bg-white shadow-lg transition hover:-translate-y-1 hover:shadow-maroon-xl">
                        <div class="relative h-48 overflow-hidden bg-maroon/10">
                            <?php if (!empty($post['featured_image'])): ?>
                                <img src="<?= htmlspecialchars($post['featured_image']); ?>"
                                    alt="<?= htmlspecialchars($post['title']); ?>"
                                    loading="lazy"
                                    class="h-full w-full object-cover transition duration-500 group-hover:scale-105">
                            <?php else: ?>
                                <div class="flex h-full w-full items-center justify-center text-xs font-semibold uppercase tracking-[0.3em] text-maroon/50">
                                    My Club Hub
                                </div>
                            <?php endif; ?>
                        </div>
                        <div class="flex flex-1 flex-col gap-4 p-6">
                            <div class="flex items-center justify-between text-xs font-semibold uppercase tracking-[0.24em] text-burgundy/80">
                                <span><?= htmlspecialchars($post['category'] ?? 'Club News'); ?></span>
                                <span><?= date('j F Y', strtotime($post['created_at'])); ?></span>
                            </div>
                            <h3 class="font-display text-xl font-semibold text-maroon line-clamp-2">
                                <a href="/news/post.php?id=<?= (int) $post['id']; ?>" class="transition hover:text-gold">
                                    <?= htmlspecialchars($post['title']); ?>
                                </a>
                            </h3>
                            <p class="text-sm text-charcoal/70 line-clamp-3">
                                <?= htmlspecialchars(newsExcerpt($post['content'])); ?>
                            </p>
                            <div class="mt-auto pt-4">
                                <a href="/news/post.php?id=<?= (int) $post['id']; ?>"
                                    class="inline-flex items-center gap-2 text-xs font-semibold uppercase tracking-[0.24em] text-burgundy transition hover:text-maroon">
                                    Read article
                                    <span aria-hidden="true">→</span>
                                </a>
                            </div>
                        </div>
                    </article>
                <?php endforeach; ?>
            </div>

            <?php if ($totalPages > 1): ?>
                <nav class="flex flex-wrap items-center justify-center gap-3 text-xs font-semibold uppercase tracking-[0.24em] text-maroon"
                    aria-label="News pagination">
                    <?php if ($page > 1): ?>
                        <a href="?page=<?= $page - 1; ?><?= $category ? '&category=' . urlencode($category) : ''; ?>"
                            class="inline-flex items-center rounded-full border border-maroon/20 px-4 py-2 transition hover:bg-maroon hover:text-cream">
                            Previous
                        </a>
                    <?php endif; ?>

                    <?php for ($i = 1; $i <= $totalPages; $i++): ?>
                        <?php if ($i === $page): ?>
                            <span class="inline-flex items-center rounded-full bg-maroon px-4 py-2 text-cream"><?= $i; ?></span>
                        <?php else: ?>
                            <a href="?page=<?= $i; ?><?= $category ? '&category=' . urlencode($category) : ''; ?>"
                                class="inline-flex items-center rounded-full border border-maroon/20 px-4 py-2 transition hover:bg-maroon hover:text-cream">
                                <?= $i; ?>
                            </a>
                        <?php endif; ?>
                    <?php endfor; ?>

                    <?php if ($page < $totalPages): ?>
                        <a href="?page=<?= $page + 1; ?><?= $category ? '&category=' . urlencode($category) : ''; ?>"
                            class="inline-flex items-center rounded-full border border-maroon/20 px-4 py-2 transition hover:bg-maroon hover:text-cream">
                            Next
                        </a>
                    <?php endif; ?>
                </nav>
            <?php endif; ?>
        </section>
    <?php endif; ?>

    <?php if (!empty($GLOBALS['myclubhub_session']['user_id'])): ?>
        <div class="rounded-3xl border border-teal/20 bg-teal/10 p-6 text-center shadow-lg">
            <strong class="text-xs font-semibold uppercase tracking-[0.24em] text-teal/80">Admin:</strong>
            <a href="https://admin.myclubhub.co.uk/news/"
                class="ml-2 text-xs font-semibold uppercase tracking-[0.24em] text-teal transition hover:text-maroon">
                Manage news posts
            </a>
        </div>
    <?php endif; ?>
</div>

<?php
require_once __DIR__ . '/../../shared/includes/public_footer.php';


