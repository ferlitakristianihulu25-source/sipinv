FROM php:8.2-apache

# Disable semua MPM lalu aktifkan hanya prefork
RUN a2dismod mpm_event mpm_worker 2>/dev/null || true && \
    a2enmod mpm_prefork

# Install ekstensi mysqli
RUN docker-php-ext-install mysqli pdo pdo_mysql

# Aktifkan mod_rewrite Apache
RUN a2enmod rewrite

# Copy semua file project ke server
COPY . /var/www/html/

# Set permission
RUN chown -R www-data:www-data /var/www/html/

# Script untuk menyesuaikan port Railway
CMD sed -i "s/80/${PORT}/g" /etc/apache2/sites-available/000-default.conf /etc/apache2/ports.conf && apache2-foreground