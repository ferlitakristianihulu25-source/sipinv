FROM php:8.2-apache

# Install ekstensi mysqli
RUN docker-php-ext-install mysqli pdo pdo_mysql

# Aktifkan mod_rewrite Apache
RUN a2enmod rewrite

# Copy semua file project ke server
COPY . /var/www/html/

# Set permission
RUN chown -R www-data:www-data /var/www/html/

# Expose port
EXPOSE 80