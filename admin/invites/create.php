<?php
require_once __DIR__ . '/../../shared/includes/session_init.php';
require_once __DIR__ . '/../../shared/includes/simple_session.php';
require_once __DIR__ . '/../../portal/auth/auth_functions.php';
checkRole([1, 2]); // Admin or Super Admin

$db = getDB();
$roles = $db->query("SELECT id, name FROM roles WHERE name NOT IN ('Super Admin') ORDER BY id ASC")->fetchAll(PDO::FETCH_ASSOC);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
          $email = trim($_POST['email']);
          $roleId = (int)$_POST['role_id'];
          $token = bin2hex(random_bytes(16));
          $expiresAt = date('Y-m-d H:i:s', strtotime('+7 days'));
          $createdBy = $_SESSION['user_id'];

          $stmt = $db->prepare("INSERT INTO invites (email, role_id, token, created_by, expires_at, status)
                          VALUES (:email, :role_id, :token, :created_by, :expires_at, 'pending')");
          $stmt->execute([
                    'email' => $email,
                    'role_id' => $roleId,
                    'token' => $token,
                    'created_by' => $createdBy,
                    'expires_at' => $expiresAt
          ]);

          $inviteUrl = "https://portal.myclubhub.co.uk/auth/register_invite.php?token=" . urlencode($token);
          echo "<div class='p-4 bg-emerald-700 text-white rounded-lg'>Invite created!<br>Share this link:<br>
          <input class='w-full bg-slate-900 mt-2 p-2 rounded text-white' value='$inviteUrl' readonly></div>";
}
?>

<form method="POST" class="p-6 bg-slate-900 text-slate-100 rounded-2xl max-w-md mx-auto mt-10">
          <h2 class="text-xl font-semibold text-gold mb-4">Create New Invite</h2>
          <label>Email</label>
          <input type="email" name="email" required class="w-full p-2 mt-2 mb-4 bg-slate-800 rounded border border-white/10">
          <label>Role</label>
          <select name="role_id" required class="w-full p-2 mt-2 mb-6 bg-slate-800 rounded border border-white/10">
                    <?php foreach ($roles as $r): ?>
                              <option value="<?= $r['id']; ?>"><?= htmlspecialchars($r['name']); ?></option>
                    <?php endforeach; ?>
          </select>
          <button type="submit" class="w-full py-2 rounded bg-gold text-maroon font-semibold hover:bg-gold/90">
                    Generate Invite
          </button>
</form>
