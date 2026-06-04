-- Optional: fix rows that were saved as draft before the roster update.
-- Run in phpMyAdmin after alter_schedule_roster.sql Step 1.
-- Review results with: SELECT id, event_date, status, nonprofit_id, notes FROM schedule;

UPDATE schedule
SET status = 'open'
WHERE status = 'draft'
  AND (nonprofit_id IS NULL OR nonprofit_id = 0)
  AND (notes IS NULL OR TRIM(notes) = '');

UPDATE schedule
SET status = 'cancelled'
WHERE status = 'draft'
  AND (nonprofit_id IS NULL OR nonprofit_id = 0)
  AND notes IS NOT NULL
  AND TRIM(notes) <> '';
