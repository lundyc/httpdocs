<?php
require_once __DIR__.'/../../shared/includes/session_init.php';
require_once __DIR__.'/../../shared/includes/simple_session.php';
require_once __DIR__.'/../../portal/auth/auth_functions.php';
require_once __DIR__.'/../../portal/auth/middleware.php';

checkRole([1,2,3]);
$db = getDB();
$fixtureId = (int)($_GET['id'] ?? 0);
if(!$fixtureId) die('Missing match ID.');

$fixtureStmt=$db->prepare("SELECT f.*,s.name AS season_name,c.name AS competition_name,ht.name AS home_team_name,at.name AS away_team_name,v.name AS venue_name FROM fixtures f LEFT JOIN seasons s ON f.season_id=s.id LEFT JOIN competitions c ON f.competition_id=c.id LEFT JOIN teams ht ON f.home_team_id=ht.id LEFT JOIN teams at ON f.away_team_id=at.id LEFT JOIN venues v ON f.venue_id=v.id WHERE f.id=:id");
$fixtureStmt->execute([':id'=>$fixtureId]);
$fixture=$fixtureStmt->fetch(PDO::FETCH_ASSOC);
if(!$fixture) die('Fixture not found.');

$allowedTabs=['match','lineup','discipline','ratings','coach-notes','veo'];
$defaultTab=$_GET['tab']??'match';
if(!in_array($defaultTab,$allowedTabs,true)) $defaultTab='match';
$showSaved=isset($_GET['saved']);

if($_SERVER['REQUEST_METHOD']==='POST'){
    $ctx=$_POST['form_context']??'match';
    switch($ctx){
        case 'match':
            $update=$db->prepare("UPDATE fixtures SET match_date=:md, match_time=:mt, venue_id=:vid, competition_id=:cid, home_score=:hs, away_score=:as, status=:st, notes=:nt, updated_at=NOW() WHERE id=:id");
            $update->execute([
                ':md'=>($_POST['match_date']??'')?:null,
                ':mt'=>($_POST['match_time']??'')?:null,
                ':vid'=>($_POST['venue_id']??'')?:null,
                ':cid'=>($_POST['competition_id']??'')?:null,
                ':hs'=>($_POST['home_score']===''?null:(int)($_POST['home_score']??0)),
                ':as'=>($_POST['away_score']===''?null:(int)($_POST['away_score']??0)),
                ':st'=>$_POST['status']??'scheduled',
                ':nt'=>($_POST['notes']??'')?:null,
                ':id'=>$fixtureId
            ]);
            header("Location: edit.php?id={$fixtureId}&tab=match&saved=1");
            exit;
        case 'veo':
            $action=$_POST['veo_action']??'';
            if($action==='link'){
                $veo=(int)($_POST['veo_match_id']??0);
                if($veo) $db->prepare("UPDATE veo_matches SET fixture_id=:fx WHERE id=:id")->execute([':fx'=>$fixtureId,':id'=>$veo]);
            }elseif($action==='unlink'){
                $db->prepare("UPDATE veo_matches SET fixture_id=NULL WHERE fixture_id=:fx")->execute([':fx'=>$fixtureId]);
            }
            header("Location: edit.php?id={$fixtureId}&tab=veo&saved=1");
            exit;
    }
}

$competitions=$db->query("SELECT id,name FROM competitions ORDER BY name ASC")->fetchAll(PDO::FETCH_ASSOC);
$venues=$db->query("SELECT id,name FROM venues ORDER BY name ASC")->fetchAll(PDO::FETCH_ASSOC);
$statuses=['scheduled','played','postponed','cancelled','abandoned'];

$notesStmt=$db->prepare("SELECT n.*,u.name AS author_name FROM match_notes n LEFT JOIN users u ON n.created_by=u.id WHERE n.fixture_id=:fx ORDER BY n.created_at DESC");
$notesStmt->execute([':fx'=>$fixtureId]);
$notesData=$notesStmt->fetchAll(PDO::FETCH_ASSOC);

