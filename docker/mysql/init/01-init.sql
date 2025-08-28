-- Create database if it doesn't exist
CREATE DATABASE IF NOT EXISTS sachs_admin CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;

-- Grant privileges to the application user
GRANT ALL PRIVILEGES ON sachs_admin.* TO 'sachs_admin'@'%';
FLUSH PRIVILEGES; 