FROM php:8.2-cli

# Install ekstensi mysqli
RUN docker-php-ext-install mysqli pdo pdo_mysql

# Copy semua file project
COPY . /app

# Set working directory
WORKDIR /app

# Jalankan PHP built-in server
CMD php -S 0.0.0.0:$PORT -t /app