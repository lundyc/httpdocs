<?php
require_once '../includes/db.php';

if (isset($_GET['id'])) {
          $id = $_GET['id'];
          $conn->query("DELETE FROM veo_matches WHERE id = $id");
          header("Location: index.php?deleted=1");
          exit;
}

if (isset($_GET['clip_id'])) {
          $clip_id = $_GET['clip_id'];
          $conn->query("DELETE FROM veo_clips WHERE id = $clip_id");
          header("Location: index.php?deleted=1");
          exit;
}

header("Location: index.php");
