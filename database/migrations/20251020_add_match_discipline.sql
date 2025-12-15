-- Match discipline offences per player
CREATE TABLE IF NOT EXISTS match_discipline (
    id INT AUTO_INCREMENT PRIMARY KEY,
    fixture_id INT NOT NULL,
    player_id INT NOT NULL,
    card_type ENUM(''yellow'', ''red'') NOT NULL DEFAULT ''yellow'',
    minute TINYINT UNSIGNED NULL,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

    CONSTRAINT fk_match_discipline_fixture
        FOREIGN KEY (fixture_id) REFERENCES fixtures(id)
        ON DELETE CASCADE,

    CONSTRAINT fk_match_discipline_player
        FOREIGN KEY (player_id) REFERENCES players(id)
        ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE INDEX idx_match_discipline_fixture ON match_discipline (fixture_id);
CREATE INDEX idx_match_discipline_player ON match_discipline (player_id);
