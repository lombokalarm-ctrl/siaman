ALTER TABLE invoices
  ADD COLUMN share_token CHAR(64) NULL AFTER notes,
  ADD COLUMN share_expires_at DATETIME NULL AFTER share_token,
  ADD KEY idx_invoices_share_token (share_token);
