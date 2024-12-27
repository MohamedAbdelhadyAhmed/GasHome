<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;

class MadarSmsService
{
    protected $username;
    protected $password;
    protected $sender;

    public function __construct()
    {
        $this->username = env('MADAR_SMS_USERNAME');
        $this->password = env('MADAR_SMS_PASSWORD');
        $this->sender   = env('MADAR_SMS_SENDER');
    }

    public function send($to, $message)
    {
        $url = "https://www.mdar.com.sa/api/sendsms.php";

        $response = Http::asForm()->post($url, [
            'user'    => $this->username,
            'pass'    => $this->password,
            'to'      => $to,
            'message' => $message,
            'sender'  => $this->sender,
        ]);

        if ($response->successful()) {
            return [
                'status' => true,
                'response' => $response->body(),
            ];
        }

        return [
            'status' => false,
            'response' => $response->body(),
        ];
    }
}
