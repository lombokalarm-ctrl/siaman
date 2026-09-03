CREATE TABLE IF NOT EXISTS roomlists (
  id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  paket_id BIGINT UNSIGNED NOT NULL,
  hotel_nama VARCHAR(180) NOT NULL,
  created_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (id),
  KEY idx_roomlists_paket (paket_id),
  CONSTRAINT fk_roomlists_paket FOREIGN KEY (paket_id) REFERENCES paket(id) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS rooms (
  id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  roomlist_id BIGINT UNSIGNED NOT NULL,
  room_no INT UNSIGNED NOT NULL,
  room_type VARCHAR(8) NOT NULL,
  room_gender VARCHAR(8) NOT NULL,
  capacity INT UNSIGNED NOT NULL,
  room_code VARCHAR(20) NOT NULL,
  nomor_kunci VARCHAR(40) NULL,
  created_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (id),
  UNIQUE KEY uq_rooms_room_code (roomlist_id, room_code),
  KEY idx_rooms_roomlist (roomlist_id),
  CONSTRAINT fk_rooms_roomlist FOREIGN KEY (roomlist_id) REFERENCES roomlists(id) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS room_members (
  id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  room_id BIGINT UNSIGNED NOT NULL,
  jamaah_id BIGINT UNSIGNED NOT NULL,
  position INT UNSIGNED NOT NULL,
  created_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (id),
  UNIQUE KEY uq_room_members_room_position (room_id, position),
  UNIQUE KEY uq_room_members_room_jamaah (room_id, jamaah_id),
  KEY idx_room_members_jamaah (jamaah_id),
  CONSTRAINT fk_room_members_room FOREIGN KEY (room_id) REFERENCES rooms(id) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT fk_room_members_jamaah FOREIGN KEY (jamaah_id) REFERENCES jamaah(id) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
