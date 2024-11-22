CREATE DATABASE moodle_test CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;

GRANT ALL PRIVILEGES ON moodle_test.* TO 'moodle'@'%';
FLUSH PRIVILEGES;
