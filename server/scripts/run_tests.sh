#!/bin/bash

MOODLE_ROOT="/var/www/html"

check_phpunit() {
  if [ ! -f "$MOODLE_ROOT/vendor/bin/phpunit" ]; then
    echo "no phpunit installed installing it now"
    install_phpunit
  fi
  if [ ! -f "$MOODLE_ROOT/vendor/bin/phpunit" ]; then
    echo "installing failed exiting"
    exit 1
  fi
  echo "phpunit found"
}

install_phpunit() {
  CURRENT_PATH="$(pwd)"
  cd "$MOODLE_ROOT"
  composer require --dev phpunit/phpunit
  echo "success installing phpunit"
  cd "$CURRENT_PATH"
}

create_config() {
  if [ ! -f "$MOODLE_ROOT/config.php" ]; then
    echo "no config found creating a new one"
    php "$MOODLE_ROOT/moodle/admin/cli/install.php" \
      --lang=en \
      --wwwroot="http://localhost:80" \
      --dataroot="$MOODLE_ROOT/moodledata" \
      --dbtype="mariadb" \
      --dbhost="moodle-mariadb" \
      --dbuser="moodle" \
      --dbpass="moodlepassword" \
      --dbport=3306 \
      --dbname="moodle" \
      --prefix="mdl_" \
      --non-interactive \
      --agree-license \
      --skip-database \
      --allow-unstable \
      --fullname="Moodle testing thing" \
      --shortname="mtt" \
      --adminpass="hunter2"
  else
    echo "config found skipping creating a new one"
  fi
  if [ -f "$MOODLE_ROOT/config.php" ]; then
    if ! grep -Fxq "\$CFG->phpunit_dataroot = '$(realpath "$MOODLE_ROOT/moodledata/phpunit")';" "$MOODLE_ROOT/config.php"; then
      echo "adding phpunit to config"
      echo "\$CFG->phpunit_prefix = 'phpu_';" >>"$MOODLE_ROOT/config.php"
      echo "\$CFG->phpunit_dataroot = '$(realpath "$MOODLE_ROOT/moodledata/phpunit")';" >>"$MOODLE_ROOT/config.php"
    else
      echo "phpunit found in config skipping modifying it"
    fi
  fi
}

runit() {
  check_phpunit
  create_config

  # Initialize PHPUnit
  if [ -f "$MOODLE_ROOT/admin/tool/phpunit/cli/init.php" ]; then
    php "$MOODLE_ROOT/admin/tool/phpunit/cli/init.php"
  else
    echo "Could not find init.php"
    exit 1
  fi

  CURRENT_PATH="$(pwd)"
  cd "$MOODLE_ROOT"

  # Run PHPUnit tests in the specified directory
  TEST_DIR=${1:-"mod/livequiz/tests/phpunit"}
  for file in $(find "$TEST_DIR" -type f); do
    echo "Running tests in $file"
    vendor/bin/phpunit "$file"
  done

  cd "$CURRENT_PATH"
}

sniffit() {
  if [ ! -d "$MOODLE_ROOT/vendor/moodlehq" ]; then
    echo "no codesniffer installed installing it now"
    CURRENT_PATH="$(pwd)"
    cd "$MOODLE_ROOT"
    composer config --no-plugins allow-plugins.dealerdirect/phpcodesniffer-composer-installer true
    composer require moodlehq/moodle-cs
    cd "$CURRENT_PATH"
  fi
  if [[ ":$PATH:" != *"$MOODLE_ROOT/vendor/bin"* ]]; then
    export PATH="$MOODLE_ROOT/vendor/bin:$PATH"
  fi
  phpcs "$MOODLE_ROOT/mod/livequiz/"
}

fixit() {
  if ! command -v phpcbf &> /dev/null; then
    echo "phpcbf could not be found"
    exit 1
  fi

  echo "Running phpcbf on $MOODLE_ROOT/mod/livequiz/"
  phpcbf "$MOODLE_ROOT/mod/livequiz/"
  if [ $? -eq 0 ]; then
    echo "phpcbf ran successfully"
  else
    echo "phpcbf encountered an error"
  fi

  # Fix deprecated warning by declaring the property
  SNIFF_FILE="$MOODLE_ROOT/vendor/moodlehq/moodle-cs/moodle/Sniffs/ControlStructures/ControlSignatureSniff.php"
  if [ -f "$SNIFF_FILE" ]; then
    if ! grep -q 'public $supportedTokenizers' "$SNIFF_FILE"; then
      sed -i "/class ControlSignatureSniff implements Sniff/a\ \ \ \ public \$supportedTokenizers = ['PHP', 'JS'];" "$SNIFF_FILE"
      echo "Added property declaration to ControlSignatureSniff.php"
    else
      echo "Property declaration already exists in ControlSignatureSniff.php"
    fi
  else
    echo "ControlSignatureSniff.php not found"
  fi
}

beit() {
  CURRENT_PATH="$(pwd)"
  cd "$MOODLE_ROOT"
  if [ ! -f "$MOODLE_ROOT/moodle-browser-config/init.php" ]; then
    git clone https://github.com/andrewnicols/moodle-browser-config
    if [ -d "$MOODLE_ROOT/moodle-browser-config" ] && [ ! -f "$MOODLE_ROOT/moodle-browser-config/init.php" ]; then
      cd "$MOODLE_ROOT/moodle-browser-config"
      git stash
      cd "$MOODLE_ROOT"
    fi
  fi
  cd ../moodledata
  BEHAT_PATH="$(pwd)"
  mkdir behat
  cd "$MOODLE_ROOT"
  if ! grep -Fxq "\$CFG->behat_dataroot = \$CFG->dataroot . '/behat';" "config.php"; then
    sed -i "/^require_once(__DIR__ . '\/lib\/setup.php');/i \$CFG->behat_dataroot = \$CFG->dataroot . '/behat';" config.php
    sed -i "/^require_once(__DIR__ . '\/lib\/setup.php');/i \$CFG->behat_wwwroot = 'http:\/\/127.0.0.1:8000';" config.php
    sed -i "/^require_once(__DIR__ . '\/lib\/setup.php');/i \$CFG->behat_dataroot_parent = \$CFG->dataroot . '\/behat';" config.php
    sed -i "/^require_once(__DIR__ . '\/lib\/setup.php');/i \$CFG->behat_prefix = 'beh_';" config.php
    sed -i "/^require_once(__DIR__ . '\/lib\/setup.php');/i require_once('$MOODLE_ROOT/moodle-browser-config/init.php');" config.php
  else
    echo "phpunit found in config skipping modifying it"
  fi
  php admin/tool/behat/cli/init.php
  if ! netstat -tuln | grep -q ':4444'; then
    selenium-server &
    SELENIUM_PID=$!
  fi

  vendor/bin/behat --config "$BEHAT_PATH/behat/behatrun/behat/behat.yml" --profile=firefox --tags @mod_livequiz

  cd "$CURRENT_PATH"
}

# Run the desired function
"$@"