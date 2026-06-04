-- Contact directory, edit tracking, and login verification prompt.
-- Run in phpMyAdmin on the lunchinthepark database.
-- If a column already exists, skip that statement or comment it out.

-- ---------------------------------------------------------------------------
-- Users: verification prompt tracking (nonprofit login popup)
-- ---------------------------------------------------------------------------
ALTER TABLE users
    ADD COLUMN last_verified_prompt DATE NULL AFTER created_at;

-- ---------------------------------------------------------------------------
-- Nonprofits: full contact directory columns (new installs / first migration)
-- Skip this block if you already ran alter_nonprofit_contacts.sql
-- ---------------------------------------------------------------------------
/*
ALTER TABLE nonprofits
    ADD COLUMN contact_name VARCHAR(100) NULL AFTER logo_filename,
    ADD COLUMN phone VARCHAR(20) NULL AFTER contact_name,
    ADD COLUMN phone_textable TINYINT(1) NOT NULL DEFAULT 0 AFTER phone,
    ADD COLUMN preferred_contact ENUM('call', 'text', 'email') NOT NULL DEFAULT 'email' AFTER phone_textable,
    ADD COLUMN share_preference ENUM('all', 'admin_only', 'none') NOT NULL DEFAULT 'admin_only' AFTER preferred_contact,
    ADD COLUMN last_verified DATE NULL AFTER share_preference;
*/

-- ---------------------------------------------------------------------------
-- Nonprofits: edit tracking and admin notes (run if base contact columns exist)
-- ---------------------------------------------------------------------------
ALTER TABLE nonprofits
    ADD COLUMN last_edited_by VARCHAR(50) NULL AFTER last_verified,
    ADD COLUMN last_edited_at DATETIME NULL AFTER last_edited_by,
    ADD COLUMN admin_notes TEXT NULL AFTER last_edited_at;
