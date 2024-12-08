<?php

namespace Tests\Unit;

use App\Jobs\ProcessMessage;
use App\Models\Message;
use Exception;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Queue;
use Tests\TestCase;

class ProcessMessageTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Queue::fake();
    }

    public function test_process_message_creates_database_record()
    {
        // Arrange
        $messageData = [
            'task' => 'test_task',
            'data' => 'test_data'
        ];

        // Act
        $job = new ProcessMessage($messageData);
        $job->handle();

        // Assert
        $this->assertDatabaseHas('messages', [
            'task' => 'test_task',
            'data' => 'test_data',
            'status' => Message::STATUS_COMPLETED
        ]);
    }

    public function test_process_message_handles_failure()
    {
        // Arrange
        $messageData = [
            'task' => 'test_task',
            'data' => 'test_data'
        ];
        $job = new ProcessMessage($messageData);

        // Mock the processMessage method to always throw an exception
        $this->expectException(Exception::class);

        // Act & Assert
        try {
            $job->handle();
        } catch (Exception $e) {
            $this->assertDatabaseHas('messages', [
                'task' => 'test_task',
                'status' => Message::STATUS_FAILED,
                'attempts' => 1
            ]);
            throw $e;
        }
    }

    public function test_message_retry_mechanism()
    {
        // Arrange
        $messageData = [
            'task' => 'test_task',
            'data' => 'test_data'
        ];
        $job = new ProcessMessage($messageData);

        // Assert retry configuration
        $this->assertEquals(3, $job->tries);
        $this->assertEquals([30, 60, 120], $job->backoff);
    }

    public function test_successful_message_marks_as_completed()
    {
        // Arrange
        $messageData = [
            'task' => 'test_task',
            'data' => 'test_data'
        ];

        // Act
        $job = new ProcessMessage($messageData);
        $job->handle();

        // Assert
        $message = Message::where('task', 'test_task')->first();
        $this->assertEquals(Message::STATUS_COMPLETED, $message->status);
        $this->assertEquals(1, $message->attempts);
        $this->assertNull($message->error_message);
    }
}
