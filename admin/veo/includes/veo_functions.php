<?php
// admin/veo/includes/veo_functions.php
require_once __DIR__ . '/../../includes/db.php';

/**
 * Fetch all VEO matches
 */
function getVeoMatches($conn)
{
          $sql = "SELECT v.*, f.match_date, f.home_team_id, f.away_team_id
            FROM veo_matches v
            LEFT JOIN fixtures f ON f.id = v.fixture_id
            ORDER BY v.date_played DESC";
          return $conn->query($sql);
}

/**
 * Fetch one VEO match by ID
 */
function getVeoMatch($conn, $id)
{
          $stmt = $conn->prepare("SELECT * FROM veo_matches WHERE id = ?");
          $stmt->bind_param("i", $id);
          $stmt->execute();
          return $stmt->get_result()->fetch_assoc();
}

/**
 * Fetch clips for a VEO match
 */
function getVeoClips($conn, $match_id)
{
          $stmt = $conn->prepare("SELECT c.*, p.name AS player_name
                            FROM veo_clips c
                            LEFT JOIN players p ON p.id = c.player_id
                            WHERE c.match_id = ?
                            ORDER BY c.timestamp_start ASC");
          $stmt->bind_param("i", $match_id);
          $stmt->execute();
          return $stmt->get_result();
}
