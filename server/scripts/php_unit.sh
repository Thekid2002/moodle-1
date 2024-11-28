#!/bin/bash

MOODLE_DIR="/var/www/html"
PHPUNIT_DATAROOT="/var/www/phpunit_dataroot"
PHPUNIT_DBNAME="moodle_test"
PHPUNIT_DBUSER="moodle"
PHPUNIT_DBPASS="moodlepassword"
REQUIRED_PHPUNIT_VERSION="9.5"
TEST_DIR="/var/www/html/mod/livequiz/tests/phpunit"

# Backup the original config.php
cp "$MOODLE_DIR/config.php" "$MOODLE_DIR/config.php.bak"

# Add PHPUnit configuration to config.php
cat <<EOL >> "$MOODLE_DIR/config.php"
\$CFG->phpunit_dataroot = '$PHPUNIT_DATAROOT';
\$CFG->phpunit_dbtype    = 'mariadb';
\$CFG->phpunit_dblibrary = 'native';
\$CFG->phpunit_dbhost    = 'moodle-mariadb';
\$CFG->phpunit_dbname    = '$PHPUNIT_DBNAME';
\$CFG->phpunit_dbuser    = '$PHPUNIT_DBUSER';
\$CFG->phpunit_dbpass    = '$PHPUNIT_DBPASS';
\$CFG->phpunit_prefix    = 'phpu_';
EOL

# Create PHPUnit dataroot
mkdir -p "$PHPUNIT_DATAROOT"
chmod 777 "$PHPUNIT_DATAROOT"

# Install correct PHPUnit version
composer require --dev phpunit/phpunit:^$REQUIRED_PHPUNIT_VERSION

# Initialize PHPUnit
php "$MOODLE_DIR/admin/tool/phpunit/cli/init.php"

# Run PHPUnit tests for each file in the directory
if [ -f "$MOODLE_DIR/vendor/bin/phpunit" ]; then
  for test_file in "$TEST_DIR"/*.php; do
    echo " "
    echo "Running PHPUnit tests for $test_file"
    vendor/bin/phpunit "$test_file"
  done
else
  echo "Error: PHPUnit binary not found. Please check your setup."
fi