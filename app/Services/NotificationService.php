<?php

namespace App\Services;
use App\Models\constant\Constant;
use App\Models\User;

class NotificationService 
{
    public function sendNotification($ads , $user_id)
    {

        $SERVER_API_KEY = Constant::SERVER_KEY; 
        $ads_notification_type = Constant::ADS_NOTIFICATION_TYPE; 
        $extraData = [
            'notificationType' => $ads_notification_type,
            'extra' => $ads,
        ];
        $users = User::where('id', '!=', $user_id)->get();
        $tokens = $users->pluck('fcm_token')->unique()->toArray();
        $data = [
            "registration_ids" => $tokens,
            "notification" => [
                "title" => $ads->name,
                "body" => $ads->description,
                "sound" => "default"
            ],
            'data' => $extraData,
        ];

        $dataString = json_encode($data);

        $headers = [
            'Authorization: key=' . $SERVER_API_KEY,
            'Content-Type: application/json',
        ];

        $ch = curl_init();

        curl_setopt($ch, CURLOPT_URL, 'https://fcm.googleapis.com/fcm/send');

        curl_setopt($ch, CURLOPT_POST, true);

        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);

        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);

        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

        curl_setopt($ch, CURLOPT_POSTFIELDS, $dataString);

        $response = curl_exec($ch);

        // Check for errors
        if ($response === false) {
            // $error = curl_error($ch);
        }
        // Close cURL
        curl_close($ch);
    }
    public function sendNotificationToOneUser($ads , $user_id)
    {
        $SERVER_API_KEY = Constant::SERVER_KEY; 
        $ads_notification_type = Constant::ADS_NOTIFICATION_TYPE; 
        $extraData = [
            'notificationType' => $ads_notification_type,
            'extra' => $ads,
        ];
        $users = User::where('id', '!=', $user_id)->get();
        $tokens = $users->pluck('fcm_token')->unique()->toArray();
        $data = [
            "registration_ids" => $tokens,
            "notification" => [
                "title" => $ads->name,
                "body" => $ads->description,
                "sound" => "default"
            ],
            'data' => $extraData,
        ];

        $dataString = json_encode($data);

        $headers = [
            'Authorization: key=' . $SERVER_API_KEY,
            'Content-Type: application/json',
        ];

        $ch = curl_init();

        curl_setopt($ch, CURLOPT_URL, 'https://fcm.googleapis.com/fcm/send');

        curl_setopt($ch, CURLOPT_POST, true);

        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);

        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);

        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

        curl_setopt($ch, CURLOPT_POSTFIELDS, $dataString);

        $response = curl_exec($ch);

        // Check for errors
        if ($response === false) {
            // $error = curl_error($ch);
        }
        // Close cURL
        curl_close($ch);
    }
}