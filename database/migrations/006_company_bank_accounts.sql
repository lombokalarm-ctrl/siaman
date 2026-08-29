CREATE TABLE IF NOT EXISTS company_bank_accounts (
  id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  bank_nama VARCHAR(120) NOT NULL,
  no_rekening VARCHAR(60) NOT NULL,
  nama_rekening VARCHAR(150) NOT NULL,
  created_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (id),
  KEY idx_company_bank_accounts_bank (bank_nama),
  KEY idx_company_bank_accounts_no (no_rekening)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
