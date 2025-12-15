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

/* Existing lineup (as announced) */
$stmt = $db->prepare("
  SELECT ml.*, p.name, pos.short_label AS pos_short
  FROM match_lineups ml
  LEFT JOIN players p ON ml.player_id = p.id
  LEFT JOIN positions pos ON ml.position_id = pos.id
  WHERE ml.fixture_id = :fx
  ORDER BY ml.shirt_number ASC, ml.id ASC
");
$stmt->execute([':fx' => $fixtureId]);
$lineup = $stmt->fetchAll(PDO::FETCH_ASSOC);

/* Positions for dropdown */
$positions = $db->query("
  SELECT id, short_label, full_name
  FROM positions
  ORDER BY id ASC
")->fetchAll(PDO::FETCH_ASSOC);

/* UI */
$pageTitle = 'Match Line-Up Manager';
$pageContextLabel = 'Match operations';
$pageTitleText = 'Edit Line-Up';
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

        <section class="<?= $embedded ? 'px-4 py-6' : 'mx-auto max-w-6xl px-6 py-10'; ?>">
                <div class="rounded-3xl border border-white/5 bg-slate-900/70 <?= $embedded ? 'p-4' : 'p-6 shadow-maroon-lg'; ?>">
                        <h3 class="text-cream font-semibold text-lg mb-4">Match Line-Up</h3>

                        <!-- Search -->
                        <div class="relative mb-4">
                                <input type="text" id="playerSearch" placeholder="Start typing to search for a player..."
                                          class="w-full rounded-md bg-slate-800/70 border border-white/10 text-slate-100 px-3 py-2 focus:border-gold/40 focus:ring-0">
                                <div id="suggestions" class="absolute z-50 mt-1 w-full bg-slate-800/90 border border-white/10 rounded-md hidden"></div>
                        </div>

                        <form method="post" action="save_lineup.php" id="lineupForm">
                                <input type="hidden" name="fixture_id" value="<?= (int)$fixtureId ?>">
                                <?php if ($embedded && !empty($returnTo)): ?>
                                        <input type="hidden" name="return_to" value="<?= htmlspecialchars($returnTo); ?>">
                                <?php endif; ?>

                                <div class="overflow-x-auto">
                                        <table class="w-full text-sm border-separate border-spacing-y-1" id="lineupTable">
                                                <thead class="text-xs uppercase text-slate-400 bg-slate-900/60">
                                                        <tr>
                                                                <th class="px-2 py-2 text-center w-10">#</th>
                                                                <th class="px-2 py-2 text-left">Player</th>
                                                                <th class="px-2 py-2 text-center">Position</th>
                                                                <th class="px-2 py-2 text-center">Started</th>
                                                                <th class="px-2 py-2 text-center">Sub?</th>
                                                                <th class="px-2 py-2 text-center">Minute On</th>
                                                                <th class="px-2 py-2 text-center">Replaced</th>
                                                                <th class="px-2 py-2 text-center">Captain</th>
                                                                <th class="px-2 py-2 text-center">Remove</th>
                                                        </tr>
                                                </thead>
                                                <tbody id="lineupBody" class="text-slate-200">
                                                        <?php foreach ($lineup as $idx => $row): ?>
                                                                <tr data-player-id="<?= (int)$row['player_id']; ?>">
                                                                        <td class="px-2 py-2 text-center font-semibold text-cream">
                                                                                <?= htmlspecialchars($row['shirt_number'] ?? ''); ?>
                                                                                <input type="hidden" name="shirt_number[]" value="<?= htmlspecialchars($row['shirt_number'] ?? ''); ?>">
                                                                        </td>
                                                                        <td class="px-2 py-2">
                                                                                <?= htmlspecialchars($row['name'] ?? ''); ?>
                                                                                <input type="hidden" name="player_id[]" value="<?= (int)$row['player_id']; ?>">
                                                                        </td>
                                                                        <td class="px-2 py-2 text-center">
                                                                                <select name="position_id[]" class="w-28 rounded-md bg-slate-800/70 border border-white/10 text-slate-100 px-1 py-1 focus:border-gold/40 focus:ring-0">
                                                                                        <option value="">Select:</option>
                                                                                        <?php foreach ($positions as $pos): ?>
                                                                                                <option value="<?= (int)$pos['id'] ?>"
                                                                                                          title="<?= htmlspecialchars($pos['full_name']); ?>"
                                                                                                          <?= (!empty($row['position_id']) && (int)$row['position_id'] === (int)$pos['id']) ? 'selected' : '' ?>>
                                                                                                          <?= htmlspecialchars($pos['short_label']); ?>
                                                                                                </option>
                                                                                        <?php endforeach; ?>
                                                                                </select>
                                                                        </td>
                                                                        <td class="px-2 py-2 text-center">
                                                                                <input type="checkbox" class="startedChk" name="started[<?= (int)$row['player_id']; ?>]" <?= (int)$row['is_substitute'] === 0 ? 'checked' : '' ?>>
                                                                        </td>
                                                                        <td class="px-2 py-2 text-center">
                                                                                <input type="checkbox" class="subChk" name="is_sub[<?= (int)$row['player_id']; ?>]" <?= (int)$row['is_substitute'] === 1 ? 'checked' : '' ?>>
                                                                        </td>
                                                                        <td class="px-2 py-2 text-center">
                                                                                <input type="number" name="minute_on[<?= (int)$row['player_id']; ?>]" value="<?= htmlspecialchars($row['minutes_played'] ?? ''); ?>"
                                                                                          class="minuteInput w-16 text-center bg-slate-800/70 border border-white/10 rounded-md text-slate-100 no-spinner"
                                                                                          <?= (int)$row['is_substitute'] === 1 ? '' : 'disabled' ?>>
                                                                        </td>
                                                                        <td class="px-2 py-2 text-center">
                                                                                <select name="player_replaced[<?= (int)$row['player_id']; ?>]" class="replacedSelect w-full rounded-md bg-slate-800/70 border border-white/10 text-slate-100 px-2 py-1"
                                                                                          <?= (int)$row['is_substitute'] === 1 ? '' : 'disabled' ?>">
                                                                                        <option value="">Select starter</option>
                                                                                        <?php foreach ($lineup as $opt): if ((int)$opt['is_substitute'] === 0): ?>
                                                                                                <option value="<?= (int)$opt['player_id']; ?>" <?= (!empty($row['player_replaced_id']) && (int)$row['player_replaced_id'] === (int)$opt['player_id']) ? 'selected' : '' ?>>
                                                                                                        <?= htmlspecialchars($opt['name']); ?>
                                                                                                </option>
                                                                                        <?php endif; endforeach; ?>
                                                                                </select>
                                                                        </td>
                                                                        <td class="px-2 py-2 text-center">
                                                                                <input type="checkbox" name="captain[<?= (int)$row['player_id']; ?>]" <?= !empty($row['captain']) ? 'checked' : '' ?>>
                                                                        </td>
                                                                        <td class="px-2 py-2 text-center">
                                                                                <button type="button" class="removeBtn text-rose-400 hover:text-rose-300">
                                                                                        <i class="fa-solid fa-xmark"></i>
                                                                                </button>
                                                                        </td>
                                                                </tr>
                                                        <?php endforeach; ?>
                                                </tbody>
                                        </table>
                                </div>

                                <div class="flex justify-end mt-6">
                                        <button type="submit" class="px-4 py-2 text-sm font-semibold text-cream bg-maroon border border-maroon/50 rounded-md hover:bg-maroon/80 transition">
                                                Save Line-Up
                                        </button>
                                </div>
                        </form>
                </div>
        </section>

        <style>
                input.no-spinner::-webkit-inner-spin-button,
                input.no-spinner::-webkit-outer-spin-button {
                        -webkit-appearance: none;
                        margin: 0;
                }

                input.no-spinner[type=number] {
                        -moz-appearance: textfield;
                }

                #suggestions div {
                        padding: 6px 10px;
                        cursor: pointer;
                }

                #suggestions div:hover {
                        background-color: rgba(212, 163, 27, 0.15);
                }
        </style>

        <script>
                const searchInput = document.getElementById('playerSearch');
                const suggestionsBox = document.getElementById('suggestions');
                const tableBody = document.getElementById('lineupBody');

                let availablePlayers = [];
                const positions = <?= json_encode($positions, JSON_UNESCAPED_SLASHES); ?>;

                /* Load active squad for search */
                (async function loadPlayers() {
                        const res = await fetch('/admin/matches/search_players.php');
                        availablePlayers = await res.json();
                })();

                /* Live suggestions */
                searchInput.addEventListener('input', () => {
                        const term = searchInput.value.trim().toLowerCase();
                        if (!term) return suggestionsBox.classList.add('hidden');

                        const matches = availablePlayers.filter(p => p.name.toLowerCase().includes(term));
                        if (!matches.length) return suggestionsBox.classList.add('hidden');

                        suggestionsBox.innerHTML = matches.map((p) => `
                                <div data-id="${p.id}" data-pos="${p.position_id || ''}" data-name="${p.name}">
                                        ${p.name} ${p.position ? `<span class="text-slate-400 text-xs">(${p.position})</span>` : ''}
                                </div>`).join('');
                        suggestionsBox.classList.remove('hidden');
                });

                suggestionsBox.addEventListener('click', e => {
                        const div = e.target.closest('div[data-id]');
                        if (!div) return;
                        addPlayerRow({
                                id: div.dataset.id,
                                name: div.dataset.name,
                                position_id: div.dataset.pos
                        });
                        suggestionsBox.classList.add('hidden');
                        searchInput.value = '';
                });

                /* Add a player row with default: Started=checked, Sub=unchecked */
                function addPlayerRow(player) {
                        if (document.querySelector(`tr[data-player-id="${player.id}"]`)) {
                                alert('Player already added.');
                                return;
                        }
                        // Find next available number 1..18 skipping 13, not used in table
                        const pool = [1, 2, 3, 4, 5, 6, 7, 8, 9, 10, 11, 12, 14, 15, 16, 17, 18];
                        const used = new Set([...tableBody.querySelectorAll('input[name="shirt_number[]"]')].map(i => parseInt(i.value, 10)));
                        const number = pool.find(n => !used.has(n));
                        if (!number) {
                                alert('No numbers available.');
                                return;
                        }

                        let posOpts = '<option value="">Select:</option>';
                        for (const pos of positions) {
                                const selected = (player.position_id && pos.id == player.position_id) ? 'selected' : '';
                                posOpts += `<option value="${pos.id}" title="${pos.full_name}" ${selected}>${pos.short_label}</option>`;
                        }

                        const row = document.createElement('tr');
                        row.dataset.playerId = player.id;
                        row.innerHTML = `
                                <td class="px-2 py-2 text-center font-semibold text-cream">
                                        ${number}
                                        <input type="hidden" name="shirt_number[]" value="${number}">
                                </td>
                                <td class="px-2 py-2">
                                        ${player.name}
                                        <input type="hidden" name="player_id[]" value="${player.id}">
                                </td>
                                <td class="px-2 py-2 text-center">
                                        <select name="position_id[]" class="w-28 rounded-md bg-slate-800/70 border border-white/10 text-slate-100 px-1 py-1 focus:border-gold/40 focus:ring-0">
                                                ${posOpts}
                                        </select>
                                </td>
                                <td class="px-2 py-2 text-center">
                                        <input type="checkbox" class="startedChk" name="started[${player.id}]" checked>
                                </td>
                                <td class="px-2 py-2 text-center">
                                        <input type="checkbox" class="subChk" name="is_sub[${player.id}]">
                                </td>
                                <td class="px-2 py-2 text-center">
                                        <input type="number" name="minute_on[${player.id}]" class="minuteInput w-16 text-center bg-slate-800/70 border border-white/10 rounded-md text-slate-100 no-spinner" disabled>
                                </td>
                                <td class="px-2 py-2 text-center">
                                        <select name="player_replaced[${player.id}]" class="replacedSelect w-full rounded-md bg-slate-800/70 border border-white/10 text-slate-100 px-2 py-1" disabled>
                                                <option value="">Select starter</option>
                                        </select>
                                </td>
                                <td class="px-2 py-2 text-center"><input type="checkbox" name="captain[${player.id}]"></td>
                                <td class="px-2 py-2 text-center"><button type="button" class="removeBtn text-rose-400 hover:text-rose-300"><i class="fa-solid fa-xmark"></i></button></td>
                        `;
                        tableBody.appendChild(row);
                        wireRow(row);
                        refreshReplacedDropdowns();
                }

                /* Wire mutual exclusion + enable/disable per row */
                function wireRow(tr) {
                        const started = tr.querySelector('.startedChk');
                        const sub = tr.querySelector('.subChk');
                        const minute = tr.querySelector('.minuteInput');
                        const replaced = tr.querySelector('.replacedSelect');

                        function sync() {
                                if (sub.checked) started.checked = false;
                                if (started.checked) sub.checked = false;

                                const isSub = sub.checked === true;
                                minute.disabled = !isSub;
                                replaced.disabled = !isSub;
                                if (!isSub) {
                                        minute.value = '';
                                        replaced.value = '';
                                }
                        }

                        started.addEventListener('change', sync);
                        sub.addEventListener('change', sync);
                        sync();

                        tr.querySelector('.removeBtn').addEventListener('click', () => {
                                tr.remove();
                                refreshReplacedDropdowns();
                        });
                }

                document.querySelectorAll('#lineupBody tr').forEach(wireRow);

                function refreshReplacedDropdowns() {
                        const starters = [];
                        document.querySelectorAll('#lineupBody tr').forEach(tr => {
                                const isStarted = tr.querySelector('.startedChk')?.checked;
                                const isSub = tr.querySelector('.subChk')?.checked;
                                if (isStarted && !isSub) {
                                        starters.push({
                                                id: tr.dataset.playerId,
                                                name: tr.querySelector('td:nth-child(2)').textContent.trim()
                                        });
                                }
                        });

                        document.querySelectorAll('.replacedSelect').forEach(sel => {
                                const keep = sel.value;
                                sel.innerHTML = '<option value="">Select starter</option>' +
                                        starters.map(s => `<option value="${s.id}">${s.name}</option>`).join('');
                                if (keep && starters.some(s => s.id == keep)) sel.value = keep;
                        });
                }

                document.getElementById('lineupBody').addEventListener('change', e => {
                        if (e.target.classList.contains('startedChk') || e.target.classList.contains('subChk')) {
                                refreshReplacedDropdowns();
                        }
                });
        </script>
        <?php if (!$embedded): ?>
                <?php require_once __DIR__ . '/../includes/footer.php'; ?>
        <?php endif; ?>
</main>
<?php require_once __DIR__ . '/../includes/layout_end.php'; ?>
