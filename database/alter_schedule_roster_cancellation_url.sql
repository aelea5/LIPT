-- Run only if cancellation_url is missing from schedule (check in phpMyAdmin Structure tab first).
ALTER TABLE schedule
    ADD COLUMN cancellation_url VARCHAR(512) NULL AFTER notes;
