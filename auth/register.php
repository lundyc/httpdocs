<?php
require_once "auth_functions.php";
$code = $_GET['code'] ?? null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
          if (registerFromInvite($_POST['code'], $_POST['name'], $_POST['password'])) {
                    header("Location: login.php");
                    exit;
          } else {
                    $error = "Invalid or expired invite.";
          }
}
?>
<!DOCTYPE html>
<html>

<head>
          <title>Register</title>
</head>

<body>
          <h2>Register with Invite</h2>
          <?php if (!empty($error)) echo "<p style='color:red;'>$error</p>"; ?>
          <form method="POST">
                    <input type="hidden" name="code" value="<?php echo htmlspecialchars($code); ?>">
                    Name: <input type="text" name="name" required><br>
                    Password: <input type="password" name="password" required><br>
                    <button type="submit">Register</button>
          </form>
</body>

</html>