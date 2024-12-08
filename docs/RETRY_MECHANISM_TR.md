# Yeniden Deneme Mekanizması ve Hata İşleme

Bu belge, RabbitMQ mesajlaşma sistemindeki yeniden deneme mekanizması ve hata işleme uygulamasını anlatıyorum.

## Genel Bakış

Yeniden deneme mekanizması, mesaj işlemede geçici hataları ele almak ve güvenilir mesaj iletimi ve işlemeyi sağlamak için tasarladım.

## Uygulama Detayları

### 1. Yeniden Deneme Yapılandırması

```php
class ProcessMessage implements ShouldQueue
{
    public $tries = 3;                    // Maksimum yeniden deneme sayısı
    public $backoff = [30, 60, 120];      // Denemeler arası gecikme (saniye)
}
```

### 2. Mesaj Durumları

Mesajlar şu durumlardan birinde olabilir:

- `pending`: Mesaj alındığında başlangıç durumu
- `processing`: Mesaj işleniyor
- `completed`: Başarıyla işlendi
- `failed`: Tüm yeniden denemelerden sonra başarısız

### 3. Veritabanı Şeması

```php
Schema::create('messages', function (Blueprint $table) {
    $table->id();
    $table->string('task');
    $table->text('data');
    $table->integer('attempts')->default(0);
    $table->timestamp('last_attempt_at')->nullable();
    $table->string('status')->default('pending');
    $table->text('error_message')->nullable();
    $table->timestamps();
}
```

### 4. Hata İşleme Akışı

1. **İlk İşleme**

   ```php
   try {
       // Mesajı işle
   } catch (Exception $e) {
       $this->handleFailure($e);
   }
   ```

2. **Hata İşleme**

   ```php
   protected function handleFailure(Exception $e): void
   {
       $message->markAsFailed($e->getMessage());
       
       if ($this->attempts() < $this->tries) {
           throw $e;  // Yeniden denemeyi tetikle
       }
   }
   ```

3. **Son Başarısız durum için**

   ```php
   public function failed(Exception $e): void
   {
       Log::error('Mesaj işleme kalıcı olarak başarısız oldu', [
           'task' => $this->messageData['task'],
           'error' => $e->getMessage(),
           'attempts' => $this->attempts()
       ]);
   }
   ```

## İzleme ve Hata Ayıklama

### 1. Loglama

Tüm yeniden deneme girişimleri detaylı bilgilerle loglanır:

- Mesaj ID
- Deneme sayısı
- Hata mesajı
- Zaman damgası

### 2. Veritabanı Sorguları

Yeniden deneme durumunu izlemek için:

```sql
-- Başarısız mesajları bulmak için
SELECT * FROM messages WHERE status = 'failed';

-- Yeniden deneme girişimlerini kontrol etmek için
SELECT task, attempts, error_message 
FROM messages 
WHERE attempts > 0;
```

### 3. RabbitMQ Yönetimi

RabbitMQ Yönetim Arayüzü üzerinden izle:

- Başarısız mesaj sayısı
- Kuyruk boyutu
- Mesaj oranları
