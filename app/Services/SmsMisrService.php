<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use App\Models\Verification;
use App\Models\User;
use Illuminate\Support\Facades\Log;
use RuntimeException;

class SmsMisrService
{
    private string $endpoint = 'https://smsmisr.com/api/SMS/';
    private string $balanceEndpoint = 'https://smsmisr.com/api/Balance/';
    
    public function checkBalance(): array
    {
        $response = Http::asForm()
            ->timeout(30)
            ->post($this->balanceEndpoint, [
                'environment' => config('services.sms_misr.environment'),
                'username' => config('services.sms_misr.username'),
                'password' => config('services.sms_misr.password'),
            ]);

        if ($response->failed()) {
            throw new RuntimeException(
                'SMS Misr balance check request failed: ' . $response->body()
            );
        }

        $result = $response->json();

        Log::info('SMS Misr balance check result', [
            'result' => $result,
        ]);

        return $result;
    }

    public function send(
        string $mobile,
        string $message,
        int $language = 2
    ): array {
        $response = Http::asForm()
            ->timeout(30)
            ->post($this->endpoint, [
                'environment' => config('services.sms_misr.environment'),
                'username' => config('services.sms_misr.username'),
                'password' => config('services.sms_misr.password'),
                'sender' => config('services.sms_misr.sender'),
                'mobile' => $mobile,
                'language' => $language,
            'message' => $message,
            ]);

        if ($response->failed()) {
            throw new RuntimeException(
                'SMS Misr request failed: ' . $response->body()
            );
        }

        $result = $response->json();

        if (($result['code'] ?? null) !== '1901') {
            throw new RuntimeException(
                'SMS Misr rejected the message: ' . $response->body()
            );
        }

        return $result;
    }
    
    public function sendPhoneVerification(User $user)
    {
        $code = (string) random_int(100000, 999999);
        // $code = 123456;
    
        $result = $this->send(
            $user->phone,
            "Your OTP is: {$code}"
        );
        
        Log::info('SMS Misr result', [
            'user_id' => $user->id,
            'phone' => $user->phone,
            'result' => $result,
            'code' => $code,
        ]);
        
        Verification::create([
            'user_id' => $user->id,
            'type' => 'phone',
            'target' => $user->phone,
            'code' => $code,
            'expires_at' => now()->addMinutes(10),
        ]);
    
        return $result;
    }
    public function sendLoginOTP(User $user)
    {
        $code = (string) random_int(100000, 999999);
        // $code = 123456;
    
        $result = $this->send(
            $user->phone,
            "Your Login OTP is: {$code}"
        );
        
        Log::info('SMS Misr result', [
            'user_id' => $user->id,
            'phone' => $user->phone,
            'result' => $result,
            'code' => $code,
        ]);
        
        Verification::create([
            'user_id' => $user->id,
            'type' => 'phone',
            'target' => $user->phone,
            'code' => $code,
            'expires_at' => now()->addMinutes(10),
        ]);
    
        return $result;
    }
    public function sendResetPasswordOTP(User $user)
    {
        $code = (string) random_int(100000, 999999);
        // $code = 123456;
    
        $result = $this->send(
            $user->phone,
            "Your OTP to reset your password is: {$code}"
        );
        
        Log::info('SMS Misr result', [
            'user_id' => $user->id,
            'phone' => $user->phone,
            'result' => $result,
            'code' => $code,
        ]);
        
        Verification::create([
            'user_id' => $user->id,
            'type' => 'phone',
            'target' => $user->phone,
            'code' => $code,
            'expires_at' => now()->addMinutes(10),
        ]);
    
        return $result;
    }
}