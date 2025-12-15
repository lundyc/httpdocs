<?php
require_once __DIR__ . "/../../shared/includes/db_session.php";

$db = getDB();

$competition = trim((string) ($_GET['competition'] ?? $_GET['season'] ?? ''));
$venueFilter = trim((string) ($_GET['venue'] ?? $_GET['match_type'] ?? ''));

$whereConditions = ["status != 'cancelled'"];
$whereParams = [];

if ($competition !== '') {
    $whereConditions[] = "competition = ?";
    $whereParams[] = $competition;
}

if ($venueFilter !== '') {
    if (in_array(strtolower($venueFilter), ['home', 'away'], true)) {
        $whereConditions[] = "is_home = ?";
        $whereParams[] = strtolower($venueFilter) === 'home' ? 1 : 0;
    } elseif ($competition === '') {
        $whereConditions[] = "competition = ?";
        $whereParams[] = $venueFilter;
        $competition = $venueFilter;
        $venueFilter = '';
    }
}

$whereClause = 'WHERE ' . implode(' AND ', $whereConditions);

$stmt = $db->prepare("SELECT * FROM fixtures {$whereClause} ORDER BY match_date ASC, match_time ASC");
$stmt->execute($whereParams);
$fixtures = $stmt->fetchAll(PDO::FETCH_ASSOC);

$now = new DateTime();
$upcomingFixtures = [];
$pastFixtures = [];

foreach ($fixtures as $fixture) {
    $matchDate = $fixture['match_date'] ?? null;
    $matchTime = $fixture['match_time'] ?? null;

    $fixtureDateTime = $matchDate ? new DateTime(trim($matchDate . ' ' . ($matchTime ?: '00:00:00'))) : clone $now;
    if ($fixtureDateTime >= $now) {
        $upcomingFixtures[] = $fixture;
    } else {
        $pastFixtures[] = $fixture;
    }
}

$seasonsStmt = $db->query("SELECT DISTINCT competition FROM fixtures WHERE competition IS NOT NULL AND competition != '' ORDER BY competition ASC");
$competitionOptions = $seasonsStmt->fetchAll(PDO::FETCH_COLUMN);

$nextFixture = !empty($upcomingFixtures) ? $upcomingFixtures[0] : null;
$nextFixtureDate = $nextFixture ? new DateTime($nextFixture['match_date']) : null;
$nextFixtureTime = ($nextFixture && !empty($nextFixture['match_time']))
    ? new DateTime($nextFixture['match_time'])
    : null;
$nextFixtureVenue = $nextFixture
    ? (!empty($nextFixture['venue'])
        ? $nextFixture['venue']
        : (($nextFixture['is_home'] ?? false) ? 'Home fixture' : 'Away fixture'))
    : null;

$currentPage = 'fixtures';
$pageTitle = 'Fixtures';
$pageBanner = true;
$pageBannerEyebrow = 'Match centre';
$headerTitle = 'Every fixture, result, and venue in one place';
$headerSubtitle = 'Plan the next matchday or revisit recent results for My Club Hub.';

require_once __DIR__ . '/../../shared/includes/public_header.php';
?>

