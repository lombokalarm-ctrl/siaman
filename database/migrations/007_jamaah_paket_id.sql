ALTER TABLE jamaah
  ADD COLUMN paket_id BIGINT UNSIGNED NULL AFTER nomor_pendaftaran,
  ADD KEY idx_jamaah_paket (paket_id);

ALTER TABLE jamaah
  ADD CONSTRAINT fk_jamaah_paket FOREIGN KEY (paket_id)
    REFERENCES paket(id)
    ON DELETE SET NULL
    ON UPDATE CASCADE;
