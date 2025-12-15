<?php
require_once __DIR__ . "/../../shared/includes/db_session.php";

$db = getDB();

$postId = isset($_GET['id']) ? (int) $_GET['id'] : 0;
if ($postId <= 0) {
    header('Location: /news/');
    exit;
}

$stmt = $db->prepare("SELECT * FROM news WHERE id = ?");
$stmt->execute([$postId]);
$post = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$post) {
    header('Location: /news/');
    exit;
}

function article_excerpt(string $content, int $limit = 160): string
{
    $clean = trim(preg_replace('/\s+/', ' ', strip_tags($content)));
    if ($clean === '') {
        return 'My Club Hub news article.';
    }

    if (function_exists('mb_strlen') && function_exists('mb_substr')) {
        return mb_strlen($clean) > $limit ? mb_substr($clean, 0, $limit) . '...' : $clean;
    }

    return strlen($clean) > $limit ? substr($clean, 0, $limit) . '...' : $clean;
}

$summary = article_excerpt($post['content'] ?? '');
$category = $post['category'] ?? 'Club News';

$currentPage = 'news';
$pageTitle = $post['title'];
$pageDescription = $summary;
$pageBanner = true;
$pageBannerEyebrow = $category;
$headerTitle = $post['title'];
$headerSubtitle = $category . ' | ' . date('j F Y', strtotime($post['created_at']));

