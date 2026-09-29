<?php

namespace App\Http\Controllers;

use Kreait\Firebase\Messaging\CloudMessage;
use Kreait\Firebase\Messaging\Notification;
use Kreait\Laravel\Firebase\Facades\Firebase;
use App\Services\SmsMisrService;


class NotificationController extends Controller
{
    public function test()
    {
        $messaging = Firebase::messaging();
        $token = 'dIyjTbhVStiO3JJ2j3tJaJ:APA91bEAdj_xh3WIeMHAs87grGFvibwmZgH4M7oe0HMfOPXGauHwMnVyhg8gZtegnUg6DS3BwLa_7l4KlG3gV_2Ddo1A5iktBie2QBCBBIgQghzy53_eiUI';
       $message = CloudMessage::new()
            ->toToken($token)
            ->withNotification(
                Notification::create(
                    'Testing!',
                    'Firebase Laravel works!'
                )
            )
            ->withData([
                'click_action' => 'FLUTTER_NOTIFICATION_CLICK',
                'custom_key' => 'custom_value'
            ]);

        $result = $messaging->send($message);

        dd($result);
    }
    public function sendSMS(SmsMisrService $sms)
    {
        $result = $sms->send(
            '201022994534',
            'Your order #1234 has been confirmed.'
        );

        return response()->json($result);
    }
    
    public function checkBalance(SmsMisrService $sms)
    {
        return response()->json($sms->checkBalance());
    }
}