$veoStmt=$db->prepare("SELECT id,title,video_url,date_played FROM veo_matches WHERE fixture_id=:fx LIMIT 1");
$veoStmt->execute([':fx'=>$fixtureId]);
$veoMatch=$veoStmt->fetch(PDO::FETCH_ASSOC);
$veoOptions=$db->query("SELECT id,title,date_played FROM veo_matches WHERE fixture_id IS NULL ORDER BY date_played DESC")->fetchAll(PDO::FETCH_ASSOC);

$homeTeamName=$fixture['home_team_name']??'Home';
$awayTeamName=$fixture['away_team_name']??'Away';
$matchDateValue=$fixture['match_date']??'';
$matchTimeValue=!empty($fixture['match_time'])?substr($fixture['match_time'],0,5):'';

$pageTitle='Edit Match';
$pageContextLabel='Match operations';
$pageTitleText="Edit Match - {$homeTeamName} vs {$awayTeamName}";
require_once __DIR__.'/../includes/layout_start.php';
$activeNav='matches';
require_once __DIR__.'/../includes/navigation.php';
?>
<main class="flex-1 bg-slate-900/40 backdrop-blur">
        <?php require_once __DIR__.'/../includes/header.php'; ?>
        <section class="mx-auto max-w-6xl px-6 py-10">
                <div class="rounded-3xl border border-white/5 bg-slate-900/70 p-6 shadow-maroon-lg">
                        <div class="flex flex-col md:flex-row md:justify-between md:items-center gap-4 border-b border-white/10 pb-4 mb-6">
                                <div>
                                        <h2 class="text-xl font-semibold text-cream"><?= htmlspecialchars($homeTeamName); ?> vs <?= htmlspecialchars($awayTeamName); ?></h2>
                                        <p class="text-xs text-slate-400 mt-1">
                                                <?= $matchDateValue ? date('j M Y', strtotime($matchDateValue)) : 'Date TBC'; ?>
                                                <?php if ($matchTimeValue): ?>&middot; <?= htmlspecialchars($matchTimeValue); ?><?php endif; ?>
                                                <?php if (!empty($fixture['competition_name'])): ?>&middot; <?= htmlspecialchars($fixture['competition_name']); ?><?php endif; ?>
                                        </p>
                                </div>
                                <a href="view.php?id=<?= (int)$fixtureId; ?>" class="btn-sm border border-emerald-400/30 text-emerald-300 hover:bg-emerald-500/20 px-3 py-1 rounded-md text-xs font-semibold transition">View Match</a>
                        </div>
                        <?php if ($showSaved): ?><div class="mb-4 rounded-lg border border-emerald-400/40 bg-emerald-500/10 px-3 py-2 text-sm text-emerald-200">Changes saved successfully.</div><?php endif; ?>
                        <div data-tab-container data-default-tab="<?= htmlspecialchars($defaultTab); ?>">
                                <ul class="flex flex-wrap border-b border-white/10 text-sm text-slate-300 gap-4">
                                        <li><a href="#matchTab" class="tab-link" data-tab="match" data-target="#matchTab">Match Details</a></li>
                                        <li><a href="#lineupTab" class="tab-link" data-tab="lineup" data-target="#lineupTab">Line-Up</a></li>
                                        <li><a href="#disciplineTab" class="tab-link" data-tab="discipline" data-target="#disciplineTab">Discipline</a></li>
                                        <li><a href="#ratingsTab" class="tab-link" data-tab="ratings" data-target="#ratingsTab">Ratings</a></li>
                                        <li><a href="#coachNotesTab" class="tab-link" data-tab="coach-notes" data-target="#coachNotesTab">Coach Notes</a></li>
                                        <li><a href="#veoTab" class="tab-link" data-tab="veo" data-target="#veoTab">VEO</a></li>
                                </ul>
                                <div class="tab-content mt-6 space-y-6">
                                        <div id="matchTab" class="tab-pane<?= $defaultTab==='match'?'':' hidden'; ?>">
                                                <form method="post" class="space-y-6">
                                                        <input type="hidden" name="form_context" value="match">
                                                        <div class="grid sm:grid-cols-2 gap-4">
                                                                <div><label class="block text-sm font-medium text-slate-300 mb-1">Match Date</label><input type="date" name="match_date" value="<?= htmlspecialchars($matchDateValue); ?>" class="w-full rounded-md bg-slate-800/70 border border-white/10 text-slate-100 px-3 py-2 focus:border-gold/40 focus:ring-0"></div>
                                                                <div><label class="block text-sm font-medium text-slate-300 mb-1">Match Time</label><input type="time" name="match_time" value="<?= htmlspecialchars($matchTimeValue); ?>" class="w-full rounded-md bg-slate-800/70 border border-white/10 text-slate-100 px-3 py-2 focus:border-gold/40 focus:ring-0"></div>
                                                        </div>
                                                        <div class="grid sm:grid-cols-2 gap-4">
                                                                <div><label class="block text-sm font-medium text-slate-300 mb-1">Venue</label><select name="venue_id" class="w-full rounded-md bg-slate-800/70 border border-white/10 text-slate-100 px-3 py-2 focus:border-gold/40 focus:ring-0"><option value="">Select venue</option><?php foreach($venues as $venue): ?><option value="<?= (int)$venue['id']; ?>" <?= ((int)($fixture['venue_id'] ?? 0) === (int)$venue['id']) ? 'selected' : ''; ?>><?= htmlspecialchars($venue['name']); ?></option><?php endforeach; ?></select></div>
                                                                <div><label class="block text-sm font-medium text-slate-300 mb-1">Competition</label><select name="competition_id" class="w-full rounded-md bg-slate-800/70 border border-white/10 text-slate-100 px-3 py-2 focus:border-gold/40 focus:ring-0"><option value="">Select competition</option><?php foreach($competitions as $comp): ?><option value="<?= (int)$comp['id']; ?>" <?= ((int)($fixture['competition_id'] ?? 0) === (int)$comp['id']) ? 'selected' : ''; ?>><?= htmlspecialchars($comp['name']); ?></option><?php endforeach; ?></select></div>
                                                        </div>
                                                        <div class="grid sm:grid-cols-2 gap-4">
                                                                <div><label class="block text-sm font-medium text-slate-300 mb-1"><?= htmlspecialchars($homeTeamName); ?> Score</label><input type="number" name="home_score" value="<?= htmlspecialchars($fixture['home_score'] ?? ''); ?>" class="w-full rounded-md bg-slate-800/70 border border-white/10 text-slate-100 px-3 py-2 focus:border-gold/40 focus:ring-0"></div>
                                                                <div><label class="block text-sm font-medium text-slate-300 mb-1"><?= htmlspecialchars($awayTeamName); ?> Score</label><input type="number" name="away_score" value="<?= htmlspecialchars($fixture['away_score'] ?? ''); ?>" class="w-full rounded-md bg-slate-800/70 border border-white/10 text-slate-100 px-3 py-2 focus:border-gold/40 focus:ring-0"></div>
                                                        </div>
                                                        <div><label class="block text-sm font-medium text-slate-300 mb-1">Match Status</label><select name="status" class="w-full rounded-md bg-slate-800/70 border border-white/10 text-slate-100 px-3 py-2 focus:border-gold/40 focus:ring-0"><?php foreach($statuses as $status): ?><option value="<?= $status; ?>" <?= ($fixture['status'] ?? 'scheduled') === $status ? 'selected' : ''; ?>><?= ucfirst($status); ?></option><?php endforeach; ?></select></div>
                                                        <div><label class="block text-sm font-medium text-slate-300 mb-1">Internal Notes</label><textarea name="notes" rows="3" class="w-full rounded-md bg-slate-800/70 border border-white/10 text-slate-100 px-3 py-2 focus:border-gold/40 focus:ring-0"><?= htmlspecialchars($fixture['notes'] ?? ''); ?></textarea></div>
                                                        <div class="flex justify-end gap-3">
                                                                <a href="view.php?id=<?= (int)$fixtureId; ?>" class="px-4 py-2 text-sm font-semibold text-slate-300 border border-white/10 rounded-md hover:text-gold hover:border-gold/40 transition">Cancel</a>
                                                                <button type="submit" class="px-4 py-2 text-sm font-semibold text-cream bg-maroon border border-maroon/50 rounded-md hover:bg-maroon/80 transition">Save Match Details</button>
                                                        </div>
                                                </form>
                                        </div>

                                        <div id="lineupTab" class="tab-pane<?= $defaultTab==='lineup'?'':' hidden'; ?>">
                                                <div class="lazy-pane w-full h-[70vh] rounded-2xl border border-white/10 bg-slate-900/80 flex items-center justify-center text-slate-400 text-sm"
                                                     data-src="lineup.php?fixture_id=<?= (int)$fixtureId; ?>&embedded=1&return_to=edit">
                                                        <span>Loading line-up editor…</span>
                                                </div>
                                        </div>

                                        <div id="disciplineTab" class="tab-pane<?= $defaultTab==='discipline'?'':' hidden'; ?>">
                                                <div class="lazy-pane w-full h-[70vh] rounded-2xl border border-white/10 bg-slate-900/80 flex items-center justify-center text-slate-400 text-sm"
                                                     data-src="discipline.php?fixture_id=<?= (int)$fixtureId; ?>&embedded=1&return_to=edit">
                                                        <span>Loading discipline offences…</span>
                                                </div>
                                        </div>

                                        <div id="ratingsTab" class="tab-pane<?= $defaultTab==='ratings'?'':' hidden'; ?>">
                                                <div class="lazy-pane w-full h-[70vh] rounded-2xl border border-white/10 bg-slate-900/80 flex items-center justify-center text-slate-400 text-sm"
                                                     data-src="ratings.php?fixture_id=<?= (int)$fixtureId; ?>&embedded=1&return_to=edit">
                                                        <span>Loading player ratings…</span>
                                                </div>
                                        </div>

                                        <div id="coachNotesTab" class="tab-pane<?= $defaultTab==='coach-notes'?'':' hidden'; ?>">
                                                <form method="post" action="notes.php" class="mb-4 space-y-2">
                                                        <input type="hidden" name="fixture_id" value="<?= (int)$fixtureId; ?>">
                                                        <input type="hidden" name="return_to" value="edit">
                                                        <textarea name="content" rows="2" class="w-full rounded-md bg-slate-800/70 text-slate-100 p-2" placeholder="Add note..."></textarea>
                                                        <div class="flex flex-wrap items-center gap-3">
                                                                <select name="note_type" class="bg-slate-800/70 text-slate-200 rounded-md text-sm px-2 py-1">
                                                                        <option value="general">General</option>
                                                                        <option value="tactical">Tactical</option>
                                                                        <option value="postmatch">Post-Match</option>
                                                                </select>
                                                                <button class="btn-sm border border-emerald-400/30 text-emerald-300 hover:bg-emerald-500/20 px-3 py-1 rounded-md text-xs font-semibold transition">Add Note</button>
                                                        </div>
                                                </form>
                                                <?php if ($notesData): ?>
                                                        <ul class="space-y-2">
                                                                <?php foreach ($notesData as $note): ?>
                                                                        <li class="rounded-lg border border-white/5 bg-slate-900/60 p-3">
                                                                                <p class="text-slate-200 text-sm"><?= htmlspecialchars($note['content'] ?? ''); ?></p>
                                                                                <p class="text-xs text-slate-400 mt-1">[<?= htmlspecialchars(ucfirst($note['note_type'] ?? 'general')); ?>]&middot; <?= !empty($note['created_at']) ? date('d M Y H:i', strtotime($note['created_at'])) : 'Unknown'; ?><?php if (!empty($note['author_name'])): ?>&middot; <?= htmlspecialchars($note['author_name']); ?><?php endif; ?></p>
                                                                        </li>
                                                                <?php endforeach; ?>
                                                        </ul>
                                                <?php else: ?><p class="text-slate-400 text-sm">No notes recorded yet.</p><?php endif; ?>
                                        </div>

                                        <div id="veoTab" class="tab-pane<?= $defaultTab==='veo'?'':' hidden'; ?>">
                                                <div class="space-y-4">
                                                        <?php if ($veoMatch): ?>
                                                                <div class="rounded-xl border border-white/10 bg-slate-900/60 p-4 space-y-2">
                                                                        <h4 class="text-sm font-semibold text-cream">Linked VEO Match</h4>
                                                                        <p class="text-slate-200 text-sm"><?= htmlspecialchars($veoMatch['title'] ?? 'Unnamed'); ?></p>
                                                                        <?php if (!empty($veoMatch['date_played'])): ?><p class="text-xs text-slate-400"><?= date('j M Y', strtotime($veoMatch['date_played'])); ?></p><?php endif; ?>
                                                                        <?php if (!empty($veoMatch['video_url'])): ?><a href="<?= htmlspecialchars($veoMatch['video_url']); ?>" target="_blank" rel="noopener" class="text-xs text-emerald-300 underline">Open Video</a><?php endif; ?>
                                                                        <form method="post" class="mt-3">
                                                                                <input type="hidden" name="form_context" value="veo">
                                                                                <input type="hidden" name="veo_action" value="unlink">
                                                                                <button class="btn-sm border border-rose-400/30 text-rose-300 hover:bg-rose-500/20 px-3 py-1 rounded-md text-xs font-semibold transition">Unlink VEO Match</button>
                                                                        </form>
                                                                </div>
                                                        <?php else: ?><p class="text-slate-400 text-sm">No VEO match linked.</p><?php endif; ?>
                                                        <form method="post" class="space-y-3">
                                                                <input type="hidden" name="form_context" value="veo">
                                                                <input type="hidden" name="veo_action" value="link">
                                                                <label class="block text-sm font-medium text-slate-300">Link Available VEO Match</label>
                                                                <?php if ($veoOptions): ?>
                                                                        <select name="veo_match_id" class="w-full rounded-md bg-slate-800/70 border border-white/10 text-slate-100 px-3 py-2 focus:border-gold/40 focus:ring-0">
                                                                                <?php foreach ($veoOptions as $option): ?>
                                                                                        <option value="<?= (int)$option['id']; ?>"><?= htmlspecialchars($option['title'] ?? 'Untitled'); ?><?php if (!empty($option['date_played'])): ?> (<?= date('j M Y', strtotime($option['date_played'])); ?>)<?php endif; ?></option>
                                                                                <?php endforeach; ?>
                                                                        </select>
                                                                        <button class="px-4 py-2 text-sm font-semibold text-cream bg-maroon border border-maroon/50 rounded-md hover:bg-maroon/80 transition">Link Selected Match</button>
                                                                <?php else: ?><p class="text-xs text-slate-500">No unlinked VEO matches available.</p><?php endif; ?>
                                                        </form>
                                                </div>
                                        </div>
                                </div>
                        </div>
                </div>
        </section>
        <?php require_once __DIR__.'/../includes/footer.php'; ?>
