CREATE TABLE IF NOT EXISTS settings (
  id INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `key` VARCHAR(100) NOT NULL,
  `value` TEXT NOT NULL,
  updated_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (id),
  UNIQUE KEY uq_settings_key (`key`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS number_sequences (
  id INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `key` VARCHAR(100) NOT NULL,
  scope_year INT NULL,
  scope_month INT NULL,
  last_number INT UNSIGNED NOT NULL,
  updated_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (id),
  UNIQUE KEY uq_number_sequences_scope (`key`, scope_year, scope_month)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS jamaah (
  id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  id_jamaah VARCHAR(20) NOT NULL,
  nomor_pendaftaran VARCHAR(40) NOT NULL,
  paket_id BIGINT UNSIGNED NULL,

  nama_lengkap VARCHAR(150) NOT NULL,
  nama_bapak_kandung VARCHAR(150) NOT NULL,
  nik VARCHAR(32) NOT NULL,
  passport_no VARCHAR(40) NULL,
  passport_expire_date DATE NULL,
  nomor_kk VARCHAR(32) NOT NULL,
  tempat_lahir VARCHAR(80) NOT NULL,
  tanggal_lahir DATE NOT NULL,
  jenis_kelamin VARCHAR(20) NOT NULL,
  status_pernikahan VARCHAR(30) NOT NULL,
  pendidikan VARCHAR(30) NOT NULL,
  pekerjaan VARCHAR(80) NOT NULL,
  alamat_lengkap TEXT NOT NULL,
  hp VARCHAR(30) NOT NULL,
  email VARCHAR(150) NULL,

  status VARCHAR(20) NOT NULL DEFAULT 'aktif',
  created_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (id),
  UNIQUE KEY uq_jamaah_id_jamaah (id_jamaah),
  UNIQUE KEY uq_jamaah_nomor_pendaftaran (nomor_pendaftaran),
  UNIQUE KEY uq_jamaah_nik (nik),
  KEY idx_jamaah_paket (paket_id),
  KEY idx_jamaah_passport_no (passport_no),
  KEY idx_jamaah_nama (nama_lengkap),
  KEY idx_jamaah_hp (hp)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO settings (`key`, `value`) VALUES
  ('pendaftaran.prefix', 'REG'),
  ('pendaftaran.year_token', '{YYYY}'),
  ('pendaftaran.separator', '-'),
  ('pendaftaran.seq_length', '5'),
  ('pendaftaran.reset_policy', 'yearly')
ON DUPLICATE KEY UPDATE `value` = VALUES(`value`), updated_at = CURRENT_TIMESTAMP;
