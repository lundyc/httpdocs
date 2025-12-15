<?php
require_once '../includes/db.php';

// Placeholder example
$conn->query("INSERT INTO veo_matches (veo_match_id, title, date_played, video_url, status)
              VALUES ('SIM12345', 'Saltcoats vs Dyce', '2025-10-18', 'https://app.veo.co/match/SIM12345', 'pending')");
header("Location: index.php?success=1");
exit;
