-- Season Tickets table for My Club Hub Phase 1
-- Run this after the main schema.sql

CREATE TABLE season_tickets
(
          id INT
          AUTO_INCREMENT PRIMARY KEY,
    holder_name VARCHAR
          (150) NOT NULL,
    email VARCHAR
          (150),
    phone VARCHAR
          (20),
    seat_section VARCHAR
          (50) NOT NULL,
    seat_number VARCHAR
          (10) NOT NULL,
    price DECIMAL
          (10,2) NOT NULL,
    status ENUM
          ('active','expired','suspended') DEFAULT 'active',
    created_by INT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY
          (created_by) REFERENCES users
          (id) ON
          DELETE
          SET NULL
          ,
    UNIQUE KEY unique_seat
          (seat_section, seat_number)
);
