-- Public roster: open dates, cancellation notes, optional host.
-- Run in phpMyAdmin on an existing database (after setup.sql).
--
-- If you get "Duplicate column name", that column already exists — skip that step.

-- Step 1: Always run (safe to re-run)
ALTER TABLE schedule
    MODIFY nonprofit_id INT UNSIGNED NULL,
    MODIFY status ENUM('draft', 'open', 'confirmed', 'completed', 'cancelled') NOT NULL DEFAULT 'draft';

-- Step 2: Run only if notes does NOT exist yet (skip if you see #1060 Duplicate column 'notes')
-- ALTER TABLE schedule
--     ADD COLUMN notes VARCHAR(255) NULL AFTER status;

-- Step 3: Run only if cancellation_url does NOT exist yet (skip if duplicate column error)
-- ALTER TABLE schedule
--     ADD COLUMN cancellation_url VARCHAR(512) NULL AFTER notes;
