CREATE TABLE IF NOT EXISTS paket (
  id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  kode VARCHAR(40) NULL,
  nama VARCHAR(150) NOT NULL,
  durasi_hari INT UNSIGNED NOT NULL,
  tanggal_berangkat DATE NULL,
  harga DECIMAL(15,2) NOT NULL DEFAULT 0,
  currency VARCHAR(10) NOT NULL DEFAULT 'IDR',
  deskripsi TEXT NULL,
  status VARCHAR(20) NOT NULL DEFAULT 'aktif',
  created_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (id),
  UNIQUE KEY uq_paket_kode (kode),
  KEY idx_paket_nama (nama)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS invoices (
  id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  nomor VARCHAR(40) NOT NULL,
  jamaah_id BIGINT UNSIGNED NOT NULL,
  paket_id BIGINT UNSIGNED NULL,
  tanggal DATE NOT NULL,
  subtotal DECIMAL(15,2) NOT NULL DEFAULT 0,
  diskon DECIMAL(15,2) NOT NULL DEFAULT 0,
  pajak DECIMAL(15,2) NOT NULL DEFAULT 0,
  grand_total DECIMAL(15,2) NOT NULL DEFAULT 0,
  status VARCHAR(20) NOT NULL DEFAULT 'unpaid',
  notes TEXT NULL,
  created_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (id),
  UNIQUE KEY uq_invoices_nomor (nomor),
  KEY idx_invoices_status (status),
  KEY idx_invoices_jamaah (jamaah_id),
  CONSTRAINT fk_invoices_jamaah FOREIGN KEY (jamaah_id) REFERENCES jamaah(id) ON DELETE RESTRICT ON UPDATE CASCADE,
  CONSTRAINT fk_invoices_paket FOREIGN KEY (paket_id) REFERENCES paket(id) ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS invoice_items (
  id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  invoice_id BIGINT UNSIGNED NOT NULL,
  label VARCHAR(200) NOT NULL,
  qty DECIMAL(12,2) NOT NULL DEFAULT 1,
  price DECIMAL(15,2) NOT NULL DEFAULT 0,
  total DECIMAL(15,2) NOT NULL DEFAULT 0,
  PRIMARY KEY (id),
  KEY idx_invoice_items_invoice (invoice_id),
  CONSTRAINT fk_invoice_items_invoice FOREIGN KEY (invoice_id) REFERENCES invoices(id) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS payments (
  id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  invoice_id BIGINT UNSIGNED NOT NULL,
  nomor_kuitansi VARCHAR(40) NOT NULL,
  tanggal DATE NOT NULL,
  amount DECIMAL(15,2) NOT NULL DEFAULT 0,
  metode VARCHAR(30) NOT NULL,
  reference VARCHAR(100) NULL,
  pengirim VARCHAR(150) NULL,
  outlet VARCHAR(150) NULL,
  sales VARCHAR(150) NULL,
  created_by BIGINT UNSIGNED NULL,
  created_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (id),
  UNIQUE KEY uq_payments_nomor_kuitansi (nomor_kuitansi),
  KEY idx_payments_invoice (invoice_id),
  KEY idx_payments_tanggal (tanggal),
  CONSTRAINT fk_payments_invoice FOREIGN KEY (invoice_id) REFERENCES invoices(id) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO settings (`key`, `value`) VALUES
  ('invoice.prefix', 'INV'),
  ('invoice.year_token', '{YYYY}'),
  ('invoice.separator', '-'),
  ('invoice.seq_length', '5'),
  ('invoice.reset_policy', 'yearly'),
  ('kuitansi.prefix', 'RCT'),
  ('kuitansi.year_token', '{YYYY}'),
  ('kuitansi.separator', '-'),
  ('kuitansi.seq_length', '5'),
  ('kuitansi.reset_policy', 'yearly')
ON DUPLICATE KEY UPDATE `value` = VALUES(`value`), updated_at = CURRENT_TIMESTAMP;

