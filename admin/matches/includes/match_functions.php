<?php
// admin/matches/includes/match_functions.php
require_once __DIR__ . '/../../../includes/db.php';

function getFixtures($conn)
{
        $sql = "SELECT f.*, 
                 h.name AS home_team, 
                 a.name AS away_team, 
                 c.name AS competition_name
          FROM fixtures f
          LEFT JOIN teams h ON f.home_team_id = h.id
          LEFT JOIN teams a ON f.away_team_id = a.id
          LEFT JOIN competitions c ON f.competition_id = c.id
          ORDER BY f.match_date DESC";
        return $conn->query($sql);
}

function getFixture($conn, $id)
{
        $stmt = $conn->prepare("SELECT f.*, h.name AS home_team, a.name AS away_team, c.name AS competition_name
                          FROM fixtures f
                          LEFT JOIN teams h ON f.home_team_id = h.id
                          LEFT JOIN teams a ON f.away_team_id = a.id
                          LEFT JOIN competitions c ON f.competition_id = c.id
                          WHERE f.id = ?");
        $stmt->bind_param("i", $id);
        $stmt->execute();
        return $stmt->get_result()->fetch_assoc();
}

function getMatchStats($conn, $fixture_id)
{
        $stmt = $conn->prepare("SELECT * FROM match_stats WHERE fixture_id = ?");
        $stmt->bind_param("i", $fixture_id);
        $stmt->execute();
        return $stmt->get_result();
}

function getMatchLineup($conn, $fixture_id)
{
        $stmt = $conn->prepare("SELECT ml.*, p.name
                           FROM match_lineups ml
                           LEFT JOIN players p ON ml.player_id = p.id
                           WHERE fixture_id = ?");
        $stmt->bind_param("i", $fixture_id);
        $stmt->execute();
        return $stmt->get_result();
}

function getMatchRatings($conn, $fixture_id)
{
        $stmt = $conn->prepare("SELECT mr.*, p.name
                           FROM match_ratings mr
                           LEFT JOIN players p ON mr.player_id = p.id
                           WHERE fixture_id = ?");
        $stmt->bind_param("i", $fixture_id);
        $stmt->execute();
        return $stmt->get_result();
}

function getMatchNotes($conn, $fixture_id)
{
        return $conn->query("SELECT * FROM match_notes WHERE fixture_id = $fixture_id ORDER BY created_at DESC");
}
