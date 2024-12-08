# Dağıtım Kılavuzu

Bu kılavuz, Laravel RabbitMQ mesajlaşma sisteminin dağıtım sürecini detaylandırmaktadır.

## Üretime Geçiş Kontrol Listesi

### 1. Ortam Kurulumu

1. **Sistem Gereksinimleri**
   - PHP 8.2+
   - Composer
   - Docker & Docker Compose
   - Loglar ve kuyruk depolaması için yeterli disk alanı

2. **Ortam Değişkenleri**

   ```bash
   # Üretim ayarları
   APP_ENV=production
   APP_DEBUG=false
   
   # Kuyruk ayarları
   QUEUE_CONNECTION=rabbitmq
   RABBITMQ_HOST=rabbitmq
   RABBITMQ_PORT=5672
   
   # Loglama
   LOG_CHANNEL=stack
   LOG_LEVEL=error
   ```

### 2. Güvenlik Önlemleri

1. **RabbitMQ Güvenliği**

   ```bash
   # Özel kullanıcı oluşturma
   rabbitmqctl add_user myapp mypassword
   rabbitmqctl set_permissions -p / myapp ".*" ".*" ".*"
   
   # Varsayılan kullanıcıyı devre dışı bırakma
   rabbitmqctl delete_user guest
   ```

2. **Laravel Güvenliği**
   - Güvenli APP_KEY ayarlama
   - HTTPS etkinleştirme
   - Güvenlik başlıkları yapılandırma

### 3. Performans Optimizasyonu

1. **Laravel Optimizasyonu**

   ```bash
   # Autoloader optimizasyonu
   composer install --optimize-autoloader --no-dev
   
   # Önbellek optimizasyonu
   php artisan config:cache
   php artisan view:cache
   ```

2. **Kuyruk Worker Yapılandırması**

   ```bash
   # Supervisor yapılandırması
   [program:laravel-worker]
   process_name=%(program_name)s_%(process_num)02d
   command=php /path/to/artisan queue:work --tries=3
   autostart=true
   autorestart=true
   numprocs=8
   redirect_stderr=true
   stdout_logfile=/path/to/worker.log
   ```

## Dağıtım Adımları

### 1. Sistem Gereksinimleri

```bash
# Gerekli paketleri yükle
sudo apt-get update
sudo apt-get install docker docker-compose
```

### 2. Uygulama Dağıtımı

```bash
# Uygulamayı kopyala
git clone <repo-url>
cd <proje-dizini>

# Docker imajlarını oluştur
docker-compose build

# Servisleri başlat
docker-compose up -d

# Migrasyonları çalıştır
docker-compose exec app1-sender php artisan migrate --force
docker-compose exec app2-receiver php artisan migrate --force
```

### 3. Worker Süreci Kurulumu

1. **Supervisor Kurulumu**

   ```bash
   sudo apt-get install supervisor
   ```

2. **Workerleri Yapılandırma**

   ```bash
   sudo nano /etc/supervisor/conf.d/laravel-worker.conf
   sudo supervisorctl reread
   sudo supervisorctl update
   sudo supervisorctl start laravel-worker:*
   ```

### 1. Sağlık Kontrolleri

1. **Kuyruk Sağlığı**

   ```bash
   # Kuyruk durumunu kontrol et
   rabbitmqctl list_queues name messages_ready messages_unacknowledged
   ```

2. **Worker Sağlığı**

   ```bash
   # Workerdurumunu kontrol et
   supervisorctl status
   ```
