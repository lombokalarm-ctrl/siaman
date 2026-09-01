ALTER TABLE jamaah
  ADD COLUMN passport_no VARCHAR(40) NULL AFTER nik,
  ADD COLUMN passport_expire_date DATE NULL AFTER passport_no,
  ADD KEY idx_jamaah_passport_no (passport_no);
