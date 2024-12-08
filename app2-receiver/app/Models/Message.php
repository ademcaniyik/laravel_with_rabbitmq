<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Message extends Model
{
    use HasFactory;

    protected $fillable = [
        'task',
        'data',
        'attempts',
        'last_attempt_at',
        'status',
        'error_message'
    ];

    protected $casts = [
        'last_attempt_at' => 'datetime'
    ];

    const STATUS_PENDING = 'pending';
    const STATUS_PROCESSING = 'processing';
    const STATUS_COMPLETED = 'completed';
    const STATUS_FAILED = 'failed';

    public function incrementAttempts()
    {
        $this->attempts++;
        $this->last_attempt_at = now();
        $this->save();
    }

    public function markAsFailed(string $error)
    {
        $this->status = self::STATUS_FAILED;
        $this->error_message = $error;
        $this->save();
    }

    public function markAsCompleted()
    {
        $this->status = self::STATUS_COMPLETED;
        $this->save();
    }
}
