<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Queue;

class MessageController extends Controller
{
    public function sendMessage(Request $request)
    {
        $message = [
            'task' => $request->input('task', 'process_data'),
            'data' => $request->input('data', 'sample_data')
        ];

        Queue::connection('rabbitmq')->push('process_message', $message);

        return response()->json([
            'status' => 'success',
            'message' => 'Message sent to queue successfully'
        ]);
    }
}
