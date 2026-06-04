-- Add nonprofit contact directory fields to an existing database.
-- Run in phpMyAdmin (SQL tab) on the lunchinthepark database.
-- Then run alter_directory_system.sql for edit tracking, admin notes, and user prompt.

ALTER TABLE nonprofits
    ADD COLUMN contact_name VARCHAR(100) NULL AFTER logo_filename,
    ADD COLUMN phone VARCHAR(20) NULL AFTER contact_name,
    ADD COLUMN phone_textable TINYINT(1) NOT NULL DEFAULT 0 AFTER phone,
    ADD COLUMN preferred_contact ENUM('call', 'text', 'email') NOT NULL DEFAULT 'email' AFTER phone_textable,
    ADD COLUMN share_preference ENUM('all', 'admin_only', 'none') NOT NULL DEFAULT 'admin_only' AFTER preferred_contact,
    ADD COLUMN last_verified DATE NULL AFTER share_preference,
    ADD COLUMN last_edited_by VARCHAR(50) NULL AFTER last_verified,
    ADD COLUMN last_edited_at DATETIME NULL AFTER last_edited_by,
    ADD COLUMN admin_notes TEXT NULL AFTER last_edited_at;
