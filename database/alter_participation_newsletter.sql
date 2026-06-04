-- Participation requests and newsletter subscribers.
-- Run in phpMyAdmin on the lunchinthepark database.

CREATE TABLE IF NOT EXISTS nonprofit_requests (
    id INT UNSIGNED NOT NULL AUTO_INCREMENT,
    contact_name VARCHAR(100) NOT NULL,
    org_name VARCHAR(255) NOT NULL,
    phone VARCHAR(20) NULL,
    email VARCHAR(255) NOT NULL,
    requested_username VARCHAR(64) NULL,
    status ENUM('pending', 'approved', 'declined') NOT NULL DEFAULT 'pending',
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    KEY idx_nonprofit_requests_status (status)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS newsletter_subscribers (
    id INT UNSIGNED NOT NULL AUTO_INCREMENT,
    name VARCHAR(255) NOT NULL,
    email VARCHAR(255) NOT NULL,
    opted_in_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    opted_out TINYINT(1) NOT NULL DEFAULT 0,
    PRIMARY KEY (id),
    UNIQUE KEY uk_newsletter_email (email)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
