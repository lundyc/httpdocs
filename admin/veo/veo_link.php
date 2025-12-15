<?php
require_once '../includes/db.php';
$fixture_id = $_GET['fixture_id'] ?? null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
          $veo_id = $_POST['veo_match_id'];
          $conn->query("UPDATE veo_matches SET fixture_id = $fixture_id WHERE id = $veo_id");
          header("Location: ../matches/index.php?linked=1");
          exit;
}

$matches = $conn->query("SELECT id, title, date_played FROM veo_matches WHERE fixture_id IS NULL");
?>
<form method="post">
          <h5>Link VEO Match</h5>
          <select name="veo_match_id" class="form-control">
                    <?php while ($row = $matches->fetch_assoc()): ?>
                              <option value="<?= $row['id'] ?>"><?= $row['title'] ?> (<?= $row['date_played'] ?>)</option>
                    <?php endwhile; ?>
          </select>
          <button class="btn btn-primary mt-2">Link Match</button>
</form>