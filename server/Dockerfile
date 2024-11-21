FROM --platform=linux/arm64 php:8.1-apache

# Set the ServerName to suppress the warning
RUN echo "ServerName localhost" >> /etc/apache2/apache2.conf

# Set working directory in the container
WORKDIR /var/www/html

# Install necessary PHP extensions and dependencies
RUN apt-get update && apt-get install -y \
    libzip-dev \
    zip \
    libpcre2-dev \
    libonig-dev \
    libpng-dev \
    libjpeg-dev \
    libfreetype6-dev \
    libssl-dev \
    libsodium-dev \
    default-mysql-client \
    libpq-dev \
    libicu-dev \
    && docker-php-ext-install \
    zip \
    pdo \
    pdo_mysql \
    mysqli \
    sodium \
    pgsql \
    pdo_pgsql \
    && docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install gd \
    && docker-php-ext-install intl \
    && apt-get clean \
    && rm -rf /var/lib/apt/lists/*

COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Copy the PHP project into the container
COPY ./moodle /var/www/html
COPY ./moodledata /var/www/moodledata

# Set file ownership and permissions
RUN chown -R www-data:www-data /var/www/html \
    && chmod -R 755 /var/www/html \
    && chown www-data:www-data /var/www \
    && chmod 755 /var/www \
    && chown -R www-data:www-data /var/www/moodledata \
    && chmod -R 755 /var/www/moodledata

# Check and install PHPUnit
RUN if [ ! -f /var/www/html/vendor/bin/phpunit ]; then \
      echo "no phpunit installed installing it now"; \
      composer require --dev phpunit/phpunit; \
      if [ ! -f /var/www/html/vendor/bin/phpunit ]; then \
        echo "installing failed exiting"; \
        exit 1; \
      fi; \
      echo "success installing phpunit"; \
    else \
      echo "phpunit found"; \
    fi

# Allow the dealerdirect/phpcodesniffer-composer-installer plugin
RUN composer global config --no-plugins allow-plugins.dealerdirect/phpcodesniffer-composer-installer true

# Install PHP CodeSniffer and Moodle coding standards
RUN composer global require "squizlabs/php_codesniffer=*"
RUN composer global require "moodlehq/moodle-cs"

ENV PATH="/root/.composer/vendor/bin:${PATH}"

# Expose port 80
EXPOSE 80

# Start Apache
CMD ["apache2-foreground"]