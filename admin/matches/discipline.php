<?php
require_once __DIR__ . '/../../shared/includes/session_init.php';
require_once __DIR__ . '/../../shared/includes/simple_session.php';
require_once __DIR__ . '/../../portal/auth/auth_functions.php';
require_once __DIR__ . '/../../portal/auth/middleware.php';

checkRole([1, 2, 3]);
$db = getDB();

$fixtureId = (int)($_GET['fixture_id'] ?? 0);
if (!$fixtureId) die('Missing fixture_id');

$embedded = (int)($_GET['embedded'] ?? 0);
$returnTo = $_GET['return_to'] ?? '';

$cardsStmt = $db->prepare("
        SELECT md.*, p.name AS player_name
        FROM match_discipline md
        LEFT JOIN players p ON md.player_id = p.id
        WHERE md.fixture_id = :fx
        ORDER BY md.minute IS NULL, md.minute ASC, p.name ASC
");
$cardsStmt->execute([':fx' => $fixtureId]);
$cards = $cardsStmt->fetchAll(PDO::FETCH_ASSOC);

$pageTitle = 'Match Discipline';
$pageContextLabel = 'Match operations';
$pageTitleText = 'Discipline';
$activeNav = 'matches';

require_once __DIR__ . '/../includes/layout_start.php';
if (!$embedded) {
        require_once __DIR__ . '/../includes/navigation.php';
}
?>
<main class="flex-1 bg-slate-900/40 backdrop-blur">
        <?php if (!$embedded): ?>
                <?php require_once __DIR__ . '/../includes/header.php'; ?>
        <?php endif; ?>

        <section class="<?= $embedded ? 'px-4 py-6' : 'mx-auto max-w-5xl px-6 py-10'; ?>">
                <div class="rounded-3xl border border-white/5 bg-slate-900/70 <?= $embedded ? 'p-4' : 'p-6 shadow-maroon-lg'; ?>">
                        <h3 class="text-cream font-semibold text-lg mb-4">Discipline offences</h3>

                        <div class="relative mb-4">
                                <input type="text" id="cardPlayerSearch" placeholder="Start typing to search for a player..."
                                       class="w-full rounded-md bg-slate-800/70 border border-white/10 text-slate-100 px-3 py-2 focus:border-gold/40 focus:ring-0">
                                <div id="cardSuggestions" class="absolute z-50 mt-1 w-full bg-slate-800/90 border border-white/10 rounded-md hidden"></div>
                        </div>

                        <form method="post" action="save_discipline.php" id="disciplineForm">
                                <input type="hidden" name="fixture_id" value="<?= (int)$fixtureId ?>">
                                <?php if ($embedded && !empty($returnTo)): ?>
                                        <input type="hidden" name="return_to" value="<?= htmlspecialchars($returnTo); ?>">
                                <?php endif; ?>

                                <div class="overflow-x-auto">
                                        <table class="w-full text-sm border-separate border-spacing-y-1" id="disciplineTable">
                                                <thead class="text-xs uppercase text-slate-400 bg-slate-900/60">
                                                        <tr>
                                                                <th class="px-2 py-2 text-left">Player</th>
                                                                <th class="px-2 py-2 text-center">Card</th>
                                                                <th class="px-2 py-2 text-center">Minute</th>
                                                                <th class="px-2 py-2 text-center w-12">Remove</th>
                                                        </tr>
                                                </thead>
                                                <tbody id="disciplineBody" class="text-slate-200">
                                                        <?php foreach ($cards as $row): ?>
                                                                <tr data-player-id="<?= (int)$row['player_id']; ?>">
                                                                        <td class="px-2 py-2">
                                                                                <?= htmlspecialchars($row['player_name'] ?? 'Unknown'); ?>
                                                                                <input type="hidden" name="player_id[]" value="<?= (int)$row['player_id']; ?>">
                                                                        </td>
                                                                        <td class="px-2 py-2 text-center">
                                                                                <select name="card_type[]" class="rounded-md bg-slate-800/70 border border-white/10 text-slate-100 px-2 py-1 focus:border-gold/40 focus:ring-0">
                                                                                        <option value="yellow" <?= ($row['card_type'] ?? '') === 'yellow' ? 'selected' : ''; ?>>Yellow</option>
                                                                                        <option value="red" <?= ($row['card_type'] ?? '') === 'red' ? 'selected' : ''; ?>>Red</option>
                                                                                </select>
                                                                        </td>
                                                                        <td class="px-2 py-2 text-center">
                                                                                <input type="number" name="minute[]" value="<?= htmlspecialchars($row['minute'] ?? ''); ?>"
                                                                                       class="w-20 rounded-md bg-slate-800/70 border border-white/10 text-slate-100 px-2 py-1 text-center no-spinner">
                                                                        </td>
                                                                        <td class="px-2 py-2 text-center">
                                                                                <button type="button" class="removeCardBtn text-rose-400 hover:text-rose-300">
                                                                                        <i class="fa-solid fa-xmark"></i>
                                                                                </button>
                                                                        </td>
                                                                </tr>
                                                        <?php endforeach; ?>
                                                </tbody>
                                        </table>
                                </div>

                                <div class="flex justify-end mt-6">
                                        <button class="px-4 py-2 text-sm font-semibold text-cream bg-maroon border border-maroon/50 rounded-md hover:bg-maroon/80 transition">
                                                Save Discipline
                                        </button>
                                </div>
                        </form>
                </div>
        </section>

        <?php if (!$embedded): ?>
                <?php require_once __DIR__ . '/../includes/footer.php'; ?>
        <?php endif; ?>
</main>
<?php require_once __DIR__ . '/../includes/layout_end.php'; ?>

<style>
        input.no-spinner::-webkit-inner-spin-button,
        input.no-spinner::-webkit-outer-spin-button {
                -webkit-appearance: none;
                margin: 0;
        }

        input.no-spinner[type=number] {
                -moz-appearance: textfield;
        }

        #cardSuggestions div {
                padding: 6px 10px;
                cursor: pointer;
        }

        #cardSuggestions div:hover {
                background-color: rgba(212, 163, 27, 0.15);
        }
