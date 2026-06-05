-- Require new nonprofit users to set their own password on first login (run in phpMyAdmin).

ALTER TABLE users
    ADD COLUMN force_password_change TINYINT(1) NOT NULL DEFAULT 0 AFTER role;
