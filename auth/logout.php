<?php
require_once "auth_functions.php";
logout();
header("Location: login.php");
exit;