<div class="mx-auto max-w-7xl space-y-12 px-4 py-16 md:px-6 lg:px-8">
    <?php if ($nextFixture): ?>
        <section class="rounded-4xl border border-maroon/15 bg-white/80 p-8 shadow-lg sm:p-12">
            <div class="flex flex-col gap-6 lg:flex-row lg:items-center lg:justify-between">
                <div class="space-y-4">
                    <span class="inline-flex items-center gap-2 rounded-full border border-gold/40 px-4 py-1 text-xs font-semibold uppercase tracking-[0.3em] text-burgundy/80">
                        Next opponent
                    </span>
                    <h2 class="font-display text-3xl font-semibold text-maroon md:text-4xl">
                        My Club Hub vs <?= htmlspecialchars($nextFixture['opponent']); ?>
                    </h2>
                    <p class="text-sm font-semibold uppercase tracking-[0.24em] text-charcoal/60">
                        <?= $nextFixtureDate ? $nextFixtureDate->format('l j F Y') : 'Date TBC'; ?>
                        <?php if ($nextFixtureTime): ?>
                            <span class="mx-2 text-charcoal/40">|</span><?= $nextFixtureTime->format('g:i A'); ?>
                        <?php endif; ?>
                        <?php if ($nextFixtureVenue): ?>
                            <span class="mx-2 text-charcoal/40">|</span><?= htmlspecialchars($nextFixtureVenue); ?>
                        <?php endif; ?>
                    </p>
                    <?php if (!empty($nextFixture['competition'])): ?>
                        <span class="inline-flex items-center gap-2 rounded-full bg-maroon/10 px-4 py-2 text-xs font-semibold uppercase tracking-[0.28em] text-burgundy">
                            <?= htmlspecialchars($nextFixture['competition']); ?>
                        </span>
                    <?php endif; ?>
                </div>
                <div class="flex flex-col gap-3 text-xs font-semibold uppercase tracking-[0.24em] text-maroon">
                    <div class="rounded-3xl border border-maroon/15 bg-cream px-5 py-4 text-center shadow-inner">
                        <p id="next-fixture-countdown" class="text-sm font-semibold uppercase tracking-[0.24em] text-maroon">
                            Countdown loading...
                        </p>
                    </div>
                    <div class="flex flex-wrap gap-3">
                        <a href="/fixtures/?competition=<?= urlencode($nextFixture['competition'] ?? ''); ?>"
                            class="inline-flex items-center gap-2 rounded-full border border-maroon/20 px-5 py-2 transition hover:bg-maroon hover:text-cream">
                            View competition
                        </a>
                        <a href="/history/"
                            class="inline-flex items-center gap-2 rounded-full border border-maroon/20 px-5 py-2 transition hover:bg-maroon hover:text-cream">
                            Match archive
                        </a>
                    </div>
                </div>
            </div>
        </section>
    <?php else: ?>
        <section class="rounded-4xl border border-dashed border-maroon/20 bg-white/70 p-10 text-center shadow-lg">
            <h2 class="font-display text-2xl text-maroon">Competitive fixtures return soon</h2>
            <p class="mt-2 text-sm text-charcoal/70">
                We are finalising pre-season opponents and community friendlies. Check back shortly for the full calendar.
            </p>
            <div class="mt-6 flex flex-wrap justify-center gap-3 text-xs font-semibold uppercase tracking-[0.24em]">
                <a href="/news/" class="inline-flex items-center gap-2 rounded-full border border-maroon/20 px-4 py-2 text-maroon transition hover:bg-maroon hover:text-cream">
                    Club updates
                </a>
                <a href="/history/" class="inline-flex items-center gap-2 rounded-full border border-maroon/20 px-4 py-2 text-maroon transition hover:bg-maroon hover:text-cream">
                    Relive classic matches
                </a>
            </div>
        </section>
    <?php endif; ?>

    <section class="rounded-4xl border border-maroon/15 bg-white/80 p-8 shadow-lg sm:p-12">
        <div class="flex flex-col gap-6 lg:flex-row lg:items-end lg:justify-between">
            <div class="space-y-3">
                <span class="inline-flex items-center gap-2 rounded-full border border-gold/30 px-4 py-1 text-xs font-semibold uppercase tracking-[0.3em] text-burgundy/80">
                    Filters
                </span>
                <h2 class="font-display text-3xl font-semibold text-maroon md:text-4xl">Refine the fixture list</h2>
                <p class="max-w-2xl text-sm text-charcoal/70">
                    Narrow the list by competition or venue to focus on the matches that matter to you and your squad.
                </p>
            </div>
        </div>
        <form method="get" class="mt-8 space-y-6">
            <div class="grid gap-6 md:grid-cols-2">
                <label class="flex flex-col gap-2 text-xs font-semibold uppercase tracking-[0.28em] text-charcoal/60">
                    Competition
                    <select name="competition"
                        class="w-full rounded-2xl border border-maroon/15 bg-cream px-4 py-3 text-sm font-semibold uppercase tracking-[0.22em] text-maroon focus:border-maroon focus:outline-none focus:ring-2 focus:ring-maroon/30">
                        <option value="">All competitions</option>
                        <?php foreach ($competitionOptions as $option): ?>
                            <option value="<?= htmlspecialchars($option); ?>" <?= $competition === $option ? 'selected' : ''; ?>>
                                <?= htmlspecialchars($option); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </label>
                <label class="flex flex-col gap-2 text-xs font-semibold uppercase tracking-[0.28em] text-charcoal/60">
                    Venue
                    <select name="venue"
                        class="w-full rounded-2xl border border-maroon/15 bg-cream px-4 py-3 text-sm font-semibold uppercase tracking-[0.22em] text-maroon focus:border-maroon focus:outline-none focus:ring-2 focus:ring-maroon/30">
                        <option value="">Home and away</option>
                        <option value="home" <?= strtolower($venueFilter) === 'home' ? 'selected' : ''; ?>>Home fixtures</option>
                        <option value="away" <?= strtolower($venueFilter) === 'away' ? 'selected' : ''; ?>>Away fixtures</option>
                    </select>
                </label>
            </div>
            <div class="flex flex-wrap gap-3 text-xs font-semibold uppercase tracking-[0.24em]">
                <button type="submit"
                    class="inline-flex items-center gap-2 rounded-full bg-maroon px-5 py-2 text-cream transition hover:-translate-y-1 hover:shadow-maroon-xl">
                    Apply filters
                </button>
                <a href="/fixtures/"
                    class="inline-flex items-center gap-2 rounded-full border border-maroon/20 px-5 py-2 text-maroon transition hover:bg-maroon hover:text-cream">
                    Reset
                </a>
            </div>
        </form>
    </section>

    <section class="space-y-6">
        <div class="flex flex-col gap-3 md:flex-row md:items-end md:justify-between">
            <div>
                <span class="text-xs font-semibold uppercase tracking-[0.28em] text-burgundy/70">Upcoming</span>
                <h2 class="font-display text-3xl font-semibold text-maroon md:text-4xl">Next fixtures on the horizon</h2>
            </div>
        </div>
        <?php if (!empty($upcomingFixtures)): ?>
            <div class="grid gap-4">
                <?php foreach ($upcomingFixtures as $fixture): ?>
                    <?php
                    $fixtureDate = !empty($fixture['match_date']) ? new DateTime($fixture['match_date']) : null;
                    $fixtureTime = !empty($fixture['match_time']) ? new DateTime($fixture['match_time']) : null;
                    $fixtureVenue = !empty($fixture['venue'])
                        ? $fixture['venue']
                        : (($fixture['is_home'] ?? false) ? 'Home fixture' : 'Away fixture');
                    ?>
                    <article class="flex flex-col gap-4 rounded-3xl border border-maroon/10 bg-white px-6 py-5 shadow-lg transition hover:-translate-y-1 hover:shadow-maroon-xl md:flex-row md:items-center md:justify-between">
                        <div class="space-y-2">
                            <p class="text-xs font-semibold uppercase tracking-[0.28em] text-burgundy/70">
                                <?= htmlspecialchars($fixture['competition'] ?? 'Competition TBC'); ?>
                            </p>
                            <h3 class="font-display text-xl font-semibold text-maroon">
                                My Club Hub vs <?= htmlspecialchars($fixture['opponent'] ?? 'TBC'); ?>
                            </h3>
                            <p class="text-xs font-semibold uppercase tracking-[0.24em] text-charcoal/60">
                                <?= $fixtureDate ? $fixtureDate->format('l j F Y') : 'Date TBC'; ?>
                                <?php if ($fixtureTime): ?>
                                    <span class="mx-2 text-charcoal/40">|</span><?= $fixtureTime->format('g:i A'); ?>
                                <?php endif; ?>
                                <span class="mx-2 text-charcoal/40">|</span><?= htmlspecialchars($fixtureVenue); ?>
                            </p>
                        </div>
                        <?php if (!empty($fixture['status_detail']) || !empty($fixture['status'])): ?>
                            <span class="rounded-full bg-maroon/10 px-4 py-2 text-xs font-semibold uppercase tracking-[0.24em] text-burgundy">
                                <?= htmlspecialchars($fixture['status_detail'] ?? ucfirst($fixture['status'])); ?>
                            </span>
                        <?php endif; ?>
                    </article>
                <?php endforeach; ?>
            </div>
        <?php else: ?>
            <div class="rounded-3xl border border-dashed border-maroon/20 bg-white/70 p-10 text-center shadow-lg">
                <h3 class="font-display text-2xl text-maroon">No upcoming fixtures posted</h3>
                <p class="mt-2 text-sm text-charcoal/70">Fixtures will appear here once the new schedule is confirmed.</p>
            </div>
        <?php endif; ?>
    </section>

    <section class="space-y-6">
        <div class="flex flex-col gap-3 md:flex-row md:items-end md:justify-between">
            <div>
                <span class="text-xs font-semibold uppercase tracking-[0.28em] text-burgundy/70">Final whistles</span>
                <h2 class="font-display text-3xl font-semibold text-maroon md:text-4xl">Recent results</h2>
            </div>
        </div>
        <?php if (!empty($pastFixtures)): ?>
            <div class="grid gap-4">
                <?php foreach ($pastFixtures as $fixture): ?>
                    <?php
                    $fixtureDate = !empty($fixture['match_date']) ? new DateTime($fixture['match_date']) : null;
                    $fixtureVenue = !empty($fixture['venue'])
                        ? $fixture['venue']
                        : (($fixture['is_home'] ?? false) ? 'Home fixture' : 'Away fixture');
                    $hasScore = isset($fixture['home_score'], $fixture['away_score']) &&
                        $fixture['home_score'] !== null && $fixture['away_score'] !== null;
                    ?>
                    <article class="flex flex-col gap-4 rounded-3xl border border-maroon/10 bg-white px-6 py-5 shadow-lg transition hover:-translate-y-1 hover:shadow-maroon-xl md:flex-row md:items-center md:justify-between">
                        <div class="space-y-2">
                            <p class="text-xs font-semibold uppercase tracking-[0.28em] text-burgundy/70">
                                <?= htmlspecialchars($fixture['competition'] ?? 'Competition TBC'); ?>
                            </p>
                            <h3 class="font-display text-xl font-semibold text-maroon">
                                My Club Hub vs <?= htmlspecialchars($fixture['opponent'] ?? 'TBC'); ?>
                            </h3>
                            <p class="text-xs font-semibold uppercase tracking-[0.24em] text-charcoal/60">
                                <?= $fixtureDate ? $fixtureDate->format('l j F Y') : 'Date TBC'; ?>
                                <span class="mx-2 text-charcoal/40">|</span><?= htmlspecialchars($fixtureVenue); ?>
                            </p>
                        </div>
                        <div class="flex items-center gap-4">
                            <?php if ($hasScore): ?>
                                <div class="rounded-2xl border border-maroon/10 bg-cream px-4 py-3 text-center">
                                    <p class="text-xs font-semibold uppercase tracking-[0.24em] text-maroon/70">Final score</p>
                                    <p class="text-2xl font-display font-semibold text-maroon">
                                        <?= (int) $fixture['home_score']; ?>
                                        <span class="mx-1 text-base font-sans text-maroon/60">-</span>
                                        <?= (int) $fixture['away_score']; ?>
                                    </p>
                                </div>
                            <?php endif; ?>
                            <?php if (!empty($fixture['status_detail']) || !empty($fixture['status'])): ?>
                                <span class="rounded-full bg-maroon/10 px-4 py-2 text-xs font-semibold uppercase tracking-[0.24em] text-burgundy">
                                    <?= htmlspecialchars($fixture['status_detail'] ?? ucfirst($fixture['status'])); ?>
                                </span>
                            <?php endif; ?>
                        </div>
                    </article>
                <?php endforeach; ?>
            </div>
        <?php else: ?>
            <div class="rounded-3xl border border-dashed border-maroon/20 bg-white/70 p-10 text-center shadow-lg">
                <h3 class="font-display text-2xl text-maroon">Results will land soon</h3>
                <p class="mt-2 text-sm text-charcoal/70">Once fixtures have been played, final scorelines will be displayed here.</p>
            </div>
        <?php endif; ?>
    </section>

    <?php if (!empty($GLOBALS['myclubhub_session']['user_id'])): ?>
        <div class="rounded-3xl border border-teal/20 bg-teal/10 p-6 text-center shadow-lg">
            <strong class="text-xs font-semibold uppercase tracking-[0.24em] text-teal/80">Admin:</strong>
            <a href="https://admin.myclubhub.co.uk/fixtures/"
                class="ml-2 text-xs font-semibold uppercase tracking-[0.24em] text-teal transition hover:text-maroon">
                Manage fixtures
            </a>
        </div>
    <?php endif; ?>
</div>

<?php if ($nextFixture): ?>
    <script>
        (function () {
            const countdownTarget = document.getElementById('next-fixture-countdown');
            if (!countdownTarget) return;

            const targetDate = new Date('<?= $nextFixture['match_date']; ?> <?= $nextFixture['match_time'] ?? '12:00:00'; ?>');

            function updateCountdown() {
                const now = new Date();
                const diff = targetDate - now;

                if (diff <= 0) {
                    countdownTarget.textContent = 'Kick-off imminent';
                    return;
                }

                const days = Math.floor(diff / (1000 * 60 * 60 * 24));
                const hours = Math.floor((diff / (1000 * 60 * 60)) % 24);
                const minutes = Math.floor((diff / (1000 * 60)) % 60);

                countdownTarget.textContent = 'Kick-off in ' + days + 'd ' + hours + 'h ' + minutes + 'm';
            }

            updateCountdown();
            setInterval(updateCountdown, 60000);
        })();
    </script>
<?php endif; ?>

<?php
require_once __DIR__ . '/../../shared/includes/public_footer.php';

