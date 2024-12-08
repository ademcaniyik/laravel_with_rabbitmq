# Laravel RabbitMQ Mesajlaşma Sistemi

Laravel ve RabbitMQ ile oluşturulmuş, mesaj kuyrukları üzerinden iletişim kuran iki ayrı uygulamadan oluşan dağıtık mesajlaşma sistemi.

## Sistem Mimarisi

```
┌─────────────┐     ┌──────────┐     ┌─────────────┐
│  App1       │     │          │     │  App2       │
│  (Gönderici)│────▶│ RabbitMQ │────▶│  (Alıcı)   │
│  :8000      │     │  :5672   │     │  :8001      │
└─────────────┘     └──────────┘     └─────────────┘
```

## Proje Yapısı

```
rabbitmq-project/
├── app1-sender/              # Mesaj gönderme uygulaması
│   ├── app/
│   │   ├── Http/
│   │   │   └── Controllers/
│   │   │       └── MessageController.php
│   │   └── ...
│   ├── tests/
│   │   └── Unit/
│   │       └── MessageControllerTest.php
│   └── ...
├── app2-receiver/           # Mesaj işleme uygulaması
│   ├── app/
│   │   ├── Jobs/
│   │   │   └── ProcessMessage.php
│   │   ├── Models/
│   │   │   └── Message.php
│   │   └── ...
│   ├── tests/
│   │   └── Unit/
│   │       └── ProcessMessageTest.php
│   └── ...
└── docker-compose.yml
```

## Başlatmak için

1. **Depoyu klonlayın:**

```bash
git clone <repository-url>
cd rabbitmq-project
```

2. **Ortam Kurulumu:**

```bash
# App1 (Gönderici)
cd app1-sender
cp .env.example .env
# App2 (Alıcı)
cd ../app2-receiver
cp .env.example .env
```

3. **Docker konteynerlerini başlatın:**

```bash
docker-compose up -d
```

4. **Bağımlılıkları yükleyin ve migrasyonları çalıştırın:**

```bash

# App1 (Gönderici)
docker-compose exec app1-sender composer install
docker-compose exec app1-sender php artisan key:generate
docker-compose exec app1-sender php artisan migrate
# App2 (Alıcı)
docker-compose exec app2-receiver composer install
docker-compose exec app2-receiver php artisan key:generate
docker-compose exec app2-receiver php artisan migrate
```

5. **Kuyruk işçisini başlatın:**

```bash
docker-compose exec app2-receiver php artisan queue:work
```

## API Dokümantasyonu

### Mesaj Gönderme Uç Noktası

**POST** `/send-message`
İstek gövdesi:

```json
{
    "task": "process_data",
    "data": "örnek_veri"
}
```

Yanıt:

```json
{
    "status": "success",
    "message": "Mesaj başarıyla kuyruğa gönderildi"
}
```

## Test

Her iki uygulama için birim testlerini çalıştırmak için:

```bash

# App1 (Gönderici)
docker-compose exec app1-sender php artisan test
# App2 (Alıcı)
docker-compose exec app2-receiver php artisan test

```

@author [Ademcan İyik](https://github.com/ademcaniyik)
