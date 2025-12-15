<?php
require_once "auth_functions.php";
checkRole([1, 2]); // Super Admin or Admin

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
          $user_id = addUserManual($_POST['name'], $_POST['email'], $_POST['role_id'], $_POST['password']);
          $message = "User created with ID: " . $user_id;
}
?>
<!DOCTYPE html>
<html>

<head>
          <title>Manual Add User</title>
</head>

<body>
          <h2>Manual User Creation</h2>
          <?php if (!empty($message)) echo "<p style='color:green;'>$message</p>"; ?>
          <form method="POST">
                    Name: <input type="text" name="name" required><br>
                    Email: <input type="email" name="email" required><br>
                    Password: <input type="password" name="password" required><br>
                    Role:
                    <select name="role_id">
                              <option value="1">Super Admin</option>
                              <option value="2">Admin</option>
                              <option value="3">Manager</option>
                              <option value="4">Volunteer</option>
                    </select><br>
                    <button type="submit">Create User</button>
          </form>
</body>

</html>