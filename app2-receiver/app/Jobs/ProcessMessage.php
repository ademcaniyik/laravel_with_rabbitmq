<?php

namespace App\Jobs;

use App\Models\Message;
use Exception;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class ProcessMessage implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $messageData;
    public $tries = 3;
    public $backoff = [30, 60, 120]; // Retry after 30s, 60s, then 120s

    public function __construct(array $messageData)
    {
        $this->messageData = $messageData;
    }

    public function handle(): void
    {
        try {
            // Create or find the message record
            $message = Message::firstOrCreate(
                ['task' => $this->messageData['task']],
                [
                    'data' => $this->messageData['data'],
                    'status' => Message::STATUS_PENDING
                ]
            );

            // Update status to processing
            $message->status = Message::STATUS_PROCESSING;
            $message->save();

            // Increment attempt counter
            $message->incrementAttempts();

            // Process the message (you can add your custom processing logic here)
            $this->processMessage($message);

            // Mark as completed if successful
            $message->markAsCompleted();

            Log::info('Message processed successfully', [
                'message_id' => $message->id,
                'attempts' => $message->attempts
            ]);
        } catch (Exception $e) {
            $this->handleFailure($e);
        }
    }

    protected function processMessage(Message $message): void
    {
        // Simulate processing with potential failure
        if (rand(1, 10) === 1) { // 10% chance of failure for testing
            throw new Exception('Random processing failure');
        }

        // Add your actual message processing logic here
        // For example:
        // - Parse the message data
        // - Make API calls
        // - Process files
        // - etc.
    }

    protected function handleFailure(Exception $e): void
    {
        $message = Message::where('task', $this->messageData['task'])->first();

        if ($message) {
            $message->markAsFailed($e->getMessage());

            Log::error('Message processing failed', [
                'message_id' => $message->id,
                'attempts' => $message->attempts,
                'error' => $e->getMessage()
            ]);

            // If we haven't exceeded max retries, throw the exception to trigger a retry
            if ($this->attempts() < $this->tries) {
                throw $e;
            }
        }
    }

    public function failed(Exception $e): void
    {
        // Log the final failure
        Log::error('Message processing failed permanently', [
            'task' => $this->messageData['task'],
            'error' => $e->getMessage(),
            'attempts' => $this->attempts()
        ]);
    }
}
