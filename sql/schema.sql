-- My Club Hub Operating System
-- Phase 1 Database Schema (Core + Website + POS + Stock)

-- ===============================
-- CORE AUTHENTICATION & ROLES
-- ===============================

CREATE TABLE roles (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(50) NOT NULL, -- Super Admin, Admin, Manager, Volunteer
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    role_id INT NOT NULL,
    name VARCHAR(100) NOT NULL,
    email VARCHAR(150) UNIQUE NOT NULL,
    password_hash VARCHAR(255) NOT NULL,
    status ENUM('active','inactive') DEFAULT 'active',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (role_id) REFERENCES roles(id) ON DELETE CASCADE
);

CREATE TABLE audit_logs (
    id BIGINT AUTO_INCREMENT PRIMARY KEY,
    user_id INT,
    action VARCHAR(255) NOT NULL,
    ip_address VARCHAR(45),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL
);

-- ===============================
-- PUBLIC WEBSITE (NEWS, FIXTURES, SPONSORS)
-- ===============================

CREATE TABLE news (
    id INT AUTO_INCREMENT PRIMARY KEY,
    title VARCHAR(255) NOT NULL,
    category ENUM('Match Report','Club News','Community') DEFAULT 'Club News',
    content TEXT NOT NULL,
    featured_image VARCHAR(255),
    created_by INT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (created_by) REFERENCES users(id) ON DELETE SET NULL
);

CREATE TABLE fixtures (
    id INT AUTO_INCREMENT PRIMARY KEY,
    match_date DATE NOT NULL,
    match_time TIME NOT NULL,
    opponent VARCHAR(150) NOT NULL,
    venue VARCHAR(255),
    competition VARCHAR(150),
    status ENUM('upcoming','played','cancelled') DEFAULT 'upcoming',
    home_away ENUM('home','away') DEFAULT 'home',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE sponsors (
    id INT AUTO_INCREMENT PRIMARY KEY,
    company_name VARCHAR(150) NOT NULL,
    contact_name VARCHAR(100),
    contact_email VARCHAR(150),
    tier ENUM('Main','Partner','Supporter') DEFAULT 'Supporter',
    logo VARCHAR(255),
    website VARCHAR(255),
    start_date DATE,
    end_date DATE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- ===============================
-- POS (MATCHDAY SALES)
-- ===============================

CREATE TABLE pos_locations (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL, -- e.g. Bar, Kiosk, Gate, Merch
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE pos_sessions (
    id INT AUTO_INCREMENT PRIMARY KEY,
    location_id INT NOT NULL,
    opened_by INT NOT NULL,
    start_float DECIMAL(10,2) NOT NULL,
    end_float DECIMAL(10,2),
    status ENUM('open','closed') DEFAULT 'open',
    opened_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    closed_at TIMESTAMP NULL,
    FOREIGN KEY (location_id) REFERENCES pos_locations(id),
    FOREIGN KEY (opened_by) REFERENCES users(id)
);

CREATE TABLE pos_sales (
    id INT AUTO_INCREMENT PRIMARY KEY,
    session_id INT NOT NULL,
    item_name VARCHAR(150) NOT NULL,
    quantity INT NOT NULL DEFAULT 1,
    price DECIMAL(10,2) NOT NULL,
    payment_method ENUM('cash','card') NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (session_id) REFERENCES pos_sessions(id)
);

CREATE TABLE pos_refunds (
    id INT AUTO_INCREMENT PRIMARY KEY,
    sale_id INT,
    refunded_by INT NOT NULL,
    reason VARCHAR(255),
    refund_amount DECIMAL(10,2) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (sale_id) REFERENCES pos_sales(id) ON DELETE SET NULL,
    FOREIGN KEY (refunded_by) REFERENCES users(id)
);

-- ===============================
-- STOCK (BASIC)
-- ===============================

CREATE TABLE stock_items (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(150) NOT NULL,
    category VARCHAR(100),
    supplier VARCHAR(150),
    unit_cost DECIMAL(10,2),
    unit_price DECIMAL(10,2),
    quantity INT DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE stock_movements (
    id INT AUTO_INCREMENT PRIMARY KEY,
    item_id INT NOT NULL,
    movement_type ENUM('delivery','sale','wastage','donation') NOT NULL,
    quantity INT NOT NULL,
    reference VARCHAR(100), -- e.g. linked POS sale
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (item_id) REFERENCES stock_items(id)
);

