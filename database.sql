CREATE DATABASE IF NOT EXISTS gsm_firmware_db
  CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE gsm_firmware_db;

CREATE TABLE users (
    user_id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    email VARCHAR(190) NOT NULL UNIQUE,
    password_hash VARCHAR(255) NOT NULL,
    role ENUM('user','admin') NOT NULL DEFAULT 'user',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

CREATE TABLE download_logs (
    download_id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    user_id INT UNSIGNED NOT NULL,
    firmware_id VARCHAR(120) NULL,
    device VARCHAR(150) NOT NULL,
    model VARCHAR(100) NULL,
    region VARCHAR(30) NULL,
    version VARCHAR(255) NULL,
    ota_version VARCHAR(255) NULL,
    size_bytes BIGINT UNSIGNED NULL,
    source_url TEXT NULL,
    status ENUM('started','completed','failed') NOT NULL DEFAULT 'started',
    started_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    completed_at TIMESTAMP NULL,
    INDEX idx_download_user (user_id),
    INDEX idx_download_device (device),
    INDEX idx_download_started (started_at),
    CONSTRAINT fk_download_user FOREIGN KEY (user_id)
      REFERENCES users(user_id) ON DELETE CASCADE
) ENGINE=InnoDB;

CREATE TABLE site_settings (
    setting_key VARCHAR(100) PRIMARY KEY,
    setting_value TEXT NULL
) ENGINE=InnoDB;

-- Demo admin/user accounts are intentionally created by the PHP installer/setup script.
