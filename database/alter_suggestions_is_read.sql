-- Add read tracking for public suggestions (run in phpMyAdmin).

ALTER TABLE suggestions
    ADD COLUMN is_read TINYINT(1) NOT NULL DEFAULT 0 AFTER message;
