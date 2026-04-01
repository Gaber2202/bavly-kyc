-- Run as MySQL admin (e.g. root): mysql -u root -p < scripts/mysql-init.sql
-- Adjust password and user name to match .env (DB_USERNAME / DB_PASSWORD)

CREATE DATABASE IF NOT EXISTS `bavly_kyc`
  CHARACTER SET utf8mb4
  COLLATE utf8mb4_unicode_ci;

CREATE USER IF NOT EXISTS 'bavly'@'localhost' IDENTIFIED BY 'bavly_local_secret';
GRANT ALL PRIVILEGES ON `bavly_kyc`.* TO 'bavly'@'localhost';
FLUSH PRIVILEGES;