</style>

<script>
        const searchInput = document.getElementById('cardPlayerSearch');
        const suggestionsBox = document.getElementById('cardSuggestions');
        const tableBody = document.getElementById('disciplineBody');

        let availablePlayers = [];
        (async function loadPlayers() {
                const res = await fetch('/admin/matches/search_players.php');
                availablePlayers = await res.json();
        })();

        searchInput.addEventListener('input', () => {
                const term = searchInput.value.trim().toLowerCase();
                if (!term) return suggestionsBox.classList.add('hidden');

                const matches = availablePlayers.filter(p => p.name.toLowerCase().includes(term));
                if (!matches.length) return suggestionsBox.classList.add('hidden');

                suggestionsBox.innerHTML = matches.map(p => `
                        <div data-id="${p.id}" data-name="${p.name}">
                                ${p.name}
                        </div>`).join('');
                suggestionsBox.classList.remove('hidden');
        });

        suggestionsBox.addEventListener('click', e => {
                const div = e.target.closest('div[data-id]');
                if (!div) return;
                addCardRow({
                        id: div.dataset.id,
                        name: div.dataset.name
                });
                suggestionsBox.classList.add('hidden');
                searchInput.value = '';
        });

        function addCardRow(player) {
                if (document.querySelector(`tr[data-player-id="${player.id}"]`)) {
                        alert('Player already added.');
                        return;
                }

                const tr = document.createElement('tr');
                tr.dataset.playerId = player.id;
                tr.innerHTML = `
                        <td class="px-2 py-2">
                                ${player.name}
                                <input type="hidden" name="player_id[]" value="${player.id}">
                        </td>
                        <td class="px-2 py-2 text-center">
                                <select name="card_type[]" class="rounded-md bg-slate-800/70 border border-white/10 text-slate-100 px-2 py-1 focus:border-gold/40 focus:ring-0">
                                        <option value="yellow">Yellow</option>
                                        <option value="red">Red</option>
                                </select>
                        </td>
                        <td class="px-2 py-2 text-center">
                                <input type="number" name="minute[]" class="w-20 rounded-md bg-slate-800/70 border border-white/10 text-slate-100 px-2 py-1 text-center no-spinner">
                        </td>
                        <td class="px-2 py-2 text-center">
                                <button type="button" class="removeCardBtn text-rose-400 hover:text-rose-300"><i class="fa-solid fa-xmark"></i></button>
                        </td>
                `;
                tableBody.appendChild(tr);
        }

        tableBody.addEventListener('click', e => {
                if (e.target.closest('.removeCardBtn')) {
                        e.target.closest('tr')?.remove();
                }
        });
</script>
