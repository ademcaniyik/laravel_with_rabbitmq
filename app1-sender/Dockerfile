FROM php:8.1-fpm

# Gerekli sistem bağımlılıkları
RUN apt-get update && apt-get install -y \
    git \
    curl \
    libpng-dev \
    libonig-dev \
    libxml2-dev \
    zip \
    unzip

# PHP eklentilerini yükle
RUN docker-php-ext-install pdo_mysql mbstring exif pcntl bcmath gd

# Composer'ı yükle
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Uygulama dizinini ayarla
WORKDIR /var/www/html

# Uygulama dosyalarını kopyala
COPY . .

# Composer bağımlılıklarını yükle
RUN composer install

# Gerekli izinleri ayarla
RUN chown -R www-data:www-data /var/www/html/storage

# Portu aç
EXPOSE 8000

# Laravel sunucusunu başlat
CMD php artisan serve --host=0.0.0.0 --port=8000