CREATE TABLE IF NOT EXISTS clients (
  id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  nama_perusahaan VARCHAR(200) NOT NULL,
  nama_pic VARCHAR(150) NOT NULL,
  alamat TEXT NOT NULL,
  no_tlp VARCHAR(50) NOT NULL,
  email VARCHAR(150) NULL,
  status VARCHAR(20) NOT NULL DEFAULT 'aktif',
  created_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (id),
  KEY idx_clients_nama (nama_perusahaan),
  KEY idx_clients_pic (nama_pic),
  KEY idx_clients_tlp (no_tlp)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

ALTER TABLE invoices
  DROP FOREIGN KEY fk_invoices_jamaah;

ALTER TABLE invoices
  MODIFY jamaah_id BIGINT UNSIGNED NULL,
  ADD COLUMN client_id BIGINT UNSIGNED NULL AFTER jamaah_id,
  ADD KEY idx_invoices_client (client_id);

ALTER TABLE invoices
  ADD CONSTRAINT fk_invoices_jamaah FOREIGN KEY (jamaah_id) REFERENCES jamaah(id) ON DELETE RESTRICT ON UPDATE CASCADE,
  ADD CONSTRAINT fk_invoices_client FOREIGN KEY (client_id) REFERENCES clients(id) ON DELETE RESTRICT ON UPDATE CASCADE;
