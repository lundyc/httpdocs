<?php
require_once '../includes/header.php';
require_once './includes/veo_functions.php';

$match_id = $_GET['id'] ?? null;
$clip_id = $_GET['clip_id'] ?? null;

// If editing a clip
if ($clip_id) {
          $stmt = $conn->prepare("SELECT * FROM veo_clips WHERE id = ?");
          $stmt->bind_param("i", $clip_id);
          $stmt->execute();
          $clip = $stmt->get_result()->fetch_assoc();

          if ($_SERVER['REQUEST_METHOD'] === 'POST') {
                    $event_type = $_POST['event_type'];
                    $comment = $_POST['coach_comment'];
                    $stmt = $conn->prepare("UPDATE veo_clips SET event_type=?, coach_comment=? WHERE id=?");
                    $stmt->bind_param("ssi", $event_type, $comment, $clip_id);
                    $stmt->execute();
                    header("Location: view.php?id=" . $clip['match_id']);
                    exit;
          }
?>
          <div class="container mt-4">
                    <h3>Edit Clip</h3>
                    <form method="post">
                              <div class="mb-3">
                                        <label>Event Type</label>
                                        <input type="text" name="event_type" value="<?= htmlspecialchars($clip['event_type']) ?>" class="form-control">
                              </div>
                              <div class="mb-3">
                                        <label>Coach Comment</label>
                                        <textarea name="coach_comment" class="form-control"><?= htmlspecialchars($clip['coach_comment']) ?></textarea>
                              </div>
                              <button class="btn btn-success">Save</button>
                    </form>
          </div>
<?php
          require_once '../includes/footer.php';
          exit;
}

// Otherwise editing a match
if ($match_id) {
          $match = getVeoMatch($conn, $match_id);

          if ($_SERVER['REQUEST_METHOD'] === 'POST') {
                    $title = $_POST['title'];
                    $status = $_POST['status'];
                    $stmt = $conn->prepare("UPDATE veo_matches SET title=?, status=? WHERE id=?");
                    $stmt->bind_param("ssi", $title, $status, $match_id);
                    $stmt->execute();
                    header("Location: view.php?id=$match_id");
                    exit;
          }
?>
          <div class="container mt-4">
                    <h3>Edit VEO Match</h3>
                    <form method="post">
                              <div class="mb-3">
                                        <label>Title</label>
                                        <input type="text" name="title" value="<?= htmlspecialchars($match['title']) ?>" class="form-control">
                              </div>
                              <div class="mb-3">
                                        <label>Status</label>
                                        <select name="status" class="form-control">
                                                  <option <?= $match['status'] == 'pending' ? 'selected' : '' ?> value="pending">Pending</option>
                                                  <option <?= $match['status'] == 'analysed' ? 'selected' : '' ?> value="analysed">Analysed</option>
                                                  <option <?= $match['status'] == 'published' ? 'selected' : '' ?> value="published">Published</option>
                                        </select>
                              </div>
                              <button class="btn btn-success">Save Changes</button>
                    </form>
          </div>
<?php
}
require_once '../includes/footer.php';
