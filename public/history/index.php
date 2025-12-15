<?php
$currentPage = 'history';
$pageTitle = 'History';
$pageBanner = true;
$pageBannerEyebrow = 'Heritage';
$headerTitle = 'My Club Hub across the decades';
$headerSubtitle = 'Discover how a grassroots dream evolved into a modern community football movement.';

require_once __DIR__ . '/../../shared/includes/public_header.php';

$heritageColumns = [
    [
        'eyebrow' => 'Foundations',
        'title' => 'Built by local pioneers',
        'text' => 'In the 1920s a group of local volunteers founded the club to give the town a competitive heartbeat. Their commitment still shapes how we serve supporters today.',
    ],
    [
        'eyebrow' => 'Silverware',
        'title' => 'Moments that defined us',
        'text' => 'League titles, cup runs, and derby victories have created a tapestry of memories that unite generations of players and fans.',
    ],
    [
        'eyebrow' => 'Community',
        'title' => 'More than ninety minutes',
        'text' => 'Our outreach projects, charity partnerships, and school sessions keep My Club Hub rooted in everyday life across every community.',
    ],
    [
        'eyebrow' => 'Identity',
        'title' => 'A badge and colours to believe in',
        'text' => 'The My Club Hub crest, paired with bold modern colours, reflects resilience, ambition, and unity - the values we bring to every matchday.',
    ],
];

$timeline = [
    [
        'period' => '1920s',
        'title' => 'Club foundation',
        'body' => 'My Club Hub is formed by local players and volunteers, laying the groundwork for organised community football.',
    ],
    [
        'period' => '1950s',
        'title' => 'Ground development',
        'body' => 'Purpose-built changing rooms and spectator areas arrive, elevating the matchday experience for the town.',
    ],
    [
        'period' => '1980s',
        'title' => 'Youth revolution',
        'body' => 'A structured academy pathway is introduced, nurturing homegrown talent and future club leaders.',
    ],
    [
        'period' => '2000s',
        'title' => 'Modern era',
        'body' => 'Enhanced sports science, coaching frameworks, and governance take the club into a new professional chapter.',
    ],
    [
        'period' => '2020s',
        'title' => 'Digital transformation',
        'body' => 'Digital platforms, data-informed decision making, and hybrid training models redefine how the club operates.',
    ],
];
?>

<div class="mx-auto max-w-6xl space-y-16 px-4 py-16 md:px-6 lg:px-8">
    <section class="space-y-10 rounded-4xl border border-maroon/10 bg-white/80 p-10 shadow-lg sm:p-12">
        <div class="space-y-4 text-center">
            <span class="inline-flex items-center gap-2 rounded-full border border-gold/40 px-4 py-1 text-xs font-semibold uppercase tracking-[0.32em] text-burgundy/80">
                DNA
            </span>
            <h2 class="font-display text-3xl font-semibold text-maroon md:text-4xl">What anchors My Club Hub</h2>
            <p class="mx-auto max-w-3xl text-sm text-charcoal/70">
                Four pillars power our progress - the people who built the club, the trophies we lift, the community we serve, and the identity we celebrate.
            </p>
        </div>
        <div class="grid gap-6 md:grid-cols-2">
            <?php foreach ($heritageColumns as $panel): ?>
                <article class="rounded-3xl border border-maroon/10 bg-white p-6 text-left shadow-lg transition hover:-translate-y-1 hover:shadow-maroon-xl">
                    <span class="text-xs font-semibold uppercase tracking-[0.3em] text-burgundy/70">
                        <?= htmlspecialchars($panel['eyebrow']); ?>
                    </span>
                    <h3 class="mt-3 font-display text-xl font-semibold text-maroon">
                        <?= htmlspecialchars($panel['title']); ?>
                    </h3>
                    <p class="mt-3 text-sm text-charcoal/70">
                        <?= htmlspecialchars($panel['text']); ?>
                    </p>
                </article>
            <?php endforeach; ?>
        </div>
    </section>

    <section class="space-y-10 rounded-4xl border border-maroon/10 bg-white/80 p-10 shadow-lg sm:p-12">
        <div class="space-y-4 text-center">
            <span class="inline-flex items-center gap-2 rounded-full border border-gold/40 px-4 py-1 text-xs font-semibold uppercase tracking-[0.32em] text-burgundy/80">
                Timeline
            </span>
            <h2 class="font-display text-3xl font-semibold text-maroon md:text-4xl">Milestones that shaped the club</h2>
            <p class="mx-auto max-w-3xl text-sm text-charcoal/70">
                Step through the decades to see how My Club Hub has adapted, grown, and continued to champion football for all.
            </p>
        </div>
        <div class="relative">
            <span class="absolute left-4 top-0 hidden h-full w-px bg-maroon/10 sm:block"></span>
            <div class="space-y-8">
                <?php foreach ($timeline as $entry): ?>
                    <article class="relative flex flex-col gap-3 rounded-3xl border border-maroon/10 bg-cream/60 p-6 shadow-inner transition hover:-translate-y-1 hover:shadow-maroon-xl sm:pl-14">
                        <span class="absolute left-0 top-6 hidden h-3 w-3 -translate-x-1/2 rounded-full border border-maroon bg-gold sm:block"></span>
                        <span class="text-xs font-semibold uppercase tracking-[0.34em] text-burgundy">
                            <?= htmlspecialchars($entry['period']); ?>
                        </span>
                        <h3 class="font-display text-xl font-semibold text-maroon">
                            <?= htmlspecialchars($entry['title']); ?>
                        </h3>
                        <p class="text-sm text-charcoal/70">
                            <?= htmlspecialchars($entry['body']); ?>
                        </p>
                    </article>
                <?php endforeach; ?>
            </div>
        </div>
    </section>

    <section class="rounded-4xl border border-maroon/10 bg-maroon-noise text-cream shadow-lg">
        <div class="relative z-10 flex flex-col gap-6 px-8 py-16 text-center sm:px-12 lg:px-16">
            <h2 class="font-display text-3xl font-semibold text-cream md:text-4xl">
                Add your chapter to the archive
            </h2>
            <p class="mx-auto max-w-2xl text-sm text-cream/80">
                Do you have programmes, photos, or stories from My Club Hub? Share them with our heritage team to help preserve the club's legacy for future generations.
            </p>
            <div class="flex flex-wrap justify-center gap-4 text-xs font-semibold uppercase tracking-[0.24em]">
                <a href="mailto:history@myclubhub.co.uk"
                    class="inline-flex items-center gap-2 rounded-full bg-gold px-6 py-3 text-maroon shadow-gold transition hover:-translate-y-1 hover:shadow-gold-soft">
                    Share a memory
                </a>
                <a href="/news/"
                    class="inline-flex items-center gap-2 rounded-full border border-gold/40 px-6 py-3 text-gold transition hover:bg-gold hover:text-maroon">
                    Club newsroom
                </a>
            </div>
        </div>
    </section>
</div>

<?php
require_once __DIR__ . '/../../shared/includes/public_footer.php';


