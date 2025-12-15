<?php
require_once __DIR__ . "/../../shared/includes/db_session.php";

$db = getDB();

$stmt = $db->prepare("
    SELECT *
    FROM sponsors
    WHERE end_date IS NULL OR end_date >= CURDATE()
    ORDER BY 
        CASE tier
            WHEN 'Main' THEN 1
            WHEN 'Partner' THEN 2
            WHEN 'Supporter' THEN 3
            ELSE 4
        END,
        company_name ASC
");
$stmt->execute();
$sponsors = $stmt->fetchAll(PDO::FETCH_ASSOC);

$sponsorsByTier = [];
foreach ($sponsors as $sponsor) {
    $tier = $sponsor['tier'] ?: 'Supporter';
    $sponsorsByTier[$tier][] = $sponsor;
}

$tierInfo = [
    'Main' => [
        'title' => 'Principal partners',
        'description' => 'Flagship collaborators whose investment powers the club on and off the pitch.',
    ],
    'Partner' => [
        'title' => 'Club partners',
        'description' => 'Businesses partnering with us to enhance programmes, facilities, and people.',
    ],
    'Supporter' => [
        'title' => 'Community supporters',
        'description' => 'Local champions backing My Club Hub football through funding and expertise.',
    ],
];

$currentPage = 'sponsors';
$pageTitle = 'Sponsors';
$pageBanner = true;
$pageBannerEyebrow = 'Partnerships';
$headerTitle = 'Proud to be powered by local partners';
$headerSubtitle = 'Investment from businesses and supporters keeps My Club Hub thriving for every generation.';
$yearsBacking = (int) date('Y') - 1920;

require_once __DIR__ . '/../../shared/includes/public_header.php';
?>

<div class="mx-auto max-w-7xl space-y-16 px-4 py-16 md:px-6 lg:px-8">
    <section class="rounded-4xl border border-maroon/15 bg-white/80 p-10 shadow-lg sm:p-12">
        <div class="grid gap-8 lg:grid-cols-2">
            <div class="space-y-4">
                <span class="inline-flex items-center gap-2 rounded-full border border-gold/40 px-4 py-1 text-xs font-semibold uppercase tracking-[0.32em] text-burgundy/80">
                    Impact
                </span>
                <h2 class="font-display text-3xl font-semibold text-maroon md:text-4xl">
                    Partnerships that elevate My Club Hub
                </h2>
                <p class="text-sm text-charcoal/70">
                    Every contribution funds coaching projects, youth scholarships, facility upgrades, and community outreach.
                    Together we deliver modern football experiences for the My Club Hub community.
                </p>
            </div>
            <div class="grid gap-4 sm:grid-cols-2">
                <div class="rounded-3xl border border-maroon/10 bg-cream/70 p-6 text-center shadow-inner">
                    <p class="text-3xl font-display font-semibold text-maroon"><?= count($sponsors); ?></p>
                    <p class="mt-2 text-xs font-semibold uppercase tracking-[0.24em] text-charcoal/60">Active sponsors</p>
                </div>
                <div class="rounded-3xl border border-maroon/10 bg-cream/70 p-6 text-center shadow-inner">
                    <p class="text-3xl font-display font-semibold text-maroon"><?= count($sponsorsByTier); ?></p>
                    <p class="mt-2 text-xs font-semibold uppercase tracking-[0.24em] text-charcoal/60">Partnership tiers</p>
                </div>
                <div class="rounded-3xl border border-maroon/10 bg-cream/70 p-6 text-center shadow-inner">
                    <p class="text-3xl font-display font-semibold text-maroon">100%</p>
                    <p class="mt-2 text-xs font-semibold uppercase tracking-[0.24em] text-charcoal/60">Community backed</p>
                </div>
                <div class="rounded-3xl border border-maroon/10 bg-cream/70 p-6 text-center shadow-inner">
                    <p class="text-3xl font-display font-semibold text-maroon"><?= $yearsBacking; ?>+</p>
                    <p class="mt-2 text-xs font-semibold uppercase tracking-[0.24em] text-charcoal/60">Years of support</p>
                </div>
            </div>
        </div>
    </section>

    <?php if (!empty($sponsorsByTier)): ?>
        <section class="space-y-10">
            <div class="space-y-4 text-center">
                <span class="inline-flex items-center gap-2 rounded-full border border-gold/40 px-4 py-1 text-xs font-semibold uppercase tracking-[0.32em] text-burgundy/80">
                    Partners
                </span>
                <h2 class="font-display text-3xl font-semibold text-maroon md:text-4xl">
                    Meet the businesses backing the badge
                </h2>
                <p class="mx-auto max-w-3xl text-sm text-charcoal/70">
                    Our partners share our belief that football can build stronger communities. Explore who is standing alongside the club.
                </p>
            </div>

            <div class="space-y-12">
                <?php foreach (['Main', 'Partner', 'Supporter'] as $tier): ?>
                    <?php if (!empty($sponsorsByTier[$tier])): ?>
                        <article class="rounded-4xl border border-maroon/10 bg-white/80 p-8 shadow-lg sm:p-10">
                            <div class="flex flex-col gap-4 border-b border-maroon/10 pb-6 md:flex-row md:items-center md:justify-between">
                                <div>
                                    <h3 class="font-display text-2xl font-semibold text-maroon">
                                        <?= htmlspecialchars($tierInfo[$tier]['title'] ?? $tier); ?>
                                    </h3>
                                    <p class="mt-2 text-sm text-charcoal/70">
                                        <?= htmlspecialchars($tierInfo[$tier]['description'] ?? ''); ?>
                                    </p>
                                </div>
                                <span class="rounded-full bg-maroon/10 px-4 py-2 text-xs font-semibold uppercase tracking-[0.24em] text-burgundy">
                                    <?= count($sponsorsByTier[$tier]); ?> partners
                                </span>
                            </div>
                            <div class="mt-6 grid gap-6 sm:grid-cols-2 lg:grid-cols-3">
                                <?php foreach ($sponsorsByTier[$tier] as $sponsor): ?>
                                    <div class="group relative overflow-hidden rounded-3xl border border-maroon/10 bg-cream/70 p-6 text-center shadow-lg transition hover:-translate-y-1 hover:shadow-maroon-xl">
                                        <div class="flex h-24 items-center justify-center rounded-2xl bg-white shadow-inner">
                                            <?php if (!empty($sponsor['logo'])): ?>
                                                <img src="<?= htmlspecialchars($sponsor['logo']); ?>"
                                                    alt="<?= htmlspecialchars($sponsor['company_name']); ?>" loading="lazy"
                                                    class="max-h-20 max-w-full object-contain transition duration-500 group-hover:scale-105">
                                            <?php else: ?>
                                                <span class="text-xs font-semibold uppercase tracking-[0.3em] text-maroon/60">
                                                    <?= htmlspecialchars($sponsor['company_name']); ?>
                                                </span>
                                            <?php endif; ?>
                                        </div>
                                        <div class="mt-4 space-y-2">
                                            <h4 class="font-display text-lg font-semibold text-maroon">
                                                <?= htmlspecialchars($sponsor['company_name']); ?>
                                            </h4>
                                            <?php if (!empty($sponsor['website'])): ?>
                                                <a href="<?= htmlspecialchars($sponsor['website']); ?>" target="_blank" rel="noopener"
                                                    class="inline-flex items-center gap-2 text-xs font-semibold uppercase tracking-[0.24em] text-burgundy transition hover:text-maroon">
                                                    Visit website <span aria-hidden="true">→</span>
                                                </a>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        </article>
                    <?php endif; ?>
                <?php endforeach; ?>
            </div>
        </section>
    <?php else: ?>
        <section class="rounded-4xl border border-dashed border-maroon/20 bg-white/70 p-12 text-center shadow-lg">
            <h3 class="font-display text-2xl text-maroon">We are welcoming new partners</h3>
            <p class="mt-2 text-sm text-charcoal/70">
                Ready to support My Club Hub football? Let us co-create a package that delivers impact for your business and the community.
            </p>
        </section>
    <?php endif; ?>

    <section class="rounded-4xl border border-maroon/10 bg-maroon-noise text-cream shadow-lg">
        <div class="relative z-10 flex flex-col gap-6 px-8 py-16 text-center sm:px-12 lg:px-16">
            <h2 class="font-display text-3xl font-semibold text-cream md:text-4xl">
                Create a partnership with purpose
            </h2>
            <p class="mx-auto max-w-2xl text-sm text-cream/80">
                From shirt placement to digital campaigns and community activations, we craft tailored partnerships that align with your goals.
                Start a conversation with the commercial team today.
            </p>
            <div class="flex flex-wrap justify-center gap-4 text-xs font-semibold uppercase tracking-[0.24em]">
                <a href="mailto:sponsor@myclubhub.co.uk"
                    class="inline-flex items-center gap-2 rounded-full bg-gold px-6 py-3 text-maroon shadow-gold transition hover:-translate-y-1 hover:shadow-gold-soft">
                    Start a conversation
                </a>
                <a href="/history/"
                    class="inline-flex items-center gap-2 rounded-full border border-gold/40 px-6 py-3 text-gold transition hover:bg-gold hover:text-maroon">
                    Discover our story
                </a>
            </div>
        </div>
    </section>

    <?php if (!empty($GLOBALS['myclubhub_session']['user_id'])): ?>
        <div class="rounded-3xl border border-teal/20 bg-teal/10 p-6 text-center shadow-lg">
            <strong class="text-xs font-semibold uppercase tracking-[0.24em] text-teal/80">Admin:</strong>
            <a href="https://admin.myclubhub.co.uk/sponsors/"
                class="ml-2 text-xs font-semibold uppercase tracking-[0.24em] text-teal transition hover:text-maroon">
                Manage sponsors
            </a>
        </div>
    <?php endif; ?>
</div>

<?php
require_once __DIR__ . '/../../shared/includes/public_footer.php';

