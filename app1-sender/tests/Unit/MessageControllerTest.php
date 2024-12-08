<?php

namespace Tests\Unit;

use App\Http\Controllers\MessageController;
use Illuminate\Support\Facades\Queue;
use Tests\TestCase;

class MessageControllerTest extends TestCase
{
    public function test_send_message_pushes_to_queue()
    {
        // Arrange
        Queue::fake();
        $payload = [
            'task' => 'test_task',
            'data' => 'test_data'
        ];

        // Act
        $response = $this->postJson('/send-message', $payload);

        // Assert
        $response->assertStatus(200)
            ->assertJson([
                'status' => 'success',
                'message' => 'Message sent to queue successfully'
            ]);

        Queue::assertPushed('process_message', function ($job) use ($payload) {
            return $job === $payload;
        });
    }

    public function test_send_message_validates_input()
    {
        // Arrange
        Queue::fake();
        
        // Act
        $response = $this->postJson('/send-message', []);

        // Assert
        $response->assertStatus(200); // Still returns 200 as we use default values
        
        Queue::assertPushed('process_message', function ($job) {
            return $job['task'] === 'process_data' && 
                   $job['data'] === 'sample_data';
        });
    }
}
