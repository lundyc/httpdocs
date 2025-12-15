<?php
require_once '../includes/header.php';
require_once './includes/veo_functions.php';

$id = $_GET['id'] ?? null;
if (!$id) die("Missing match ID");

$match = getVeoMatch($conn, $id);
$clips = getVeoClips($conn, $id);
?>
<div class="container mt-4">
          <h2><i class="fa fa-video"></i> <?= htmlspecialchars($match['title']) ?></h2>
          <p><strong>Date:</strong> <?= $match['date_played'] ?> |
                    <strong>Status:</strong> <?= ucfirst($match['status']) ?> |
                    <strong>Source:</strong> <?= ucfirst($match['source']) ?>
          </p>

          <div class="mb-4">
                    <h5>Full Match Video</h5>
                    <?php if ($match['video_url']): ?>
                              <iframe src="<?= htmlspecialchars($match['video_url']) ?>" frameborder="0" width="100%" height="400"></iframe>
                    <?php else: ?>
                              <p class="text-muted">No video available</p>
                    <?php endif; ?>
          </div>

          <hr>

          <h4><i class="fa fa-film"></i> Highlight Clips</h4>
          <div class="row">
                    <?php while ($clip = $clips->fetch_assoc()): ?>
                              <div class="col-md-4 mb-3">
                                        <div class="card">
                                                  <iframe src="<?= htmlspecialchars($clip['video_url']) ?>" frameborder="0" width="100%" height="200"></iframe>
                                                  <div class="card-body">
                                                            <h6><?= htmlspecialchars($clip['event_type'] ?: 'Clip') ?></h6>
                                                            <p class="small text-muted"><?= htmlspecialchars($clip['player_name'] ?: 'Unassigned') ?></p>
                                                            <?php if ($clip['coach_comment']): ?>
                                                                      <p class="text-secondary"><em><?= htmlspecialchars($clip['coach_comment']) ?></em></p>
                                                            <?php endif; ?>
                                                            <a href="manage.php?clip_id=<?= $clip['id'] ?>" class="btn btn-sm btn-outline-warning">
                                                                      Edit
                                                            </a>
                                                            <a href="delete.php?clip_id=<?= $clip['id'] ?>" class="btn btn-sm btn-outline-danger">
                                                                      Delete
                                                            </a>
                                                  </div>
                                        </div>
                              </div>
                    <?php endwhile; ?>
          </div>
</div>
<?php require_once '../includes/footer.php'; ?>