</main>
<?php require_once __DIR__.'/../includes/layout_end.php'; ?>

<script>
(function(){
        const container=document.querySelector('[data-tab-container]');
        if(!container) return;
        const links=container.querySelectorAll('.tab-link');
        const panes=container.querySelectorAll('.tab-pane');
        const loadPaneContent = pane => {
                if (!pane) return;
                const target = pane.querySelector('.lazy-pane[data-src]');
                if (!target || target.dataset.loaded === 'true') return;
                const src = target.dataset.src;
                if (!src) return;
                target.dataset.loaded = 'pending';
                target.innerHTML = '<span class="text-slate-400 text-sm">Loading…</span>';
                fetch(src, { credentials: 'same-origin' })
                        .then(resp => {
                                if (!resp.ok) throw new Error('Request failed');
                                return resp.text();
                        })
                        .then(html => {
                                target.innerHTML = html;
                                target.dataset.loaded = 'true';
                        })
                        .catch(() => {
                                target.innerHTML = '<p class="text-rose-300 text-sm">Unable to load this section. Please refresh and try again.</p>';
                                target.dataset.loaded = 'error';
                        });
        };
        const activate=(target,link)=>{
                panes.forEach(p=>p.classList.add('hidden'));
                links.forEach(l=>l.classList.remove('text-gold','font-semibold'));
                const pane=container.querySelector(target);
                if(pane) pane.classList.remove('hidden');
                if(link) link.classList.add('text-gold','font-semibold');
                loadPaneContent(pane);
        };
        links.forEach(link=>link.addEventListener('click',e=>{e.preventDefault();activate(link.dataset.target,link);}));
        const def=container.dataset.defaultTab||'match';
        const defLink=container.querySelector(`.tab-link[data-tab="${def}"]`)||links[0];
        if(defLink) activate(defLink.dataset.target,defLink);
        else loadPaneContent(panes[0]);
})();
</script>
