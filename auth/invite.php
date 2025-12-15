<?php
require_once "auth_functions.php";
checkRole([1]); // only Super Admin (role_id = 1)

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
          $code = createInvite($_POST['email'], $_POST['role_id']);
          $message = "Invite created with code: " . $code; // replace later with email sending
}
?>
<!DOCTYPE html>
<html>

<head>
          <title>Create Invite</title>
</head>

<body>
          <h2>Create Invite</h2>
          <?php if (!empty($message)) echo "<p style='color:green;'>$message</p>"; ?>
          <form method="POST">
                    Email: <input type="email" name="email" required><br>
                    Role:
                    <select name="role_id">
                              <option value="1">Super Admin</option>
                              <option value="2">Admin</option>
                              <option value="3">Manager</option>
                              <option value="4">Volunteer</option>
                    </select><br>
                    <button type="submit">Send Invite</button>
          </form>
</body>

</html>