$relatedStmt = $db->prepare("
    SELECT id, title, category, created_at
    FROM news
    WHERE category = ? AND id != ?
    ORDER BY created_at DESC
    LIMIT 3
");
$relatedStmt->execute([$category, $postId]);
$relatedPosts = $relatedStmt->fetchAll(PDO::FETCH_ASSOC);

require_once __DIR__ . '/../../shared/includes/public_header.php';

$scheme = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
$host = $_SERVER['HTTP_HOST'] ?? 'myclubhub.co.uk';
$shareUrl = $scheme . '://' . $host . '/news/post.php?id=' . $postId;
$shareUrlEncoded = urlencode($shareUrl);
$shareTextEncoded = urlencode($post['title']);
?>

<div class="mx-auto max-w-4xl space-y-12 px-4 py-16 md:px-6 lg:px-8">
    <nav class="flex items-center gap-2 text-xs font-semibold uppercase tracking-[0.24em] text-charcoal/60">
        <a href="/news/" class="text-maroon transition hover:text-gold">News</a>
        <span>/</span>
        <span class="text-charcoal/40 line-clamp-1"><?= htmlspecialchars($post['title']); ?></span>
    </nav>

    <article class="space-y-10">
        <header class="space-y-4">
            <span class="inline-flex items-center gap-2 rounded-full border border-maroon/20 px-4 py-1 text-xs font-semibold uppercase tracking-[0.28em] text-burgundy/80">
                <?= htmlspecialchars($category); ?>
            </span>
            <h1 class="font-display text-4xl font-semibold text-maroon md:text-5xl">
                <?= htmlspecialchars($post['title']); ?>
            </h1>
            <div class="flex flex-wrap gap-4 text-xs font-semibold uppercase tracking-[0.24em] text-charcoal/60">
                <span>Published <?= date('l j F Y', strtotime($post['created_at'])); ?></span>
                <?php if (!empty($post['author'])): ?>
                    <span>By <?= htmlspecialchars($post['author']); ?></span>
                <?php endif; ?>
            </div>
        </header>

        <?php if (!empty($post['featured_image'])): ?>
            <figure class="overflow-hidden rounded-4xl border border-maroon/10 shadow-lg">
                <img src="<?= htmlspecialchars($post['featured_image']); ?>" alt="<?= htmlspecialchars($post['title']); ?>" loading="lazy"
                    class="h-full w-full object-cover">
            </figure>
        <?php endif; ?>

        <div class="space-y-10 rounded-4xl border border-maroon/10 bg-white p-8 shadow-lg sm:p-10">
            <div class="prose prose-lg max-w-none text-charcoal prose-headings:font-display prose-headings:text-maroon prose-a:text-burgundy">
                <?= $post['content']; ?>
            </div>

            <div class="space-y-4">
                <span class="text-xs font-semibold uppercase tracking-[0.3em] text-charcoal/50">Share this story</span>
                <div class="flex flex-wrap gap-3">
                    <a class="inline-flex items-center gap-2 rounded-full bg-[#1877F2] px-5 py-2 text-xs font-semibold uppercase tracking-[0.22em] text-white transition hover:-translate-y-1 hover:shadow-lg"
                        href="https://www.facebook.com/sharer/sharer.php?u=<?= $shareUrlEncoded; ?>"
                        target="_blank" rel="noopener">
                        Facebook
                    </a>
                    <a class="inline-flex items-center gap-2 rounded-full bg-[#0EA5E9] px-5 py-2 text-xs font-semibold uppercase tracking-[0.22em] text-[#063970] transition hover:-translate-y-1 hover:shadow-lg"
                        href="https://twitter.com/intent/tweet?url=<?= $shareUrlEncoded; ?>&text=<?= $shareTextEncoded; ?>"
                        target="_blank" rel="noopener">
                        Twitter
                    </a>
                    <a class="inline-flex items-center gap-2 rounded-full bg-[#22C55E] px-5 py-2 text-xs font-semibold uppercase tracking-[0.22em] text-[#064e3b] transition hover:-translate-y-1 hover:shadow-lg"
                        href="https://wa.me/?text=<?= urlencode($post['title'] . ' - ' . $shareUrl); ?>"
                        target="_blank" rel="noopener">
                        WhatsApp
                    </a>
                </div>
            </div>
        </div>
    </article>

    <div class="flex flex-wrap items-center justify-between gap-4 text-xs font-semibold uppercase tracking-[0.24em] text-maroon">
        <a href="/news/"
            class="inline-flex items-center gap-2 rounded-full border border-maroon/20 px-4 py-2 transition hover:bg-maroon hover:text-cream">
            Back to all news
        </a>
        <a href="/news/?category=<?= urlencode($category); ?>"
            class="inline-flex items-center gap-2 rounded-full border border-maroon/20 px-4 py-2 transition hover:bg-maroon hover:text-cream">
            More <?= htmlspecialchars($category); ?> stories
        </a>
    </div>

    <?php if (!empty($relatedPosts)): ?>
        <section class="space-y-6 rounded-3xl border border-maroon/10 bg-white/80 p-8 shadow-lg sm:p-10">
            <div class="flex flex-col gap-3 md:flex-row md:items-end md:justify-between">
                <div>
                    <span class="text-xs font-semibold uppercase tracking-[0.28em] text-burgundy/70">Related reading</span>
                    <h2 class="font-display text-2xl font-semibold text-maroon md:text-3xl">More from this category</h2>
                </div>
            </div>
            <div class="grid gap-6 md:grid-cols-3">
                <?php foreach ($relatedPosts as $related): ?>
                    <article class="rounded-2xl border border-maroon/10 bg-white p-5 shadow transition hover:-translate-y-1 hover:shadow-maroon-xl">
                        <a href="/news/post.php?id=<?= (int) $related['id']; ?>"
                            class="font-display text-lg font-semibold text-maroon transition hover:text-gold line-clamp-2">
                            <?= htmlspecialchars($related['title']); ?>
                        </a>
                        <p class="mt-3 text-xs font-semibold uppercase tracking-[0.24em] text-charcoal/50">
                            <?= htmlspecialchars($related['category']); ?> | <?= date('j F Y', strtotime($related['created_at'])); ?>
                        </p>
                    </article>
                <?php endforeach; ?>
            </div>
        </section>
    <?php endif; ?>

    <?php if (!empty($GLOBALS['myclubhub_session']['user_id'])): ?>
        <div class="rounded-3xl border border-teal/20 bg-teal/10 p-6 text-center shadow-lg">
            <strong class="text-xs font-semibold uppercase tracking-[0.24em] text-teal/80">Admin:</strong>
            <a href="https://admin.myclubhub.co.uk/news/?edit=<?= $postId; ?>"
                class="ml-2 text-xs font-semibold uppercase tracking-[0.24em] text-teal transition hover:text-maroon">
                Edit this article
            </a>
        </div>
    <?php endif; ?>
</div>

<?php
require_once __DIR__ . '/../../shared/includes/public_footer.php';

