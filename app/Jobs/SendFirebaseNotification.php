<?php

namespace App\Jobs;

use App\Services\FirebaseNotificationService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Log;

class SendFirebaseNotification implements ShouldQueue
{
    use Queueable;

    public $token;
    public $title;
    public $body;
    public $data;

    /**
     * Create a new job instance.
     */
    public function __construct($token, $title, $body, $data)
    {
        $this->token = $token;
        $this->title = $title;
        $this->body = $body;
        $this->data = $data;

    }

    /**
     * Execute the job.
     */
    public function handle(FirebaseNotificationService $firebase)
    {
        try {
            $firebase->sendToToken($this->token, $this->title, $this->body, $this->data);
        } catch (\Exception $e) {
            Log::error('Firebase Notification Failed: '.$e->getMessage());
        }
    }
}
