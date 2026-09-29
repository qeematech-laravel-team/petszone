<?php
namespace App\Services;

use Kreait\Laravel\Firebase\Facades\Firebase;
use Kreait\Firebase\Messaging\CloudMessage;
use Kreait\Firebase\Messaging\Notification;

class FirebaseNotificationService
{
    // Send notification to a single user token
    public function sendToToken($token, $title, $body, $data = [])
    {
        $messaging = Firebase::messaging();

        $message = CloudMessage::withTarget('token', $token)
            ->withNotification(Notification::create($title, $body))
            ->withData($data);

        return $messaging->send($message); // Message ID
    }

    // Send notification to multiple tokens
    public function sendToTokens(array $tokens, $title, $body, $data = [])
    {
        $messaging = Firebase::messaging();

        foreach ($tokens as $token) {
            if ($token) {
                $message = CloudMessage::withTarget('token', $token)
                    ->withNotification(Notification::create($title, $body))
                    ->withData($data);

                $messaging->send($message);
            }
        }

        return true;
    }
}
