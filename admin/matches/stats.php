<?php
require_once __DIR__ . '/../../shared/includes/session_init.php';
require_once __DIR__ . '/../../shared/includes/simple_session.php';
require_once __DIR__ . '/../../portal/auth/auth_functions.php';
require_once __DIR__ . '/../../portal/auth/middleware.php';

checkRole([1, 2, 3]);
$db = getDB();

$fixtureId = (int)($_GET['fixture_id'] ?? 0);
if (!$fixtureId) die('Missing fixture_id');

/* -------------------------------------------------------------
   FETCH MATCH + TEAM NAMES
------------------------------------------------------------- */
$stmt = $db->prepare("
    SELECT f.id, f.home_team_id, f.away_team_id, 
           ht.name AS home_team, at.name AS away_team
    FROM fixtures f
    LEFT JOIN teams ht ON f.home_team_id = ht.id
    LEFT JOIN teams at ON f.away_team_id = at.id
    WHERE f.id = :id
");
$stmt->execute([':id' => $fixtureId]);
$fixture = $stmt->fetch(PDO::FETCH_ASSOC);
if (!$fixture) die('Fixture not found');

$homeTeam = $fixture['home_team'];
$awayTeam = $fixture['away_team'];

/* -------------------------------------------------------------
   EXISTING STATS (if any)
------------------------------------------------------------- */
$getStats = $db->prepare("SELECT * FROM match_stats WHERE fixture_id = :id");
$getStats->execute([':id' => $fixtureId]);
$statsRows = $getStats->fetchAll(PDO::FETCH_ASSOC);

$stats = ['home' => [], 'away' => []];
foreach ($statsRows as $row) {
        if ($row['team_id'] == $fixture['home_team_id']) $stats['home'] = $row;
        if ($row['team_id'] == $fixture['away_team_id']) $stats['away'] = $row;
}

/* -------------------------------------------------------------
   PAGE CONFIG
------------------------------------------------------------- */
$pageTitle        = 'Match Stats';
$pageContextLabel = 'Match operations';
$pageTitleText    = "Stats – {$fixture['home_team']} vs {$fixture['away_team']}";

require_once __DIR__ . '/../includes/layout_start.php';
$activeNav = 'matches';
require_once __DIR__ . '/../includes/navigation.php';
?>
<main class="flex-1 bg-slate-900/40 backdrop-blur">
        <?php require_once __DIR__ . '/../includes/header.php'; ?>

        <section class="mx-auto max-w-5xl px-6 py-10">
                <div class="rounded-3xl border border-white/5 bg-slate-900/70 p-8 shadow-maroon-lg">
                        <h2 class="text-xl font-semibold text-cream mb-4">
                                Enter Match Statistics<br>
                                <span class="text-slate-400 text-sm"><?= htmlspecialchars($homeTeam) ?> vs <?= htmlspecialchars($awayTeam) ?></span>
                        </h2>

                        <form method="post" action="save_stats.php" class="space-y-4" onsubmit="return validatePossession();">
                                <input type="hidden" name="fixture_id" value="<?= $fixtureId; ?>">
                                <input type="hidden" name="home_team_id" value="<?= $fixture['home_team_id']; ?>">
                                <input type="hidden" name="away_team_id" value="<?= $fixture['away_team_id']; ?>">

                                <table class="w-full text-sm text-slate-200 border-separate border-spacing-y-3">
                                        <thead class="text-xs uppercase text-slate-400 bg-slate-900/60">
                                                <tr>
                                                        <th class="text-right w-[35%]"><?= htmlspecialchars($homeTeam); ?></th>
                                                        <th class="text-center w-[30%]">Statistic</th>
                                                        <th class="text-left w-[35%]"><?= htmlspecialchars($awayTeam); ?></th>
                                                </tr>
                                        </thead>
                                        <tbody>
                                                <?php
                                                $fields = [
                                                        'possession'      => 'Possession (%)',
                                                        'shots_total'     => 'Shots Total',
                                                        'shots_on_target' => 'Shots On Target',
                                                        'corners'         => 'Corners',
                                                        'freekicks'       => 'Free Kicks',
                                                        'fouls'           => 'Fouls',
                                                        'offsides'        => 'Offsides',
                                                        'yellow_cards'    => 'Yellow Cards',
                                                        'red_cards'       => 'Red Cards'
                                                ];
                                                foreach ($fields as $key => $label): ?>
                                                        <tr class="bg-slate-900/60 hover:bg-slate-800/60 rounded-xl transition">
                                                                <td class="text-right pr-3 py-1">
                                                                        <input type="number" step="1" min="0"
                                                                                name="home[<?= $key; ?>]"
                                                                                value="<?= htmlspecialchars($stats['home'][$key] ?? ''); ?>"
                                                                                class="w-24 text-center rounded-md bg-slate-800/70 border border-white/10 px-2 py-1 text-slate-100 focus:border-gold/40 focus:ring-0 no-spinner"
                                                                                <?= $key === 'possession' ? 'id="home-possession"' : '' ?>>
                                                                </td>
                                                                <td class="text-center py-1 text-slate-300 font-medium"><?= $label; ?></td>
                                                                <td class="text-left pl-3 py-1">
                                                                        <input type="number" step="1" min="0"
                                                                                name="away[<?= $key; ?>]"
                                                                                value="<?= htmlspecialchars($stats['away'][$key] ?? ''); ?>"
                                                                                class="w-24 text-center rounded-md bg-slate-800/70 border border-white/10 px-2 py-1 text-slate-100 focus:border-gold/40 focus:ring-0 no-spinner"
                                                                                <?= $key === 'possession' ? 'id="away-possession"' : '' ?>>
                                                                </td>
                                                        </tr>
                                                <?php endforeach; ?>
                                        </tbody>
                                </table>

                                <div class="flex justify-between items-center mt-6">
                                        <p id="possession-warning" class="text-rose-400 text-sm font-medium hidden">
                                                Possession values must add up to 100%.
                                        </p>
                                        <button type="submit" class="px-4 py-2 text-sm font-semibold text-cream bg-maroon border border-maroon/50 rounded-md hover:bg-maroon/80 transition">
                                                Save Stats
                                        </button>
                                </div>
                        </form>
                </div>
        </section>

        <?php require_once __DIR__ . '/../includes/footer.php'; ?>
</main>
<?php require_once __DIR__ . '/../includes/layout_end.php'; ?>

<!-- 🔹 JS: Center inputs, hide spinners, possession validation -->
<style>
        /* Remove number input spinners (Chrome, Safari, Edge, Opera) */
        input.no-spinner::-webkit-outer-spin-button,
        input.no-spinner::-webkit-inner-spin-button {
                -webkit-appearance: none;
                margin: 0;
        }

        /* Remove in Firefox */
        input.no-spinner[type=number] {
                -moz-appearance: textfield;
        }
</style>

<script>
        function validatePossession() {
                const home = parseInt(document.getElementById('home-possession').value) || 0;
                const away = parseInt(document.getElementById('away-possession').value) || 0;
                const total = home + away;
                const warning = document.getElementById('possession-warning');

                if (total !== 100) {
                        warning.classList.remove('hidden');
                        warning.textContent = `Possession must total 100% (current total: ${total}%)`;
                        return false;
                }
                warning.classList.add('hidden');
                return true;
        }
</script>
