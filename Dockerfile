# Use the official PHP image with Apache
FROM php:8.2.8-apache

# Set working directory to Laravel root
WORKDIR /var/www/html

# Install system dependencies
RUN apt-get update && \
    DEBIAN_FRONTEND=noninteractive apt-get install -y --no-install-recommends \
    libpng-dev \
    libjpeg-dev \
    libfreetype6-dev \
    libwebp-dev \
    default-mysql-client \
    zip \
    unzip \
    libzip-dev \
    libssl-dev \
    git  \
    vim \
    iputils-ping \
    curl \
    sudo \
    net-tools && \
    rm -rf /var/lib/apt/lists/*

# Explicitly set PHP memory limit and enable FTP extension in php.ini
RUN sed -i 's/memory_limit = .*/memory_limit = 256M/' /usr/local/etc/php/php.ini-development && \
    sed -i 's/memory_limit = .*/memory_limit = 256M/' /usr/local/etc/php/php.ini-production && \
    sed -i 's/upload_max_filesize = .*/upload_max_filesize = 128M/' /usr/local/etc/php/php.ini-development && \
    sed -i 's/upload_max_filesize = .*/upload_max_filesize = 128M/' /usr/local/etc/php/php.ini-production && \
    sed -i 's/post_max_size = .*/post_max_size = 128M/' /usr/local/etc/php/php.ini-development && \
    sed -i 's/post_max_size = .*/post_max_size = 128M/' /usr/local/etc/php/php.ini-production && \
    cp /usr/local/etc/php/php.ini-production /usr/local/etc/php/php.ini && \
    echo "extension=ftp" >> /usr/local/etc/php/php.ini

# Configure GD with additional support
RUN docker-php-ext-configure gd --with-freetype --with-jpeg --with-webp

# Install PHP extensions
RUN docker-php-ext-install pdo pdo_mysql gd zip ftp

# Install Composer globally
RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer

# Modify the default Apache port from 80 to 8080
RUN sed -i 's/80/8080/g' /etc/apache2/sites-available/000-default.conf /etc/apache2/ports.conf

# Modify the default Apache configuration to point to Laravel's public directory
RUN sed -i 's|DocumentRoot /var/www/html|DocumentRoot /var/www/html/public|' /etc/apache2/sites-available/000-default.conf

# Enable mod_rewrite for Apache
RUN a2enmod rewrite

# Copy the existing Laravel project into the container
COPY . /var/www/html/

# Replace current project files with ADFS files
RUN mv /var/www/html/ADFS/session.php /var/www/html/config && \
    mv /var/www/html/ADFS/AuthController.php /var/www/html/app/Http/Controllers

# Delete ADFS directory
RUN rm -rf /var/www/html/ADFS

# Workaround to allow composer install as a superUser
ENV COMPOSER_ALLOW_SUPERUSER=1

# Run Composer install for Laravel project dependencies
RUN composer install --ignore-platform-reqs

# Grant permissions for Laravel's storage and bootstrap/cache directories
RUN chown -R www-data:www-data storage bootstrap/cache

# Additionally, set the permissions for the directories
RUN chmod -R 777 storage bootstrap/cache

# Add the storage:link command
RUN php artisan storage:link

# Create a script to run Laravel Scheduler
RUN echo '#!/bin/bash\n\
    # Start Apache in the foreground\n\
    apache2-foreground &\n\
    # Start the scheduler in a separate process\n\
    (\n\
    while [ true ]; do\n\
    php /var/www/html/artisan schedule:run --verbose --no-interaction &\n\
    sleep 60\n\
    done\n\
    ) &\n\
    # Monitor the scheduler process\n\
    SCHEDULER_PID=$!\n\
    while [ -e /proc/$SCHEDULER_PID ]; do\n\
    # Optionally, add any monitoring or logging code here\n\
    sleep 5\n\
    done\n\
    fi' > /usr/local/bin/start-scheduler

# Execute the command to clear Laravel log file
RUN echo "" > storage/logs/laravel.log

# Make the script executable
RUN chmod +x /usr/local/bin/start-scheduler

# Add a health check instruction
HEALTHCHECK --interval=5m --timeout=3s \
    CMD curl -f http://localhost:8080/ || exit 1

# Expose port 8080
EXPOSE 8080

# Add www-data to sudoers
RUN echo 'www-data ALL=(ALL) NOPASSWD:ALL' >> /etc/sudoers

# Set password for www-data
RUN echo 'www-data:admin' | chpasswd

# Switch to user www-data for subsequent commands and container runtime
USER www-data

# Start script or Apache in the foreground based on the container role
CMD ["/usr/local/bin/start-scheduler"]
