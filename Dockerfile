# B2b klasörünüzün içinde Dockerfile
FROM php:8.2-fpm

# Sistem paketleri ve PHP eklentileri
RUN apt-get update && apt-get install -y \
    git \
    curl \
    libpng-dev \
    libonig-dev \
    libxml2-dev \
    zip \
    unzip \
 && docker-php-ext-install pdo pdo_mysql mbstring exif pcntl bcmath gd

# Redis uzantısını yükle ve etkinleştir
RUN pecl install redis \
 && docker-php-ext-enable redis

# Composer ikilisini ekle
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Çalışma dizini
WORKDIR /var/www

# Uygulama kodlarını kopyala
COPY . /var/www

# Dosya izinlerini ayarla
RUN chown -R www-data:www-data /var/www \
 && chmod -R 755 /var/www

EXPOSE 9000

CMD ["php-fpm"]
