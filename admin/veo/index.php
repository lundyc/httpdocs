<?php
require_once '../includes/header.php';
require_once '../includes/db.php';

$sql = "SELECT v.*, f.match_date, f.home_team_id, f.away_team_id
        FROM veo_matches v
        LEFT JOIN fixtures f ON f.id = v.fixture_id
        ORDER BY v.date_played DESC";
$result = $conn->query($sql);
?>
<div class="container mt-4">
          <h2><i class="fa fa-video"></i> VEO Matches</h2>
          <a href="sync.php" class="btn btn-success mb-3"><i class="fa fa-sync"></i> Sync with VEO</a>

          <table class="table table-striped">
                    <thead>
                              <tr>
                                        <th>Date</th>
                                        <th>Fixture</th>
                                        <th>Status</th>
                                        <th>Source</th>
                                        <th>Actions</th>
                              </tr>
                    </thead>
                    <tbody>
                              <?php while ($row = $result->fetch_assoc()): ?>
                                        <tr>
                                                  <td><?= htmlspecialchars($row['date_played']) ?></td>
                                                  <td><?= $row['home_team_id'] ?> vs <?= $row['away_team_id'] ?></td>
                                                  <td><span class="badge bg-<?= $row['status'] == 'published' ? 'success' : 'secondary' ?>">
                                                                      <?= ucfirst($row['status']) ?></span></td>
                                                  <td><?= ucfirst($row['source']) ?></td>
                                                  <td>
                                                            <a href="view.php?id=<?= $row['id'] ?>" class="btn btn-sm btn-primary">View</a>
                                                            <a href="manage.php?id=<?= $row['id'] ?>" class="btn btn-sm btn-outline-warning">Edit</a>
                                                            <a href="delete.php?id=<?= $row['id'] ?>" class="btn btn-sm btn-outline-danger">Delete</a>
                                                  </td>
                                        </tr>
                              <?php endwhile; ?>
                    </tbody>
          </table>
</div>
<?php require_once '../includes/footer.php'; ?>