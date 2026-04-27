CREATE DATABASE IF NOT EXISTS aclcapi_db
  CHARACTER SET utf8mb4
  COLLATE utf8mb4_unicode_ci;

USE aclcapi_db;

CREATE TABLE IF NOT EXISTS removed_students (
  id INT UNSIGNED NOT NULL AUTO_INCREMENT,
  student_id VARCHAR(50) NOT NULL,
  name VARCHAR(150) NOT NULL DEFAULT "",
  program VARCHAR(150) NOT NULL DEFAULT "",
  year_level VARCHAR(50) NOT NULL DEFAULT "",
  gmail VARCHAR(150) NOT NULL DEFAULT "",
  removed_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (id),
  KEY idx_removed_student_id (student_id)
);

CREATE TABLE IF NOT EXISTS admins (
  id INT NOT NULL AUTO_INCREMENT,
  username VARCHAR(50) NOT NULL,
  email VARCHAR(120) NOT NULL,
  full_name VARCHAR(120) NOT NULL,
  avatar LONGTEXT NULL,
  password_hash VARCHAR(255) NOT NULL,
  role VARCHAR(30) NOT NULL DEFAULT 'admin',
  created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (id),
  UNIQUE KEY uniq_admin_username (username),
  UNIQUE KEY uniq_admin_email (email)
);


CREATE TABLE IF NOT EXISTS bse_students (
  id INT UNSIGNED NOT NULL AUTO_INCREMENT,
  student_id VARCHAR(50) NOT NULL,
  name VARCHAR(150) NOT NULL,
  program VARCHAR(150) NOT NULL DEFAULT '',
  year_level VARCHAR(50) NOT NULL DEFAULT '',
  gmail VARCHAR(150) NOT NULL DEFAULT '',
  downpayment_date DATETIME NULL,
  prelim_date DATETIME NULL,
  midterm_date DATETIME NULL,
  prefinal_date DATETIME NULL,
  final_date DATETIME NULL,
  total_balance_date DATETIME NULL,
  downpayment_paid_amount DECIMAL(12,2) NULL,
  prelim_paid_amount DECIMAL(12,2) NULL,
  midterm_paid_amount DECIMAL(12,2) NULL,
  prefinal_paid_amount DECIMAL(12,2) NULL,
  final_paid_amount DECIMAL(12,2) NULL,
  total_balance_paid_amount DECIMAL(12,2) NULL,
  created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (id),
  UNIQUE KEY uniq_bse_student_id (student_id)
);

CREATE TABLE IF NOT EXISTS bsis_students (
  id INT UNSIGNED NOT NULL AUTO_INCREMENT,
  student_id VARCHAR(50) NOT NULL,
  name VARCHAR(150) NOT NULL,
  program VARCHAR(150) NOT NULL DEFAULT '',
  year_level VARCHAR(50) NOT NULL DEFAULT '',
  gmail VARCHAR(150) NOT NULL DEFAULT '',
  downpayment_date DATETIME NULL,
  prelim_date DATETIME NULL,
  midterm_date DATETIME NULL,
  prefinal_date DATETIME NULL,
  final_date DATETIME NULL,
  total_balance_date DATETIME NULL,
  downpayment_paid_amount DECIMAL(12,2) NULL,
  prelim_paid_amount DECIMAL(12,2) NULL,
  midterm_paid_amount DECIMAL(12,2) NULL,
  prefinal_paid_amount DECIMAL(12,2) NULL,
  final_paid_amount DECIMAL(12,2) NULL,
  total_balance_paid_amount DECIMAL(12,2) NULL,
  created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (id),
  UNIQUE KEY uniq_bsis_student_id (student_id)
);

-- Update existing admin name if present (change Bebén Magbanua to Ms. Amy Alpay)
-- Run this after importing or against your live database if the admin row already exists.
UPDATE admins SET full_name = 'Ms. Amy Alpay' WHERE full_name LIKE '%BEBEN%' OR full_name LIKE '%Magbanua%';

-- Insert default admin (username: admin, password: admin123)
INSERT INTO admins (username, email, full_name, password_hash, role) VALUES
('admin', 'admin@aclc.edu', 'Administrator', '$2y$10$4QsO5ZBaqPjwfKqnlnsntuQiI/JX3Ixlub.BcQ85B04qqnyLx5m2u', 'admin');

CREATE TABLE IF NOT EXISTS api_tokens (
  id INT UNSIGNED NOT NULL AUTO_INCREMENT,
  user_id INT NOT NULL,
  token VARCHAR(64) NOT NULL,
  expires_at DATETIME NOT NULL,
  created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (id),
  UNIQUE KEY uniq_token (token),
  KEY idx_user_id (user_id),
  KEY idx_expires_at (expires_at)
);


