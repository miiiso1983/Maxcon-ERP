-- Laravel Cache Tables Creation Script
-- Fixes: SQLSTATE[42S02]: Base table or view not found: 1146 Table 'cache' doesn't exist

-- Create cache table for Laravel database cache driver
CREATE TABLE IF NOT EXISTS `cache` (
  `key` varchar(255) NOT NULL,
  `value` mediumtext NOT NULL,
  `expiration` int(11) NOT NULL,
  PRIMARY KEY (`key`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Create cache_locks table for Laravel cache locking mechanism
CREATE TABLE IF NOT EXISTS `cache_locks` (
  `key` varchar(255) NOT NULL,
  `owner` varchar(255) NOT NULL,
  `expiration` int(11) NOT NULL,
  PRIMARY KEY (`key`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Verify tables were created
SELECT 'Cache tables created successfully!' as status;
SELECT COUNT(*) as cache_table_exists FROM information_schema.tables 
WHERE table_schema = DATABASE() AND table_name = 'cache';
SELECT COUNT(*) as cache_locks_table_exists FROM information_schema.tables 
WHERE table_schema = DATABASE() AND table_name = 'cache_locks';
