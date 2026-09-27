USE web;

ALTER TABLE applications
    ADD COLUMN interview_mode ENUM('online', 'office') NULL AFTER status,
    ADD COLUMN interview_at DATETIME NULL AFTER interview_mode,
    ADD COLUMN meeting_url VARCHAR(500) NULL AFTER interview_at,
    ADD COLUMN interview_location VARCHAR(255) NULL AFTER meeting_url,
    ADD COLUMN interview_notes TEXT NULL AFTER interview_location;