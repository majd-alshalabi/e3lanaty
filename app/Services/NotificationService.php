<?php

namespace App\Services;
use App\Models\constant\Constant;
use App\Models\User;
use Illuminate\Support\Facades\Log;

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
        $tokens = $users->filter(function ($user) {
            return $user->fcm_token !== null;
        })->pluck('fcm_token')->unique()->toArray();
        $data = [
            "registration_ids" => $tokens,
            "notification" => [
                "title" => $ads->user->name . " added new ads",
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
    public function sendCommentNotificationToOneUser($comment , $user)
    {
        $SERVER_API_KEY = Constant::SERVER_KEY; 
        $ads_notification_type = Constant::COMMENT_NOTIFICATION_TYPE; 
        $extraData = [
            'notificationType' => $ads_notification_type,
            'extra' => $comment,
        ];
        $tokens = [$user->fcm_token];
        $data = [
            "registration_ids" => $tokens,
            "notification" => [
                "title" => $comment->user->name . " commented on your ads",
                "body" => $comment->comment,
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
    public function sendFeedbackNotificationToOneUser($feedback , $user,$description)
    {
        $SERVER_API_KEY = Constant::SERVER_KEY; 
        $ads_notification_type = Constant::FEED_BACK_NOTIFICATION_TYPE; 
        $extraData = [
            'notificationType' => $ads_notification_type,
            'extra' => $feedback,
        ];
        $tokens = [$user->fcm_token];
        $data = [
            "registration_ids" => $tokens,
            "notification" => [
                "title" => "admin answer your feedback",
                "body" => $description ,
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