-- Migration: Add season_tickets table
-- Created: Phase 1 completion
-- Purpose: Season ticket holder management

CREATE TABLE
IF NOT EXISTS season_tickets
(
    id INT AUTO_INCREMENT PRIMARY KEY,
    holder_name VARCHAR
(255) NOT NULL,
    email VARCHAR
(255) NOT NULL,
    phone VARCHAR
(20),
    seat_number VARCHAR
(10) UNIQUE,
    season VARCHAR
(20) NOT NULL,
    price DECIMAL
(10,2) NOT NULL,
    status ENUM
('active', 'expired', 'suspended') DEFAULT 'active',
    purchase_date DATE NOT NULL,
    notes TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON
UPDATE CURRENT_TIMESTAMP
);

-- Add indexes for performance
CREATE INDEX idx_season_tickets_season ON season_tickets(season);
CREATE INDEX idx_season_tickets_status ON season_tickets(status);
CREATE INDEX idx_season_tickets_email ON season_tickets(email);