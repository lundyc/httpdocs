<?php
require_once '../includes/header.php';
require_once '../includes/db.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
          $org_id = $_POST['org_id'];
          $api_key = $_POST['api_key'];
          $conn->query("DELETE FROM veo_settings");
          $stmt = $conn->prepare("INSERT INTO veo_settings (org_id, api_key) VALUES (?, ?)");
          $stmt->bind_param("ss", $org_id, $api_key);
          $stmt->execute();
          echo "<div class='alert alert-success'>Settings updated.</div>";
}
$settings = $conn->query("SELECT * FROM veo_settings LIMIT 1")->fetch_assoc();
?>
<div class="container mt-4">
          <h2><i class="fa fa-cog"></i> VEO Configuration</h2>
          <form method="post">
                    <div class="mb-3">
                              <label>Organisation ID</label>
                              <input type="text" name="org_id" class="form-control"
                                        value="<?= htmlspecialchars($settings['org_id'] ?? '') ?>">
                    </div>
                    <div class="mb-3">
                              <label>API Key</label>
                              <input type="text" name="api_key" class="form-control"
                                        value="<?= htmlspecialchars($settings['api_key'] ?? '') ?>">
                    </div>
                    <button class="btn btn-primary">Save</button>
          </form>
</div>
<?php require_once '../includes/footer.php'; ?>